<?php
/**
 * Account Created Funnel Step
 *
 * Html for the accoutn created funnel step in the Funnel builder
 *
 * @package     groundhogg
 * @subpackage  Includes/Funnels/Steps
 * @copyright   Copyright (c) 2018, Adrian Tobey
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

function wpgh_account_created_funnel_step_html( $step_id )
{

    $account_role = wpgh_get_step_meta( $step_id, 'role', true );

    $args = array();

    if ( ! $account_role )
        $account_role = 'subscriber'

    ?>

    <table class="form-table">
        <tbody>
        <tr>
            <th><?php echo esc_html__( 'Run when the following type of account is created', 'groundhogg' ); ?>:</th>
            <td>
                <select name="<?php echo wpgh_prefix_step_meta( $step_id, 'role' ); ?>" id="<?php echo wpgh_prefix_step_meta( $step_id, 'role' ); ?>">
                    <?php wp_dropdown_roles( $account_role ); ?>
                </select>
                <script>jQuery(document).ready(function(){jQuery( '#<?php echo wpgh_prefix_step_meta( $step_id, 'role' ); ?>' ).select2()});</script>
            </td>
        </tr>
        </tbody>
    </table>

    <?php
}

add_action( 'wpgh_get_step_settings_account_created', 'wpgh_account_created_funnel_step_html' );

function wpgh_account_created_icon_html()
{
    ?>
    <div class="dashicons dashicons-admin-users"></div><p>User Created</p>
    <?php
}

add_action( 'wpgh_benchmark_element_icon_html_account_created', 'wpgh_account_created_icon_html' );

function wpgh_save_account_created_funnel_step( $step_id )
{
    $role = sanitize_text_field( $_POST[ wpgh_prefix_step_meta( $step_id, 'role' ) ] );
    wpgh_update_step_meta( $step_id, 'role', $role );
}

add_action( 'wpgh_save_step_account_created', 'wpgh_save_account_created_funnel_step' );