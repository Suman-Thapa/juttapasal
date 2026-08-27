<?php $active = $active ?? 'dashboard'; $adminName = $_SESSION['full_name'] ?? 'Administrator'; ?>
<aside class="admin-sidebar">
  <a class="admin-brand" href="dashboard.php">
    <div class="brand-logo"><img src="logo.png" alt="JuttaPasal" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"><span class="brand-fallback"><?= icon('shoe') ?></span></div>
    <div><strong>JuttaPasal</strong><small>Admin Panel</small></div>
  </a>
  <div class="admin-menu-title">ADMIN MENU</div>
  <nav class="admin-nav">
    <a class="<?= $active==='dashboard'?'active':'' ?>" href="dashboard.php"><?= icon('grid') ?><span>Dashboard</span></a>
    <a class="<?= $active==='orders'?'active':'' ?>" href="orders.php"><?= icon('orders') ?><span>Orders</span></a>
    <a class="<?= $active==='products'?'active':'' ?>" href="products.php"><?= icon('box') ?><span>Products</span></a>
    <a class="<?= $active==='users'?'active':'' ?>" href="users.php"><?= icon('users') ?><span>Users</span></a>
  </nav>
  <div class="sidebar-bottom">
    <div class="admin-user-card">
      <div class="admin-avatar"><?= h(strtoupper(substr($adminName,0,1))) ?></div>
      <div><b><?= h($adminName) ?></b><span>Administrator</span></div>
    </div>
    <a class="admin-logout" href="../logout.php"><?= icon('logout') ?><span>Logout</span></a>
  </div>
</aside>
