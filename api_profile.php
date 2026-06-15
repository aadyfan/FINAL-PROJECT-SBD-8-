<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
require_once 'db_connect.php';
try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $userId = (int)($_GET['user_id'] ?? 0);
        if ($userId <= 0) throw new Exception('user_id wajib dikirim.');
        $stmt = $pdo->prepare("SELECT user_id, full_name, full_name AS name, email, phone, address, city, province, role, created_at FROM users WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if (!$user) throw new Exception('User tidak ditemukan.');
        $user['user_id'] = (int)$user['user_id'];
        echo json_encode(['success'=>true,'data'=>['user'=>$user]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method tidak diizinkan.']); exit; }
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!$payload) throw new Exception('Payload profile tidak valid.');
    $userId = (int)($payload['user_id'] ?? 0);
    $name = trim($payload['full_name'] ?? $payload['name'] ?? '');
    $phone = trim($payload['phone'] ?? '');
    $address = trim($payload['address'] ?? '');
    $city = trim($payload['city'] ?? '');
    $province = trim($payload['province'] ?? '');
    if ($userId <= 0) throw new Exception('user_id wajib dikirim.');
    if ($name === '') throw new Exception('Nama lengkap wajib diisi.');
    $stmt = $pdo->prepare("UPDATE users SET full_name=?, phone=?, address=?, city=?, province=? WHERE user_id=?");
    $stmt->execute([$name, $phone, $address, $city, $province, $userId]);
    $stmt = $pdo->prepare("SELECT user_id, full_name, full_name AS name, email, phone, address, city, province, role, created_at FROM users WHERE user_id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    $user['user_id'] = (int)$user['user_id'];
    echo json_encode(['success'=>true,'message'=>'Profile berhasil diperbarui.', 'data'=>['user'=>$user]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}
?>
