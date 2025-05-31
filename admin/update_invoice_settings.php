<?php
require_once 'include/db.php';

try {
    $db = (new Database())->connect();
    
    // Update invoice settings
    $query = "UPDATE invoice_settings SET 
              company_name = 'PHONE SHOP',
              address = 'St 123, Phnom Penh, Cambodia',
              phone = '+855 977626855',
              email = 'phoneshop@gmail.com'
              WHERE id = 1";
              
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    echo "Invoice settings updated successfully!";
} catch (PDOException $e) {
    echo "Error updating invoice settings: " . $e->getMessage();
}
?> 