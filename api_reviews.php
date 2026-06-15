<?php
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
require_once 'db_connect.php';

function fetchReviews(PDO $pdo): array {
    $stmt = $pdo->query("SELECT r.review_id, r.product_id, r.user_id, COALESCE(u.full_name, 'Customer') AS full_name, r.rating, r.comment, r.review_date FROM reviews r LEFT JOIN users u ON r.user_id = u.user_id ORDER BY r.review_date DESC, r.review_id DESC");
    $rows = $stmt->fetchAll();
    foreach ($rows as &$r) { $r['review_id']=(int)$r['review_id']; $r['user_id']=(int)$r['user_id']; $r['rating']=(int)$r['rating']; }
    return $rows;
}
function customerCanReview(PDO $pdo, string $productId, int $userId): bool {
    if ($productId === '' || $userId <= 0) return false;
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM orders o
        INNER JOIN order_details od ON od.order_id = o.order_id
        INNER JOIN product_variants pv ON pv.variant_id = od.variant_id
        LEFT JOIN payments pay ON pay.order_id = o.order_id
        WHERE od.user_id = ?
          AND pv.product_id = ?
          AND o.status = 'delivered'
          AND (pay.status = 'success' OR pay.payment_id IS NULL)
    "
    );
    $stmt->execute([$userId, $productId]);
    return ((int)$stmt->fetchColumn()) > 0;
}


try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $pid = trim($_GET['product_id'] ?? '');
        $canReview = false;
        $userId = (int)($_GET['user_id'] ?? 0);
        if ($pid !== '') {
            $stmt = $pdo->prepare("SELECT r.review_id, r.product_id, r.user_id, COALESCE(u.full_name, 'Customer') AS full_name, r.rating, r.comment, r.review_date FROM reviews r LEFT JOIN users u ON r.user_id = u.user_id WHERE r.product_id = ? ORDER BY r.review_date DESC, r.review_id DESC");
            $stmt->execute([$pid]);
            $rows = $stmt->fetchAll();
            foreach ($rows as &$r) { $r['review_id']=(int)$r['review_id']; $r['user_id']=(int)$r['user_id']; $r['rating']=(int)$r['rating']; }
            $canReview = customerCanReview($pdo, $pid, $userId);
        } else {
            $rows = fetchReviews($pdo);
        }
        echo json_encode(['success'=>true,'data'=>['reviews'=>$rows,'can_review'=>$canReview]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Gunakan GET atau POST']); exit; }
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!$payload) throw new Exception('Payload review tidak valid.');

    $productId = trim($payload['product_id'] ?? '');
    $userId = (int)($payload['user_id'] ?? 0);
    $rating = (int)($payload['rating'] ?? 0);
    $comment = trim($payload['comment'] ?? '');

    if ($productId === '') throw new Exception('Produk tidak valid.');
    if ($userId <= 0) throw new Exception('User tidak valid. Login sebagai pelanggan terlebih dahulu.');
    if ($rating < 1 || $rating > 5) throw new Exception('Rating harus antara 1 sampai 5.');
    if (mb_strlen($comment) < 5) throw new Exception('Komentar review minimal 5 karakter.');
    if (!customerCanReview($pdo, $productId, $userId)) {
        throw new Exception('Review hanya bisa dikirim setelah pesanan produk ini selesai dan diterima.');
    }

    $pdo->beginTransaction();
    $check = $pdo->prepare("SELECT review_id FROM reviews WHERE product_id = ? AND user_id = ? LIMIT 1");
    $check->execute([$productId, $userId]);
    $existing = $check->fetchColumn();
    if ($existing) {
        $stmt = $pdo->prepare("UPDATE reviews SET rating = ?, comment = ?, review_date = NOW() WHERE review_id = ?");
        $stmt->execute([$rating, $comment, $existing]);
        $message = 'Review berhasil diperbarui.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$productId, $userId, $rating, $comment]);
        $message = 'Review berhasil ditambahkan.';
    }
    try {
        $log = $pdo->prepare("INSERT INTO activity_log (user_id, activity, activity_time) VALUES (?, ?, NOW())");
        $log->execute([$userId, 'Menulis review produk ' . $productId . ' dengan rating ' . $rating . ' bintang']);
    } catch (Throwable $ignore) {}
    $pdo->commit();

    echo json_encode(['success'=>true,'message'=>$message,'data'=>['reviews'=>fetchReviews($pdo)]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}
?>
