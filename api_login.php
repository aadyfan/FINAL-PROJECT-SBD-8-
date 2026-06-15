<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Gunakan POST']); exit; }
require_once 'db_connect.php';
try {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!$payload) throw new Exception('Payload login tidak valid.');
    $email = trim($payload['email'] ?? '');
    $password = (string)($payload['password'] ?? '');
    $role = trim($payload['role'] ?? 'customer');
    if ($email === '' || $password === '') throw new Exception('Email dan password wajib diisi.');
    if (!in_array($role, ['customer','admin'], true)) throw new Exception('Role tidak valid.');

    $stmt = $pdo->prepare("SELECT user_id, full_name, full_name AS name, email, phone, password, address, city, province, role, created_at FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user) throw new Exception('Email tidak ditemukan di tabel users.');
    $stored = (string)$user['password'];
    $valid = password_verify($password, $stored) || hash_equals($stored, $password);
    if (!$valid) throw new Exception('Password salah.');
    if ($user['role'] !== $role) throw new Exception('Akun ini bukan role ' . $role . '.');
    unset($user['password']);
    $user['user_id'] = (int)$user['user_id'];
    echo json_encode(['success'=>true,'message'=>'Login berhasil sebagai ' . $role . '.', 'data'=>['user'=>$user]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}
?>
