/* =====================================================================
   InkCorner site-wide jQuery: AJAX cart, live search, small UI helpers
   
   
   */
(function ($) {
    'use strict';

    const BASE = document.body.dataset.baseUrl || '';
    const CSRF = $('meta[name="csrf-token"]').attr('content');

    // Every AJAX request carries the CSRF token (checked by verify_csrf() in PHP)
    $.ajaxSetup({ headers: { 'X-CSRF-Token': CSRF }, dataType: 'json' });

    /* Toast messages  */
    window.inkToast = function (message, type) {
        let wrap = document.querySelector('.ink-toast-wrap');
        if (!wrap) {
            wrap = document.createElement('div');
            wrap.className = 'ink-toast-wrap';
            document.body.appendChild(wrap);
        }
        const toast = document.createElement('div');
        toast.className = 'toast align-items-center border-0 text-bg-' + (type || 'dark');
        toast.setAttribute('role', 'status');
        const row = document.createElement('div');
        row.className = 'd-flex';
        const body = document.createElement('div');
        body.className = 'toast-body';
        body.textContent = message;                    // textContent → never runs HTML
        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'btn-close btn-close-white me-2 m-auto';
        close.setAttribute('data-bs-dismiss', 'toast');
        close.setAttribute('aria-label', 'Close');
        row.append(body, close);
        toast.appendChild(row);
        wrap.appendChild(toast);
        if (window.bootstrap) {
            new bootstrap.Toast(toast, { delay: 3500 }).show();
            toast.addEventListener('hidden.bs.toast', () => toast.remove());
        }
    };

    window.inkUpdateBadge = function (count) {
        $('#cartBadge').text(count).toggleClass('d-none', !count);
    };

    
    window.inkCart = function (data) {
        return $.post(BASE + '/api/cart.php', data)
            .done(res => { if (res.cart) { inkUpdateBadge(res.cart.count); } })
            .fail(xhr => {
                const res = xhr.responseJSON || {};
                if (xhr.status === 401 && res.login_url) {
                    window.location.href = res.login_url;
                    return;
                }
                if (res.cart) { inkUpdateBadge(res.cart.count); }
                inkToast(res.message || 'Something went wrong. Please try again.', 'danger');
            });
    };

    /*Add to cart from product cards*/
    $(document).on('click', '.js-add-to-cart', function () {
        const $btn = $(this).prop('disabled', true);
        inkCart({ action: 'add', product_id: $btn.data('product-id'), quantity: 1 })
            .done(res => inkToast(res.message, 'success'))
            .always(() => $btn.prop('disabled', false));
    });

    /*Add to cart / Buy now on the product page*/
    $(document).on('submit', '.js-add-form', function (e) {
        e.preventDefault();
        const form = this;
        const submitter = e.originalEvent && e.originalEvent.submitter;
        const goToCart = submitter && submitter.value === 'cart';
        const $variant = $(form).find('[name="variant_id"]');

        if ($variant.length && !$variant.val()) {
            $variant.addClass('is-invalid').trigger('focus');
            inkToast('Please choose an option first.', 'warning');
            return;
        }
        $variant.removeClass('is-invalid');

        inkCart({
            action: 'add',
            product_id: $(form).find('[name="product_id"]').val(),
            variant_id: $variant.val() || '',
            quantity: $(form).find('[name="quantity"]').val()
        }).done(res => {
            if (goToCart) {
                window.location.href = BASE + '/cart/index.php';
            } else {
                inkToast(res.message, 'success');
            }
        });
    });

    /*Quantity +/- buttons*/
    $(document).on('click', '.js-qty-minus, .js-qty-plus', function () {
        const $input = $(this).closest('.input-group').find('input');
        const min = parseInt($input.attr('min'), 10) || 1;
        const max = parseInt($input.attr('max'), 10) || 50;
        let value = parseInt($input.val(), 10) || min;
        value += $(this).hasClass('js-qty-plus') ? 1 : -1;
        $input.val(Math.min(max, Math.max(min, value))).trigger('change');
    });

    /*Live search*/
    const $search = $('#liveSearch');
    const $results = $('#liveSearchResults');
    let timer = null;
    let lastQuery = '';

    $('#searchToggle').on('click', function () {
        const $form = $('.ink-search').toggleClass('open');
        if ($form.hasClass('open')) { $search.trigger('focus'); } else { $results.empty(); }
    });
    if ($search.val()) { $('.ink-search').addClass('open'); }

    $search.on('input', function () {
        clearTimeout(timer);
        const q = $.trim(this.value);
        if (q.length < 2) { $results.empty(); lastQuery = ''; return; }
        timer = setTimeout(() => {
            if (q === lastQuery) { return; }
            lastQuery = q;
            $.getJSON(BASE + '/api/products.php', { q: q, limit: 6 }).done(res => {
                $results.empty();
                if (!res.products.length) {
                    $('<div class="list-group-item small">').text('No matches').appendTo($results);
                    return;
                }
                res.products.forEach(p => {
                    const $a = $('<a class="list-group-item list-group-item-action d-flex gap-2 align-items-center">').attr('href', p.url);
                    $('<img alt="">').attr('src', p.image).appendTo($a);
                    const $txt = $('<div class="flex-grow-1">').appendTo($a);
                    $('<div class="small fw-semibold">').text(p.name).appendTo($txt);
                    $('<div class="small text-muted">').text(p.price_formatted + (p.in_stock ? '' : ' · out of stock')).appendTo($txt);
                    $a.appendTo($results);
                });
            });
        }, 250);
    });
    $(document).on('click', e => { if (!$(e.target).closest('.ink-search').length) { $results.empty(); } });
    $search.on('keydown', e => { if (e.key === 'Escape') { $results.empty(); } });

    /*Small helpers */
    $(document).on('change', '.js-autosubmit', function () { this.form.submit(); });
    $(document).on('submit', '.js-confirm', function (e) {
        if (!window.confirm(this.dataset.confirm || 'Are you sure?')) { e.preventDefault(); }
    });
    $(document).on('click', '.js-print', () => window.print());
}(jQuery));
