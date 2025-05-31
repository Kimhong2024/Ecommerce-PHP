$(document).ready(function () {
    // Load initial customers
    loadCustomers();
  
    // Add Customer Button Click
    $('#addCustomerBtn').click(function () {
      $('#customerModalLabel').text('Add Customer');
      $('#customerForm')[0].reset();
      $('#customerId').val('');
      $('#customerModal').modal('show');
    });
  
    // Save Customer (Add/Update)
    $('#saveCustomerBtn').click(function () {
      const customerData = {
        id: $('#customerId').val(),
        name: $('#customerName').val(),
        email: $('#customerEmail').val(),
        phone: $('#customerPhone').val(),
        address: $('#customerAddress').val()
      };
  
      const url = customerData.id ? 'api/update_customer.php' : 'api/add_customer.php';
      const method = customerData.id ? 'PUT' : 'POST';
  
      $.ajax({
        url: url,
        method: method,
        data: customerData,
        success: function (response) {
          $('#customerModal').modal('hide');
          loadCustomers();
        },
        error: function (xhr, status, error) {
          alert('Error saving customer: ' + error);
        }
      });
    });
  
    // Edit Customer
    $(document).on('click', '.editCustomerBtn', function () {
      const customerId = $(this).data('id');
      $.ajax({
        url: 'api/get_customer.php?id=' + customerId,
        method: 'GET',
        success: function (response) {
          const customer = JSON.parse(response);
          $('#customerModalLabel').text('Edit Customer');
          $('#customerId').val(customer.id);
          $('#customerName').val(customer.name);
          $('#customerEmail').val(customer.email);
          $('#customerPhone').val(customer.phone);
          $('#customerAddress').val(customer.address);
          $('#customerModal').modal('show');
        }
      });
    });
  
    // Delete Customer
    $(document).on('click', '.deleteCustomerBtn', function () {
      const customerId = $(this).data('id');
      if (confirm('Are you sure you want to delete this customer?')) {
        $.ajax({
          url: 'api/delete_customer.php',
          method: 'DELETE',
          data: { id: customerId },
          success: function () {
            loadCustomers();
          },
          error: function (xhr, status, error) {
            alert('Error deleting customer: ' + error);
          }
        });
      }
    });
  
    // Search Customers
    $('#searchCustomer').on('input', function () {
      const searchTerm = $(this).val();
      $.ajax({
        url: 'api/search_customers.php',
        method: 'GET',
        data: { search: searchTerm },
        success: function (response) {
          $('#customerTable tbody').html(response);
        }
      });
    });
  
    // Load Customers Function
    function loadCustomers() {
      $.ajax({
        url: 'api/get_customers.php',
        method: 'GET',
        success: function (response) {
          $('#customerTable tbody').html(response);
        }
      });
    }
  });