$(document).ready(function () {
    // Load initial roles
    loadRoles();
  
    // Add Role Button Click
    $('#addRoleBtn').click(function () {
      $('#roleModalLabel').text('Add Role');
      $('#roleForm')[0].reset();
      $('#roleId').val('');
      $('#roleModal').modal('show');
    });
  
    // Save Role (Add/Update)
    $('#saveRoleBtn').click(function () {
      const roleData = {
        id: $('#roleId').val(),
        name: $('#roleName').val(),
        description: $('#roleDescription').val()
      };
  
      const url = roleData.id ? 'api/update_role.php' : 'api/add_role.php';
      const method = roleData.id ? 'PUT' : 'POST';
  
      $.ajax({
        url: url,
        method: method,
        data: roleData,
        success: function (response) {
          $('#roleModal').modal('hide');
          loadRoles();
        },
        error: function (xhr, status, error) {
          alert('Error saving role: ' + error);
        }
      });
    });
  
    // Edit Role
    $(document).on('click', '.editRoleBtn', function () {
      const roleId = $(this).data('id');
      $.ajax({
        url: 'api/get_role.php?id=' + roleId,
        method: 'GET',
        success: function (response) {
          const role = JSON.parse(response);
          $('#roleModalLabel').text('Edit Role');
          $('#roleId').val(role.id);
          $('#roleName').val(role.name);
          $('#roleDescription').val(role.description);
          $('#roleModal').modal('show');
        }
      });
    });
  
    // Delete Role
    $(document).on('click', '.deleteRoleBtn', function () {
      const roleId = $(this).data('id');
      if (confirm('Are you sure you want to delete this role?')) {
        $.ajax({
          url: 'api/delete_role.php',
          method: 'DELETE',
          data: { id: roleId },
          success: function () {
            loadRoles();
          },
          error: function (xhr, status, error) {
            alert('Error deleting role: ' + error);
          }
        });
      }
    });
  
    // Search Roles
    $('#searchRole').on('input', debounce(function () {
      const searchTerm = $(this).val();
      $.ajax({
        url: 'api/search_roles.php',
        method: 'GET',
        data: { search: searchTerm },
        success: function (response) {
          $('#rolesTable tbody').html(response);
        }
      });
    }, 300));
  
    // Load Roles Function
    function loadRoles() {
      $.ajax({
        url: 'api/get_roles.php',
        method: 'GET',
        success: function (response) {
          $('#rolesTable tbody').html(response);
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