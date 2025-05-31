<?php
// Invoice.php
require_once 'include/db.php';

class Invoice {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
        $this->checkInvoiceTable();
    }

    // Check if invoices table exists and create it if it doesn't
    private function checkInvoiceTable() {
        try {
            // Check if table exists
            $query = "SHOW TABLES LIKE 'invoices'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                // Table doesn't exist, create it
                $query = "CREATE TABLE invoices (
                    id INT(11) AUTO_INCREMENT PRIMARY KEY,
                    order_id INT(11) NOT NULL,
                    invoice_number VARCHAR(50) NOT NULL,
                    invoice_date DATE NOT NULL,
                    due_date DATE NOT NULL,
                    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                    tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                    shipping_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                    status VARCHAR(20) NOT NULL DEFAULT 'draft',
                    notes TEXT,
                    billing_address TEXT,
                    shipping_address TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
                )";
                $this->db->exec($query);
                
                // Create settings table if it doesn't exist
                $query = "SHOW TABLES LIKE 'invoice_settings'";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                
                if ($stmt->rowCount() == 0) {
                    $query = "CREATE TABLE invoice_settings (
                        id INT(11) AUTO_INCREMENT PRIMARY KEY,
                        company_name VARCHAR(100) NOT NULL,
                        address TEXT,
                        phone VARCHAR(20),
                        email VARCHAR(100),
                        tax_id VARCHAR(50),
                        prefix VARCHAR(10) DEFAULT 'INV',
                        terms TEXT,
                        notes TEXT,
                        logo VARCHAR(255),
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )";
                    $this->db->exec($query);
                    
                    // Insert default settings
                    $query = "INSERT INTO invoice_settings (company_name, address, phone, email, prefix) 
                             VALUES ('PHONE SHOP', 'St 123, Phnom Penh, Cambodia', '+855 977626855', 'phoneshop@gmail.com', 'INV')";
                    $this->db->exec($query);
                }
            }
        } catch (PDOException $e) {
            error_log("Error checking/creating invoice table: " . $e->getMessage());
            // We don't throw exception here to allow the system to continue even if table creation fails
        }
    }

// Get all orders for creating invoices
// Temporarily modify the method to debug:
    public function getAllOrders() {
        try {
            error_log("Checking for orders table..."); // Debug log
            
            $query = "SHOW TABLES LIKE 'orders'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            error_log("Orders table exists: ".$stmt->rowCount()); // Debug log
            
            if ($stmt->rowCount() == 0) {
                error_log("Orders table does not exist"); // Debug log
                return [];
            }
            
            // Simple test query - remove all filters
            $query = "SELECT * FROM orders LIMIT 5";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Found orders: ".count($results)); // Debug log
            
            return $results;
        } catch (PDOException $e) {
            error_log("Database error: ".$e->getMessage()); // Debug log
            return [];
        }
    }
    // Get a single order by ID
    public function getOrderById($id) {
        try {
            $query = "SELECT * FROM orders WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($order) {
                // Fetch order items
                $itemQuery = "SELECT * FROM order_items WHERE order_id = ?";
                $itemStmt = $this->db->prepare($itemQuery);
                $itemStmt->execute([$id]);
                $order['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return $order;
        } catch (PDOException $e) {
            error_log("Error fetching order: " . $e->getMessage());
            throw new Exception("Failed to fetch order");
        }
    }

    // Add a new invoice
    public function addInvoice($orderID, $invoiceNumber, $invoiceDate, $dueDate, $status, $notes, $billingAddress, $shippingAddress) {
        if (empty($orderID) || empty($invoiceNumber)) {
            throw new Exception("Required fields are missing");
        }

        $this->db->beginTransaction();

        try {
            // Get order details
            $order = $this->getOrderById($orderID);
            if (!$order) {
                throw new Exception("Order not found");
            }

            // Insert the invoice
            $query = "INSERT INTO invoices (order_id, invoice_number, invoice_date, due_date, total_amount, 
                     tax_amount, shipping_amount, discount_amount, status, notes, billing_address, shipping_address, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $orderID, 
                $invoiceNumber, 
                $invoiceDate, 
                $dueDate, 
                $order['total'], 
                $order['tax'], 
                $order['shipping'], 
                0, // discount_amount
                $status, 
                $notes, 
                $billingAddress, 
                $shippingAddress
            ]);
            $invoiceId = $this->db->lastInsertId();

            $this->db->commit();
            return $invoiceId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error adding invoice: " . $e->getMessage());
            throw new Exception("Failed to add invoice: " . $e->getMessage());
        }
    }

    // Get all invoices
    public function getAllInvoices($search = '', $statusFilter = '', $dateFrom = '', $dateTo = '') {
        try {
            // Check if table exists first
            $checkQuery = "SHOW TABLES LIKE 'invoices'";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() == 0) {
                // Table doesn't exist, try to create it
                $this->checkInvoiceTable();
                return []; // Return empty array as there are no invoices yet
            }
            
            $query = "SELECT i.*, o.order_reference 
                     FROM invoices i 
                     LEFT JOIN orders o ON i.order_id = o.id";
            
            $conditions = [];
            $params = [];
            
            if ($search) {
                $conditions[] = "(i.invoice_number LIKE ? OR o.order_reference LIKE ? OR o.customer_name LIKE ?)";
                $searchParam = "%$search%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            if ($statusFilter) {
                $conditions[] = "i.status = ?";
                $params[] = $statusFilter;
            }
            
            if ($dateFrom) {
                $conditions[] = "i.invoice_date >= ?";
                $params[] = $dateFrom;
            }
            
            if ($dateTo) {
                $conditions[] = "i.invoice_date <= ?";
                $params[] = $dateTo;
            }
            
            if (count($conditions) > 0) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $query .= " ORDER BY i.created_at DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching invoices: " . $e->getMessage());
            // Return empty array instead of throwing exception
            return [];
        }
    }

    // Get invoice by ID
    public function getInvoiceById($id) {
        try {
            // Check if table exists first
            $checkQuery = "SHOW TABLES LIKE 'invoices'";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() == 0) {
                // Table doesn't exist, try to create it
                $this->checkInvoiceTable();
                return null; // Return null as there are no invoices yet
            }
            
            $query = "SELECT i.*, o.order_reference, o.customer_name, o.customer_email, o.customer_phone
                     FROM invoices i 
                     LEFT JOIN orders o ON i.order_id = o.id
                     WHERE i.id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($invoice) {
                // Get order items
                $itemQuery = "SELECT * FROM order_items WHERE order_id = ?";
                $itemStmt = $this->db->prepare($itemQuery);
                $itemStmt->execute([$invoice['order_id']]);
                $invoice['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return $invoice;
        } catch (PDOException $e) {
            error_log("Error fetching invoice: " . $e->getMessage());
            return null; // Return null instead of throwing exception
        }
    }

    // Update invoice status
    public function updateInvoiceStatus($id, $status) {
        try {
            // Check if table exists first
            $checkQuery = "SHOW TABLES LIKE 'invoices'";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() == 0) {
                // Table doesn't exist, try to create it
                $this->checkInvoiceTable();
                return false; // Can't update a non-existent invoice
            }
            
            $query = "UPDATE invoices SET status = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$status, $id]);
            return true;
        } catch (PDOException $e) {
            error_log("Error updating invoice status: " . $e->getMessage());
            return false; // Return false instead of throwing exception
        }
    }

    // Delete invoice
    public function deleteInvoice($id) {
        try {
            // Check if table exists first
            $checkQuery = "SHOW TABLES LIKE 'invoices'";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() == 0) {
                // Table doesn't exist, try to create it
                $this->checkInvoiceTable();
                return false; // Can't delete a non-existent invoice
            }
            
            $query = "DELETE FROM invoices WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            return true;
        } catch (PDOException $e) {
            error_log("Error deleting invoice: " . $e->getMessage());
            return false; // Return false instead of throwing exception
        }
    }
    
    // Generate invoice HTML for download
    public function generateInvoice($id) {
        try {
            // Get invoice data
            $invoice = $this->getInvoiceById($id);
            if (!$invoice) {
                throw new Exception("Invoice not found");
            }
            
            // Get company settings
            $settings = $this->getInvoiceSettings();
            
            // Create directory for invoices if it doesn't exist
            $filePath = 'uploads/invoices/';
            if (!file_exists($filePath)) {
                mkdir($filePath, 0777, true);
            }
            
            $fileName = 'Invoice_' . $invoice['invoice_number'] . '.html';
            $fullPath = $filePath . $fileName;
            
            // Generate HTML content
            $html = $this->generateInvoiceHTML($invoice, $settings);
            
            // Save HTML to file
            file_put_contents($fullPath, $html);
            
            return $fullPath;
        } catch (Exception $e) {
            error_log("Error generating invoice: " . $e->getMessage());
            throw new Exception("Failed to generate invoice: " . $e->getMessage());
        }
    }
    
    // Generate HTML content for the invoice
    private function generateInvoiceHTML($invoice, $settings) {
        $items = $invoice['items'];
        $subtotal = 0;
        
        foreach ($items as $item) {
            $subtotal += floatval($item['total']);
        }
        
        $html = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Invoice #' . $invoice['invoice_number'] . '</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 20px;
                    color: #333;
                }
                .invoice-container {
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 30px;
                    border: 1px solid #eee;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
                }
                .invoice-header {
                    margin-bottom: 20px;
                    border-bottom: 1px solid #eee;
                    padding-bottom: 20px;
                }
                .invoice-title {
                    font-size: 24px;
                    color: #333;
                    text-align: center;
                    margin-bottom: 20px;
                }
                .row {
                    display: flex;
                    flex-wrap: wrap;
                    margin: 0 -10px;
                }
                .col {
                    flex: 1;
                    padding: 0 10px;
                }
                .company-details {
                    text-align: right;
                }
                .invoice-details {
                    margin-bottom: 20px;
                }
                .customer-details {
                    margin-bottom: 20px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }
                table th, table td {
                    padding: 10px;
                    text-align: left;
                    border-bottom: 1px solid #eee;
                }
                table th {
                    background-color: #f8f8f8;
                }
                .text-right {
                    text-align: right;
                }
                .totals {
                    width: 100%;
                    display: flex;
                    justify-content: flex-end;
                }
                .totals table {
                    width: 300px;
                }
                .notes {
                    margin-top: 20px;
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                }
                .footer {
                    margin-top: 30px;
                    text-align: center;
                    color: #888;
                    font-size: 12px;
                }
                @media print {
                    body {
                        padding: 0;
                    }
                    .invoice-container {
                        box-shadow: none;
                        border: none;
                    }
                    .no-print {
                        display: none;
                    }
                }
            </style>
        </head>
        <body>
            <div class="invoice-container">
                <div class="invoice-header">
                    <div class="invoice-title">INVOICE</div>
                    <div class="row">
                        <div class="col">
                            <h2>' . htmlspecialchars($settings['company_name']) . '</h2>
                            <p>' . nl2br(htmlspecialchars($settings['address'])) . '</p>
                            <p>Phone: ' . htmlspecialchars($settings['phone']) . '</p>
                            <p>Email: ' . htmlspecialchars($settings['email']) . '</p>
                            ' . ($settings['tax_id'] ? '<p>Tax ID: ' . htmlspecialchars($settings['tax_id']) . '</p>' : '') . '
                        </div>
                        <div class="col company-details">
                            <h3>Invoice #' . htmlspecialchars($invoice['invoice_number']) . '</h3>
                            <p>Order #: ' . htmlspecialchars($invoice['order_reference']) . '</p>
                            <p>Date: ' . date('M d, Y', strtotime($invoice['invoice_date'])) . '</p>
                            <p>Due Date: ' . date('M d, Y', strtotime($invoice['due_date'])) . '</p>
                            <p>Status: ' . ucfirst($invoice['status']) . '</p>
                        </div>
                    </div>
                </div>
                
                <div class="invoice-details">
                    <div class="row">
                        <div class="col customer-details">
                            <h3>Bill To:</h3>
                            <p>' . htmlspecialchars($invoice['customer_name']) . '</p>
                            <p>' . htmlspecialchars($invoice['customer_email']) . '</p>
                            ' . ($invoice['customer_phone'] ? '<p>Phone: ' . htmlspecialchars($invoice['customer_phone']) . '</p>' : '') . '
                            <p>' . nl2br(htmlspecialchars($invoice['billing_address'])) . '</p>
                        </div>
                        <div class="col customer-details">
                            <h3>Ship To:</h3>
                            <p>' . nl2br(htmlspecialchars($invoice['shipping_address'])) . '</p>
                        </div>
                  </div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        $i = 1;
        foreach ($items as $item) {
            $html .= '
                        <tr>
                            <td>' . $i . '</td>
                            <td>' . htmlspecialchars($item['product_name']) . '</td>
                            <td>' . htmlspecialchars($item['quantity']) . '</td>
                            <td>$' . number_format($item['price'], 2) . '</td>
                            <td>$' . number_format($item['total'], 2) . '</td>
                        </tr>';
            $i++;
        }
        
        $html .= '
                    </tbody>
                </table>
                
                <div class="totals">
                    <table>
                        <tr>
                            <td>Subtotal:</td>
                            <td class="text-right">$' . number_format($subtotal, 2) . '</td>
                        </tr>
                        <tr>
                            <td>Tax:</td>
                            <td class="text-right">$' . number_format($invoice['tax_amount'], 2) . '</td>
                        </tr>
                        <tr>
                            <td>Shipping:</td>
                            <td class="text-right">$' . number_format($invoice['shipping_amount'], 2) . '</td>
                        </tr>';
        
        if (floatval($invoice['discount_amount']) > 0) {
            $html .= '
                        <tr>
                            <td>Discount:</td>
                            <td class="text-right">-$' . number_format($invoice['discount_amount'], 2) . '</td>
                        </tr>';
        }
        
        $html .= '
                        <tr>
                            <th>Total:</th>
                            <th class="text-right">$' . number_format($invoice['total_amount'], 2) . '</th>
                        </tr>
                    </table>
                </div>';
        
        if ($invoice['notes']) {
            $html .= '
                <div class="notes">
                    <h3>Notes:</h3>
                    <p>' . nl2br(htmlspecialchars($invoice['notes'])) . '</p>
                </div>';
        }
        
        if ($settings['terms']) {
            $html .= '
                <div class="notes">
                    <h3>Terms & Conditions:</h3>
                    <p>' . nl2br(htmlspecialchars($settings['terms'])) . '</p>
                </div>';
        }
        
        $html .= '
                <div class="footer">
                    <p>Thank you for your business!</p>
                    <p>' . date('Y') . ' &copy; ' . htmlspecialchars($settings['company_name']) . '</p>
              </div>

                <div class="no-print" style="text-align: center; margin-top: 20px;">
                    <button onclick="window.print();" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">Print Invoice</button>
                </div>
              </div>
        </body>
        </html>';
        
        return $html;
    }
    
    // Get invoice settings
    private function getInvoiceSettings() {
        try {
            $query = "SELECT * FROM invoice_settings LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$settings) {
                // Return default settings if none found
                return [
                    'company_name' => 'PHONE SHOP',
                    'address' => 'St 123, Phnom Penh, Cambodia',
                    'phone' => '+855 977626855',
                    'email' => 'phoneshop@gmail.com',
                    'tax_id' => '',
                    'prefix' => 'INV',
                    'terms' => 'Thank you for your business!',
                    'notes' => ''
                ];
            }
            
            return $settings;
        } catch (PDOException $e) {
            error_log("Error fetching invoice settings: " . $e->getMessage());
            throw new Exception("Failed to fetch invoice settings");
        }
    }
    
    // Generate a unique invoice number
    public function generateInvoiceNumber() {
        try {
            // Get settings for prefix
            $settings = $this->getInvoiceSettings();
            $prefix = $settings['prefix'] ?? 'INV';
            
            // Get the current year and month
            $ym = date('Ym');
            
            // Get the last invoice number
            $query = "SELECT MAX(invoice_number) as last_number FROM invoices WHERE invoice_number LIKE ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$prefix . $ym . '%']);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $lastNumber = $result['last_number'] ?? null;
            
            if ($lastNumber) {
                // Extract the sequence number
                $sequence = (int)substr($lastNumber, -4);
                $sequence++;
            } else {
                $sequence = 1;
            }
            
            // Format as PREFIX-YYYYMM-NNNN
            return $prefix . $ym . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
        } catch (PDOException $e) {
            error_log("Error generating invoice number: " . $e->getMessage());
            throw new Exception("Failed to generate invoice number");
        }
    }
    
    // Send invoice via email
    public function sendInvoice($id) {
        try {
            // Get invoice data
            $invoice = $this->getInvoiceById($id);
            if (!$invoice) {
                throw new Exception("Invoice not found");
            }
            
            // Get company settings
            $settings = $this->getInvoiceSettings();
            
            // Prepare email
            $to = $invoice['customer_email'];
            $subject = "Invoice #" . $invoice['invoice_number'] . " from " . $settings['company_name'];
            
            // Create HTML email
            $message = "<html><body>";
            $message .= "<p>Dear " . htmlspecialchars($invoice['customer_name']) . ",</p>";
            $message .= "<p>Please find your invoice details below for Invoice #" . htmlspecialchars($invoice['invoice_number']) . ".</p>";
            $message .= "<h3>Invoice Details:</h3>";
            $message .= "<p><strong>Amount:</strong> $" . number_format($invoice['total_amount'], 2) . "<br>";
            $message .= "<strong>Due Date:</strong> " . date('M d, Y', strtotime($invoice['due_date'])) . "</p>";
            
            if ($invoice['notes']) {
                $message .= "<p><strong>Notes:</strong> " . nl2br(htmlspecialchars($invoice['notes'])) . "</p>";
            }
            
            // Add link to view invoice online
            $invoiceUrl = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/uploads/invoices/Invoice_' . $invoice['invoice_number'] . '.html';
            $message .= "<p><a href=\"" . $invoiceUrl . "\" style=\"padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;\">View Invoice</a></p>";
            
            $message .= "<p>Thank you for your business.</p>";
            $message .= "<p>Regards,<br>";
            $message .= htmlspecialchars($settings['company_name']) . "<br>";
            $message .= htmlspecialchars($settings['phone']) . "<br>";
            $message .= htmlspecialchars($settings['email']) . "</p>";
            $message .= "</body></html>";
            
            // Create email headers
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: " . $settings['email'] . "\r\n";
            
            // Send email
            mail($to, $subject, $message, $headers);
            
            // Update invoice status to sent
            $this->updateInvoiceStatus($id, 'sent');
            
            return true;
        } catch (Exception $e) {
            error_log("Error sending invoice: " . $e->getMessage());
            throw new Exception("Failed to send invoice: " . $e->getMessage());
        }
    }
}

// Initialize the Invoice class
$invoiceManager = new Invoice();

// Handle AJAX requests
if (isset($_GET['action'])) {
    try {
        $action = $_GET['action'];
        
        if ($action === 'download' && isset($_GET['id'])) {
            // Generate invoice HTML for download
            $id = (int)$_GET['id'];
            try {
                $filePath = $invoiceManager->generateInvoice($id);
                
                // Display a styled page with links to the invoice
                echo <<<HTML
                    <div class="container text-center">
                        <h2 class="mb-4">Invoice Ready</h2>
                        <p>Your invoice has been generated successfully.</p>
                        <div class="d-flex justify-content-center">
                            <a href="{$filePath}" target="_blank" class="btn btn-primary">
                                <i class="fas fa-eye"></i> View Invoice
                            </a>
                            <a href="{$filePath}" download class="btn btn-success">
                                <i class="fas fa-download"></i> Download Invoice
                            </a>
                            <a href="index.php?p=Invoice" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Invoices
                            </a>
                        </div>
                        <iframe src="{$filePath}" style="width:100%; height:500px; margin-top:30px; border:1px solid #ddd;"></iframe>
                    </div>
                    <script>
                        // Auto-open the invoice in a new tab after 1 second
                        setTimeout(function() {
                            window.open("{$filePath}", "_blank");
                        }, 1000);
                    </script>
HTML;
                exit;
            } catch (Exception $e) {
                $_SESSION['error_message'] = $e->getMessage();
                echo "<script>window.location.href='index.php?p=Invoice';</script>";
                exit;
            }
        } elseif ($action === 'send' && isset($_GET['id'])) {
            // Send invoice via email
            $id = (int)$_GET['id'];
            if ($invoiceManager->sendInvoice($id)) {
                $_SESSION['success_message'] = 'Invoice sent successfully';
            } else {
                $_SESSION['error_message'] = 'Failed to send invoice';
            }
            // Use JavaScript to redirect back to avoid header issues
            echo "<script>window.location.href='index.php?p=Invoice';</script>";
            exit;
        } elseif ($action === 'delete' && isset($_GET['id'])) {
            // Delete invoice
            $id = (int)$_GET['id'];
            if ($invoiceManager->deleteInvoice($id)) {
                $_SESSION['success_message'] = 'Invoice deleted successfully';
            } else {
                $_SESSION['error_message'] = 'Failed to delete invoice';
            }
            // Use JavaScript to redirect back to avoid header issues
            echo "<script>window.location.href='index.php?p=Invoice';</script>";
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        // Use JavaScript to redirect back to avoid header issues
        echo "<script>window.location.href='index.php?p=Invoice';</script>";
        exit;
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action']) && $_POST['action'] === 'add') {
            // Add new invoice
            $orderID = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
            $invoiceNumber = trim($_POST['invoice_number']);
            $invoiceDate = trim($_POST['invoice_date']);
            $dueDate = trim($_POST['due_date']);
            $status = trim($_POST['status']);
            $notes = trim($_POST['notes'] ?? '');
            $billingAddress = trim($_POST['billing_address'] ?? '');
            $shippingAddress = trim($_POST['shipping_address'] ?? '');
            
            if (empty($orderID) || empty($invoiceNumber) || empty($invoiceDate) || empty($dueDate)) {
                throw new Exception('Required fields are missing');
            }
            
            $invoiceId = $invoiceManager->addInvoice(
                $orderID, 
                $invoiceNumber, 
                $invoiceDate, 
                $dueDate, 
                $status, 
                $notes, 
                $billingAddress, 
                $shippingAddress
            );
            
            if ($invoiceId) {
                $_SESSION['success_message'] = 'Invoice added successfully';
                // Use JavaScript to redirect back to avoid header issues
                echo "<script>window.location.href='index.php?p=Invoice';</script>";
                exit;
            } else {
                throw new Exception('Failed to add invoice');
            }
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        // Use JavaScript to redirect back to avoid header issues
        echo "<script>window.location.href='index.php?p=Invoice';</script>";
        exit;
    }
}

// Get data for the page
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
$dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

$invoices = $invoiceManager->getAllInvoices($search, $statusFilter, $dateFrom, $dateTo);
$orders = $invoiceManager->getAllOrders();
$newInvoiceNumber = $invoiceManager->generateInvoiceNumber();
?>

<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Invoice Management</h3>
            </div>
            <div class="ms-md-auto py-2 py-md-0">
                <button type="button" class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#addInvoiceModal">
                    <i class="fas fa-plus"></i> Create Invoice
                            </button>
            </div>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card card-round">
                    <div class="card-body">
                        <form method="GET" action="index.php">
                            <input type="hidden" name="p" value="Invoice">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="search">Search</label>
                                        <input type="text" class="form-control" id="search" name="search" placeholder="Invoice #, Order #..." value="<?php echo htmlspecialchars($search); ?>">
                                    </div>
                        </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="">All Statuses</option>
                                            <option value="draft" <?php echo $statusFilter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                            <option value="sent" <?php echo $statusFilter === 'sent' ? 'selected' : ''; ?>>Sent</option>
                                            <option value="paid" <?php echo $statusFilter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                            <option value="overdue" <?php echo $statusFilter === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                                            <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                          </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="date_from">Date From</label>
                                        <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="date_to">Date To</label>
                                        <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>">
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-round w-100">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                        </div>
                      </div>

        <!-- Invoice List Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="card card-round">
                    <div class="card-header">
                        <div class="card-title">Invoice List</div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="invoiceTable">
                                <thead>
                          <tr>
                            <th>Invoice #</th>
                            <th>Order #</th>
                                        <th>Invoice Date</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                                    <?php if (empty($invoices)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No invoices found</td>
                            </tr>
                                    <?php else: ?>
                                        <?php foreach ($invoices as $invoice): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                                                <td><?php echo htmlspecialchars($invoice['order_reference']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($invoice['invoice_date'])); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($invoice['due_date'])); ?></td>
                                                <td>$<?php echo number_format($invoice['total_amount'], 2); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo getStatusColor($invoice['status']); ?>">
                                                        <?php echo ucfirst($invoice['status']); ?>
                                  </span>
                                </td>
                                <td>
                                                    <div class="btn-group">
                                                        <a href="index.php?p=Invoice&action=download&id=<?php echo $invoice['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        <a href="index.php?p=Invoice&action=send&id=<?php echo $invoice['id']; ?>" 
                                                           class="btn btn-sm btn-info" 
                                                           onclick="return confirm('Send this invoice via email?')">
                                                            <i class="fas fa-envelope"></i>
                                                        </a>
                                                        <a href="index.php?p=Invoice&action=delete&id=<?php echo $invoice['id']; ?>" 
                                                           class="btn btn-sm btn-danger" 
                                                           onclick="return confirm('Are you sure you want to delete this invoice?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                  </div>
                                </td>
                              </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

<!-- Add Invoice Modal -->
<div class="modal fade" id="addInvoiceModal" tabindex="-1" aria-labelledby="addInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addInvoiceModalLabel">Create New Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
            <form method="POST" id="invoiceForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="order_id" class="form-label">Order</label>
                                <select class="form-control" id="order_id" name="order_id" required onchange="loadOrderDetails(this.value)">
                                    <option value="">Select Order</option>
                                    <?php foreach ($orders as $order): ?>
                                        <option value="<?php echo $order['id']; ?>">
                                            <?php echo $order['order_reference'] . ' - ' . $order['customer_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                  </div>
                </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="invoice_number" class="form-label">Invoice Number</label>
                                <input type="text" class="form-control" id="invoice_number" name="invoice_number" value="<?php echo htmlspecialchars($newInvoiceNumber); ?>" required>
          </div>
        </div>
      </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="invoice_date" class="form-label">Invoice Date</label>
                                <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="<?php echo date('Y-m-d'); ?>" required>
              </div>
                  </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" class="form-control" id="due_date" name="due_date" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                  </div>
                  </div>
                  </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status" required>
                      <option value="draft">Draft</option>
                      <option value="sent">Sent</option>
                      <option value="paid">Paid</option>
                    </select>
                  </div>
                  </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="billing_address" class="form-label">Billing Address</label>
                                <textarea class="form-control" id="billing_address" name="billing_address" rows="3"></textarea>
                    </div>
                  </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="shipping_address" class="form-label">Shipping Address</label>
                                <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3"></textarea>
              </div>
            </div>
          </div>
                    
                    <div class="form-group mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Helper function to get status color
function getStatusColor($status) {
    switch ($status) {
        case 'draft':
            return 'secondary';
        case 'sent':
            return 'primary';
        case 'paid':
            return 'success';
        case 'overdue':
            return 'danger';
        case 'cancelled':
            return 'warning';
        default:
            return 'info';
    }
}
?>

<script>
// Function to load order details
function loadOrderDetails(orderId) {
    if (!orderId) return;
    
    // Fetch order details and populate form fields
    fetch(`ajax/get_order.php?id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data && !data.error) {
                // Populate shipping and billing addresses
                document.getElementById('billing_address').value = data.shipping_address || '';
                document.getElementById('shipping_address').value = data.shipping_address || '';
            } else if (data.error) {
                console.error('Error:', data.error);
                alert('Error loading order details: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load order details. Please try again.');
        });
}

// Initialize DataTable for the invoice table
$(document).ready(function() {
    $('#invoiceTable').DataTable({
        "order": [[2, "desc"]],
        "pageLength": 10
    });
});
</script>

