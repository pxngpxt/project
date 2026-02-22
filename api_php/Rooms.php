<?php
include 'condb.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ดึงข้อมูลห้องทั้งหมด
    case 'GET':
        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';

        $sql = "SELECT * FROM rooms WHERE 1=1";
        $params = [];

        if ($status) {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }
        if ($search) {
            $sql .= " AND (room_name LIKE :search OR room_code LIKE :search2 OR location LIKE :search3)";
            $params[':search']  = "%$search%";
            $params[':search2'] = "%$search%";
            $params[':search3'] = "%$search%";
        }
        $sql .= " ORDER BY room_id";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $rooms]);
        break;

    // เพิ่มห้องใหม่
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("
            INSERT INTO rooms (room_code, room_name, capacity, location, status)
            VALUES (:room_code, :room_name, :capacity, :location, :status)
        ");
        $stmt->execute([
            ':room_code'  => $data['room_code'],
            ':room_name'  => $data['room_name'],
            ':capacity'   => $data['capacity'],
            ':location'   => $data['location'],
            ':status'     => $data['status'] ?? 'Available'
        ]);
        echo json_encode(["success" => true, "message" => "เพิ่มห้องสำเร็จ"]);
        break;

    // แก้ไขห้อง
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("
            UPDATE rooms SET room_code=:room_code, room_name=:room_name,
            capacity=:capacity, location=:location, status=:status
            WHERE room_id=:room_id
        ");
        $stmt->execute([
            ':room_code'  => $data['room_code'],
            ':room_name'  => $data['room_name'],
            ':capacity'   => $data['capacity'],
            ':location'   => $data['location'],
            ':status'     => $data['status'],
            ':room_id'    => $data['room_id']
        ]);
        echo json_encode(["success" => true, "message" => "แก้ไขห้องสำเร็จ"]);
        break;

    // ลบห้อง
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("DELETE FROM rooms WHERE room_id = :room_id");
        $stmt->execute([':room_id' => $data['room_id']]);
        echo json_encode(["success" => true, "message" => "ลบห้องสำเร็จ"]);
        break;
}
?>