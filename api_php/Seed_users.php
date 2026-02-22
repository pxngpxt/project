<?php
/**
 * seed_users.php
 * รัน: php seed_users.php  (จาก command line)
 * หรือเปิดผ่าน browser: http://localhost/room_booking/api_php/seed_users.php
 * 
 * สร้าง user ตัวอย่างพร้อม bcrypt hash password
 */

include 'condb.php';

$users = [
    ['admin01',   'admin1234',  'สมชาย',   'ใจดี',     'admin@uni.ac.th',     '0812345678', 1],
    ['staff01',   'staff1234',  'วิไล',    'รักงาน',   'staff@uni.ac.th',     '0823456789', 2],
    ['student01', 'stu1234',    'ธนกร',    'มานะดี',   'student01@uni.ac.th', '0834567890', 3],
    ['student02', 'stu5678',    'พิมพ์ใจ', 'สุขสันต์', 'student02@uni.ac.th', '0845678901', 3],
    ['teacher01', 'teach1234',  'วรพล',    'ปัญญาดี',  'teacher@uni.ac.th',   '0856789012', 4],
];

$inserted = 0;
foreach ($users as [$username, $password, $first, $last, $email, $phone, $role_id]) {
    // เช็คซ้ำ
    $chk = $conn->prepare("SELECT COUNT(*) FROM users WHERE username=:u");
    $chk->execute([':u' => $username]);
    if ($chk->fetchColumn() > 0) {
        echo "⚠️  Skip: $username (มีอยู่แล้ว)\n";
        continue;
    }

    $stmt = $conn->prepare("
        INSERT INTO users (username, password_hash, first_name, last_name, email, phone, role_id)
        VALUES (:username, :password_hash, :first_name, :last_name, :email, :phone, :role_id)
    ");
    $stmt->execute([
        ':username'      => $username,
        ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ':first_name'    => $first,
        ':last_name'     => $last,
        ':email'         => $email,
        ':phone'         => $phone,
        ':role_id'       => $role_id
    ]);
    echo "✅ Created: $username / $password\n";
    $inserted++;
}

echo "\n🎉 เพิ่มผู้ใช้สำเร็จ $inserted คน\n";
echo "\n📋 ข้อมูลเข้าสู่ระบบ:\n";
echo "  admin01  / admin1234  (Admin)\n";
echo "  staff01  / staff1234  (Staff)\n";
echo "  student01/ stu1234    (Student)\n";
echo "  teacher01/ teach1234  (Teacher)\n";
?>