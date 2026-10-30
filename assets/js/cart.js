/* =====================================================================
   InkCorner – cart page: change quantities and remove items with AJAX
   Owner: Member 4 (Cart, checkout & orders)
   Without JavaScript, the normal forms (cart/actions.php) still work.
   ===================================================================== */
(function ($) {
    'use strict';

    $('.js-hide-with-ajax').addClass('d-none');   // AJAX saves instantly, no "Update" needed

    function render(cart) {
        $('#cartTotal').text(cart.total_formatted);
        const byKey = {};
        cart.lines.forEach(l => { byKey[l.key] = l; });
        $('tr[data-key]').each(function () {
            const line = byKey[this.dataset.key];
            if (!line) {
                $(this).fadeOut(200, function () { $(this).remove(); if (!$('tr[data-key]').length) { location.reload(); } });
                return;
            }
            $(this).find('.js-line-subtotal').text(line.subtotal_formatted);
            $(this).find('.js-cart-qty').val(line.quantity);
        });
    }

    let timer = null;
    $(document).on('change input', '.js-cart-qty', function () {
        const $input = $(this);
        const key = $input.closest('tr').data('key');
        clearTimeout(timer);
        timer = setTimeout(() => {
            const qty = parseInt($input.val(), 10);
            if (Number.isNaN(qty) || qty < 0) { return; }
            inkCart({ action: 'update', key: String(key), quantity: qty }).done(res => {
                render(res.cart);
                if (res.message !== 'Cart updated.') { inkToast(res.message, 'info'); }
            }).fail(xhr => { if (xhr.responseJSON && xhr.responseJSON.cart) { render(xhr.responseJSON.cart); } });
        }, 400);
    });

    $(document).on('click', '.js-cart-remove', function (e) {
        e.preventDefault();
        const key = $(this).closest('tr').data('key');
        inkCart({ action: 'remove', key: String(key) }).done(res => {
            render(res.cart);
            inkToast(res.message, 'success');
        });
    });
}(jQuery));
