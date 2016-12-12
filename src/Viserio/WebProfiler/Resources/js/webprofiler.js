Zepto(function($) {
    var $panels = $('.a[data-panel-target-id]');

    // Symfony VarDumper: Close the by default expanded objects
    $('.sf-dump-expanded')
        .removeClass('sf-dump-expanded')
        .addClass('sf-dump-compact');

    $('.sf-dump-toggle span').html('&#9654;');
});
