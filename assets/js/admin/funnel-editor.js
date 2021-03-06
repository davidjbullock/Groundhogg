var wpghDoingAutoSave = false;

jQuery( function($) {

    jQuery('form').on('submit', function( e ){
        e.preventDefault();
        jQuery('.spinner').css('visibility','visible');
        jQuery('form').unbind( 'submit' ).submit();
    });
    
    var funnelSortables = jQuery( ".ui-sortable" ).sortable({
        placeholder: "sortable-placeholder",
        connectWith: ".ui-sortable",
        axis: 'y',
        start: function(e, ui){
            ui.helper.css( 'left', ( ui.item.parent().width() - ui.item.width() ) / 2 );
            ui.placeholder.height(ui.item.height());
            ui.placeholder.width(ui.item.width());
        }
    });

    funnelSortables.disableSelection();

    var funnelDraggables = jQuery( ".ui-draggable" ).draggable({
        connectToSortable: ".ui-sortable",
        helper: "clone",
        stop: function ( e, ui ){
            var el = this;
            var step_type = el.id;

            var sortables = jQuery('#normal-sortables');

            sortables.find('.ui-draggable').replaceWith(
                '<div id="temp-step" class="postbox step replace-me" style="width: 500px;margin-right: auto;margin-left: auto;"><h3 class="hndle">Please Wait...</h3><div class="inside">Loading content...</div></div>'
            );

            var order = $( '.step' ).index( $( '#temp-step' ) ) + 1;

            if ( sortables.find( '.replace-me' ).length ){
                var ajaxCall = jQuery.ajax({
                    type : "post",
                    url : ajaxurl,
                    data : {action: "wpgh_get_step_html", step_type: step_type, step_order: order },
                    success: function( html )
                    {
                        jQuery('#normal-sortables').find('.replace-me').replaceWith( html );
                    }
                });
            }
        }
    });


    funnelSortables.on( 'sortupdate', function( e, ui ){
        $( '.email_opened' ).each( wpgh_update_inside_contents );
    });
    funnelSortables.on( 'sortupdate', function( e, ui ){
        wpgh_update_funnel_step_order();
        wpgh_auto_save_funnel();
    });

    $('.sidebar').stickySidebar({
        topSpacing: 40,
        bottomSpacing: 40
    });

    $('a').click( function( e ){
        e.preventDefault();
        /* auto save before redirect */
        wpgh_auto_save_funnel();
        window.location = this.href;
    });

    /* Auto save funnels */
    setInterval(
        wpgh_auto_save_funnel,
        20000
    );
});

function wpgh_duplicate_step()
{
    var el = jQuery( this );
    var id = el.parent().attr( 'id' );

    var ajaxCall = jQuery.ajax({
        type : "post",
        url : ajaxurl,
        data : {action: "wpgh_duplicate_funnel_step", step_id: id },
        success: function( html )
        {
           jQuery( html ).insertBefore( el.parent() )
        }
    });

}

function wpgh_delete_funnel_step()
{
    var el = this;
    var id = el.parentNode.id;
    var result = confirm("Are you sure you want to delete this step?");
    if ( result ){
        var ajaxCall = jQuery.ajax({
            type : "post",
            url : ajaxurl,
            data : {action: "wpgh_delete_funnel_step",step_id: id },
            success: function( result )
            {
                el.parentNode.remove();
            }
        });
    }

    wpgh_auto_save_funnel();
}

function wpgh_update_funnel_step_order()
{
    jQuery( "input[name$='_order']" ).each(
        function( index ){
            jQuery( this ).val( index + 1 );
        }
    );
}

function wpgh_auto_save_funnel()
{
    if ( wpghDoingAutoSave )
        return;

    wpghDoingAutoSave = true;

    var fd = jQuery('form').serialize();

    fd = fd +  '&action=wpgh_auto_save_funnel_via_ajax';

    var ajaxCall = jQuery.ajax({
        type : "post",
        url : ajaxurl,
        //data : fd,
        data : fd,
        success: function( result )
        {
            wpghDoingAutoSave = false;
            jQuery( '.save-notification' ).fadeIn();
            setTimeout( function(){
                jQuery( '.save-notification' ).fadeOut()
            }, 3000);
        }
    });
}

function wpgh_update_inside_contents()
{
    var order = jQuery( '.step' ).index( jQuery( this ) ) + 1;

    var e = jQuery( this );

    var ajaxCall = jQuery.ajax({
        type : "post",
        url : ajaxurl,
        data : {action: "wpgh_get_step_html_inside", step_id: e.attr( 'id' ) , step_order: order },
        success: function( html )
        {
            e.find( '.custom-settings' ).html( html );
        }
    });
}
