<?php
include 'condb.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $conn->prepare("
            SELECT u.user_id, u.username, u.first_name, u.last_name, 
                   u.email, u.phone, u.role_id, u.created_at, r.role_name
            FROM users u JOIN roles r ON u.role_id = r.role_id
            ORDER BY u.created_at DESC
        ");
        $stmt->execute();
        echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        // เช็ค username ซ้ำ
        $chk = $conn->prepare("SELECT COUNT(*) FROM users WHERE username=:u OR email=:e");
        $chk->execute([':u' => $data['username'], ':e' => $data['email']]);
        if ($chk->fetchColumn() > 0) {
            echo json_encode(["success" => false, "message" => "Username หรือ Email ซ้ำในระบบ"]);
            exit;
        }

        $stmt = $conn->prepare("
            INSERT INTO users (username, password_hash, first_name, last_name, email, phone, role_id)
            VALUES (:username, :password_hash, :first_name, :last_name, :email, :phone, :role_id)
        ");
        $stmt->execute([
            ':username'      => $data['username'],
            ':password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':first_name'    => $data['first_name'],
            ':last_name'     => $data['last_name'],
            ':email'         => $data['email'],
            ':phone'         => $data['phone'] ?? null,
            ':role_id'       => $data['role_id']
        ]);
        echo json_encode(["success" => true, "message" => "เพิ่มผู้ใช้สำเร็จ"]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id=:id");
        $stmt->execute([':id' => $data['user_id']]);
        echo json_encode(["success" => true, "message" => "ลบผู้ใช้สำเร็จ"]);
        break;
}
?>