$(document).ready(function () {
    let products = [];
    let customers = [];
    
    // Initialize
    loadCustomers();
    loadProducts();
    loadOrders();
  
    // Open Order Modal
    $('#addOrderBtn').click(function () {
      resetOrderForm();
      $('#orderModalLabel').text('Create New Order');
      $('#orderModal').modal('show');
    });
  
    // Add Order Item
    $('#addOrderItem').click(function () {
      addOrderItemRow();
    });
  
    // Save Order
    $('#saveOrderBtn').click(function () {
      const orderData = {
        id: $('#orderId').val(),
        customer_id: $('#customerSelect').val(),
        status: $('#orderStatus').val(),
        items: getOrderItems()
      };
  
      const url = orderData.id ? 'api/update_order.php' : 'api/add_order.php';
      const method = orderData.id ? 'PUT' : 'POST';
  
      $.ajax({
        url: url,
        method: method,
        data: orderData,
        success: function (response) {
          $('#orderModal').modal('hide');
          loadOrders();
        },
        error: function (xhr, status, error) {
          alert('Error saving order: ' + error);
        }
      });
    });
  
    // Edit Order
    $(document).on('click', '.editOrderBtn', function () {
      const orderId = $(this).data('id');
      $.ajax({
        url: 'api/get_order.php?id=' + orderId,
        method: 'GET',
        success: function (response) {
          const order = JSON.parse(response);
          populateOrderForm(order);
          $('#orderModal').modal('show');
        }
      });
    });
  
    // Delete Order
    $(document).on('click', '.deleteOrderBtn', function () {
      const orderId = $(this).data('id');
      if (confirm('Are you sure you want to delete this order?')) {
        $.ajax({
          url: 'api/delete_order.php',
          method: 'DELETE',
          data: { id: orderId },
          success: function () {
            loadOrders();
          },
          error: function (xhr, status, error) {
            alert('Error deleting order: ' + error);
          }
        });
      }
    });
  
    // Search Orders
    $('#searchOrder').on('input', debounce(function () {
      const searchTerm = $(this).val();
      $.ajax({
        url: 'api/search_orders.php',
        method: 'GET',
        data: { search: searchTerm },
        success: function (response) {
          $('#orderTable tbody').html(response);
        }
      });
    }, 300));
  
    // Helper Functions
    function loadOrders() {
      $.ajax({
        url: 'api/get_orders.php',
        method: 'GET',
        success: function (response) {
          $('#orderTable tbody').html(response);
        }
      });
    }
  
    function loadCustomers() {
      $.ajax({
        url: 'api/get_customers.php',
        method: 'GET',
        success: function (response) {
          customers = JSON.parse(response);
          $('#customerSelect').empty().append(
            customers.map(c => $('<option>', {
              value: c.id,
              text: c.name
            }))
          );
        }
      });
    }
  
    function loadProducts() {
      $.ajax({
        url: 'api/get_products.php',
        method: 'GET',
        success: function (response) {
          products = JSON.parse(response);
        }
      });
    }
  
    function addOrderItemRow(product = null) {
      const row = `
        <tr>
          <td>
            <select class="form-control product-select">
              ${products.map(p => `<option value="${p.id}" ${product?.product_id === p.id ? 'selected' : ''}>${p.name}</option>`)}
            </select>
          </td>
          <td>
            <input type="number" class="form-control quantity" value="${product?.quantity || 1}" min="1">
          </td>
          <td>
            <input type="number" class="form-control price" value="${product?.price || ''}" step="0.01" readonly>
          </td>
          <td class="item-total">$${(product?.quantity * product?.price || 0).toFixed(2)}</td>
          <td>
            <button class="btn btn-sm btn-danger remove-item">
              <i class="fas fa-times"></i>
            </button>
          </td>
        </tr>`;
      $('#orderItemsBody').append(row);
      updateOrderTotal();
    }
  
    function getOrderItems() {
      return $('#orderItemsBody tr').map(function () {
        return {
          product_id: $(this).find('.product-select').val(),
          quantity: $(this).find('.quantity').val(),
          price: $(this).find('.price').val()
        };
      }).get();
    }
  
    function updateOrderTotal() {
      let total = 0;
      $('#orderItemsBody tr').each(function () {
        total += parseFloat($(this).find('.item-total').text().replace('$', ''));
      });
      $('#orderTotal').text('$' + total.toFixed(2));
    }
  
    function resetOrderForm() {
      $('#orderForm')[0].reset();
      $('#orderItemsBody').empty();
      $('#orderTotal').text('$0.00');
    }
  
    function populateOrderForm(order) {
      $('#orderId').val(order.id);
      $('#customerSelect').val(order.customer_id);
      $('#orderStatus').val(order.status);
      $('#orderItemsBody').empty();
      order.items.forEach(item => addOrderItemRow(item));
      updateOrderTotal();
    }
  
    // Event Delegation
    $(document).on('change', '.product-select', function () {
      const productId = $(this).val();
      const product = products.find(p => p.id == productId);
      $(this).closest('tr').find('.price').val(product.price);
      updateOrderTotal();
    });
  
    $(document).on('input', '.quantity', function () {
      const price = parseFloat($(this).closest('tr').find('.price').val());
      const quantity = parseFloat($(this).val());
      $(this).closest('tr').find('.item-total').text('$' + (price * quantity).toFixed(2));
      updateOrderTotal();
    });
  
    $(document).on('click', '.remove-item', function () {
      $(this).closest('tr').remove();
      updateOrderTotal();
    });
  
    // Debounce Function
    function debounce(func, wait) {
      let timeout;
      return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
      };
    }
  });