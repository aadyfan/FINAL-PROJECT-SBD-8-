<?php
$pid = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['product_id'] ?? $_GET['id'] ?? 'new');
require_once 'db_connect.php';
$name = 'Smartphone';
$brand = 'ephone';
$category = 'Produk';
try {
    $stmt = $pdo->prepare("SELECT p.product_name, b.brand_name, c.category_name FROM products p JOIN brands b ON p.brand_id=b.brand_id JOIN categories c ON p.category_id=c.category_id WHERE p.product_id=? LIMIT 1");
    $stmt->execute([$pid]);
    if ($row = $stmt->fetch()) {
        $name = $row['product_name'] ?: $name;
        $brand = $row['brand_name'] ?: $brand;
        $category = $row['category_name'] ?: $category;
    }
} catch (Throwable $e) {}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$brandKey = strtolower($brand);
$palettes = [
    'apple' => ['#0f172a','#e2e8f0','#ffffff','#111827'],
    'samsung' => ['#1d4ed8','#dbeafe','#ffffff','#172554'],
    'xiaomi' => ['#f97316','#ffedd5','#ffffff','#7c2d12'],
    'vivo' => ['#6366f1','#eef2ff','#ffffff','#312e81'],
    'oppo' => ['#059669','#d1fae5','#ffffff','#064e3b'],
];
$colors = $palettes[$brandKey] ?? ['#0f172a','#e2e8f0','#ffffff','#111827'];
[$primary,$soft,$screen,$dark] = $colors;
$seed = abs(crc32($pid . $name));
$cameraCount = 2 + ($seed % 3);
$phoneW = 250 + ($seed % 30);
$phoneH = 500 + ($seed % 34);
$x = 450 - ($phoneW / 2);
$y = 92;
$rx = 42 + ($seed % 14);
$cam = '';
for ($i=0; $i<$cameraCount; $i++) {
    $cx = $x + 52 + (($i % 2) * 54);
    $cy = $y + 62 + (floor($i / 2) * 54);
    $cam .= '<circle cx="'.$cx.'" cy="'.$cy.'" r="18" fill="'.$dark.'"/><circle cx="'.$cx.'" cy="'.$cy.'" r="9" fill="#64748b"/>';
}
$shortName = function_exists('mb_substr') ? mb_substr($name, 0, 32, 'UTF-8') : substr($name, 0, 32);
header('Content-Type: image/svg+xml; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
echo '<svg xmlns="http://www.w3.org/2000/svg" width="900" height="760" viewBox="0 0 900 760">'
    .'<defs><radialGradient id="bg" cx="50%" cy="30%" r="72%"><stop offset="0%" stop-color="#ffffff"/><stop offset="45%" stop-color="'.$soft.'"/><stop offset="100%" stop-color="#f8fafc"/></radialGradient><linearGradient id="body" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="'.$primary.'"/><stop offset="100%" stop-color="'.$dark.'"/></linearGradient><filter id="shadow"><feDropShadow dx="0" dy="24" stdDeviation="22" flood-color="#0f172a" flood-opacity="0.20"/></filter></defs>'
    .'<rect width="900" height="760" fill="url(#bg)"/>'
    .'<circle cx="718" cy="145" r="92" fill="'.$primary.'" opacity="0.12"/><circle cx="178" cy="615" r="120" fill="'.$primary.'" opacity="0.10"/>'
    .'<g filter="url(#shadow)"><rect x="'.h($x).'" y="'.h($y).'" width="'.h($phoneW).'" height="'.h($phoneH).'" rx="'.h($rx).'" fill="url(#body)"/><rect x="'.h($x+18).'" y="'.h($y+18).'" width="'.h($phoneW-36).'" height="'.h($phoneH-36).'" rx="'.h($rx-12).'" fill="'.$screen.'" opacity="0.95"/><rect x="'.h($x+38).'" y="'.h($y+112).'" width="'.h($phoneW-76).'" height="'.h($phoneH-190).'" rx="28" fill="'.$soft.'"/><rect x="'.h($x+55).'" y="'.h($y+134).'" width="'.h($phoneW-110).'" height="14" rx="7" fill="'.$primary.'" opacity="0.28"/><rect x="'.h($x+55).'" y="'.h($y+166).'" width="'.h($phoneW-135).'" height="14" rx="7" fill="'.$primary.'" opacity="0.20"/>'.$cam.'<circle cx="450" cy="'.h($y+$phoneH-42).'" r="8" fill="'.$primary.'" opacity="0.75"/></g>'
    .'<text x="450" y="680" text-anchor="middle" font-family="Arial, sans-serif" font-size="38" font-weight="900" fill="#0f172a">'.h($brand).'</text>'
    .'<text x="450" y="718" text-anchor="middle" font-family="Arial, sans-serif" font-size="24" font-weight="700" fill="#64748b">'.h($shortName).' • '.h($category).'</text>'
    .'</svg>';
