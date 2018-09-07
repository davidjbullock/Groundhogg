<?php
/**
 * Created by PhpStorm.
 * User: adria
 * Date: 2018-09-07
 * Time: 11:39 AM
 */

/**
 * Output content if and only if the current visitor is a contact.
 *
 * @param $atts
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

    switch ( $a[ 'needs' ] ){
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