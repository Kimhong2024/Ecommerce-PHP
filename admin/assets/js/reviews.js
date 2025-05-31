$(document).ready(function () {
    let products = [];
    let customers = [];
  
    // Load initial data
    loadProducts();
    loadCustomers();
    loadReviews();
  
    // Add Review Button Click
    $('#addReviewBtn').click(function () {
      $('#reviewModalLabel').text('Add Review');
      $('#reviewForm')[0].reset();
      $('#reviewId').val('');
      populateProductDropdown();
      populateCustomerDropdown();
      $('#reviewModal').modal('show');
    });
  
    // Save Review (Add/Update)
    $('#saveReviewBtn').click(function () {
      const reviewData = {
        id: $('#reviewId').val(),
        product_id: $('#productId').val(),
        customer_id: $('#customerId').val(),
        rating: $('#rating').val(),
        review: $('#reviewText').val(),
        review_date: $('#reviewDate').val(),
        status: $('#reviewStatus').val()
      };
  
      const url = reviewData.id ? 'api/update_review.php' : 'api/add_review.php';
      const method = reviewData.id ? 'PUT' : 'POST';
  
      $.ajax({
        url: url,
        method: method,
        data: reviewData,
        success: function (response) {
          $('#reviewModal').modal('hide');
          loadReviews();
        },
        error: function (xhr, status, error) {
          alert('Error saving review: ' + error);
        }
      });
    });
  
    // Edit Review
    $(document).on('click', '.editReviewBtn', function () {
      const reviewId = $(this).data('id');
      $.ajax({
        url: 'api/get_review.php?id=' + reviewId,
        method: 'GET',
        success: function (response) {
          const review = JSON.parse(response);
          $('#reviewModalLabel').text('Edit Review');
          $('#reviewId').val(review.id);
          $('#productId').val(review.product_id);
          $('#customerId').val(review.customer_id);
          $('#rating').val(review.rating);
          $('#reviewText').val(review.review);
          $('#reviewDate').val(review.review_date);
          $('#reviewStatus').val(review.status);
          populateProductDropdown();
          populateCustomerDropdown();
          $('#reviewModal').modal('show');
        }
      });
    });
  
    // Delete Review
    $(document).on('click', '.deleteReviewBtn', function () {
      const reviewId = $(this).data('id');
      if (confirm('Are you sure you want to delete this review?')) {
        $.ajax({
          url: 'api/delete_review.php',
          method: 'DELETE',
          data: { id: reviewId },
          success: function () {
            loadReviews();
          },
          error: function (xhr, status, error) {
            alert('Error deleting review: ' + error);
          }
        });
      }
    });
  
    // Search Reviews
    $('#searchReview').on('input', debounce(function () {
      const searchTerm = $(this).val();
      $.ajax({
        url: 'api/search_reviews.php',
        method: 'GET',
        data: { search: searchTerm },
        success: function (response) {
          $('#reviewsTable tbody').html(response);
        }
      });
    }, 300));
  
    // Load Reviews Function
    function loadReviews() {
      $.ajax({
        url: 'api/get_reviews.php',
        method: 'GET',
        success: function (response) {
          $('#reviewsTable tbody').html(response);
        }
      });
    }
  
    // Load Products Function
    function loadProducts() {
      $.ajax({
        url: 'api/get_products.php',
        method: 'GET',
        success: function (response) {
          products = JSON.parse(response);
        }
      });
    }
  
    // Load Customers Function
    function loadCustomers() {
      $.ajax({
        url: 'api/get_customers.php',
        method: 'GET',
        success: function (response) {
          customers = JSON.parse(response);
        }
      });
    }
  
    // Populate Product Dropdown
    function populateProductDropdown() {
      $('#productId').empty().append(
        products.map(p => $('<option>', {
          value: p.id,
          text: p.name
        }))
      );
    }
  
    // Populate Customer Dropdown
    function populateCustomerDropdown() {
      $('#customerId').empty().append(
        customers.map(c => $('<option>', {
          value: c.id,
          text: c.name
        }))
      );
    }
  
    // Debounce Function
    function debounce(func, wait) {
      let timeout;
      return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
      };
    }
  });