<?php
require_once '../admin/include/db.php';

header('Content-Type: application/json');

if (!isset($_GET['order_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Order ID is required']);
    exit;
}

try {
    $db = (new Database())->connect();
    $query = "SELECT oi.*, p.name as product_name, p.price as product_price, p.stock, p.status as product_status
              FROM order_items oi
              LEFT JOIN products p ON oi.product_id = p.id
              WHERE oi.order_id = ?
              ORDER BY oi.id";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['order_id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($items);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} 