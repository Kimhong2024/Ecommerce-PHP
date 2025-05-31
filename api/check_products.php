<?php
require_once '../admin/include/db.php';

header('Content-Type: application/json');

try {
    $db = (new Database())->connect();
    
    // Check if products table exists
    $stmt = $db->query("SHOW TABLES LIKE 'products'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo json_encode(['error' => 'Products table does not exist']);
        exit;
    }
    
    // Get count of products
    $stmt = $db->query("SELECT COUNT(*) as count FROM products");
    $count = $stmt->fetch()['count'];
    
    // Get sample products
    $stmt = $db->query("SELECT id, name, price, stock, status FROM products LIMIT 5");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'table_exists' => $tableExists,
        'total_products' => $count,
        'sample_products' => $products
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} 