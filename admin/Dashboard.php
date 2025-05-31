<?php
// Include database connection
require_once 'include/db.php';

// Initialize default values
$customersCount = 0;
$ordersCount = 0;
$productsCount = 0;
$categoriesCount = 0;
$totalSales = 0;
$recentOrders = [];
$topProducts = [];
$months = [];
$sales = [];

try {
    $database = new Database();
    $db = $database->connect();
    
    // Get customers count
    $stmt = $db->query("SELECT COUNT(*) FROM customers");
    if ($stmt) {
        $customersCount = (int)$stmt->fetchColumn();
    }
    
    // Get orders count
    $stmt = $db->query("SELECT COUNT(*) FROM orders");
    if ($stmt) {
        $ordersCount = (int)$stmt->fetchColumn();
    }
    
    // Get products count
    $stmt = $db->query("SELECT COUNT(*) FROM products");
    if ($stmt) {
        $productsCount = (int)$stmt->fetchColumn();
    }
    
    // Get categories count
    $stmt = $db->query("SELECT COUNT(*) FROM categories");
    if ($stmt) {
        $categoriesCount = (int)$stmt->fetchColumn();
    }
    
    // Get total sales - use 'total' column instead of 'total_amount'
    $stmt = $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status IN ('completed', 'paid')");
    if ($stmt) {
        $totalSales = (float)$stmt->fetchColumn();
    }
    
    // Get recent orders
    // Check which fields exist in the orders table
    $stmt = $db->query("SELECT * FROM orders LIMIT 1");
    if ($stmt) {
        $orderColumns = array_keys($stmt->fetch(PDO::FETCH_ASSOC) ?: []);
        
        // Construct a query based on available columns
        if (in_array('customer_name', $orderColumns)) {
            // If there's a direct customer_name field
            $recentOrdersQuery = "SELECT o.*, DATE_FORMAT(o.created_at, '%Y-%m-%d') AS formatted_date 
                                 FROM orders o 
                                 ORDER BY o.created_at DESC 
                                 LIMIT 5";
        } else if (in_array('customer_id', $orderColumns)) {
            // If orders reference customers by ID
            $recentOrdersQuery = "SELECT o.*, c.first_name, c.last_name, 
                                 CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                                 DATE_FORMAT(o.created_at, '%Y-%m-%d') AS formatted_date 
                                 FROM orders o 
                                 LEFT JOIN customers c ON o.customer_id = c.id 
                                 ORDER BY o.created_at DESC 
                                 LIMIT 5";
        } else {
            // Fallback query
            $recentOrdersQuery = "SELECT *, DATE_FORMAT(created_at, '%Y-%m-%d') AS formatted_date 
                                 FROM orders 
                                 ORDER BY created_at DESC 
                                 LIMIT 5";
        }
        
        $stmt = $db->query($recentOrdersQuery);
        if ($stmt) {
            $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    // Get top products
    // Check if order_items table exists and has structure we need
    $topProductsQuery = "SELECT p.*, 
                        (SELECT COUNT(*) FROM order_items oi WHERE oi.product_id = p.id) as order_count,
                        c.name as category_name
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        ORDER BY order_count DESC, p.id DESC
                        LIMIT 5";
    
    $stmt = $db->query($topProductsQuery);
    if ($stmt) {
        $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get sales by month for the chart - use 'total' column
    if (in_array('created_at', $orderColumns)) {
        try {
            $stmt = $db->query("SELECT 
                                DATE_FORMAT(created_at, '%Y-%m') as month, 
                                COALESCE(SUM(total), 0) as total 
                                FROM orders 
                                WHERE status IN ('completed', 'paid') 
                                GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                                ORDER BY month ASC 
                                LIMIT 6");
            
            if ($stmt) {
                $monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // If we have data, use it
                if (!empty($monthlyData)) {
                    foreach ($monthlyData as $data) {
                        $months[] = date('M Y', strtotime($data['month'] . '-01'));
                        $sales[] = floatval($data['total']);
                    }
                }
            }
        } catch (Exception $e) {
            // If any error occurs in date processing, use sample data
            error_log("Error in sales chart query: " . $e->getMessage());
            $months = [];
            $sales = [];
        }
    }
    
    // If no sales data is available, create sample data
    if (empty($months)) {
        for ($i = 5; $i >= 0; $i--) {
            $months[] = date('M Y', strtotime("-$i months"));
            $sales[] = 0;
        }
    }
    
} catch (PDOException $e) {
    // Log error
    error_log("Dashboard error: " . $e->getMessage());
}
?>

<div class="container">
  <div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
      <div>
        <h3 class="fw-bold mb-3">Dashboard</h3>
      </div>
      <div class="ms-md-auto py-2 py-md-0">
        <a href="index.php?p=Order" class="btn btn-label-info btn-round me-2">View Orders</a>
        <a href="index.php?p=Customer" class="btn btn-primary btn-round">Add Customer</a>
      </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row">
      <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-icon">
                <div class="icon-big text-center icon-primary bubble-shadow-small">
                  <i class="fas fa-users"></i>
                </div>
              </div>
              <div class="col col-stats ms-3 ms-sm-0">
                <div class="numbers">
                  <p class="card-category">Customers</p>
                  <h4 class="card-title"><?php echo number_format($customersCount); ?></h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-icon">
                <div class="icon-big text-center icon-info bubble-shadow-small">
                  <i class="fas fa-box"></i>
                </div>
              </div>
              <div class="col col-stats ms-3 ms-sm-0">
                <div class="numbers">
                  <p class="card-category">Products</p>
                  <h4 class="card-title"><?php echo number_format($productsCount); ?></h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-icon">
                <div class="icon-big text-center icon-success bubble-shadow-small">
                  <i class="fas fa-dollar-sign"></i>
                </div>
              </div>
              <div class="col col-stats ms-3 ms-sm-0">
                <div class="numbers">
                  <p class="card-category">Total Amount</p>
                  <h4 class="card-title">$<?php echo number_format($totalSales, 2); ?></h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-icon">
                <div class="icon-big text-center icon-secondary bubble-shadow-small">
                  <i class="far fa-check-circle"></i>
                </div>
              </div>
              <div class="col col-stats ms-3 ms-sm-0">
                <div class="numbers">
                  <p class="card-category">Orders</p>
                  <h4 class="card-title"><?php echo number_format($ordersCount); ?></h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row">
      <div class="col-md-8">
        <div class="card card-round">
          <div class="card-header">
            <div class="card-head-row">
              <div class="card-title">Total Amount Statistics</div>
              <div class="card-tools">
                <a href="#" class="btn btn-label-success btn-round btn-sm me-2">
                  <span class="btn-label">
                    <i class="fa fa-download"></i>
                  </span>
                  Export
                </a>
                <a href="#" class="btn btn-label-info btn-round btn-sm">
                  <span class="btn-label">
                    <i class="fa fa-print"></i>
                  </span>
                  Print
                </a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="chart-container" style="min-height: 375px">
              <canvas id="salesChart"></canvas>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-primary card-round">
          <div class="card-header">
            <div class="card-head-row">
              <div class="card-title">Categories</div>
              <div class="card-tools">
                <a href="index.php?p=Category" class="btn btn-sm btn-label-light">
                  View All
                </a>
              </div>
            </div>
          </div>
          <div class="card-body pb-0">
            <div class="mb-4 mt-2">
              <h1><?php echo number_format($categoriesCount); ?></h1>
              <p>Total Categories</p>
            </div>
            <div class="pull-in">
              <canvas id="categoriesChart"></canvas>
            </div>
          </div>
        </div>
        <div class="card card-round">
          <div class="card-body pb-0">
            <?php 
            $ordersPerCustomer = ($customersCount > 0) ? round(($ordersCount / $customersCount) * 100) : 0;
            ?>
            <div class="h1 fw-bold float-end text-primary"><?php echo $ordersPerCustomer; ?>%</div>
            <h2 class="mb-2"><?php echo number_format($ordersCount); ?></h2>
            <p class="text-muted">Total Orders</p>
            <div class="pull-in sparkline-fix">
              <div id="lineChart"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Recent Orders and Top Products -->
    <div class="row">
      <div class="col-md-8">
        <div class="card card-round">
          <div class="card-header">
            <div class="card-head-row card-tools-still-right">
              <div class="card-title">Recent Orders</div>
              <div class="card-tools">
                <a href="index.php?p=Order" class="btn btn-sm btn-label-light">
                  View All
                </a>
              </div>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table align-items-center mb-0">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Order ID</th>
                    <th scope="col">Customer</th>
                    <th scope="col" class="text-end">Date</th>
                    <th scope="col" class="text-end">Amount</th>
                    <th scope="col" class="text-end">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($recentOrders)): ?>
                    <tr>
                      <td colspan="5" class="text-center">No orders found</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($recentOrders as $order): ?>
                      <tr>
                        <th scope="row">
                          <a href="index.php?p=Order&id=<?php echo htmlspecialchars($order['id'] ?? 0); ?>" class="text-primary">
                            #<?php echo htmlspecialchars($order['id'] ?? 0); ?>
                          </a>
                        </th>
                        <td>
                          <?php 
                          if (isset($order['customer_name'])) {
                              echo htmlspecialchars($order['customer_name']); 
                          } elseif (isset($order['first_name']) && isset($order['last_name'])) {
                              echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']);
                          } else {
                              echo 'Unknown';
                          }
                          ?>
                        </td>
                        <td class="text-end">
                          <?php 
                          if (!empty($order['formatted_date'])) {
                              echo date('M d, Y', strtotime($order['formatted_date']));
                          } elseif (!empty($order['created_at'])) {
                              echo date('M d, Y', strtotime($order['created_at']));
                          } else {
                              echo 'N/A';
                          }
                          ?>
                        </td>
                        <td class="text-end">
                          <?php if (isset($order['total_amount'])): ?>
                            $<?php echo number_format($order['total_amount'] ?? 0, 2); ?>
                          <?php elseif (isset($order['total'])): ?>
                            $<?php echo number_format($order['total'] ?? 0, 2); ?>
                          <?php else: ?>
                            $0.00
                          <?php endif; ?>
                        </td>
                        <td class="text-end">
                          <?php 
                          $status = $order['status'] ?? 'pending';
                          $statusClass = 'secondary';
                          if ($status == 'completed') $statusClass = 'success';
                          if ($status == 'pending') $statusClass = 'warning';
                          if ($status == 'cancelled') $statusClass = 'danger';
                          ?>
                          <span class="badge bg-<?php echo $statusClass; ?>"><?php echo ucfirst($status); ?></span>
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
      <div class="col-md-4">
        <div class="card card-round">
          <div class="card-header">
            <div class="card-head-row card-tools-still-right">
              <div class="card-title">Top Products</div>
              <div class="card-tools">
                <a href="index.php?p=Product" class="btn btn-sm btn-label-light">
                  View All
                </a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="card-list py-4">
              <?php if (empty($topProducts)): ?>
                <p class="text-center">No products found</p>
              <?php else: ?>
                <?php foreach ($topProducts as $product): ?>
                  <div class="item-list">
                    <div class="avatar">
                      <?php
                      $imagePath = '';
                      
                      // Check different possible image path formats
                      if (!empty($product['image'])) {
                          $imagePath = $product['image'];
                          // Ensure the image path exists
                          if (!file_exists($imagePath) && strpos($imagePath, '../') === 0) {
                              // Try without the ../ prefix
                              $altPath = substr($imagePath, 3);
                              if (file_exists($altPath)) {
                                  $imagePath = $altPath;
                              }
                          } elseif (!file_exists($imagePath) && strpos($imagePath, '/') === 0) {
                              // Try with a relative path
                              $altPath = substr($imagePath, 1);
                              if (file_exists($altPath)) {
                                  $imagePath = $altPath;
                              }
                          }
                      }
                      
                      if (!empty($imagePath) && file_exists($imagePath)):
                      ?>
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                             alt="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" 
                             class="avatar-img rounded-circle">
                      <?php else: ?>
                        <span class="avatar-title rounded-circle border border-white bg-primary">
                          <?php echo substr(htmlspecialchars($product['name'] ?? 'P'), 0, 1); ?>
                        </span>
                      <?php endif; ?>
                    </div>
                    <div class="info-user ms-3">
                      <div class="username"><?php echo htmlspecialchars($product['name'] ?? 'Unknown Product'); ?></div>
                      <div class="status">
                        <?php if (isset($product['category_name']) && !empty($product['category_name'])): ?>
                          <span class="text-muted"><?php echo htmlspecialchars($product['category_name']); ?> • </span>
                        <?php endif; ?>
                        <?php echo isset($product['order_count']) ? intval($product['order_count']) : 0; ?> orders
                      </div>
                    </div>
                    <div class="ms-auto">
                      <?php if (isset($product['price'])): ?>
                        <span class="badge bg-primary">$<?php echo number_format($product['price'] ?? 0, 2); ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Chart Initialization Scripts -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Sales Chart
    var salesCtx = document.getElementById('salesChart').getContext('2d');
    var salesChart = new Chart(salesCtx, {
      type: 'line',
      data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
          label: 'Monthly Total Amount',
          data: <?php echo json_encode($sales); ?>,
          backgroundColor: 'rgba(0, 123, 255, 0.1)',
          borderColor: 'rgba(0, 123, 255, 1)',
          borderWidth: 2,
          pointBackgroundColor: 'rgba(0, 123, 255, 1)',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          pointRadius: 4,
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              drawBorder: false
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        }
      }
    });
    
    // Categories Chart (Doughnut)
    var categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
    var categoriesChart = new Chart(categoriesCtx, {
      type: 'doughnut',
      data: {
        labels: ['Categories'],
        datasets: [{
          data: [<?php echo max(1, $categoriesCount); ?>, 100 - <?php echo max(1, $categoriesCount); ?>],
          backgroundColor: [
            'rgba(0, 123, 255, 0.8)',
            'rgba(0, 0, 0, 0.05)'
          ],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '80%',
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });
    
    // Line Chart (Sparkline)
    var lineCtx = document.getElementById('lineChart').getContext('2d');
    var lineChart = new Chart(lineCtx, {
      type: 'line',
      data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
          data: <?php echo json_encode($sales); ?>,
          borderColor: 'rgba(0, 123, 255, 1)',
          borderWidth: 2,
          pointRadius: 0,
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            display: false
          },
          x: {
            display: false
          }
        }
      }
    });
  });
</script>
