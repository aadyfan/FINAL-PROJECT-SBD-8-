<?php
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Gunakan POST']); exit; }
require_once 'db_connect.php';

function responseOrder(PDO $pdo, int $orderId): array {
    $stmt = $pdo->prepare("\n        SELECT o.order_id, o.order_date, o.total_amount, o.shipping_address, o.status, o.notes,\n               COALESCE(p.payment_id,0) payment_id, COALESCE(p.method,'-') payment_method, COALESCE(p.status,'-') payment_status, p.paid_at,\n               COALESCE(u.full_name,'-') customer_name, COALESCE(u.email,'-') customer_email\n        FROM orders o\n        JOIN order_details od ON o.order_id = od.order_id\n        JOIN users u ON od.user_id = u.user_id\n        LEFT JOIN payments p ON o.order_id = p.order_id\n        WHERE o.order_id = ?\n        LIMIT 1\n    ");
    $stmt->execute([$orderId]);
    $row = $stmt->fetch();
    return $row ?: [];
}

try {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!$payload) throw new Exception('Payload JSON tidak valid.');

    $orderId = (int)($payload['order_id'] ?? 0);
    $userId  = (int)($payload['user_id'] ?? 0);
    $adminId = (int)($payload['admin_id'] ?? 0);
    if ($orderId <= 0) throw new Exception('order_id wajib dikirim.');
    if ($userId <= 0 && $adminId <= 0) throw new Exception('user_id atau admin_id wajib dikirim.');

    if ($adminId > 0) {
        $stmtAdmin = $pdo->prepare("SELECT user_id FROM users WHERE user_id=? AND role='admin' LIMIT 1");
        $stmtAdmin->execute([$adminId]);
        if (!$stmtAdmin->fetch()) throw new Exception('Akses admin tidak valid.');
    } else {
        $stmtOwner = $pdo->prepare("SELECT 1 FROM order_details WHERE order_id=? AND user_id=? LIMIT 1");
        $stmtOwner->execute([$orderId, $userId]);
        if (!$stmtOwner->fetch()) throw new Exception('Order tidak ditemukan untuk user ini.');
    }

    $pdo->beginTransaction();
    $stmt = $pdo->prepare("\n        SELECT o.order_id, o.status order_status, p.payment_id, p.method, p.status payment_status\n        FROM orders o\n        JOIN payments p ON o.order_id = p.order_id\n        WHERE o.order_id = ?\n        LIMIT 1\n        FOR UPDATE\n    ");
    $stmt->execute([$orderId]);
    $row = $stmt->fetch();
    if (!$row) throw new Exception('Data pembayaran order tidak ditemukan.');

    $method = $row['method'];
    $orderStatus = $row['order_status'];
    $paymentStatus = $row['payment_status'];
    $paymentId = (int)$row['payment_id'];
    $actorId = $adminId > 0 ? $adminId : $userId;
    $message = 'Status pembayaran sudah terbaru.';

    if ($orderStatus === 'cancelled') {
        if ($paymentStatus === 'pending') {
            $pdo->prepare("UPDATE payments SET status='failed', notes=CONCAT(COALESCE(notes,''), '\nDibatalkan oleh sistem karena order cancelled.') WHERE payment_id=?")
                ->execute([$paymentId]);
            if ($method === 'cod') {
                $pdo->prepare("UPDATE cod_payments SET cod_status='failed_collection', delivery_note='Order dibatalkan sebelum pembayaran COD.' WHERE payment_id=?")
                    ->execute([$paymentId]);
            }
        }
        $message = 'Order sudah dibatalkan, pembayaran tidak dapat diproses.';
    } elseif ($paymentStatus === 'success') {
        $message = 'Pembayaran sudah dikonfirmasi sebelumnya.';
    } elseif ($paymentStatus === 'failed') {
        $message = 'Pembayaran sebelumnya gagal. Buat order ulang atau hubungi admin.';
    } elseif ($method === 'cod') {
        if ($orderStatus === 'delivered') {
            $pdo->prepare("UPDATE payments SET status='success', paid_at=NOW(), transaction_ref=? WHERE payment_id=?")
                ->execute(['COD-PAID-' . $orderId, $paymentId]);
            $pdo->prepare("UPDATE cod_payments SET cod_status='paid_to_courier', delivery_note='Pembayaran COD diterima saat pesanan delivered.' WHERE payment_id=?")
                ->execute([$paymentId]);
            $message = 'Pembayaran COD berhasil dikonfirmasi karena pesanan sudah diterima.';
        } else {
            if ($orderStatus === 'pending') {
                $pdo->prepare("UPDATE orders SET status='confirmed' WHERE order_id=?")->execute([$orderId]);
            }
            $message = 'COD dikonfirmasi. Pembayaran tetap pending sampai admin mengubah status pesanan menjadi selesai.';
        }
    } else {
        $pdo->prepare("UPDATE payments SET status='success', paid_at=NOW(), transaction_ref=? WHERE payment_id=?")
            ->execute([strtoupper($method) . '-SIM-' . $orderId . '-' . time(), $paymentId]);
        if (in_array($orderStatus, ['pending'], true)) {
            $pdo->prepare("UPDATE orders SET status='confirmed' WHERE order_id=?")->execute([$orderId]);
        }
        $message = 'Pembayaran berhasil dikonfirmasi otomatis.';
    }

    $pdo->prepare('INSERT INTO activity_log(user_id, activity) VALUES(?, ?)')->execute([$actorId, "Cek status pembayaran order {$orderId}: {$message}"]);
    $pdo->commit();

    echo json_encode(['success'=>true,'message'=>$message,'data'=>['order'=>responseOrder($pdo, $orderId)]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Gagal cek status pembayaran: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}
?>
