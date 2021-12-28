<?php 
  $page = $this->router->fetch_class(); 
  $method = $this->router->fetch_method(); 
  $full = $page."/".$method;
?>
<!-- Main Sidebar Container -->
<aside class="main-sidebar elevation-4 sidebar-light bg-light">
    <!-- Brand Logo -->
    <a href="<?=base_url()?>" class="brand-link text-center">
      <!-- <span class="brand-text font-weight-light"><b>LITTLE</b>HATHEE</span> -->
      <img src="<?=base_url('uploads/logo.png')?>" class="w-75" alt="">
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <!-- <img src="<?=base_url()?>assets/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image"> -->
          <i class="p-2 fas fa-user img-circle"></i>
        </div>
        <div class="info">
          <a href="#" class="d-block"><?=$this->session->userdata('user_name')?></a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->
          <li class="nav-item">
            <a href="<?=base_url('Dashboard')?>" class="nav-link <?=($page == "Dashboard")?'active':''?>">
              <i class="fas fa-tachometer-alt nav-icon"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?=base_url('Categories')?>" class="nav-link <?=($page == "Categories")?'active':''?>">
            <i class="fas fa-sitemap nav-icon"></i>
              <p>Categories</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?=base_url('Investigations')?>" class="nav-link <?=($page == "Investigations")?'active':''?>">
            <i class="fas fa-vial nav-icon"></i>
              <p>Investigations</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?=base_url('Packages')?>" class="nav-link <?=($page == "Packages")?'active':''?>">
            <i class="fas fa-box nav-icon"></i>
              <p>Packages</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?=base_url('Orders')?>" class="nav-link <?=($page == "Orders")?'active':''?>">
            <i class="fas fa-tasks nav-icon"></i>
              <p>Orders</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?=base_url('Invoices')?>" class="nav-link <?=($page == "Invoices")?'active':''?>">
            <i class="fas fa-file-invoice nav-icon"></i>
              <p>Billings</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?=base_url('Users')?>" class="nav-link <?=($page == "Users")?'active':''?>">
              <i class="fas fa-users nav-icon"></i>
              <p>Customers</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?=base_url('CMS')?>" class="nav-link <?=($page == "CMS")?'active':''?>">
            <i class="fas fa-file-alt nav-icon"></i>
              <p>CMS</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?=base_url('Settings')?>" class="nav-link <?=($page == "Settings")?'active':''?>">
            <i class="fas fa-cogs nav-icon"></i>
              <p>Settings</p>
            </a>
          </li>

        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>