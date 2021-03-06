<?php
/**
 * Created by PhpStorm.
 * User: adria
 * Date: 2018-08-24
 * Time: 9:14 AM
 */


/**
 * Insert activity into the log.
 *
 * @param $contact  int the ID of a contact
 * @param $activity string the Type of activity
 * @param $subject  int the ID of the subject, for example a form or an email
 * @param $funnel   int id of the funnel which they are currently in
 * @param $step     int id of the funnel step the contact came from
 * @param $ref      string the referer URL or destination URL
 * @return bool|false|int
 */
function wpgh_log_activity( $contact, $funnel, $step, $activity, $subject, $ref='' )
{
    if ( ! is_int( $contact ) || ! is_int( $subject ) )
        return false;

    global $wpdb;

    return $wpdb->insert(
        $wpdb->prefix . WPGH_ACTIVITY,
        array(
            'timestamp'     => time(),
            'contact_id'    => absint( $contact ),
            'funnel_id'     => absint( $funnel ),
            'step_id'       => absint( $step ),
            'activity_type' => $activity,
            'object_id'     => absint( $subject ),
            'referer'      => $ref
        )
    );
}

/**
 * Return a row of activity.
 *
 * @param $contact
 * @param $funnel
 * @param $step
 * @param $activity
 * @param $subject
 * @return object
 */
function wpgh_get_activity( $contact, $funnel, $step, $activity, $subject )
{
    global $wpdb;

    $contact = intval( $contact );
    $funnel = intval( $funnel );
    $step = intval( $step );
    $subject = intval( $subject );

    $table = $wpdb->prefix . WPGH_ACTIVITY;

    $query = $wpdb->prepare(
        "SELECT * FROM $table
        WHERE contact_id = %d AND funnel_id = %d AND step_id = %d AND activity_type = %s AND object_id = %d"
        , $contact, $funnel, $step, $activity, $subject );

    return $wpdb->get_row( $query );


}

/**
 * Returns true if a similar activity for the contact given has occurred in the past.
 *
 * @param $contact int ID of the contact
 * @param $funnel int ID of the funnel
 * @param $step int ID of the funnel step
 * @param $activity string type of activity
 * @param $subject int ID of the subject matter.
 * @return bool whether the activity exists.
 */
function wpgh_activity_exists( $contact, $funnel, $step, $activity, $subject )
{
    global $wpdb;

    $table = $wpdb->prefix . WPGH_ACTIVITY;

    $query = $wpdb->prepare(
        "SELECT * FROM $table
        WHERE contact_id = %d AND funnel_id = %d AND step_id = %d AND activity_type = %s AND object_id = %d"
    , $contact, $funnel, $step, $activity, $subject );

    $results = $wpdb->get_results( $query );

    return ! empty( $results );
}

define( 'WPGH_ACTIVITY', 'activity_log' );
define( 'WPGH_ACTIVITY_DB_VERSION', '0.6' );

/**
 * Create the activity database table.
 * Activity will contain items such as Email Opens, Link clinks, Unsubscribes, form fills etc...
 */
function wpgh_create_activity_db()
{
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . WPGH_ACTIVITY;

    if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name && version_compare( get_option( 'wpgh_activity_db_version' ), WPGH_ACTIVITY_DB_VERSION, '==' ) )
        return;

    $sql = "CREATE TABLE $table_name (
	  ID bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
      timestamp bigint(20) unsigned NOT NULL,
      contact_id bigint(20) unsigned NOT NULL,
      funnel_id bigint(20) unsigned NOT NULL,
      step_id bigint(20) unsigned NOT NULL,
      activity_type VARCHAR(20) NOT NULL,
      object_id bigint(20) unsigned NOT NULL,
      referer text NOT NULL,
      KEY timestamp (timestamp),
      KEY contact_id (contact_id),
      KEY funnel_id (funnel_id),
      KEY step_id (step_id),
      KEY activity_type (activity_type),
      KEY object_id (object_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta( $sql );

    update_option( 'wpgh_activity_db_version', WPGH_ACTIVITY_DB_VERSION );
}
