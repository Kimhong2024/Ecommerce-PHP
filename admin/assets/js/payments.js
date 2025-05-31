$(document).ready(function () {
    // Load initial payments
    loadPayments();
  
    // Add Payment Button Click
    $('#addPaymentBtn').click(function () {
      $('#paymentModalLabel').text('Add Payment');
      $('#paymentForm')[0].reset();
      $('#paymentId').val('');
      $('#paymentModal').modal('show');
    });
  
    // Save Payment (Add/Update)
    $('#savePaymentBtn').click(function () {
      const paymentData = {
        id: $('#paymentId').val(),
        order_id: $('#orderId').val(),
        amount: $('#paymentAmount').val(),
        method: $('#paymentMethod').val(),
        payment_date: $('#paymentDate').val(),
        status: $('#paymentStatus').val()
      };
  
      const url = paymentData.id ? 'api/update_payment.php' : 'api/add_payment.php';
      const method = paymentData.id ? 'PUT' : 'POST';
  
      $.ajax({
        url: url,
        method: method,
        data: paymentData,
        success: function (response) {
          $('#paymentModal').modal('hide');
          loadPayments();
        },
        error: function (xhr, status, error) {
          alert('Error saving payment: ' + error);
        }
      });
    });
  
    // Edit Payment
    $(document).on('click', '.editPaymentBtn', function () {
      const paymentId = $(this).data('id');
      $.ajax({
        url: 'api/get_payment.php?id=' + paymentId,
        method: 'GET',
        success: function (response) {
          const payment = JSON.parse(response);
          $('#paymentModalLabel').text('Edit Payment');
          $('#paymentId').val(payment.id);
          $('#orderId').val(payment.order_id);
          $('#paymentAmount').val(payment.amount);
          $('#paymentMethod').val(payment.method);
          $('#paymentDate').val(payment.payment_date);
          $('#paymentStatus').val(payment.status);
          $('#paymentModal').modal('show');
        }
      });
    });
  
    // Delete Payment
    $(document).on('click', '.deletePaymentBtn', function () {
      const paymentId = $(this).data('id');
      if (confirm('Are you sure you want to delete this payment?')) {
        $.ajax({
          url: 'api/delete_payment.php',
          method: 'DELETE',
          data: { id: paymentId },
          success: function () {
            loadPayments();
          },
          error: function (xhr, status, error) {
            alert('Error deleting payment: ' + error);
          }
        });
      }
    });
  
    // Search Payments
    $('#searchPayment').on('input', debounce(function () {
      const searchTerm = $(this).val();
      $.ajax({
        url: 'api/search_payments.php',
        method: 'GET',
        data: { search: searchTerm },
        success: function (response) {
          $('#paymentsTable tbody').html(response);
        }
      });
    }, 300));
  
    // Load Payments Function
    function loadPayments() {
      $.ajax({
        url: 'api/get_payments.php',
        method: 'GET',
        success: function (response) {
          $('#paymentsTable tbody').html(response);
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