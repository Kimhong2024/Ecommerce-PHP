<?php
// Check orders data
require_once 'admin/include/db.php';

try {
    $database = new Database();
    $db = $database->connect();
    
    echo "<h1>Order Data Check</h1>";
    
    // First, check the columns in the orders table
    $stmt = $db->query("DESCRIBE orders");
    echo "<h2>Orders Table Structure</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    $orderColumns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
        $orderColumns[] = $row['Field'];
    }
    echo "</table>";
    
    // Get a sample row to see actual data
    $stmt = $db->query("SELECT * FROM orders LIMIT 1");
    echo "<h2>Sample Order</h2>";
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Column</th><th>Value</th></tr>";
        foreach ($row as $key => $value) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($key) . "</td>";
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Identify the total amount column if it exists
    $totalAmountColumn = in_array('total_amount', $orderColumns) ? 'total_amount' : 
                         (in_array('total', $orderColumns) ? 'total' : '');
    
    if ($totalAmountColumn) {
        // Check for completed orders total
        $stmt = $db->query("SELECT SUM($totalAmountColumn) as total FROM orders WHERE status = 'completed'");
        $completedTotal = $stmt->fetchColumn();
        echo "<p>Total from completed orders: $" . number_format($completedTotal, 2) . "</p>";
        
        // Check for total of all orders
        $stmt = $db->query("SELECT SUM($totalAmountColumn) as total FROM orders");
        $allTotal = $stmt->fetchColumn();
        echo "<p>Total from all orders: $" . number_format($allTotal, 2) . "</p>";
        
        // Check total by status
        $stmt = $db->query("SELECT status, COUNT(*) as count, SUM($totalAmountColumn) as total FROM orders GROUP BY status");
        echo "<h2>Orders by Status</h2>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Status</th><th>Count</th><th>Total Amount</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr><td>" . htmlspecialchars($row['status']) . "</td><td>" . $row['count'] . "</td><td>$" . number_format($row['total'], 2) . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>No total amount column found in orders table.</p>";
    }
    
    // List all orders
    $stmt = $db->query("SELECT * FROM orders ORDER BY id DESC");
    echo "<h2>All Orders</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Reference</th><th>Customer</th><th>Status</th><th>Created</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['order_reference'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['customer_name'] ?? ($row['customer_email'] ?? 'N/A')) . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check order_items
    $stmt = $db->query("SELECT order_id, COUNT(*) as item_count, SUM(total) as total FROM order_items GROUP BY order_id");
    echo "<h2>Order Items Summary</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Order ID</th><th>Item Count</th><th>Total</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['order_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['item_count']) . "</td>";
        echo "<td>$" . number_format($row['total'], 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<div style='color: red; font-weight: bold;'>";
    echo "Database Error: " . htmlspecialchars($e->getMessage());
    echo "</div>";
}
?> 