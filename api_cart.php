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


function cartRows(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare("\n        SELECT\n            c.cart_id, c.user_id, c.variant_id, c.quantity,\n            pv.product_id, pv.color, pv.ram, pv.storage, pv.price, pv.stock,\n            p.product_name, p.release_year, p.base_price,\n            b.brand_name, c2.category_name\n        FROM cart c\n        JOIN product_variants pv ON c.variant_id = pv.variant_id\n        JOIN products p ON pv.product_id = p.product_id\n        JOIN brands b ON p.brand_id = b.brand_id\n        JOIN categories c2 ON p.category_id = c2.category_id\n        WHERE c.user_id = ?\n        ORDER BY c.cart_id DESC\n    ");
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$r) {
        $r['cart_id'] = (int)$r['cart_id'];
        $r['user_id'] = (int)$r['user_id'];
        $r['variant_id'] = (int)$r['variant_id'];
        $r['quantity'] = (int)$r['quantity'];
        $r['price'] = (float)$r['price'];
        $r['stock'] = (int)$r['stock'];
        $r['subtotal'] = (float)$r['price'] * (int)$r['quantity'];
        $r['image_url'] = productImageUrl($r['product_id']);
    }
    unset($r);
    return $rows;
}

function ensureUser(PDO $pdo, int $userId): void {
    if ($userId <= 0) throw new Exception('Login customer dulu. user_id tidak valid.');
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id=? AND role='customer' LIMIT 1");
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) throw new Exception('User customer tidak ditemukan.');
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $userId = (int)($_GET['user_id'] ?? 0);
        ensureUser($pdo, $userId);
        echo json_encode(['success'=>true, 'data'=>['cart'=>cartRows($pdo, $userId)]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method tidak diizinkan.']); exit; }
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!$payload) throw new Exception('Payload cart tidak valid.');
    $action = $payload['action'] ?? 'add';
    $userId = (int)($payload['user_id'] ?? 0);
    ensureUser($pdo, $userId);

    if ($action === 'add') {
        $variantId = (int)($payload['variant_id'] ?? 0);
        $quantity = max(1, (int)($payload['quantity'] ?? 1));
        if ($variantId <= 0) throw new Exception('variant_id wajib valid.');
        $stmt = $pdo->prepare('SELECT stock FROM product_variants WHERE variant_id=? LIMIT 1');
        $stmt->execute([$variantId]);
        $stock = $stmt->fetchColumn();
        if ($stock === false) throw new Exception('Varian produk tidak ditemukan.');
        if ((int)$stock < 1) throw new Exception('Stok produk habis.');
        $stmt = $pdo->prepare('SELECT cart_id, quantity FROM cart WHERE user_id=? AND variant_id=? LIMIT 1');
        $stmt->execute([$userId, $variantId]);
        $existing = $stmt->fetch();
        if ($existing) {
            $newQty = min((int)$stock, (int)$existing['quantity'] + $quantity);
            $stmt = $pdo->prepare('UPDATE cart SET quantity=? WHERE cart_id=?');
            $stmt->execute([$newQty, (int)$existing['cart_id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO cart(user_id, variant_id, quantity) VALUES(?,?,?)');
            $stmt->execute([$userId, $variantId, min((int)$stock, $quantity)]);
        }
        echo json_encode(['success'=>true,'message'=>'Produk masuk keranjang.', 'data'=>['cart'=>cartRows($pdo, $userId)]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
    }

    if ($action === 'update') {
        $cartId = (int)($payload['cart_id'] ?? 0);
        $quantity = max(1, (int)($payload['quantity'] ?? 1));
        if ($cartId <= 0) throw new Exception('cart_id wajib valid.');
        $stmt = $pdo->prepare('SELECT c.cart_id, pv.stock FROM cart c JOIN product_variants pv ON c.variant_id=pv.variant_id WHERE c.cart_id=? AND c.user_id=? LIMIT 1');
        $stmt->execute([$cartId, $userId]);
        $row = $stmt->fetch();
        if (!$row) throw new Exception('Item keranjang tidak ditemukan.');
        $stmt = $pdo->prepare('UPDATE cart SET quantity=? WHERE cart_id=? AND user_id=?');
        $stmt->execute([min((int)$row['stock'], $quantity), $cartId, $userId]);
        echo json_encode(['success'=>true,'message'=>'Jumlah keranjang diperbarui.', 'data'=>['cart'=>cartRows($pdo, $userId)]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
    }

    if ($action === 'remove') {
        $cartId = (int)($payload['cart_id'] ?? 0);
        if ($cartId <= 0) throw new Exception('cart_id wajib valid.');
        $stmt = $pdo->prepare('DELETE FROM cart WHERE cart_id=? AND user_id=?');
        $stmt->execute([$cartId, $userId]);
        echo json_encode(['success'=>true,'message'=>'Item dihapus dari keranjang.', 'data'=>['cart'=>cartRows($pdo, $userId)]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
    }

    if ($action === 'clear') {
        $stmt = $pdo->prepare('DELETE FROM cart WHERE user_id=?');
        $stmt->execute([$userId]);
        echo json_encode(['success'=>true,'message'=>'Keranjang dikosongkan.', 'data'=>['cart'=>[]]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
    }

    throw new Exception('Action cart tidak dikenal.');
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}
?>
