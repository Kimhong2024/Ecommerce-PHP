$(document).ready(function () {
    // Load initial coupons
    loadCoupons();
  
    // Add Coupon Button Click
    $('#addCouponBtn').click(function () {
      $('#couponModalLabel').text('Add Coupon');
      $('#couponForm')[0].reset();
      $('#couponId').val('');
      $('#couponModal').modal('show');
    });
  
    // Save Coupon (Add/Update)
    $('#saveCouponBtn').click(function () {
      const couponData = {
        id: $('#couponId').val(),
        code: $('#couponCode').val(),
        discount_value: $('#discountValue').val(),
        discount_type: $('#discountType').val(),
        start_date: $('#startDate').val(),
        end_date: $('#endDate').val(),
        status: $('#couponStatus').val()
      };
  
      const url = couponData.id ? 'api/update_coupon.php' : 'api/add_coupon.php';
      const method = couponData.id ? 'PUT' : 'POST';
  
      $.ajax({
        url: url,
        method: method,
        data: couponData,
        success: function (response) {
          $('#couponModal').modal('hide');
          loadCoupons();
        },
        error: function (xhr, status, error) {
          alert('Error saving coupon: ' + error);
        }
      });
    });
  
    // Edit Coupon
    $(document).on('click', '.editCouponBtn', function () {
      const couponId = $(this).data('id');
      $.ajax({
        url: 'api/get_coupon.php?id=' + couponId,
        method: 'GET',
        success: function (response) {
          const coupon = JSON.parse(response);
          $('#couponModalLabel').text('Edit Coupon');
          $('#couponId').val(coupon.id);
          $('#couponCode').val(coupon.code);
          $('#discountValue').val(coupon.discount_value);
          $('#discountType').val(coupon.discount_type);
          $('#startDate').val(coupon.start_date);
          $('#endDate').val(coupon.end_date);
          $('#couponStatus').val(coupon.status);
          $('#couponModal').modal('show');
        }
      });
    });
  
    // Delete Coupon
    $(document).on('click', '.deleteCouponBtn', function () {
      const couponId = $(this).data('id');
      if (confirm('Are you sure you want to delete this coupon?')) {
        $.ajax({
          url: 'api/delete_coupon.php',
          method: 'DELETE',
          data: { id: couponId },
          success: function () {
            loadCoupons();
          },
          error: function (xhr, status, error) {
            alert('Error deleting coupon: ' + error);
          }
        });
      }
    });
  
    // Search Coupons
    $('#searchCoupon').on('input', debounce(function () {
      const searchTerm = $(this).val();
      $.ajax({
        url: 'api/search_coupons.php',
        method: 'GET',
        data: { search: searchTerm },
        success: function (response) {
          $('#couponsTable tbody').html(response);
        }
      });
    }, 300));
  
    // Load Coupons Function
    function loadCoupons() {
      $.ajax({
        url: 'api/get_coupons.php',
        method: 'GET',
        success: function (response) {
          $('#couponsTable tbody').html(response);
        }
      });
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