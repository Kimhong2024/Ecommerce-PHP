<?php
require_once '../include/db.php';

// Allow any requests to this endpoint
// This is a simplified approach for this project

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'Order ID is required']);
    exit;
}

try {
    $db = (new Database())->connect();
    
    // Get order details
    $query = "SELECT * FROM orders WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        // Get order items
        $itemQuery = "SELECT * FROM order_items WHERE order_id = ?";
        $itemStmt = $db->prepare($itemQuery);
        $itemStmt->execute([$_GET['id']]);
        $order['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($order);
    } else {
        echo json_encode(['error' => 'Order not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}