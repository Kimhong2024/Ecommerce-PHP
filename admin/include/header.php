<div class="main-header">
          <div class="main-header-logo">
            <!-- Logo Header -->
            <div class="logo-header" data-background-color="dark">
              <a href="index.php" class="logo">
                <img
                  src="assets/img/kaiadmin/logo_light.svg"
                  alt="navbar brand"
                  class="navbar-brand"
                  height="20"
                />
              </a>
              <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                  <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                  <i class="gg-menu-left"></i>
                </button>
              </div>
              <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
              </button>
            </div>
            <!-- End Logo Header -->
          </div>
          <!-- Navbar Header -->
          <nav
            class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom"
          >
            <div class="container-fluid">
              <nav
                class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex"
              >
                <div class="input-group">
                  <div class="input-group-prepend">
                    <button type="submit" class="btn btn-search pe-1">
                      <i class="fa fa-search search-icon"></i>
                    </button>
                  </div>
                  <input
                    type="text"
                    placeholder="Search ..."
                    class="form-control"
                  />
                </div>
              </nav>

              <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                <li
                  class="nav-item topbar-icon dropdown hidden-caret d-flex d-lg-none"
                >
                  <a
                    class="nav-link dropdown-toggle"
                    data-bs-toggle="dropdown"
                    href="#"
                    role="button"
                    aria-expanded="false"
                    aria-haspopup="true"
                  >
                    <i class="fa fa-search"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-search animated fadeIn">
                    <form class="navbar-left navbar-form nav-search">
                      <div class="input-group">
                        <input
                          type="text"
                          placeholder="Search ..."
                          class="form-control"
                        />
                      </div>
                    </form>
                  </ul>
                </li>
                <li class="nav-item topbar-icon dropdown hidden-caret">
                  <a
                    class="nav-link dropdown-toggle"
                    href="#"
                    id="messageDropdown"
                    role="button"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                  >
                    <i class="fa fa-envelope"></i>
                  </a>
                  <ul
                    class="dropdown-menu messages-notif-box animated fadeIn"
                    aria-labelledby="messageDropdown"
                  >
                    <li>
                      <div
                        class="dropdown-title d-flex justify-content-between align-items-center"
                      >
                        Messages
                        <a href="#" class="small">Mark all as read</a>
                      </div>
                    </li>
                    <li>
                      <div class="message-notif-scroll scrollbar-outer">
                        <div class="notif-center">
                          <a href="#">
                            <div class="notif-img">
                              <img
                                src="assets/img/jm_denis.jpg"
                                alt="Img Profile"
                              />
                            </div>
                            <div class="notif-content">
                              <span class="subject">Jimmy Denis</span>
                              <span class="block"> How are you ? </span>
                              <span class="time">5 minutes ago</span>
                            </div>
                          </a>
                          <a href="#">
                            <div class="notif-img">
                              <img
                                src="assets/img/chadengle.jpg"
                                alt="Img Profile"
                              />
                            </div>
                            <div class="notif-content">
                              <span class="subject">Chad</span>
                              <span class="block"> Ok, Thanks ! </span>
                              <span class="time">12 minutes ago</span>
                            </div>
                          </a>
                          <a href="#">
                            <div class="notif-img">
                              <img
                                src="assets/img/mlane.jpg"
                                alt="Img Profile"
                              />
                            </div>
                            <div class="notif-content">
                              <span class="subject">Jhon Doe</span>
                              <span class="block">
                                Ready for the meeting today...
                              </span>
                              <span class="time">12 minutes ago</span>
                            </div>
                          </a>
                          <a href="#">
                            <div class="notif-img">
                              <img
                                src="assets/img/talha.jpg"
                                alt="Img Profile"
                              />
                            </div>
                            <div class="notif-content">
                              <span class="subject">Talha</span>
                              <span class="block"> Hi, Apa Kabar ? </span>
                              <span class="time">17 minutes ago</span>
                            </div>
                          </a>
                        </div>
                      </div>
                    </li>
                    <li>
                      <a class="see-all" href="javascript:void(0);"
                        >See all messages<i class="fa fa-angle-right"></i>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="nav-item topbar-icon dropdown hidden-caret">
                  <a
                    class="nav-link dropdown-toggle"
                    href="#"
                    id="notifDropdown"
                    role="button"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                  >
                    <i class="fa fa-bell"></i>
                    <span class="notification">4</span>
                  </a>
                  <ul
                    class="dropdown-menu notif-box animated fadeIn"
                    aria-labelledby="notifDropdown"
                  >
                    <li>
                      <div class="dropdown-title">
                        You have 4 new notification
                      </div>
                    </li>
                    <li>
                      <div class="notif-scroll scrollbar-outer">
                        <div class="notif-center">
                          <a href="#">
                            <div class="notif-icon notif-primary">
                              <i class="fa fa-user-plus"></i>
                            </div>
                            <div class="notif-content">
                              <span class="block"> New user registered </span>
                              <span class="time">5 minutes ago</span>
                            </div>
                          </a>
                          <a href="#">
                            <div class="notif-icon notif-success">
                              <i class="fa fa-comment"></i>
                            </div>
                            <div class="notif-content">
                              <span class="block">
                                Rahmad commented on Admin
                              </span>
                              <span class="time">12 minutes ago</span>
                            </div>
                          </a>
                          <a href="#">
                            <div class="notif-img">
                              <img
                                src="assets/img/profile2.jpg"
                                alt="Img Profile"
                              />
                            </div>
                            <div class="notif-content">
                              <span class="block">
                                Reza send messages to you
                              </span>
                              <span class="time">12 minutes ago</span>
                            </div>
                          </a>
                          <a href="#">
                            <div class="notif-icon notif-danger">
                              <i class="fa fa-heart"></i>
                            </div>
                            <div class="notif-content">
                              <span class="block"> Farrah liked Admin </span>
                              <span class="time">17 minutes ago</span>
                            </div>
                          </a>
                        </div>
                      </div>
                    </li>
                    <li>
                      <a class="see-all" href="javascript:void(0);"
                        >See all notifications<i class="fa fa-angle-right"></i>
                      </a>
                    </li>
                  </ul>
                </li>
              

                <li class="nav-item topbar-user dropdown hidden-caret">
                  <!-- User Profile/Login -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="relative">
                    <button id="profileDropdown" class="flex items-center space-x-2 text-gray-500 hover:text-green-500 transition duration-300">
                        <div class="avatar-circle">
                        <i class="fas fa-user"></i>
                        </div>
                        <span class="ms-2 d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <i class="fas fa-chevron-down text-xs ms-1"></i>
                    </button>
                    <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                        <div class="px-4 py-3  ">
                            <p class="text-sm leading-5 text-gray-900 font-semibold">
                                <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </p>
                            <p class="text-xs leading-4 text-gray-500 mt-1 truncate">
                                <?php echo htmlspecialchars($_SESSION['email']); ?>
                            </p>
                        </div>
                        <div class="dropdown-menu-items">
                            <a href="index.php?p=MyProfile" class="menu-item">
                                <div class="menu-icon bg-blue-50">
                                    <i class="fas fa-user text-blue-500"></i>
                                </div>
                                <div class="menu-content">
                                    <span class="menu-title">My Profile</span>
                                    <span class="menu-description">View and edit your profile</span>
                                </div>
                            </a>
                            
                            <div class="menu-divider"></div>
                            
                            <a href="auth/logout.php" class="menu-item">
                                <div class="menu-icon bg-red-50">
                                    <i class="fas fa-sign-out-alt text-red-500"></i>
                                </div>
                                <div class="menu-content">
                                    <span class="menu-title">Logout</span>
                                    <span class="menu-description">Sign out of your account</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="relative">
                    <a href="auth/login.php" class="flex items-center space-x-2 text-gray-500 hover:text-green-500 transition duration-300">
                        <i class="fas fa-sign-in-alt"></i>
                        <span class="ms-1 d-none d-md-inline">Login</span>
                    </a>
                </div>
                <?php endif; ?>
                </li>

                <style>
                    /* Custom styles for the profile dropdown */
                    .avatar-circle {
                        width: 32px;
                        height: 32px;
                        background-color: #e2e8f0;
                     
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: #4a5568;
                    }
                    
                    .relative {
                        position: relative;
                    }
                    
                    .absolute {
                        position: absolute;
                    }
                    
                    .right-0 {
                        right: 0;
                    }
                    
                    .mt-2 {
                        margin-top: 0.5rem;
                    }
                    
                    .w-48 {
                        width: 12rem;
                    }
                    
                    .hidden {
                        display: none;
                    }
                    
                    .show {
                        display: block;
                    }
                    
                    .rounded-md {
                        border-radius: 0.375rem;
                    }
                    
                    .shadow-lg {
                        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                    }
                    
                    .z-50 {
                        z-index: 50;
                    }
                    
                    .py-1 {
                        padding-top: 0.25rem;
                        padding-bottom: 0.25rem;
                    }
                    
                    .px-4 {
                        padding-left: 1rem;
                        padding-right: 1rem;
                    }
                    
                    .py-2 {
                        padding-top: 0.5rem;
                        padding-bottom: 0.5rem;
                    }
                    
                    .py-3 {
                        padding-top: 0.75rem;
                        padding-bottom: 0.75rem;
                    }
                    
                    .border-b {
                        border-bottom-width: 1px;
                    }
                    
                    .border-gray-200 {
                        border-color: #edf2f7;
                    }
                    
                    .text-sm {
                        font-size: 0.875rem;
                    }
                    
                    .text-xs {
                        font-size: 0.75rem;
                    }
                    
                    .leading-5 {
                        line-height: 1.25rem;
                    }
                    
                    .leading-4 {
                        line-height: 1rem;
                    }
                    
                    .font-semibold {
                        font-weight: 600;
                    }
                    
                    .text-gray-900 {
                        color: #1a202c;
                    }
                    
                    .text-gray-500 {
                        color: #a0aec0;
                    }
                    
                    .text-gray-700 {
                        color: #4a5568;
                    }
                    
                    .text-blue-500 {
                        color: #4299e1;
                    }
                    
                    .text-red-500 {
                        color: #f56565;
                    }
                    
                    .mt-1 {
                        margin-top: 0.25rem;
                    }
                    
                    .truncate {
                        overflow: hidden;
                        text-overflow: ellipsis;
                        white-space: nowrap;
                    }
                    
                    .hover\:bg-gray-100:hover {
                        background-color: #f7fafc;
                    }
                    
                    .transition {
                        transition-property: background-color, border-color, color, fill, stroke;
                        transition-duration: 150ms;
                    }
                    
                    .duration-150 {
                        transition-duration: 150ms;
                    }
                    
                    .duration-300 {
                        transition-duration: 300ms;
                    }
                    
                    .flex {
                        display: flex;
                    }
                    
                    .items-center {
                        align-items: center;
                    }
                    
                    .space-x-2 > * + * {
                        margin-left: 0.5rem;
                    }
                    
                    .ms-1 {
                        margin-left: 0.25rem;
                    }
                    
                    .ms-2 {
                        margin-left: 0.5rem;
                    }
                    
                    .mr-2 {
                        margin-right: 0.5rem;
                    }
                    
                    /* Improved dropdown menu styles */
                    .dropdown-menu-items {
                        padding: 0.5rem 0;
                    }
                    
                    .menu-item {
                        display: flex;
                        align-items: center;
                        padding: 0.625rem 1rem;
                        transition: background-color 150ms;
                        text-decoration: none;
                    }
                    
                    .menu-item:hover {
                        background-color: #f7fafc;
                    }
                    
                    .menu-icon {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        width: 2rem;
                        height: 2rem;
                        border-radius: 0.375rem;
                        margin-right: 0.75rem;
                        flex-shrink: 0;
                    }
                    
                    .menu-content {
                        display: flex;
                        flex-direction: column;
                    }
                    
                    .menu-title {
                        font-size: 0.875rem;
                        font-weight: 500;
                        color: #1a202c;
                        line-height: 1.25rem;
                    }
                    
                    .menu-description {
                        font-size: 0.75rem;
                        color: #718096;
                        line-height: 1rem;
                    }
                    
                    .menu-divider {
                        height: 1px;
                        margin: 0.25rem 0;
                        background-color: #edf2f7;
                    }
                    
                    .bg-blue-50 {
                        background-color: #ebf8ff;
                    }
                    
                    .bg-red-50 {
                        background-color: #fff5f5;
                    }
                    
                    /* Responsive adjustments */
                    @media (max-width: 768px) {
                        .d-none {
                            display: none;
                        }
                        
                        .d-md-inline {
                            display: inline;
                        }
                        
                        .menu-description {
                            display: none;
                        }
                    }
                </style>
              </ul>
            </div>
          </nav>
          <!-- End Navbar -->
        </div>
