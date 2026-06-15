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


function ensureUser(PDO $pdo, int $userId): void {
    if ($userId <= 0) throw new Exception('Login customer dulu. user_id tidak valid.');
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id=? AND role='customer' LIMIT 1");
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) throw new Exception('User customer tidak ditemukan.');
}

function wishlistRows(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare("\n        SELECT\n            w.wishlist_id, w.user_id, w.variant_id, w.added_at,\n            pv.product_id, pv.color, pv.ram, pv.storage, pv.price, pv.stock,\n            p.product_name, p.release_year, p.base_price,\n            b.brand_name, c.category_name\n        FROM wishlist w\n        JOIN product_variants pv ON w.variant_id = pv.variant_id\n        JOIN products p ON pv.product_id = p.product_id\n        JOIN brands b ON p.brand_id = b.brand_id\n        JOIN categories c ON p.category_id = c.category_id\n        WHERE w.user_id = ?\n        ORDER BY w.added_at DESC, w.wishlist_id DESC\n    ");
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$r) {
        $r['wishlist_id'] = (int)$r['wishlist_id'];
        $r['user_id'] = (int)$r['user_id'];
        $r['variant_id'] = (int)$r['variant_id'];
        $r['price'] = (float)$r['price'];
        $r['stock'] = (int)$r['stock'];
        $r['image_url'] = productImageUrl($r['product_id']);
    }
    unset($r);
    return $rows;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $userId = (int)($_GET['user_id'] ?? 0);
        ensureUser($pdo, $userId);
        echo json_encode(['success'=>true, 'data'=>['wishlist'=>wishlistRows($pdo, $userId)]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method tidak diizinkan.']); exit; }
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!$payload) throw new Exception('Payload wishlist tidak valid.');
    $action = $payload['action'] ?? 'toggle';
    $userId = (int)($payload['user_id'] ?? 0);
    ensureUser($pdo, $userId);

    if ($action === 'toggle') {
        $variantId = (int)($payload['variant_id'] ?? 0);
        if ($variantId <= 0) throw new Exception('variant_id wajib valid.');
        $stmt = $pdo->prepare('SELECT wishlist_id FROM wishlist WHERE user_id=? AND variant_id=? LIMIT 1');
        $stmt->execute([$userId, $variantId]);
        $existing = $stmt->fetchColumn();
        if ($existing) {
            $stmt = $pdo->prepare('DELETE FROM wishlist WHERE wishlist_id=? AND user_id=?');
            $stmt->execute([(int)$existing, $userId]);
            $message = 'Produk dihapus dari wishlist.';
        } else {
            $stmt = $pdo->prepare('SELECT variant_id FROM product_variants WHERE variant_id=? LIMIT 1');
            $stmt->execute([$variantId]);
            if (!$stmt->fetch()) throw new Exception('Varian produk tidak ditemukan.');
            $stmt = $pdo->prepare('INSERT INTO wishlist(user_id, variant_id) VALUES(?, ?)');
            $stmt->execute([$userId, $variantId]);
            $message = 'Produk disimpan ke wishlist.';
        }
        echo json_encode(['success'=>true,'message'=>$message,'data'=>['wishlist'=>wishlistRows($pdo, $userId)]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
    }

    if ($action === 'remove') {
        $wishlistId = (int)($payload['wishlist_id'] ?? 0);
        if ($wishlistId <= 0) throw new Exception('wishlist_id wajib valid.');
        $stmt = $pdo->prepare('DELETE FROM wishlist WHERE wishlist_id=? AND user_id=?');
        $stmt->execute([$wishlistId, $userId]);
        echo json_encode(['success'=>true,'message'=>'Item wishlist dihapus.','data'=>['wishlist'=>wishlistRows($pdo, $userId)]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
    }

    if ($action === 'clear') {
        $stmt = $pdo->prepare('DELETE FROM wishlist WHERE user_id=?');
        $stmt->execute([$userId]);
        echo json_encode(['success'=>true,'message'=>'Wishlist dikosongkan.','data'=>['wishlist'=>[]]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
    }

    throw new Exception('Action wishlist tidak dikenal.');
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}
?>
