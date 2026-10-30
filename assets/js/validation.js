/* =====================================================================
   InkCorner – client-side validation (jQuery)
*/
(function ($) {
    'use strict';

    const RULES = {
        name:     { test: v => /^[\p{L} .'-]{2,100}$/u.test(v), msg: 'Use letters, spaces, apostrophes or hyphens only.' },
        phone:    { test: v => /^(\+230)?(5\d{7}|\d{7})$/.test(v.replace(/[\s-]/g, '')), msg: 'Enter a valid Mauritian number, e.g. 5712 3456.' },
        password: { test: v => v.length >= 8 && v.length <= 72 && /[A-Z]/.test(v) && /[a-z]/.test(v) && /\d/.test(v),
                    msg: '8+ characters with an uppercase letter, a lowercase letter and a number.' },
        money:    { test: v => /^\d{1,8}(\.\d{1,2})?$/.test(v) && parseFloat(v) > 0, msg: 'Enter a positive amount, e.g. 120 or 99.50.' },
        card:     { test: v => luhn(v.replace(/\D/g, '')), msg: 'This card number is not valid.' },
        expiry:   { test: v => expiryOk(v), msg: 'Use MM/YY and a date that is not in the past.' },
        cvv:      { test: v => /^\d{3,4}$/.test(v), msg: 'Enter the 3 or 4 digit code on the back of the card.' }
    };

   
    function luhn(num) {
        if (!/^\d{13,19}$/.test(num)) { return false; }
        let sum = 0;
        let dbl = false;
        for (let i = num.length - 1; i >= 0; i--) {
            let d = parseInt(num.charAt(i), 10);
            if (dbl) { d *= 2; if (d > 9) { d -= 9; } }
            sum += d;
            dbl = !dbl;
        }
        return sum % 10 === 0;
    }

    function expiryOk(v) {
        const m = /^(0[1-9]|1[0-2])\s*\/\s*(\d{2})$/.exec($.trim(v));
        if (!m) { return false; }
        const now = new Date();
        const card = (2000 + parseInt(m[2], 10)) * 12 + parseInt(m[1], 10);
        const current = now.getFullYear() * 12 + now.getMonth() + 1;
        return card >= current && card <= current + 180;
    }

    function setError($field, message) {
        $field.addClass('is-invalid').attr('aria-invalid', 'true');
        let $fb = $field.siblings('.js-feedback');
        if (!$fb.length) {
            $fb = $('<div class="invalid-feedback js-feedback"></div>');
            $field.closest('.input-group').length ? $fb.insertAfter($field.closest('.input-group')) : $fb.insertAfter($field);
        }
        $fb.text(message).addClass('d-block');
    }

    function clearError($field) {
        $field.removeClass('is-invalid').removeAttr('aria-invalid');
        $field.siblings('.js-feedback').remove();
        $field.siblings('.invalid-feedback').removeClass('d-block');
    }

    function check(field) {
        const $f = $(field);
        if ($f.is(':disabled') || $f.attr('type') === 'hidden') { return true; }
        const value = $f.is(':checkbox') ? ($f.is(':checked') ? 'on' : '') : String($f.val() || '');
        const trimmed = $.trim(value);
        const label = $.trim($('label[for="' + field.id + '"]').first().text()) || 'This field';

        if ($f.prop('required') && trimmed === '') {
            setError($f, $f.is(':checkbox') ? 'Please tick this box to continue.' : label + ' is required.');
            return false;
        }
        if (trimmed === '') { clearError($f); return true; }

        if ($f.attr('type') === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(trimmed)) {
            setError($f, 'Please enter a valid email address.'); return false;
        }
        const min = parseInt($f.attr('minlength'), 10);
        if (min && trimmed.length < min) { setError($f, label + ' must be at least ' + min + ' characters.'); return false; }

        if ($f.attr('type') === 'number') {
            const n = Number(trimmed);
            const lo = $f.attr('min'), hi = $f.attr('max');
            if (!Number.isInteger(n) || (lo !== undefined && n < Number(lo)) || (hi !== undefined && n > Number(hi))) {
                setError($f, 'Enter a whole number' + (lo !== undefined && hi !== undefined ? ' from ' + lo + ' to ' + hi : '') + '.');
                return false;
            }
        }
        const rule = RULES[$f.data('rule')];
        if (rule && !rule.test(trimmed)) { setError($f, rule.msg); return false; }

        const match = $f.data('match');
        if (match && value !== $(match).val()) { setError($f, 'The passwords do not match.'); return false; }

        clearError($f);
        return true;
    }

    $(document).on('submit', 'form.js-validate', function (e) {
        let ok = true;
        $(this).find('input, select, textarea').each(function () {
            // Sale price only matters when "On sale" is ticked
            if (this.name === 'sale_price' && !$('#is_on_sale').is(':checked')) { clearError($(this)); return; }
            if (!check(this)) { ok = false; }
        });
        if (!ok) {
            e.preventDefault();
            e.stopImmediatePropagation();
            $(this).find('.is-invalid').first().trigger('focus');
            return;
        }
        // Stop double submits and show a spinner on the payment button
        const $btn = $(this).find('button[type="submit"]').last();
        if (this.id === 'paymentForm') {
            $btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');
        }
    });

    $(document).on('blur', 'form.js-validate input, form.js-validate textarea', function () {
        if ($(this).hasClass('is-invalid') || $.trim($(this).val()) !== '') { check(this); }
    });

    
    $(document).on('input change', 'form.js-validate .is-invalid', function () { check(this); });

    /* ---------- Card field formatting on the payment page ---------- */
    $(document).on('input', '[data-rule="card"]', function () {
        const digits = this.value.replace(/\D/g, '').slice(0, 19);
        this.value = digits.replace(/(\d{4})(?=\d)/g, '$1 ');
    });
    $(document).on('input', '[data-rule="expiry"]', function (e) {
        let v = this.value.replace(/[^\d]/g, '').slice(0, 4);
        if (v.length >= 3 || (v.length === 2 && e.originalEvent && e.originalEvent.inputType !== 'deleteContentBackward')) {
            v = v.slice(0, 2) + '/' + v.slice(2);
        }
        this.value = v;
    });
    $(document).on('input', '[data-rule="cvv"]', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 4);
    });

    window.inkLuhn = luhn;
}(jQuery));
