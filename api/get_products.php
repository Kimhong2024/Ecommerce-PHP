<?php
require_once '../admin/include/db.php';

header('Content-Type: application/json');

try {
    $db = (new Database())->connect();
    $stmt = $db->query("SELECT id, name, price, stock, status FROM products ORDER BY name");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($products);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} 