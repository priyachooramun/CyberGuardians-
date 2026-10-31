<?php

$pageScripts = $pageScripts ?? [];
clear_old_input();
?>
</main>

<footer class="ink-footer">
    <div class="container py-4">
        <div class="row gy-3">
            <div class="col-md-4">
                <h5 class="display-font">InkCorner</h5>
                <p class="small mb-0">A family bookstore and stationery shop in Port Louis for three generations, now online.</p>
            </div>
            <div class="col-6 col-md-2">
                <h6>Shop</h6>
                <ul class="list-unstyled small mb-0">
                    <li><a href="<?= e(url('shop/index.php', ['section' => 'book'])) ?>">Books</a></li>
                    <li><a href="<?= e(url('shop/index.php', ['section' => 'stationery'])) ?>">Stationery</a></li>
                    <li><a href="<?= e(url('shop/index.php', ['filter' => 'sale'])) ?>">On sale</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-2">
                <h6>Your account</h6>
                <ul class="list-unstyled small mb-0">
                    <?php if (current_customer()): ?>
                        <li><a href="<?= e(url('account/profile.php')) ?>">My profile</a></li>
                        <li><a href="<?= e(url('account/orders.php')) ?>">My orders</a></li>
                        <li><a href="<?= e(url('cart/index.php')) ?>">Cart</a></li>
                    <?php else: ?>
                        <li><a href="<?= e(url('auth/login.php')) ?>">Login</a></li>
                        <li><a href="<?= e(url('auth/register.php')) ?>">Sign up</a></li>
                    <?php endif; ?>
                    <li><a href="<?= e(url('contact.php')) ?>">Help &amp; questions</a></li>
                    <li><a href="<?= e(url('admin/login.php')) ?>">Staff login</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6>Visit us</h6>
                <p class="small mb-0">Royal Street, Port Louis<br>Mon–Sat 8:30 – 17:30<br>+230 212 0000</p>
            </div>
        </div>
        <p class="small text-center mt-4 mb-0">&copy; <?= date('Y') ?> InkCorner · University of Mauritius ICT 2213Y group project · Payments are simulated</p>
    </div>
</footer>

<?= vendor_scripts() ?>
<script src="<?= e(asset('js/main.js')) ?>"></script>
<script src="<?= e(asset('js/validation.js')) ?>"></script>
<?php foreach ($pageScripts as $script): ?>
<script src="<?= e(asset($script)) ?>"></script>
<?php endforeach; ?>
</body>
</html>
