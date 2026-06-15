<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Gunakan POST']); exit; }
require_once 'db_connect.php';
try {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!$payload) throw new Exception('Payload JSON kosong atau tidak valid.');
    $cartItems = $payload['cart_items'] ?? [];
    if (!is_array($cartItems) || count($cartItems) < 1) throw new Exception('Keranjang kosong.');
    $customer = $payload['customer'] ?? [];
    $userId = (int)($customer['user_id'] ?? $payload['user_id'] ?? 1);
    $shippingAddress = trim($customer['address'] ?? $payload['shipping_address'] ?? 'Alamat Kosong');
    $customerName = trim($customer['name'] ?? 'Customer Ephone');
    $customerPhone = trim($customer['phone'] ?? '-');
    if ($shippingAddress === '') throw new Exception('Alamat pengiriman wajib diisi.');
    $stmtUser = $pdo->prepare('SELECT user_id FROM users WHERE user_id=? LIMIT 1');
    $stmtUser->execute([$userId]);
    if (!$stmtUser->fetch()) throw new Exception('User checkout tidak ditemukan. Login customer dulu.');

    $methodMap = ['transfer_bank'=>'transfer','credit_card'=>'credit_card','ewallet'=>'ewallet','cod'=>'cod','paylater'=>'paylater','transfer'=>'transfer'];
    $rawPaymentMethod = $payload['payment_method'] ?? 'transfer_bank';
    $paymentMethod = $methodMap[$rawPaymentMethod] ?? 'transfer';

    $pdo->beginTransaction();
    $calculatedTotal = 0;
    $normalized = [];
    foreach ($cartItems as $item) {
        $variantId = (int)($item['variant_id'] ?? 0);
        $qty = max(1, (int)($item['quantity'] ?? 1));
        $stmt = $pdo->prepare('SELECT price, stock FROM product_variants WHERE variant_id=? LIMIT 1');
        $stmt->execute([$variantId]);
        $v = $stmt->fetch();
        if (!$v) throw new Exception("Varian ID {$variantId} tidak ditemukan.");
        if ((int)$v['stock'] < $qty) throw new Exception("Stok tidak cukup untuk Varian ID {$variantId}.");
        $unit = (float)$v['price'];
        $calculatedTotal += $unit * $qty;
        $normalized[] = ['variant_id'=>$variantId,'quantity'=>$qty,'unit_price'=>$unit];
    }
    $shippingCost = (float)($payload['shipping_cost'] ?? 150000);
    $totalAmount = $calculatedTotal + $shippingCost;

    $stmt = $pdo->prepare("INSERT INTO orders(order_date, total_amount, shipping_address, status, notes) VALUES(NOW(), ?, ?, 'pending', ?)");
    $stmt->execute([$totalAmount, $shippingAddress, 'Checkout dari UI ephone']);
    $orderId = (int)$pdo->lastInsertId();

    $stmtDetail = $pdo->prepare('INSERT INTO order_details(order_id, user_id, variant_id, quantity, unit_price) VALUES(?,?,?,?,?)');
    foreach ($normalized as $item) {
        $stmtDetail->execute([$orderId, $userId, $item['variant_id'], $item['quantity'], $item['unit_price']]);
        // Stok dikurangi oleh trigger MySQL trg_reduce_stock. Jangan update stok manual di sini.
    }

    $stmtPay = $pdo->prepare("INSERT INTO payments(order_id, payment_date, amount, payment_type, method, status, expired_at) VALUES(?, NOW(), ?, 'full', ?, 'pending', DATE_ADD(NOW(), INTERVAL 24 HOUR))");
    $stmtPay->execute([$orderId, $totalAmount, $paymentMethod]);
    $paymentId = (int)$pdo->lastInsertId();
    if ($paymentMethod === 'cod') {
        $stmtCod = $pdo->prepare("INSERT INTO cod_payments(payment_id, receiver_name, receiver_phone, cod_address, cod_status) VALUES(?, ?, ?, ?, 'waiting_delivery')");
        $stmtCod->execute([$paymentId, $customerName, $customerPhone, $shippingAddress]);
    }
    if ($paymentMethod === 'transfer') {
        $stmtTf = $pdo->prepare("INSERT INTO transfer_payments(payment_id, bank_name, virtual_account_number, account_name) VALUES(?, ?, ?, ?)");
        $stmtTf->execute([$paymentId, $payload['transfer_bank'] ?? 'BCA', '8808' . str_pad((string)$orderId, 8, '0', STR_PAD_LEFT), 'EPhone Official']);
    }
    if ($paymentMethod === 'ewallet') {
        $stmtEw = $pdo->prepare("INSERT INTO ewallet_payments(payment_id, ewallet_name, phone_number, checkout_token) VALUES(?, ?, ?, ?)");
        $stmtEw->execute([$paymentId, $payload['ewallet_provider'] ?? 'GoPay', $customerPhone, 'EW-' . $orderId . '-' . time()]);
    }
    if ($paymentMethod === 'paylater') {
        $stmtPl = $pdo->prepare("INSERT INTO paylater_payments(payment_id, provider_name, account_email, installment_month, approval_code) VALUES(?, ?, ?, ?, ?)");
        $stmtPl->execute([$paymentId, $payload['paylater_provider'] ?? 'Kredivo', $customer['email'] ?? null, (int)($payload['paylater_tenor'] ?? 3), 'PL-' . $orderId]);
    }
    if ($paymentMethod === 'credit_card') {
        $last4 = substr(preg_replace('/\D/', '', (string)($payload['card_number'] ?? '0000')), -4) ?: '0000';
        $stmtCc = $pdo->prepare("INSERT INTO credit_card_payments(payment_id, card_holder_name, card_brand, card_last4, bank_issuer, auth_code) VALUES(?, ?, ?, ?, ?, ?)");
        $stmtCc->execute([$paymentId, $payload['card_name'] ?? $customerName, 'Visa/Mastercard', $last4, 'Issuer Bank', 'AUTH-' . $orderId]);
    }
    $stmtClear = $pdo->prepare('DELETE FROM cart WHERE user_id = ?');
    $stmtClear->execute([$userId]);
    $pdo->prepare('INSERT INTO activity_log(user_id, activity) VALUES(?, ?)')->execute([$userId, "Checkout order {$orderId} dari website ephone"]);
    $pdo->commit();
    echo json_encode(['success'=>true,'message'=>'Pesanan berhasil dibuat.', 'data'=>['order_id'=>$orderId, 'order_code'=>'ORD-' . $orderId, 'total_amount'=>$totalAmount]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Gagal checkout: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}
?>
