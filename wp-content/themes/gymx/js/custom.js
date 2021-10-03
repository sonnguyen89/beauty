( function( $ ) {
    var customizeTheme = {
        init: function () {
            customizeTheme.customizeTopMobileMenu();
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
        }
    };
    $(document).ready(function(){
        customizeTheme.init();
    });
} )( jQuery );
