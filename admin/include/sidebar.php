<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
            <a href="index.php" class="logo">
                <img src="assets/img/kaiadmin/logo_light.svg" alt="navbar brand" class="navbar-brand" height="20" />
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
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">
                <li class="nav-item <?=($p=="Dashboard"?'active':'')?>">
                    <a class="menu-link" href="index.php">
                        <i class="fas fa-home"></i>
                        <p data-i18n="Dashboards">Dashboard</p>
                    </a>
                </li>

                <!-- Shop -->
                <li class="nav-item <?=($p=="Product"?'active':'')?>">
                    <a class="menu-link" href="index.php?p=Product">
                        <i class="fas fa-box"></i>
                        <p data-i18n="Product">Product</p>
                    </a>
                </li>
                <li class="nav-item <?=($p=="Category"?'active':'')?>">
                    <a class="menu-link" href="index.php?p=Category">
                        <i class="fas fa-chart-line"></i>
                        <p data-i18n="Category">Category</p>
                    </a>
                </li>
                <li class="nav-item <?=($p=="Order"?'active':'')?>">
                    <a class="menu-link" href="index.php?p=Order">
                        <i class="fas fa-shopping-cart"></i>
                        <p data-i18n="Order">Order</p>
                    </a>
                </li>
                <li class="nav-item <?=($p=="Customer"?'active':'')?>">
                    <a class="menu-link" href="index.php?p=Customer">
                        <i class="fas fa-users"></i>
                        <p data-i18n="Customer">Customer</p>
                    </a>
                </li>
                
                <li class="nav-item <?=($p=="Slider"?'active':'')?>">
                    <a class="menu-link" href="index.php?p=Slider">
                        <i class="fas fa-tags"></i>
                        <p data-i18n="Slider">Slider</p>
                    </a>
                </li>
                <li class="nav-item <?=($p=="Invoice"?'active':'')?>">
                    <a class="menu-link" href="index.php?p=Invoice">
                        <i class="fas fa-credit-card"></i>
                        <p data-i18n="Invoice">Invoice</p>
                    </a>
                </li>
               
                <li class="nav-item <?=($p=="Banner"?'active':'')?>">
                    <a class="menu-link" href="index.php?p=Banner">
                        <i class="fas fa-star"></i>
                        <p data-i18n="Banner">Banner</p>
                    </a>
                </li>
                <li class="nav-item <?=($p=="MyProfile"?'active':'')?>">
                    <a class="menu-link" href="index.php?p=MyProfile">
                        <i class="fas fa-user-shield"></i>
                        <p data-i18n="MyProfile">MyProfile</p>
                    </a>
                </li>
                <li class="nav-item <?=($p=="Settings"?'active':'')?>">
                    <a class="menu-link" href="index.php?p=Settings">
                        <i class="fas fa-cog"></i>
                        <p data-i18n="Settings">Settings</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>