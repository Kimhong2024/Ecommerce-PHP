<div class="container">
  <div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
      <div>
        <h3 class="fw-bold mb-3">User Role Management</h3>
      </div>
      <div class="ms-md-auto py-2 py-md-0">
        <button id="addRoleBtn" class="btn btn-primary btn-round">
          <i class="fas fa-plus"></i> Add Role
        </button>
      </div>
    </div>

    <!-- Roles Table -->
    <div class="row">
      <div class="col-md-12">
        <div class="card card-round">
          <div class="card-header">
            <div class="card-head-row">
              <div class="card-title">Roles List</div>
              <div class="card-tools">
                <input type="text" id="searchRole" class="form-control" placeholder="Search roles...">
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover" id="rolesTable">
                <thead>
                  <tr>
                    <th>Role ID</th>
                    <th>Role Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Roles rows populated here -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add/Edit Role Modal -->
<div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="roleModalLabel">Add Role</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="roleForm">
          <input type="hidden" id="roleId">
          <div class="form-group">
            <label for="roleName">Role Name</label>
            <input type="text" class="form-control" id="roleName" required>
          </div>
          <div class="form-group">
            <label for="roleDescription">Description</label>
            <textarea class="form-control" id="roleDescription" rows="3" required></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="saveRoleBtn">Save</button>
      </div>
    </div>
  </div>
</div>