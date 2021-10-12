( function( $ ) {
    var customizeTheme = {
        init: function () {
            customizeTheme.customizeTopMobileMenu();
             customizeTheme.showFloatMenu();
        },
        customizeTopMobileMenu : function() {
            $.each($('#mobile-header-nav li a'),function( key, value) {
                if(typeof  $(value).attr('title') !== 'undefined') {
                    var raw_val = $(value).html();
                    $(value).html('<span class="link-label">' + raw_val + '</span>')
                    var title_atr = $(value).attr('title');
                    $(value).append('<br/><span>' + title_atr + '</span>');
                }
            });
        },
        showFloatMenu : function() {
            $('.floating-menu').fadeOut();
            $(document).scroll(function() {
                var scroll_position = 500;
                var y = $(this).scrollTop();
                if (y > scroll_position) {
                    $('.floating-menu').fadeIn();
                } else {
                    $('.floating-menu').fadeOut();
                }
            });
        },
    };
    $(document).ready(function(){
        customizeTheme.init();
    });
} )( jQuery );
