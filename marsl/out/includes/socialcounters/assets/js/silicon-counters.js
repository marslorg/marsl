jQuery(document).ready(function ($) {
    "use strict";
    $.each($('.silicon_counters li a'), function (index, element) {
        var $counter_color;
        var $counter_color_hover;
        var $icon_color;
        var $icon_color_hover;
        var $number_color;
        var $number_color_hover;
        var $label_color;
        var $label_color_hover;

        $counter_color = $(this).css('background-color');
        $counter_color_hover = $(this).data('color-hover');
        $icon_color = $(this).find('.fa').css('color');
        $icon_color_hover = $(this).find('.fa').data('color-hover');
        $number_color = $(this).find('.count').css('color');
        $number_color_hover = $(this).find('.count').data('color-hover');
        $label_color = $(this).find('.label').css('color');
        $label_color_hover = $(this).find('.label').data('color-hover');

        $(element).hover(
            function () {
                $(this).css({
                    'background-color': $counter_color_hover,
                });

                $(this).find('.fa').css({
                    'color': $icon_color_hover,
                });

                $(this).find('.count').css({
                    'color': $number_color_hover,
                });

                $(this).find('.label').css({
                    'color': $label_color_hover,
                });
            },
            function () {
                $(this).css({
                    'background-color': $counter_color,
                });

                $(this).find('.fa').css({
                    'color': $icon_color,
                });

                $(this).find('.count').css({
                    'color': $number_color,
                });

                $(this).find('.label').css({
                    'color': $label_color,
                });
            }
        );

    });
});
