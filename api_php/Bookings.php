<?php
include 'condb.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        $user_id = $_GET['user_id'] ?? '';
        $role_id = $_GET['role_id'] ?? '';
        $status  = $_GET['status'] ?? '';

        // Admin/Staff เห็นทั้งหมด, User เห็นเฉพาะของตัวเอง
        $sql = "
            SELECT b.*, 
                   u.first_name, u.last_name, u.email,
                   r.room_name, r.room_code, r.location, r.capacity
            FROM bookings b
            JOIN users u ON b.user_id = u.user_id
            JOIN rooms r ON b.room_id = r.room_id
            WHERE 1=1
        ";
        $params = [];

        if ($role_id == 3) { // Student เห็นแค่ของตัวเอง
            $sql .= " AND b.user_id = :user_id";
            $params[':user_id'] = $user_id;
        }
        if ($status) {
            $sql .= " AND b.status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY b.created_at DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $bookings]);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        // เช็คว่าห้องว่างในช่วงเวลานั้นไหม
        $check = $conn->prepare("
            SELECT COUNT(*) FROM bookings 
            WHERE room_id = :room_id 
            AND status NOT IN ('Rejected','Cancelled')
            AND (
                (start_time < :end_time AND end_time > :start_time)
            )
        ");
        $check->execute([
            ':room_id'    => $data['room_id'],
            ':start_time' => $data['start_time'],
            ':end_time'   => $data['end_time']
        ]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(["success" => false, "message" => "ห้องนี้ถูกจองในช่วงเวลาดังกล่าวแล้ว"]);
            exit;
        }

        $stmt = $conn->prepare("
            INSERT INTO bookings (user_id, room_id, start_time, end_time, purpose, status)
            VALUES (:user_id, :room_id, :start_time, :end_time, :purpose, 'Pending')
        ");
        $stmt->execute([
            ':user_id'    => $data['user_id'],
            ':room_id'    => $data['room_id'],
            ':start_time' => $data['start_time'],
            ':end_time'   => $data['end_time'],
            ':purpose'    => $data['purpose']
        ]);
        echo json_encode(["success" => true, "message" => "ส่งคำขอจองสำเร็จ กรุณารอการอนุมัติ"]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("UPDATE bookings SET status=:status WHERE booking_id=:booking_id");
        $stmt->execute([':status' => $data['status'], ':booking_id' => $data['booking_id']]);
        echo json_encode(["success" => true, "message" => "อัปเดตสถานะสำเร็จ"]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id=:booking_id");
        $stmt->execute([':booking_id' => $data['booking_id']]);
        echo json_encode(["success" => true, "message" => "ยกเลิกการจองสำเร็จ"]);
        break;
}
?>