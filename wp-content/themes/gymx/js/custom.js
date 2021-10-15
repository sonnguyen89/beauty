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
            $(document).scroll(function() {
                var scroll_position = 300;
                var y = $(this).scrollTop();
                if (y > scroll_position) {
                    $('.floating-menu-wrapper').show();
                    $('#mobile-header').css('top' , '32px');
                } else {
                    $('.floating-menu-wrapper').hide();
                    $('#mobile-header').css('top' , '0');
                }
            });
        },
        /*customizeForDevice : function() {
            var ua = navigator.userAgent;
            var checker = {
                iphone: ua.match(/(iPhone|iPod|iPad)/),
                blackberry: ua.match(/BlackBerry/),
                android: ua.match(/Android/)
            };
            if (checker.android){
                console.log('android');
            }
            else if (checker.iphone){
                console.log('iphone');
            }
            else if (checker.blackberry){
                console.log('blackberry');
            }
            else {
                console.log('windows');
            }
        }*/
    };
    $(document).ready(function(){
        customizeTheme.init();
    });
} )( jQuery );
