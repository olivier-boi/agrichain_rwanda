  </main>
</div>
<?php $flash = get_flash(); if ($flash): ?><div id="toastData" data-msg="<?= e($flash['msg']) ?>" data-type="<?= e($flash['type']) ?>"></div><?php endif; ?>
<script src="<?= url('js/script.js') ?>"></script>
</body>
</html>
