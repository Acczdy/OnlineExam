<?php
// Docker环境中MySQL服务名称是"mysql"而不是localhost
$host = 'mysql';  // ← 这里修改为mysql容器服务名
$dbname = 'exam';
// 使用docker-compose.yml中定义的用户凭证
$user = 'exam';      // ← 修改为Docker环境中的用户名
$pass = 'exam181818';  // ← 修改为对应的密码

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // 这是修正后的行
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

