<?php

$pageScripts = $pageScripts ?? [];
clear_old_input();
?>
</main>
<?= vendor_scripts() ?>
<script src="<?= e(asset('js/main.js')) ?>"></script>
<script src="<?= e(asset('js/validation.js')) ?>"></script>
<script src="<?= e(asset('js/admin.js')) ?>"></script>
<?php foreach ($pageScripts as $script): ?>
<script src="<?= e(asset($script)) ?>"></script>
<?php endforeach; ?>
</body>
</html>
