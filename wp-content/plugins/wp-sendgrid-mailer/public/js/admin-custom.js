jQuery(document).ready(function()    {

    jQuery('[data-toggle="tooltip"]').tooltip();

});

function wp_mailplus_clear_logs()
{
    var confirmation = confirm('Are you sure you want to clear logs ?')
    if(!confirmation)
        return false;

    jQuery('.spinner').addClass('is-active');
    jQuery.post(
        ajaxurl,
        {
            'action': 'wp_mailplus_clear_logs',
        },
        function(response)  {
            jQuery('.spinner').removeClass('is-active');
            if(response == 'Success') {
                window.location.reload();
            }
            else    {
                // Show message to user
                jQuery('.wp_mailplus_settings_notification').append('<div class="notice notice-error is-dismissible"><p>Error occurred while clearing logs </p></div>');
            }
        }
    );
}

function getServiceDetails(value)
{
    jQuery('.service-forms').hide();
    if(value == 'smtp')
    {
        jQuery('#smtp-form').show();
    }
    else if(value == 'sendgrid')
    {
        jQuery('#sendgrid-form').show();
    }
}
