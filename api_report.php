<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Gunakan GET']); exit; }
require_once 'db_connect.php';
try {
    $overview = [
        'total_products'=>(int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
        'total_variants'=>(int)$pdo->query('SELECT COUNT(*) FROM product_variants')->fetchColumn(),
        'total_stock'=>(int)$pdo->query('SELECT COALESCE(SUM(stock),0) FROM product_variants')->fetchColumn(),
        'low_stock'=>(int)$pdo->query('SELECT COUNT(*) FROM product_variants WHERE stock < 10')->fetchColumn(),
        'total_orders'=>(int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
        'gross_revenue'=>(float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status <> 'cancelled'")->fetchColumn(),
        'total_customers'=>(int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn(),
    ];
    $ordersByStatus = $pdo->query('SELECT status, COUNT(*) total FROM orders GROUP BY status ORDER BY total DESC')->fetchAll();
    foreach ($ordersByStatus as &$r) $r['total']=(int)$r['total']; unset($r);
    $paymentsByMethod = $pdo->query('SELECT method, COUNT(*) total, COALESCE(SUM(amount),0) amount FROM payments GROUP BY method ORDER BY amount DESC')->fetchAll();
    foreach ($paymentsByMethod as &$r) { $r['total']=(int)$r['total']; $r['amount']=(float)$r['amount']; } unset($r);
    $bestSellers = $pdo->query("SELECT p.product_name, b.brand_name, SUM(od.quantity) qty, SUM(od.subtotal) revenue FROM order_details od JOIN product_variants pv ON od.variant_id=pv.variant_id JOIN products p ON pv.product_id=p.product_id JOIN brands b ON p.brand_id=b.brand_id GROUP BY p.product_id,p.product_name,b.brand_name ORDER BY qty DESC, revenue DESC LIMIT 10")->fetchAll();
    foreach ($bestSellers as &$r) { $r['qty']=(int)$r['qty']; $r['revenue']=(float)$r['revenue']; } unset($r);
    $lowStock = $pdo->query("SELECT pv.variant_id, p.product_name, b.brand_name, pv.color, pv.ram, pv.storage, pv.stock FROM product_variants pv JOIN products p ON pv.product_id=p.product_id JOIN brands b ON p.brand_id=b.brand_id WHERE pv.stock < 10 ORDER BY pv.stock ASC, p.product_name ASC LIMIT 30")->fetchAll();
    foreach ($lowStock as &$r) { $r['variant_id']=(int)$r['variant_id']; $r['stock']=(int)$r['stock']; } unset($r);
    $recentOrders = $pdo->query("SELECT o.order_id, o.order_date, o.total_amount, o.status, COALESCE(p.method,'-') payment_method, COALESCE(p.status,'-') payment_status FROM orders o LEFT JOIN payments p ON o.order_id=p.order_id ORDER BY o.order_date DESC LIMIT 15")->fetchAll();
    foreach ($recentOrders as &$r) { $r['order_id']=(int)$r['order_id']; $r['total_amount']=(float)$r['total_amount']; } unset($r);
    $activity = $pdo->query("SELECT al.activity_time, al.activity, u.full_name FROM activity_log al LEFT JOIN users u ON al.user_id=u.user_id ORDER BY al.activity_time DESC LIMIT 12")->fetchAll();
    echo json_encode(['success'=>true,'data'=>['overview'=>$overview,'orders_by_status'=>$ordersByStatus,'payments_by_method'=>$paymentsByMethod,'best_sellers'=>$bestSellers,'low_stock'=>$lowStock,'recent_orders'=>$recentOrders,'activity_log'=>$activity]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Gagal mengambil laporan.','error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}
?>
