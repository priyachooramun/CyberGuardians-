/* =====================================================================
   InkCorner – administrator pages (jQuery)
   Owner: Member 3 (Catalogue & admin products)
   ===================================================================== */
(function ($) {
    'use strict';

    const BASE = document.body.dataset.baseUrl || '';

    /* ---------- FR30: save stock without reloading (api/stock.php) ---------- */
    $(document).on('submit', '.js-stock-form', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $input = $form.find('[name="quantity"]');
        const $btn = $form.find('button').prop('disabled', true);
        $.post(BASE + '/api/stock.php', $form.serialize())
            .done(res => {
                $input.toggleClass('border-danger text-danger fw-bold', res.low_stock);
                inkToast(res.message, 'success');
            })
            .fail(xhr => inkToast((xhr.responseJSON || {}).message || 'Could not save the stock.', 'danger'))
            .always(() => $btn.prop('disabled', false));
    });

    /* ---------- Product form: image preview + drag and drop ---------- */
    const $zone = $('#dropzone');
    const fileInput = document.getElementById('image');

    function preview(file) {
        if (!file) { return; }
        if (!/^image\/(jpeg|png|webp|gif)$/.test(file.type)) { inkToast('Please choose a JPG, PNG, WEBP or GIF image.', 'warning'); return; }
        if (file.size > 2 * 1024 * 1024) { inkToast('The image must be 2 MB or smaller.', 'warning'); return; }
        const reader = new FileReader();
        reader.onload = ev => $('#imagePreview').attr('src', ev.target.result).removeClass('d-none');
        reader.readAsDataURL(file);
    }

    if (fileInput) {
        $(fileInput).on('change', function () { preview(this.files[0]); });
        $zone.on('dragover dragenter', e => { e.preventDefault(); $zone.addClass('dragover'); })
             .on('dragleave drop', e => { e.preventDefault(); $zone.removeClass('dragover'); })
             .on('drop', e => {
                 const files = e.originalEvent.dataTransfer.files;
                 if (files.length) { fileInput.files = files; preview(files[0]); }
             });
    }

    /* ---------- Product form: sale price + category follow the section ---------- */
    function toggleSale() { $('.js-sale-price').toggleClass('opacity-50', !$('#is_on_sale').is(':checked')); }
    function filterCategories() {
        const section = $('.js-section:checked').val();
        const $cat = $('#category');
        $cat.find('optgroup').each(function () { $(this).prop('hidden', this.dataset.section !== section); });
        if ($cat.find('option:selected').data('section') !== section) {
            $cat.val($cat.find('optgroup[data-section="' + section + '"] option').first().val());
        }
        $('.js-brand-label').text(section === 'book' ? 'Author' : 'Brand');
    }
    $(document).on('change', '#is_on_sale', toggleSale);
    $(document).on('change', '.js-section', filterCategories);
    if ($('#category').length) { toggleSale(); filterCategories(); }

    /* ---------- Customers: reveal masked contact details on request ---------- */
    $(document).on('click', '.js-reveal', function () {
        const $row = $(this).closest('tr');
        $row.find('.js-masked').each(function () { $(this).text(this.dataset.full); });
        $(this).remove();
    });

    /* ---------- Orders: confirm before cancelling ---------- */
    $(document).on('submit', '.js-confirm-cancel', function (e) {
        if ($(this).find('[name="status"]').val() === 'Cancelled'
            && !window.confirm('Cancel this order? Items go back to stock and the payment is marked refunded.')) {
            e.preventDefault();
        }
    });
}(jQuery));

/* Dashboard bar chart: heights come from data-pct */
document.querySelectorAll('.sales-bar[data-pct]').forEach(function (bar) {
    var pct = Math.max(0, Math.min(100, parseInt(bar.dataset.pct, 10) || 0));
    bar.style.height = pct + '%';
});
