<?php
include 'condb.php';

$data     = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (!$username || !$password) {
    echo json_encode(["success" => false, "message" => "กรุณากรอกข้อมูลให้ครบ"]);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT u.*, r.role_name 
        FROM users u 
        JOIN roles r ON u.role_id = r.role_id 
        WHERE u.username = :username
    ");
    $stmt->bindParam(":username", $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        echo json_encode([
            "success" => true,
            "message" => "เข้าสู่ระบบสำเร็จ",
            "user" => [
                "id"        => $user['user_id'],
                "username"  => $user['username'],
                "name"      => $user['first_name'] . " " . $user['last_name'],
                "email"     => $user['email'],
                "role_id"   => $user['role_id'],
                "role_name" => $user['role_name']
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>