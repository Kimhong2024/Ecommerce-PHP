<?php
        session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: ./auth/login.php');
            exit();
        }

           $page = "Dashboard.php"; // Default page
           $p = "Dashboard"; // Default value for $p
   
           if(isset($_GET['p'])){
               $p = $_GET['p'];
               switch($p){
                   case 'Product':
                       $page = "Product.php";
                       break;
                   case 'Order':
                       $page = "Order.php";
                       break;
                   case 'Customer':
                       $page = "Customer.php";
                       break;
                   case 'Category':
                       $page = "Category.php";
                       break;
                   case 'Slider':
                       $page = "Slider.php";
                       break;
                   case 'Invoice':
                       $page = "Invoice.php";
                       // Check if we need to redirect back after actions
                       if (isset($_GET['view']) && $_GET['view'] === 'list') {
                           unset($_GET['view']);
                       }
                       break;
                   case 'Shipping':
                       $page = "Shipping.php";
                       break;
                   case 'Banner':
                       $page = "Banner.php";
                       break;
                   case 'UserRoles':
                       $page = "UserRoles.php";
                       break;
                   case 'Settings':
                       $page = "Settings.php";
                       break;
                   case 'MyProfile':
                       $page = "MyProfile.php";
                       break;
                   default:
                       $page = "Dashboard.php";
                       break;
               }
           }

?>

<!DOCTYPE html>
<html lang="en">
 
 <?php include 'include/head.php'; ?>

  <body>
    <div class="wrapper">
      <!-- Sidebar -->
       <?php include 'include/sidebar.php'; ?>
      <!-- End Sidebar -->

      <div class="main-panel">
        <?php include 'include/header.php'; ?>

        <?php include "$page" ?>

       <?php include 'include/foot.php'; ?>
      </div>
    </div>
    <?php include 'include/footer.php'; ?>
    <script src="assets/js/customer.js"></script>
    <script src="assets/js/product.js"></script>
    <script src="assets/js/order.js"></script>
    <script src="assets/js/salesreport.js"></script>
    <script src="assets/js/coupons.js"></script>
    <script src="assets/js/payments.js"></script>
    <script src="assets/js/reviews.js"></script>
    <script src="assets/js/userroles.js"></script>
    <script src="assets/js/settings.js"></script>
  </body>
</html>
