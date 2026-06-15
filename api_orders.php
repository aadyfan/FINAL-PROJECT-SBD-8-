<?php
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
require_once 'db_connect.php';

function productImageUrl(string $pid): string {
    $safe = preg_replace('/[^a-zA-Z0-9_-]/', '', $pid);
    $local = __DIR__ . '/assets/product-images/' . $safe . '.svg';
    if (is_file($local)) return 'assets/product-images/' . $safe . '.svg';
    return 'api_product_image.php?product_id=' . rawurlencode($safe);
}


function orderItems(PDO $pdo, array $orderIds): array {
    if (!$orderIds) return [];
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $stmt = $pdo->prepare("\n        SELECT od.order_id, od.detail_id, od.user_id, od.variant_id, od.quantity, od.unit_price, od.subtotal,\n               pv.product_id, pv.color, pv.ram, pv.storage, p.product_name, b.brand_name\n        FROM order_details od\n        JOIN product_variants pv ON od.variant_id=pv.variant_id\n        JOIN products p ON pv.product_id=p.product_id\n        JOIN brands b ON p.brand_id=b.brand_id\n        WHERE od.order_id IN ($placeholders)\n        ORDER BY od.detail_id ASC\n    ");
    $stmt->execute($orderIds);
    $items = [];
    foreach ($stmt->fetchAll() as $r) {
        $r['order_id'] = (int)$r['order_id'];
        $r['detail_id'] = (int)$r['detail_id'];
        $r['user_id'] = (int)$r['user_id'];
        $r['variant_id'] = (int)$r['variant_id'];
        $r['quantity'] = (int)$r['quantity'];
        $r['unit_price'] = (float)$r['unit_price'];
        $r['subtotal'] = (float)$r['subtotal'];
        $r['image_url'] = productImageUrl($r['product_id']);
        $items[$r['order_id']][] = $r;
    }
    return $items;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $adminId = (int)($_GET['admin_id'] ?? 0);
        $userId = (int)($_GET['user_id'] ?? 0);
        if ($adminId > 0) {
            $stmtAdmin = $pdo->prepare("SELECT user_id FROM users WHERE user_id=? AND role='admin' LIMIT 1");
            $stmtAdmin->execute([$adminId]);
            if (!$stmtAdmin->fetch()) throw new Exception('Akses admin tidak valid.');
            $stmt = $pdo->query("\n                SELECT DISTINCT o.order_id, o.order_date, o.total_amount, o.shipping_address, o.status, o.notes,\n                    COALESCE(p.method,'-') payment_method, COALESCE(p.status,'-') payment_status,\n                    COALESCE(u.full_name,'-') customer_name, COALESCE(u.email,'-') customer_email,\n                    COALESCE(pl.provider_name,'') paylater_provider,\n                    COALESCE(pl.installment_month,0) paylater_tenor,\n                    COALESCE(pl.approval_code,'') paylater_code\n                FROM orders o\n                JOIN order_details od ON o.order_id = od.order_id\n                JOIN users u ON od.user_id = u.user_id\n                LEFT JOIN payments p ON o.order_id = p.order_id\n                LEFT JOIN paylater_payments pl ON p.payment_id = pl.payment_id\n                ORDER BY o.order_date DESC\n                LIMIT 120\n            ");
            $orders = $stmt->fetchAll();
        } else {
            if ($userId <= 0) throw new Exception('user_id wajib dikirim.');
            $stmt = $pdo->prepare("\n                SELECT DISTINCT o.order_id, o.order_date, o.total_amount, o.shipping_address, o.status, o.notes,\n                    COALESCE(p.method,'-') payment_method, COALESCE(p.status,'-') payment_status,\n                    COALESCE(u.full_name,'-') customer_name, COALESCE(u.email,'-') customer_email,\n                    COALESCE(pl.provider_name,'') paylater_provider,\n                    COALESCE(pl.installment_month,0) paylater_tenor,\n                    COALESCE(pl.approval_code,'') paylater_code\n                FROM orders o\n                JOIN order_details od ON o.order_id = od.order_id\n                JOIN users u ON od.user_id = u.user_id\n                LEFT JOIN payments p ON o.order_id = p.order_id\n                LEFT JOIN paylater_payments pl ON p.payment_id = pl.payment_id\n                WHERE od.user_id = ?\n                ORDER BY o.order_date DESC\n            ");
            $stmt->execute([$userId]);
            $orders = $stmt->fetchAll();
        }
        $ids = [];
        foreach ($orders as &$o) { $o['order_id']=(int)$o['order_id']; $o['total_amount']=(float)$o['total_amount']; $ids[]=$o['order_id']; }
        unset($o);
        $items = orderItems($pdo, $ids);
        foreach ($orders as &$o) { $o['items'] = $items[$o['order_id']] ?? []; }
        unset($o);
        echo json_encode(['success'=>true,'data'=>['orders'=>$orders]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!$payload) throw new Exception('Payload tidak valid.');
        $adminId = (int)($payload['admin_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id=? AND role='admin' LIMIT 1");
        $stmt->execute([$adminId]);
        if (!$stmt->fetch()) throw new Exception('Akses admin tidak valid.');
        $orderId = (int)($payload['order_id'] ?? 0);
        $status = trim($payload['status'] ?? '');
        if ($orderId <= 0 || !in_array($status, ['pending','confirmed','shipped','delivered','cancelled'], true)) throw new Exception('order_id/status tidak valid.');
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('UPDATE orders SET status=? WHERE order_id=?');
        $stmt->execute([$status, $orderId]);

        if ($status === 'delivered') {
            $stmtPay = $pdo->prepare("SELECT payment_id, method, status FROM payments WHERE order_id=? LIMIT 1 FOR UPDATE");
            $stmtPay->execute([$orderId]);
            $pay = $stmtPay->fetch();
            if ($pay && $pay['method'] === 'cod' && $pay['status'] === 'pending') {
                $pdo->prepare("UPDATE payments SET status='success', paid_at=NOW(), transaction_ref=? WHERE payment_id=?")
                    ->execute(['COD-PAID-' . $orderId, (int)$pay['payment_id']]);
                $pdo->prepare("UPDATE cod_payments SET cod_status='paid_to_courier', delivery_note='Pembayaran COD otomatis lunas saat order delivered.' WHERE payment_id=?")
                    ->execute([(int)$pay['payment_id']]);
            }
        }

        if ($status === 'cancelled') {
            $stmtPay = $pdo->prepare("SELECT payment_id, method, status FROM payments WHERE order_id=? LIMIT 1 FOR UPDATE");
            $stmtPay->execute([$orderId]);
            $pay = $stmtPay->fetch();
            if ($pay && $pay['status'] === 'pending') {
                $pdo->prepare("UPDATE payments SET status='failed', notes=CONCAT(COALESCE(notes,''), '
Order dibatalkan admin.') WHERE payment_id=?")
                    ->execute([(int)$pay['payment_id']]);
                if ($pay['method'] === 'cod') {
                    $pdo->prepare("UPDATE cod_payments SET cod_status='failed_collection', delivery_note='Order dibatalkan admin sebelum pembayaran COD.' WHERE payment_id=?")
                        ->execute([(int)$pay['payment_id']]);
                }
            }
        }

        $pdo->prepare('INSERT INTO activity_log(user_id, activity) VALUES(?, ?)')->execute([$adminId, "Admin mengubah status order {$orderId} menjadi {$status}"]);
        $pdo->commit();
        echo json_encode(['success'=>true,'message'=>'Status order berhasil diperbarui.'], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
    }
    http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method tidak diizinkan.']);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500); echo json_encode(['success'=>false,'message'=>$e->getMessage()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}
?>
