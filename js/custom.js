$(function () {
    $('.aj-nav').click(function (e) {
        e.preventDefault();
        $(this).parent().siblings().find('ul').slideUp();
        $(this).next().slideToggle();
    });

    // Bootstrap Table Class
    $('table').addClass('table');

    // Responsive menu spinner
    $('#menu-spinner-button').click(function () {
        $('#sub-nav-collapse').slideToggle();
    });

    // Catch browser resize
    $(window).resize(function () {
        // Remove transition inline style on large screens
        if ($(window).width() >= 768)
            $('#sub-nav-collapse').removeAttr('style');
    });
});

//Fix GitHub Ribbon overlapping Scrollbar
var t = $('#github-ribbon');
if (t[0] && $('article')[0].scrollHeight > $('.right-column').height()) t[0].style.right = '16px';

//Toggle Code Block Visibility
function toggleCodeBlocks() {
    var t = localStorage.getItem("toggleCodeStats")
    localStorage.setItem("toggleCodeStats", t);
    var a = $('.content-page article');
    var c = a.children().filter('pre');
    var d = $('.right-column');
    
    if (c.hasClass('hidden')) {
        c.removeClass('hidden');
        $('#toggleCodeBlockBtn')[0].innerHTML = "外置代码框";
    } else if (d.hasClass('float-view')) {
        d.removeClass('float-view');
        c.addClass('hidden');
        $('#toggleCodeBlockBtn')[0].innerHTML = "内嵌代码框";
    } else {
        d.addClass('float-view');
        $('#toggleCodeBlockBtn')[0].innerHTML = "隐藏代码框";
    }
}
