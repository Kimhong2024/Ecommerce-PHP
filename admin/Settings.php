<div class="container">
  <div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
      <div>
        <h3 class="fw-bold mb-3">System Settings</h3>
      </div>
    </div>

    <!-- Settings Navigation -->
    <div class="row">
      <div class="col-md-3">
        <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist">
          <a class="nav-link active" id="general-tab" data-toggle="pill" href="#general">General</a>
          <a class="nav-link" id="email-tab" data-toggle="pill" href="#email">Email</a>
          <a class="nav-link" id="payments-tab" data-toggle="pill" href="#payments">Payments</a>
          <a class="nav-link" id="security-tab" data-toggle="pill" href="#security">Security</a>
        </div>
      </div>
      
      <!-- Settings Content -->
      <div class="col-md-9">
        <div class="tab-content" id="v-pills-tabContent">
          
          <!-- General Settings -->
          <div class="tab-pane fade show active" id="general">
            <div class="card card-round">
              <div class="card-body">
                <form id="generalSettingsForm">
                  <div class="form-group">
                    <label>Site Name</label>
                    <input type="text" class="form-control" name="site_name" required>
                  </div>
                  <div class="form-group">
                    <label>Site Logo</label>
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" id="siteLogo" name="site_logo">
                      <label class="custom-file-label" for="siteLogo">Choose file</label>
                    </div>
                    <div id="logoPreview" class="mt-2"></div>
                  </div>
                  <div class="form-group">
                    <label>Default Currency</label>
                    <select class="form-control" name="currency" required>
                      <option value="USD">US Dollar ($)</option>
                      <option value="EUR">Euro (€)</option>
                      <option value="GBP">British Pound (£)</option>
                    </select>
                  </div>
                  <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
              </div>
            </div>
          </div>

          <!-- Email Settings -->
          <div class="tab-pane fade" id="email">
            <div class="card card-round">
              <div class="card-body">
                <form id="emailSettingsForm">
                  <div class="form-group">
                    <label>SMTP Host</label>
                    <input type="text" class="form-control" name="smtp_host" required>
                  </div>
                  <div class="form-group">
                    <label>SMTP Port</label>
                    <input type="number" class="form-control" name="smtp_port" required>
                  </div>
                  <div class="form-group">
                    <label>SMTP Username</label>
                    <input type="text" class="form-control" name="smtp_user" required>
                  </div>
                  <div class="form-group">
                    <label>SMTP Password</label>
                    <input type="password" class="form-control" name="smtp_pass" required>
                  </div>
                  <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
              </div>
            </div>
          </div>

          <!-- Payment Settings -->
          <div class="tab-pane fade" id="payments">
            <div class="card card-round">
              <div class="card-body">
                <form id="paymentSettingsForm">
                  <div class="form-group">
                    <label>Stripe Publishable Key</label>
                    <input type="text" class="form-control" name="stripe_key">
                  </div>
                  <div class="form-group">
                    <label>Stripe Secret Key</label>
                    <input type="password" class="form-control" name="stripe_secret">
                  </div>
                  <div class="form-group">
                    <label>PayPal Client ID</label>
                    <input type="text" class="form-control" name="paypal_id">
                  </div>
                  <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
              </div>
            </div>
          </div>

          <!-- Security Settings -->
          <div class="tab-pane fade" id="security">
            <div class="card card-round">
              <div class="card-body">
                <form id="securitySettingsForm">
                  <div class="form-group">
                    <label>Password Reset Timeout (minutes)</label>
                    <input type="number" class="form-control" name="password_timeout" required>
                  </div>
                  <div class="form-group">
                    <label>Max Login Attempts</label>
                    <input type="number" class="form-control" name="max_attempts" required>
                  </div>
                  <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>