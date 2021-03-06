<?php
/**
 * Created by PhpStorm.
 * User: adria
 * Date: 2018-08-21
 * Time: 2:00 PM
 */


/**
 * If the link is pobviously a superlink, then perform the request actions...
 */
function wpgh_process_superlink()
{
    if ( strpos( $_SERVER[ 'REQUEST_URI' ], '/superlinks/link/' ) === false )
        return;

    $link_path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
    $link_parts = explode( '/', $link_path );
    $link_id = intval( $link_parts[ count( $link_parts) -1 ] );

    $link = wpgh_get_superlink_by_id( $link_id );

    if ( ! $link )
        return;

    $contact = wpgh_get_the_contact();

    if ( $link[ 'tags' ] && $contact )
    {
        $tags = maybe_unserialize( $link['tags'] );

        foreach ( $tags as $tag_id )
        {
            wpgh_apply_tag( $contact->get_id(), $tag_id );
        }
    }

    if ( ! $link['target'] )
        return;

    wp_redirect( esc_url_raw( $link['target'] ) );
    die();
}

add_action( 'init', 'wpgh_process_superlink' );


/**
 * Do the link replacement...
 *
 * @param $linkId int the ID of the link
 * @param $contact WPGH_Contact the contact
 *
 * @return string the superlink url
 */
function wpgh_superlink_replacement_callback( $linkId, $contact )
{
    $linkId = absint( intval( $linkId ) );
    return site_url( 'superlinks/link/' . $linkId );
}

add_filter( 'wpgh_replacement_superlink', 'wpgh_superlink_replacement_callback', 10, 2 );

/**
 * Filter out http://http:// as a result of wpLink enforcing http:// even when using {replacements} which is really annoying.
 *
 * @param $content
 * @return string, the email content
 */
function wpgh_filter_out_double_http( $content )
{
    $schema = is_ssl()? 'https://' : 'http://';

    $content = str_replace( 'http://https://', $schema, $content );
    $content = str_replace( 'http://http://', $schema, $content );

    return $content;
}

add_filter( 'wpgh_the_email_content', 'wpgh_filter_out_double_http' );
add_filter( 'wpgh_sanitize_email_content', 'wpgh_filter_out_double_http' );

function wpgh_filter_out_http_superlink_prefix( $content )
{
    return preg_replace( '/http:\/\/({superlink\.\d})/', '${1}', $content );
}

add_filter( 'wpgh_the_email_content', 'wpgh_filter_out_http_superlink_prefix' );
add_filter( 'wpgh_sanitize_email_content', 'wpgh_filter_out_http_superlink_prefix' );

function wpgh_add_superlink()
{
	$superlink_name = sanitize_text_field( wp_unslash( $_POST['superlink_name'] ) );
	$superlink_target = sanitize_text_field( wp_unslash( $_POST['superlink_target'] ) );
	$superlink_tags = isset( $_POST['superlink_tags'] ) ? wpgh_validate_tags( $_POST['superlink_tags'] ): array() ;
	$superlink_id = wpgh_insert_new_superlink( $superlink_name, $superlink_target, $superlink_tags );
}

add_action( 'wpgh_add_superlink', 'wpgh_add_superlink' );

function wpgh_save_superlink( $id )
{
	$superlink_name = sanitize_text_field( wp_unslash( $_POST['superlink_name'] ) );
	$superlink_target = sanitize_text_field( wp_unslash( $_POST['superlink_target'] ) );
	$superlink_tags = wpgh_validate_tags( $_POST['superlink_tags'] );
	wpgh_update_superlink( $id, 'name', $superlink_name );
	wpgh_update_superlink( $id, 'target', $superlink_target );
	wpgh_update_superlink( $id, 'tags', $superlink_tags );
}

add_action( 'wpgh_update_superlink', 'wpgh_save_superlink' );