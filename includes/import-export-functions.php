<?php
/**
 * Created by PhpStorm.
 * User: adria
 * Date: 2018-08-29
 * Time: 8:56 AM
 */

/**
 * Deconstructs the given array and builds a full funnel.
 *
 * @param $import array
 * @return bool|int whether the import was successful or the ID
 */
function wpgh_import_funnel( $import )
{
    if ( ! is_array( $import ) )
        return false;

    $title = $import[ 'title' ];

    $funnel_id = wpgh_insert_new_funnel( $title, 'inactive' );

    $steps = $import[ 'steps' ];

    $valid_actions = wpgh_get_funnel_actions();
    $valid_benchmarks = wpgh_get_funnel_benchmarks();

    foreach ( $steps as $i => $step_args )
    {

        $step_title = $step_args['title'];
        $step_group = $step_args['group'];
        $step_type  = $step_args['type'];

        if ( ! isset( $valid_actions[$step_type] ) && ! isset( $valid_benchmarks[$step_type] ) )
            continue;

        $step_id = wpgh_insert_new_funnel_step( $funnel_id, $step_title, 'ready', $step_group, $step_type, $i + 1 );

        $step_meta = $step_args['meta'];

        foreach ( $step_meta as $key => $value )
        {
            if ( is_array( $value ) ){
                wpgh_update_step_meta( $step_id, $key, $value[0] );
            } else {
                wpgh_update_step_meta( $step_id, $key, $value );
            }
        }

        $import_args = $step_args[ 'args' ];

        do_action( 'wpgh_import_' . $step_type, $step_id, $import_args );

    }

    return $funnel_id;
}

/**
 * Export the funnel to a .funnel File.
 * Use filters to offer proper meta or arguments.
 */
function wpgh_export_funnel()
{
    if ( ! isset( $_GET[ 'funnel' ] ) || ! isset( $_GET[ 'export' ] ) )
        return;

    $id = intval( $_GET['funnel'] );

    $funnel = wpgh_get_funnel_by_id( $id );

    if ( ! $funnel )
        return;

    $export_string = wpgh_convert_funnel_to_json( $id );

    if ( ! $export_string )
        return;

    $filename = "groundhogg_funnel-" . $funnel->funnel_title . ' - '. date("Y-m-d_H-i", time() );

    header("Content-type: text/plain");

    header( "Content-disposition: attachment; filename=".$filename.".funnel");

    $file = fopen('php://output', 'w');

    fputs( $file, $export_string );

    fclose($file);

    exit();
}

add_action( 'init', 'wpgh_export_funnel' );

/**
 * Convert the funnel into a json object so it can be duplicated fairly easily.
 *
 * @param $funnel_id int the ID of the funnel to convert.
 * @return false|string the json string of a converted funnel or false on failure.
 */
function wpgh_convert_funnel_to_json( $funnel_id )
{
    if ( ! $funnel_id || ! is_int( $funnel_id) )
        return false;

    $funnel = wpgh_get_funnel_by_id( $funnel_id );

    if ( ! $funnel )
        return false;

    $export = array();

    $export['title'] = $funnel->funnel_title;

    $export[ 'steps' ] = array();

    $steps = wpgh_get_funnel_steps( $funnel_id );

    if ( ! $steps )
        return false;

    foreach ( $steps as $i => $step_id )
    {
        $step = wpgh_get_funnel_step_by_id( $step_id );

        $export['steps'][$i] = array();
        $export['steps'][$i]['title'] = $step->funnelstep_title;
        $export['steps'][$i]['group'] = $step->funnelstep_group;
        $export['steps'][$i]['type']  = $step->funnelstep_type;
        $export['steps'][$i]['meta']  = wpgh_get_step_meta( $step_id );
        $export['steps'][$i]['args']  = apply_filters( 'wpgh_export_' . $step->funnelstep_type, array(), $step_id );
        $export['steps'][$i] = apply_filters( 'wpgh_step_export_args', $export['steps'][$i], $step_id );
    }

    return json_encode( $export );
}

/**
 * Returns an array of args for the send email step.
 *
 * @param $args array args for the step.
 * @param $step_id int the ID of the step being exported
 * @return array
 */
function wpgh_export_send_email_step( $args, $step_id )
{
    $email_id = intval( wpgh_get_step_meta( $step_id, 'email_id' , true) );

    $email = wpgh_get_email_by_id( $email_id );

    if ( ! $email )
        return $args;

    $args[ 'subject'] = $email->subject;
    $args[ 'pre_header' ] = $email->pre_header;
    $args[ 'content' ] = $email->content;

    return $args;
}

add_filter( 'wpgh_export_send_email', 'wpgh_export_send_email_step', 10, 2 );

/**
 * Create a new email and set the step email_id to the ID of the new email.
 *
 * @param $step_id int ID of the new step to import
 * @param $args array list of args to provide criteria for import.
 */
function wpgh_import_send_email_step( $step_id, $args )
{
    $id = wpgh_insert_new_email(
        $args['content'],
        $args['subject'],
        $args['pre_header'],
        get_current_user_id(),
        get_current_user_id()
    );

    wpgh_update_step_meta( $step_id, 'email_id', $id );
}

add_action( 'wpgh_import_send_email', 'wpgh_import_send_email_step', 10, 2 );

/**
 * Export all tag related steps
 *
 * @param $args array of args
 * @param $step_id int ID of the step to export
 * @return array of tag names
 */
function wpgh_export_tags( $args, $step_id )
{
    $args['tags'] = array();

    $tags = wpgh_get_step_meta( $step_id, 'tags', true );

    if ( empty( $tags ) )
        return $args;

    foreach ( $tags as $tag_id )
    {
        $args[ 'tags' ][] = wpgh_get_tag_name( intval( $tag_id ) );
    }

    return $args;
}

add_filter( 'wpgh_export_' . 'apply_tag', 'wpgh_export_tags', 10, 2 );
add_filter( 'wpgh_export_' . 'remove_tag', 'wpgh_export_tags', 10, 2 );
add_filter( 'wpgh_export_' . 'tag_applied', 'wpgh_export_tags', 10, 2 );
add_filter( 'wpgh_export_' . 'tag_removed', 'wpgh_export_tags', 10, 2 );

/**
 * Import & create the tags and set the array of tags as the new tags.
 *
 * @param $step_id int ID of the step which is currently being imported
 * @param $args array of args
 */
function wpgh_import_tags( $step_id, $args )
{
    $tags = wpgh_validate_tags( $args[ 'tags' ] );
    wpgh_update_step_meta( $step_id, 'tags', $tags );
}

add_action( 'wpgh_import_'. 'apply_tag', 'wpgh_import_tags', 10, 2 );
add_action( 'wpgh_import_'. 'remove_tag', 'wpgh_import_tags', 10, 2 );
add_action( 'wpgh_import_'. 'tag_applied', 'wpgh_import_tags', 10, 2 );
add_action( 'wpgh_import_'. 'tag_removed', 'wpgh_import_tags', 10, 2 );

/**
 * import contacts with a CSV.
 */
function wpgh_import_contacts()
{
    //todo security check...
    if ( ! isset( $_POST[ 'import_contacts' ] ))
            return;

    if ( ! current_user_can( 'gh_manage_contacts' ) ){
        wp_die( 'You do not have permission to do that.' );
    }

    if ( ! isset( $_FILES['contacts'] ) ){
        wp_die( 'No contacts supplied!' );
    }

    if ( isset(  $_POST[ 'import_tags' ] ) ){
        $tags = wpgh_validate_tags( $_POST[ 'import_tags' ] );
    }

    if ( $_FILES['contacts']['error'] == UPLOAD_ERR_OK && is_uploaded_file( $_FILES['contacts']['tmp_name'] ) ) {

        if ( strpos( $_FILES['contacts']['name'], '.csv' ) === false ){
            wp_die( 'You did not upload a csv!' );
        }

        $row = 0;
        if ( ( $handle = fopen( $_FILES['contacts']['tmp_name'], "r" ) ) !== FALSE ) {

            $columns = fgetcsv( $handle, 1000, "," );

            $first_index = array_search( 'first_name', $columns );
            $last_index  = array_search( 'last_name', $columns );
            $email_index = array_search( 'email', $columns );

            $row++;

            while ( ( $data = fgetcsv( $handle, 1000, "," ) ) !== FALSE ) {

                $first_name = $data[ $first_index ];
                $last_name  = $data[ $last_index ];
                $email      = $data[ $email_index ];

                $cid = wpgh_quick_add_contact( $email, $first_name, $last_name );

                if ( ! $cid )
                    continue;

                unset( $data[ $first_index ] );
                unset( $data[ $last_index ] );
                unset( $data[ $email_index ] );

                foreach ( $data as $i => $attr ){
                    $meta_key = sanitize_key( $columns[$i] );
                    wpgh_update_contact_meta( $cid, $meta_key, sanitize_text_field( $attr ) );
                }

                foreach ( $tags as $tag_id )
                {
                    wpgh_apply_tag( $cid, $tag_id );
                }

                $row++;
            }

            fclose($handle);

            add_settings_error( 'import', esc_attr( 'imported' ), __( 'Imported Contacts' ), 'updated' );
        } else {
            wp_die('Oops, something went wrong.');
        }
    } else {
        wp_die( 'Please upload a proper file!' );
    }
}

add_action( 'gh_settings_tools', 'wpgh_import_contacts' );

/**
 * import contacts with a CSV.
 */
function wpgh_export_contacts()
{
    if ( ! isset( $_POST[ 'export_contacts' ] ) )
        return;




}