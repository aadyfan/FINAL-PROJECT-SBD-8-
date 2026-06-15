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

function ensureAdmin(PDO $pdo, int $adminId): void {
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id=? AND role='admin' LIMIT 1");
    $stmt->execute([$adminId]);
    if (!$stmt->fetch()) throw new Exception('Akses admin tidak valid. Login admin dulu.');
}

function nextProductId(PDO $pdo): string {
    $n = (int)$pdo->query("SELECT COALESCE(MAX(CAST(SUBSTRING(product_id,2) AS UNSIGNED)),0)+1 FROM products")->fetchColumn();
    return 'p' . str_pad((string)$n, 3, '0', STR_PAD_LEFT);
}

function fetchVariant(PDO $pdo, int $variantId): array {
    $stmt = $pdo->prepare("SELECT variant_id, product_id, color, ram, storage, price, stock FROM product_variants WHERE variant_id=? LIMIT 1");
    $stmt->execute([$variantId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) throw new Exception('Varian tidak ditemukan. Pilih varian yang valid.');
    $row['variant_id'] = (int)$row['variant_id'];
    $row['price'] = (float)$row['price'];
    $row['stock'] = (int)$row['stock'];
    return $row;
}

function fetchFirstVariantByProduct(PDO $pdo, string $productId): array {
    $stmt = $pdo->prepare("SELECT variant_id FROM product_variants WHERE product_id=? ORDER BY variant_id ASC LIMIT 1");
    $stmt->execute([$productId]);
    $variantId = $stmt->fetchColumn();
    if (!$variantId) throw new Exception('Produk ini belum memiliki varian.');
    return fetchVariant($pdo, (int)$variantId);
}

try {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!$payload) throw new Exception('Payload admin tidak valid.');
    $action = $payload['action'] ?? '';
    $adminId = (int)($payload['admin_id'] ?? 0);
    ensureAdmin($pdo, $adminId);

    if ($action === 'adjust_stock' || $action === 'set_stock') {
        $variantId = (int)($payload['variant_id'] ?? 0);
        if ($variantId <= 0) throw new Exception('variant_id wajib valid.');
        fetchVariant($pdo, $variantId); // pastikan varian ada sebelum update

        if ($action === 'adjust_stock') {
            $delta = (int)($payload['delta'] ?? 0);
            if ($delta === 0) throw new Exception('Perubahan stok tidak boleh 0.');
            $stmt = $pdo->prepare("UPDATE product_variants SET stock = GREATEST(stock + ?, 0) WHERE variant_id = ?");
            $stmt->execute([$delta, $variantId]);
        } else {
            if (!array_key_exists('stock', $payload)) throw new Exception('Nilai stok wajib dikirim.');
            $stock = max(0, (int)$payload['stock']);
            $stmt = $pdo->prepare("UPDATE product_variants SET stock = ? WHERE variant_id = ?");
            $stmt->execute([$stock, $variantId]);
        }

        $variant = fetchVariant($pdo, $variantId);
        $log = $pdo->prepare("INSERT INTO activity_log(user_id, activity) VALUES(?, ?)");
        $log->execute([$adminId, "Admin update stok variant_id {$variantId} menjadi {$variant['stock']}"]);
        echo json_encode(['success'=>true,'message'=>'Stok berhasil diperbarui.', 'data'=>$variant], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
    }

    if ($action === 'update_variant') {
        $variantId = (int)($payload['variant_id'] ?? 0);
        if ($variantId <= 0 && !empty($payload['product_id'])) {
            $variant = fetchFirstVariantByProduct($pdo, trim($payload['product_id']));
            $variantId = (int)$variant['variant_id'];
        }
        if ($variantId <= 0) throw new Exception('Varian wajib dipilih.');
        fetchVariant($pdo, $variantId);

        $color = trim($payload['color'] ?? 'Black');
        $ram = trim($payload['ram'] ?? '8GB');
        $storage = trim($payload['storage'] ?? '128GB');
        $price = (float)($payload['price'] ?? 0);
        $stock = max(0, (int)($payload['stock'] ?? 0));
        if ($color === '' || $ram === '' || $storage === '') throw new Exception('Warna, RAM, dan storage wajib diisi.');
        if ($price <= 0) throw new Exception('Harga varian wajib lebih dari 0.');

        $stmt = $pdo->prepare('UPDATE product_variants SET color=?, ram=?, storage=?, price=?, stock=? WHERE variant_id=?');
        $stmt->execute([$color, $ram, $storage, $price, $stock, $variantId]);
        $variant = fetchVariant($pdo, $variantId);
        $pdo->prepare('INSERT INTO activity_log(user_id, activity) VALUES(?, ?)')->execute([$adminId, "Admin update varian {$variantId}, stok {$variant['stock']}"]);
        echo json_encode(['success'=>true,'message'=>'Varian dan stok berhasil diperbarui.', 'data'=>$variant], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
    }

    if ($action === 'add_variant') {
        $pid = trim($payload['product_id'] ?? '');
        $color = trim($payload['color'] ?? 'Black');
        $ram = trim($payload['ram'] ?? '8GB');
        $storage = trim($payload['storage'] ?? '128GB');
        $price = (float)($payload['price'] ?? 0);
        $stock = max(0, (int)($payload['stock'] ?? 0));
        if ($pid === '') throw new Exception('Produk wajib dipilih.');
        if ($color === '' || $ram === '' || $storage === '') throw new Exception('Warna, RAM, dan storage wajib diisi.');
        if ($price <= 0) throw new Exception('Harga varian wajib lebih dari 0.');
        $stmt = $pdo->prepare('SELECT product_id FROM products WHERE product_id=? LIMIT 1');
        $stmt->execute([$pid]);
        if (!$stmt->fetch()) throw new Exception('Produk tidak ditemukan.');
        $stmt = $pdo->prepare('INSERT INTO product_variants(product_id, color, ram, storage, price, stock) VALUES(?,?,?,?,?,?)');
        $stmt->execute([$pid, $color, $ram, $storage, $price, $stock]);
        $variantId = (int)$pdo->lastInsertId();
        $variant = fetchVariant($pdo, $variantId);
        $pdo->prepare('INSERT INTO activity_log(user_id, activity) VALUES(?, ?)')->execute([$adminId, "Admin menambah varian {$variantId} untuk produk {$pid}"]);
        echo json_encode(['success'=>true,'message'=>'Varian baru berhasil ditambahkan.', 'data'=>$variant], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
    }

    if ($action === 'add_product') {
        $name = trim($payload['product_name'] ?? '');
        $brand = trim($payload['brand'] ?? '');
        $category = trim($payload['category'] ?? '');
        $year = (int)($payload['release_year'] ?? date('Y'));
        $basePrice = (float)($payload['base_price'] ?? 0);
        $color = trim($payload['color'] ?? 'Black');
        $ram = trim($payload['ram'] ?? '8GB');
        $storage = trim($payload['storage'] ?? '128GB');
        $price = (float)($payload['price'] ?? $basePrice);
        $stock = max(0, (int)($payload['stock'] ?? 0));
        $display = trim($payload['display_inch'] ?? '6.5 inch AMOLED');
        $chipset = trim($payload['chipset'] ?? 'Octa-core');
        $battery = trim($payload['battery'] ?? '5000 mAh');
        $camera = trim($payload['camera'] ?? '50MP');
        if ($name==='' || $brand==='' || $category==='') throw new Exception('Nama produk, brand, dan kategori wajib diisi.');
        if ($basePrice <= 0 || $price <= 0) throw new Exception('Harga wajib lebih dari 0.');
        $stmt = $pdo->prepare('SELECT brand_id FROM brands WHERE brand_name=? LIMIT 1'); $stmt->execute([$brand]); $brandId = $stmt->fetchColumn();
        if (!$brandId) throw new Exception('Brand tidak ditemukan.');
        $stmt = $pdo->prepare('SELECT category_id FROM categories WHERE category_name=? LIMIT 1'); $stmt->execute([$category]); $catId = $stmt->fetchColumn();
        if (!$catId) throw new Exception('Kategori tidak ditemukan.');
        $pdo->beginTransaction();
        $pid = nextProductId($pdo);
        $stmt = $pdo->prepare('INSERT INTO products(product_id, brand_id, category_id, product_name, release_year, base_price) VALUES(?,?,?,?,?,?)');
        $stmt->execute([$pid, $brandId, $catId, $name, $year, $basePrice]);
        $stmt = $pdo->prepare('INSERT INTO product_specs(product_id, model_number, display_inch, chipset, battery, camera) VALUES(?,?,?,?,?,?)');
        $stmt->execute([$pid, strtoupper($pid), $display, $chipset, $battery, $camera]);
        $stmt = $pdo->prepare('INSERT INTO product_variants(product_id, color, ram, storage, price, stock) VALUES(?,?,?,?,?,?)');
        $stmt->execute([$pid, $color, $ram, $storage, $price, $stock]);
        $variantId = (int)$pdo->lastInsertId();
        $stmt = $pdo->prepare('INSERT INTO activity_log(user_id, activity) VALUES(?, ?)');
        $stmt->execute([$adminId, "Admin menambah produk {$name} ({$pid}) dengan stok {$stock}"]);
        $pdo->commit();
        echo json_encode(['success'=>true,'message'=>'Produk baru berhasil ditambahkan.', 'data'=>['product_id'=>$pid,'variant_id'=>$variantId,'stock'=>$stock]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
    }

    if ($action === 'update_product') {
        $pid = trim($payload['product_id'] ?? '');
        $name = trim($payload['product_name'] ?? '');
        $brand = trim($payload['brand'] ?? '');
        $category = trim($payload['category'] ?? '');
        $year = (int)($payload['release_year'] ?? date('Y'));
        $basePrice = (float)($payload['base_price'] ?? 0);
        if ($pid==='' || $name==='' || $brand==='' || $category==='') throw new Exception('Data produk belum lengkap.');
        if ($basePrice <= 0) throw new Exception('Harga dasar wajib lebih dari 0.');
        $stmt = $pdo->prepare('SELECT brand_id FROM brands WHERE brand_name=? LIMIT 1'); $stmt->execute([$brand]); $brandId = $stmt->fetchColumn();
        $stmt = $pdo->prepare('SELECT category_id FROM categories WHERE category_name=? LIMIT 1'); $stmt->execute([$category]); $catId = $stmt->fetchColumn();
        if (!$brandId || !$catId) throw new Exception('Brand/kategori tidak valid.');
        $stmt = $pdo->prepare('UPDATE products SET brand_id=?, category_id=?, product_name=?, release_year=?, base_price=? WHERE product_id=?');
        $stmt->execute([$brandId, $catId, $name, $year, $basePrice, $pid]);
        $log = $pdo->prepare('INSERT INTO activity_log(user_id, activity) VALUES(?, ?)');
        $log->execute([$adminId, "Admin update produk {$pid}"]);
        echo json_encode(['success'=>true,'message'=>'Produk berhasil diperbarui.'], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
    }

    if ($action === 'delete_product') {
        $pid = trim($payload['product_id'] ?? '');
        if ($pid === '') throw new Exception('product_id wajib dikirim.');
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM order_details od JOIN product_variants pv ON od.variant_id=pv.variant_id WHERE pv.product_id=?');
        $stmt->execute([$pid]);
        if ((int)$stmt->fetchColumn() > 0) throw new Exception('Produk tidak bisa dihapus karena sudah punya transaksi. Gunakan update stok/produk saja.');
        $pdo->beginTransaction();
        $pdo->prepare('DELETE w FROM wishlist w JOIN product_variants pv ON w.variant_id=pv.variant_id WHERE pv.product_id=?')->execute([$pid]);
        $pdo->prepare('DELETE c FROM cart c JOIN product_variants pv ON c.variant_id=pv.variant_id WHERE pv.product_id=?')->execute([$pid]);
        $pdo->prepare('DELETE FROM reviews WHERE product_id=?')->execute([$pid]);
        $pdo->prepare('DELETE FROM product_variants WHERE product_id=?')->execute([$pid]);
        $pdo->prepare('DELETE FROM product_specs WHERE product_id=?')->execute([$pid]);
        $pdo->prepare('DELETE FROM products WHERE product_id=?')->execute([$pid]);
        $pdo->prepare('INSERT INTO activity_log(user_id, activity) VALUES(?, ?)')->execute([$adminId, "Admin menghapus produk {$pid}"]);
        $pdo->commit();
        echo json_encode(['success'=>true,'message'=>'Produk berhasil dihapus.'], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
    }

    throw new Exception('Action admin tidak dikenal.');
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}
?>
