$(document).ready(function () {
    // Load settings when page loads
    loadSettings();
  
    // Handle General Settings form
    $('#generalSettingsForm').submit(function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      
      $.ajax({
        url: 'api/save_general_settings.php',
        method: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          showAlert('General settings saved successfully!', 'success');
        },
        error: function (xhr, status, error) {
          showAlert('Error saving general settings: ' + error, 'danger');
        }
      });
    });
  
    // Handle Email Settings form
    $('#emailSettingsForm').submit(function (e) {
      e.preventDefault();
      const formData = $(this).serialize();
  
      $.ajax({
        url: 'api/save_email_settings.php',
        method: 'POST',
        data: formData,
        success: function (response) {
          showAlert('Email settings saved successfully!', 'success');
        },
        error: function (xhr, status, error) {
          showAlert('Error saving email settings: ' + error, 'danger');
        }
      });
    });
  
    // Handle Payment Settings form
    $('#paymentSettingsForm').submit(function (e) {
      e.preventDefault();
      const formData = $(this).serialize();
  
      $.ajax({
        url: 'api/save_payment_settings.php',
        method: 'POST',
        data: formData,
        success: function (response) {
          showAlert('Payment settings saved successfully!', 'success');
        },
        error: function (xhr, status, error) {
          showAlert('Error saving payment settings: ' + error, 'danger');
        }
      });
    });
  
    // Handle Security Settings form
    $('#securitySettingsForm').submit(function (e) {
      e.preventDefault();
      const formData = $(this).serialize();
  
      $.ajax({
        url: 'api/save_security_settings.php',
        method: 'POST',
        data: formData,
        success: function (response) {
          showAlert('Security settings saved successfully!', 'success');
        },
        error: function (xhr, status, error) {
          showAlert('Error saving security settings: ' + error, 'danger');
        }
      });
    });
  
    // File upload preview
    $('#siteLogo').change(function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
          $('#logoPreview').html(`<img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px;">`);
        }
        reader.readAsDataURL(file);
      }
    });
  
    // Load all settings
    function loadSettings() {
      $.ajax({
        url: 'api/get_settings.php',
        method: 'GET',
        success: function (response) {
          const settings = JSON.parse(response);
          
          // General Settings
          $('#generalSettingsForm [name="site_name"]').val(settings.general.site_name);
          $('#generalSettingsForm [name="currency"]').val(settings.general.currency);
          if (settings.general.site_logo) {
            $('#logoPreview').html(`<img src="${settings.general.site_logo}" class="img-thumbnail" style="max-width: 200px;">`);
          }
  
          // Email Settings
          $('#emailSettingsForm [name="smtp_host"]').val(settings.email.smtp_host);
          $('#emailSettingsForm [name="smtp_port"]').val(settings.email.smtp_port);
          $('#emailSettingsForm [name="smtp_user"]').val(settings.email.smtp_user);
  
          // Payment Settings
          $('#paymentSettingsForm [name="stripe_key"]').val(settings.payments.stripe_key);
          $('#paymentSettingsForm [name="paypal_id"]').val(settings.payments.paypal_id);
  
          // Security Settings
          $('#securitySettingsForm [name="password_timeout"]').val(settings.security.password_timeout);
          $('#securitySettingsForm [name="max_attempts"]').val(settings.security.max_attempts);
        }
      });
    }
  
    // Show alert message
    function showAlert(message, type) {
      const alert = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                      ${message}
                      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>`;
      $('.page-inner').prepend(alert);
      setTimeout(() => $('.alert').alert('close'), 3000);
    }
  });