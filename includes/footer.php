<?php $flash = get_flash(); ?>
<footer class="footer">
  <div class="container footer-grid">
    <div>
      <h3>Agri<span>Chain</span> Rwanda</h3>
      <p>A smart digital platform connecting Rwanda's farmers, inspectors and buyers through transparent quality grading and supply-chain tracking.</p>
    </div>
    <div>
      <h4>Platform</h4>
      <a href="<?= url('marketplace.php') ?>">Marketplace</a>
      <a href="<?= url('quality.php') ?>">Quality Grading</a>
      <a href="<?= url('supply-chain.php') ?>">Supply Chain</a>
      <a href="<?= url('about.php') ?>">About Us</a>
    </div>
    <div>
      <h4>Join Us</h4>
      <a href="<?= url('register.php?role=farmer') ?>">Register as Farmer</a>
      <a href="<?= url('register.php?role=buyer') ?>">Register as Buyer</a>
      <a href="<?= url('login.php') ?>">Login</a>
    </div>
    <div>
      <h4>Contact</h4>
      <p>Kigali, Rwanda<br>info@agrichain.rw<br>+250 791875132</p>
    </div>
  </div>
  <div class="footer-bottom">&copy; <?= date('Y') ?> AgriChain Rwanda - Fast & Reliable</div>
</footer>
<?php if ($flash): ?><div id="toastData" data-msg="<?= e($flash['msg']) ?>" data-type="<?= e($flash['type']) ?>"></div><?php endif; ?>
<script src="<?= url('js/script.js') ?>"></script>
</body>
</html>
