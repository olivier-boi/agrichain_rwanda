<?php
$links = ['index' => ['Home', 'index.php'], 'about' => ['About', 'about.php'], 'marketplace' => ['Marketplace', 'marketplace.php'],
          'quality' => ['Quality Grading', 'quality.php'], 'supply' => ['Supply Chain', 'supply-chain.php'],
          'farmers' => ['Farmers', 'farmers.php'], 'buyers' => ['Buyers', 'buyers.php'], 'contact' => ['Contact', 'contact.php']];
?>
<header class="navbar">
  <div class="container nav-inner">
    <a href="<?= url('index.php') ?>" class="logo">
      <svg width="34" height="34" viewBox="0 0 40 40" aria-hidden="true"><circle cx="20" cy="20" r="20" fill="#1b7a3d"/><path d="M20 31V17M20 22c-6 0-9-4-9-9 6 0 9 3 9 9zm0 3c6 0 9-4 9-9-6 0-9 3-9 9z" stroke="#fff" stroke-width="2" fill="none" stroke-linecap="round"/><rect x="0" y="34" width="40" height="3" fill="#f2c230"/></svg>
      <span>Agri<b>Chain</b> Rwanda</span>
    </a>
    <button class="nav-toggle" id="navToggle" aria-label="Open menu">☰</button>
    <nav class="nav-menu" id="navMenu">
      <?php foreach ($links as $key => $l): ?>
        <a href="<?= url($l[1]) ?>" class="<?= $active === $key ? 'active' : '' ?>"><?= $l[0] ?></a>
      <?php endforeach; ?>
      <div class="nav-auth">
        <?php if (is_logged_in()): ?>
          <a href="<?= url(dashboard_url(user()['role'])) ?>" class="btn btn-sm">My Dashboard</a>
          <a href="<?= url('logout.php') ?>" class="btn btn-sm btn-outline">Logout</a>
        <?php else: ?>
          <a href="<?= url('login.php') ?>" class="btn btn-sm btn-outline">Login</a>
          <a href="<?= url('register.php') ?>" class="btn btn-sm">Register</a>
        <?php endif; ?>
      </div>
    </nav>
  </div>
</header>
