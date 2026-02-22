<?php
include 'condb.php';

try {
    $stats = [];

    $stmt = $conn->query("SELECT COUNT(*) FROM rooms");
    $stats['total_rooms'] = $stmt->fetchColumn();

    $stmt = $conn->query("SELECT COUNT(*) FROM rooms WHERE status='Available'");
    $stats['available_rooms'] = $stmt->fetchColumn();

    $stmt = $conn->query("SELECT COUNT(*) FROM bookings WHERE status='Pending'");
    $stats['pending_bookings'] = $stmt->fetchColumn();

    $stmt = $conn->query("SELECT COUNT(*) FROM bookings WHERE status='Approved'");
    $stats['approved_bookings'] = $stmt->fetchColumn();

    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    $stats['total_users'] = $stmt->fetchColumn();

    $stmt = $conn->query("SELECT COUNT(*) FROM bookings WHERE DATE(created_at) = CURDATE()");
    $stats['today_bookings'] = $stmt->fetchColumn();

    // การจองล่าสุด
    $stmt = $conn->query("
        SELECT b.booking_id, b.purpose, b.status, b.start_time,
               CONCAT(u.first_name,' ',u.last_name) as user_name,
               r.room_name
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN rooms r ON b.room_id = r.room_id
        ORDER BY b.created_at DESC LIMIT 5
    ");
    $stats['recent_bookings'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "data" => $stats]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>