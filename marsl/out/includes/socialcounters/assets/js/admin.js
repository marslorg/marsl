jQuery(document).ready(function ($) {
    // Init the color picker.
    $('.silicon-counters-color-field').wpColorPicker();

    function siliconcountersShowHide(input) {
        var current = input.attr('id'),
            id = current.replace('_active', ''),
            elements = $('.form-table [id^="' + id + '"]').not('[id^="' + current + '"]').parents('tr');

        if (input.is(':checked')) {
            elements.show();
            $('.form-table [id^="' + id + '"]').parent().parent().parent().parent().addClass('active');
        } else {
            elements.hide();
            $('.form-table [id^="' + id + '"]').parent().parent().parent().parent().removeClass('active');
        }
    }

    $('.form-table input[id$="_active"]').each(function () {
        siliconcountersShowHide($(this));
    });

    $('.form-table input[id$="_active"]').on('click', function () {
        siliconcountersShowHide($(this));
    });

    $('.form-table .silicon-counters-icons-order').sortable({
        items: 'div.social-icon',
        cursor: 'move',
        scrollSensitivity: 40,
        forcePlaceholderSize: true,
        forceHelperSize: false,
        helper: 'clone',
        opacity: 0.65,
        placeholder: 'social-icon-placeholder',
        start: function (event, ui) {
            ui.item.css('background-color', '#f6f6f6');
        },
        stop: function (event, ui) {
            ui.item.removeAttr('style');
        },
        update: function () {
            var icons = $('.silicon-counters-icons-order .social-icon').map(function () {
                return $(this).data('icon');
            }).get().join();

            $('input', $(this)).val(icons);
        }
    });
});
