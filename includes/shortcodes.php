<?php
/**
 * Created by PhpStorm.
 * User: adria
 * Date: 2018-09-07
 * Time: 11:39 AM
 */

/**
 * Mere contact replacements into page content with this shortcode.
 *
 * @param $atts array should be empty
 * @param string $content the content to perfotm the merge fields
 * @return string the updated content,.
 */
function wpgh_merge_replacements_shortcode( $atts, $content = '' )
{
    $contact = wpgh_get_the_contact();

    if ( ! $contact )
        return '';

    return wpgh_do_replacements( $contact->get_id(), $content );
}

add_shortcode( 'gh_replacements', 'wpgh_merge_replacements_shortcode' );

/**
 * Process the contact shortcode
 */
function wpgh_contact_replacement_shortcode( $atts )
{
	$a = shortcode_atts( array(
		'field' => 'first'
	), $atts );

	$contact = wpgh_get_the_contact();

	if ( ! $contact )
		return __( 'Friend', 'groundhogg' );

	if ( substr( $a['field'], 0, 1) === '_' ) {
		$new_replacement = $contact->get_meta( substr( $a['field'], 1) );
	} else {

		if ( strpos( $a['field'], '.' ) > 0 ){

			$parts = explode( '.', $a['field'] );

			$function = $parts[0];
			$arg = $parts[1];
			$new_replacement = apply_filters( 'wpgh_replacement_' . $function, $arg, $contact );

		} else {
			$new_replacement = apply_filters( 'wpgh_replacement_' . $a['field'], $contact );
		}
	}

	return $new_replacement;
}

add_shortcode( 'gh_contact', 'wpgh_contact_replacement_shortcode' );

/**
 * Output content if and only if the current visitor is a contact.
 *
 * @param $atts[]
 * @param string $content
 * @return string
 */
function wpgh_is_contact_shortcode( $atts, $content='' )
{
    $contact = wpgh_get_current_contact();

    if ( $contact ) {
        return $content;
    } else {
        return '';
    }
}

add_shortcode( 'gh_is_contact', 'wpgh_is_contact_shortcode' );

/**
 * Output content if and only if the current visitor is NOT a contact
 *
 * @param $atts
 * @param string $content
 * @return string
 */
function wpgh_is_not_contact_shortcode( $atts, $content='' )
{
    $contact = wpgh_get_current_contact();

    if ( $contact ) {
        return '';
    } else {
        return $content;
    }
}

add_shortcode( 'gh_is_not_contact', 'wpgh_is_not_contact_shortcode' );

/**
 * Return the content if and only if the contact does have given tags
 *
 * @param $atts
 * @param string $content
 * @return string
 */
function wpgh_contact_has_tag_shortcode( $atts, $content='' )
{
    $a = shortcode_atts( array(
        'tags' => '',
        'has' => 'all'
    ), $atts );

    $tags = explode( ',', $a[ 'tags' ] );
    $tags = array_map( 'trim', $tags );
    $tags = array_map( 'intval', $tags );

    $contact = wpgh_get_current_contact();

    if ( ! $contact ) {
        return '';
    }

    switch ( $a[ 'has' ] ){
        case 'all':
            foreach ( $tags as $tag ){
                if ( ! $contact->has_tag( $tag ) ) {
                    return '';
                }
            }
            return $content;
            break;
        case 'one':
        case 'single':
        case '1':
            foreach ( $tags as $tag ){
                if ( $contact->has_tag( $tag ) ) {
                    return $content;
                }
            }
            return '';

            break;
        default:
            return '';
    }
}

add_shortcode( 'gh_has_tags', 'wpgh_contact_has_tag_shortcode' );


/**
 * Return content if and only if the contact does not have the given tags
 *
 * @param $atts
 * @param string $content
 * @return string
 */
function wpgh_contact_does_not_have_tag_shortcode( $atts, $content='' )
{
    $a = shortcode_atts( array(
        'tags' => '',
        'needs' => 'all'
    ), $atts );

    $tags = explode( ',', $a[ 'tags' ] );
    $tags = array_map( 'trim', $tags );
    $tags = array_map( 'intval', $tags );

    $contact = wpgh_get_current_contact();

    if ( ! $contact ) {
        return '';
    }

    switch ( $a[ 'needs' ] ){
        case 'all':
            foreach ( $tags as $tag ){
                if ( $contact->has_tag( $tag ) ) {
                    return '';
                }
            }
            return $content;
            break;
        case 'one':
        case 'single':
        case '1':
            foreach ( $tags as $tag ){
                if ( ! $contact->has_tag( $tag ) ) {
                    return $content;
                }
            }
            return '';
            break;
        default:
            return $content;
    }
}

add_shortcode( 'gh_does_not_have_tags', 'wpgh_contact_does_not_have_tag_shortcode' );

/**
 * Return contents if and only if the contact is logged in
 *
 * @param $atts
 * @param $content
 *
 * @return string
 */
function wpgh_is_logged_in( $atts, $content )
{
    if ( is_user_logged_in() )
        return do_shortcode( $content );
    else
        return '';
}

add_shortcode( 'gh_is_logged_in', 'wpgh_is_logged_in' );

/**
 * Return content if user is no logged in.
 *
 * @param $atts
 * @param $content
 * @return string
 */
function wpgh_is_not_logged_in( $atts, $content ){
    if ( ! is_user_logged_in() )
        return do_shortcode( $content );
    else
        return '';
}

add_shortcode( 'gh_is_not_logged_in', 'wpgh_is_not_logged_in' );
