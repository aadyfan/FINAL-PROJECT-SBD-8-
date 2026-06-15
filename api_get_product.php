<?php
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Gunakan GET']); exit; }
require_once 'db_connect.php';

function productImageUrl(string $pid): string {
    $safe = preg_replace('/[^a-zA-Z0-9_-]/', '', $pid);
    $local = __DIR__ . '/assets/product-images/' . $safe . '.svg';
    if (is_file($local)) return 'assets/product-images/' . $safe . '.svg';
    return 'api_product_image.php?product_id=' . rawurlencode($safe);
}

try {
    $products = $pdo->query("\n        SELECT\n            p.product_id, p.product_id AS id, p.product_name, p.product_name AS name,\n            b.brand_name, b.brand_name AS brand,\n            c.category_name, c.category_name AS category,\n            p.release_year, p.base_price,\n            COALESCE(MIN(pv.price), p.base_price) AS min_price,\n            COALESCE(MAX(pv.price), p.base_price) AS max_price,\n            COALESCE(MIN(pv.price), p.base_price) AS price,\n            COALESCE(SUM(pv.stock), 0) AS stock_total,\n            COUNT(pv.variant_id) AS variant_count,\n            COALESCE(ROUND(AVG(r.rating),1),0) AS rating,\n            COUNT(DISTINCT r.review_id) AS review_count\n        FROM products p\n        JOIN brands b ON p.brand_id = b.brand_id\n        JOIN categories c ON p.category_id = c.category_id\n        LEFT JOIN product_variants pv ON p.product_id = pv.product_id\n        LEFT JOIN reviews r ON p.product_id = r.product_id\n        GROUP BY p.product_id, p.product_name, b.brand_name, c.category_name, p.release_year, p.base_price\n        ORDER BY b.brand_name ASC, p.release_year DESC, p.base_price DESC\n    ")->fetchAll();
    foreach ($products as &$p) {
        $p['base_price'] = (float)$p['base_price'];
        $p['min_price'] = (float)$p['min_price'];
        $p['max_price'] = (float)$p['max_price'];
        $p['price'] = (float)$p['price'];
        $p['stock_total'] = (int)$p['stock_total'];
        $p['variant_count'] = (int)$p['variant_count'];
        $p['rating'] = (float)$p['rating'];
        $p['review_count'] = (int)$p['review_count'];
        $p['image_url'] = productImageUrl($p['product_id']);
        $p['tagline'] = $p['brand'] . ' smartphone • ' . $p['category'];
    }
    unset($p);

    $specs = $pdo->query("SELECT product_id, model_number, display_inch, chipset, battery, camera FROM product_specs ORDER BY product_id")->fetchAll();
    $variants = $pdo->query("SELECT variant_id, product_id, color, ram, storage, price, stock FROM product_variants ORDER BY product_id, price ASC, variant_id ASC")->fetchAll();
    foreach ($variants as &$v) { $v['variant_id']=(int)$v['variant_id']; $v['price']=(float)$v['price']; $v['stock']=(int)$v['stock']; }
    unset($v);

    $brands = $pdo->query("SELECT brand_id, brand_name, country, founded, website FROM brands ORDER BY brand_name")->fetchAll();
    $categories = $pdo->query("SELECT category_id, category_name, description, min_price, max_price FROM categories ORDER BY min_price")->fetchAll();
    foreach ($categories as &$c) { $c['min_price']=(float)$c['min_price']; $c['max_price']=(float)$c['max_price']; }
    unset($c);

    $reviews = $pdo->query("
        SELECT r.review_id, r.product_id, r.user_id, COALESCE(u.full_name, 'Customer') AS full_name,
               r.rating, r.comment, r.review_date
        FROM reviews r
        LEFT JOIN users u ON r.user_id = u.user_id
        ORDER BY r.review_date DESC, r.review_id DESC
    ")->fetchAll();
    foreach ($reviews as &$rv) { $rv['review_id']=(int)$rv['review_id']; $rv['user_id']=(int)$rv['user_id']; $rv['rating']=(int)$rv['rating']; }
    unset($rv);

    echo json_encode(['success'=>true,'data'=>['products'=>$products,'specs'=>$specs,'variants'=>$variants,'reviews'=>$reviews,'brands'=>$brands,'categories'=>$categories]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Gagal mengambil data produk.','error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}
?>
