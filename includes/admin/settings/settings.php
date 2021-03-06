<?php

/* Groundhogg Settings Page */
class WPGH_Settings_Page
{

	public function __construct()
    {
		//add_action( 'admin_menu', array( $this, 'wpgh_create_settings' ) );
		add_action( 'admin_init', array( $this, 'wpgh_setup_sections' ) );
		add_action( 'admin_init', array( $this, 'wpgh_setup_fields' ) );

        if ( ! class_exists( 'WPGH_Extensions_Manager' ) )
            include dirname( __FILE__ ) . '/../extensions/module-manager.php';

        //todo find new file to put this line.
        add_action( 'admin_init', array( 'WPGH_Extension_Manager', 'check_for_updates' ) );

        if ( isset( $_GET['page'] ) && $_GET['page'] === 'groundhogg' )
        {
            add_action( 'admin_init', array( 'WPGH_Extension_Manager', 'perform_activation' ) );
            add_action( 'admin_init', array( $this, 'perform_tools' ) );
        }
    }

    public function perform_tools()
    {
        do_action( 'gh_settings_tools' );
    }

	public function wpgh_settings_content()
    {
        wp_enqueue_style( 'select2' );
        wp_enqueue_script( 'select2' );

        ?>
		<div class="wrap">
			<h1>Groundhogg <?php _e( 'Settings' ); ?></h1>
			<?php settings_errors(); ?>
            <?php if ( isset( $_GET[ 'token' ] ) ) :
                ?><div class="notice notice-success is-dismissible"><p><strong><?php _e( 'Connected to Groundhogg!', 'groundhogg' ); ?></strong></p></div><?php
            endif; ?>
            <?php $active_tab = isset( $_GET[ 'tab' ] ) ?  $_GET[ 'tab' ] : 'general'; ?>
            <?php

            switch ( $active_tab ){
                case 'extensions':
                case 'tools':
                    $action = '';
                    break;
                default:
                    $action = 'options.php';
                    break;
            }

            $tabs = array(
                'general'       => 'General',
                'marketing'     => 'Marketing',
                'email'        => 'Email',
                'tools'         => 'Tools',
                'extensions'    => 'Licenses'
            );

            $tabs = apply_filters( 'gh_settings_tabs', $tabs );
            ?>

			<form method="POST" enctype="multipart/form-data" action="<?php echo $action; ?>">
                <h2 class="nav-tab-wrapper">
                    <?php foreach ( $tabs as $tab_id => $tab_name ): ?>
                        <a href="?page=groundhogg&tab=<?php echo $tab_id; ?>" class="nav-tab <?php echo $active_tab == $tab_id ? 'nav-tab-active' : ''; ?>"><?php _e( $tab_name, 'groundhogg'); ?></a>
                    <?php endforeach; ?>
                </h2>
                <?php switch ( $active_tab ):
                    case 'general':
                        settings_fields( 'groundhogg_business_settings' );
                        do_settings_sections( 'groundhogg_business_settings' );
                        submit_button();

                        break;
                    case 'marketing':
                        settings_fields( 'groundhogg_marketing_settings' );
                        do_settings_sections( 'groundhogg_marketing_settings' );
                        submit_button();

                        break;
                    case 'email':

//                        GH_Account::$instance->connect_button();

                        settings_fields( 'groundhogg_email_settings' );
                        do_settings_sections( 'groundhogg_email_settings' );
                        submit_button();

                        break;
                    case 'tools':
                        ?>
                        <div id="poststuff">
                            <!-- Begin Import Tool -->
                            <div class="postbox">
                                <h2 class="hndle"><?php _e( 'Import Contacts', 'groundhogg' ); ?></h2>
                                <div class="inside">
                                    <p>
                                        <input type="file" id="contacts" name="contacts" accept=".csv" >
                                    </p>
                                    <p class="description"><?php _e( 'Columns: first_name, last_name, email, custom_field, another_custom_field...' ) ?></p>
                                    <?php $tag_args = array();
                                    $tag_args[ 'id' ] = 'import_tags';
                                    $tag_args[ 'name' ] = 'import_tags[]';
                                    $tag_args[ 'width' ] = '100%';
                                    $tag_args[ 'class' ] = 'hidden'; ?>
                                    <?php wpgh_dropdown_tags( $tag_args ); ?>
                                    <p class="description"><?php _e( 'These tags will be applied to the contacts upon importing.', 'groundhogg' ); ?></p>
                                    <?php submit_button( 'Import', 'primary', 'import_contacts', false ); ?>
                                </div>
                            </div>
                            <!-- End Import Tool -->

                            <!-- Begin Export Tool -->
                            <div class="postbox">
                                <h2 class="hndle"><?php _e( 'Export Contacts', 'groundhogg' ); ?></h2>
                                <div class="inside">
                                    <p class="description"><?php _e( 'Export contacts to a .CSV file. This will download to your browser.', 'groundhogg' ); ?></p>
                                    <?php $tag_args = array();
                                    $tag_args[ 'id' ] = 'export_tags';
                                    $tag_args[ 'name' ] = 'export_tags[]';
                                    $tag_args[ 'width' ] = '100%';
                                    $tag_args[ 'class' ] = 'hidden'; ?>
                                    <?php wpgh_dropdown_tags( $tag_args ); ?>
                                    <p class="description"><?php _e( 'Contacts with these tags will be exported.', 'groundhogg' ); ?></p>
                                    <?php submit_button( 'Export', 'primary', 'export_contacts', false ); ?>
                                </div>
                            </div>
                            <!-- End Export Tool -->
                        </div>

                        <?php


                        break;
                    case 'extensions':

                        ?><div id="poststuff">
                        <p><?php _e( 'Enter your extension license keys here to receive updates for purchased extensions. If your license key has expired, <a href="https://groundhogg.io/account/">please renew your license.</a>' ); ?></p><?php
                        WPGH_Extension_Manager::extension_page();
                        ?></div><?php
                        break;

                    default:

                        do_action( 'grounhogg_' . $active_tab . '_settings'  );
                        submit_button();

                        break;

                    endswitch;
                    ?>
			</form>
		</div> <?php
	}

	public function wpgh_setup_sections()
    {
        add_settings_section( 'business_info', 'Edit Business Settings', array(), 'groundhogg_business_settings');
        add_settings_section( 'contact_endpoints', __ ( 'Contact Endpoints' , 'grounhogg' ), array(), 'groundhogg_marketing_settings');
        add_settings_section( 'form_settings', __ ( 'Form Settings' , 'grounhogg' ), array(), 'groundhogg_marketing_settings');
        add_settings_section( 'compliance', __( 'Compliance Settings', 'groundhogg' ), array(), 'groundhogg_marketing_settings');
//        add_settings_section( 'default_mail_settings', 'Default Mail Settings', array(), 'groundhogg_email_settings' );
        add_settings_section( 'email_bounces', 'Email Bounces', array(), 'groundhogg_email_settings' );
    }

	public function wpgh_setup_fields()
    {
		$fields = array(
			array(
				'label' => 'Business Name',
				'id' => 'gh_business_name',
				'type' => 'text',
                'placeholder' => 'My Awesome Company',
                'desc' => 'As it should appear in your email footer.',
                'section' => 'business_info',
                'page' => 'groundhogg_business_settings'
			),
			array(
				'label' => 'Street Address 1',
				'id' => 'gh_street_address_1',
				'type' => 'text',
				'placeholder' => '123 Awesome St',
                'desc' => 'As it should appear in your email footer.',
                'section' => 'business_info',
                'page' => 'groundhogg_business_settings'
			),
			array(
				'label' => 'Street Address 2',
				'id' => 'gh_street_address_2',
				'type' => 'text',
                'placeholder' => 'Unit 0',
                'desc' => '(Optional) As it should appear in your email footer.',
                'section' => 'business_info',
                'page' => 'groundhogg_business_settings'
			),
            array(
                'label' => 'City',
                'id' => 'gh_city',
                'type' => 'text',
                'placeholder' => 'Nowhere',
                'desc' => 'As it should appear in your email footer.',
                'section' => 'business_info',
                'page' => 'groundhogg_business_settings'
            ),
			array(
				'label' => 'Postal/Zip Code',
				'id' => 'gh_zip_or_postal',
				'type' => 'text',
				'placeholder' => 'A1A 1A1',
                'desc' => 'As it should appear in your email footer.',
                'section' => 'business_info',
                'page' => 'groundhogg_business_settings'
			),
			array(
				'label' => 'State/Province',
				'id' => 'gh_region',
				'type' => 'text',
				'placeholder' => 'Somewhere',
                'desc' => 'As it should appear in your email footer.',
                'section' => 'business_info',
                'page' => 'groundhogg_business_settings'
			),
			array(
				'label' => 'Country',
				'id' => 'gh_country',
				'type' => 'text',
				'placeholder' => 'Canada',
                'desc' => 'As it should appear in your email footer.',
                'section' => 'business_info',
                'page' => 'groundhogg_business_settings'
            ),
            array(
                'label' => 'Phone',
                'id' => 'gh_phone',
                'type' => 'tel',
                'placeholder' => '555-555-5555',
                'section' => 'business_info',
                'page' => 'groundhogg_business_settings'
            ),
            array(
                'label' => 'Phone',
                'id' => 'gh_phone',
                'type' => 'tel',
                'placeholder' => '555-555-5555',
                'desc' => 'As it should appear in your email footer.',
                'section' => 'business_info',
                'page' => 'groundhogg_business_settings'
            ),
            array(
                'label' => 'Email Confirmation Page',
                'id' => 'gh_email_confirmation_page',
                'type' => 'page',
                'desc' => 'Page contacts see when they confirm their email.',
                'section' => 'contact_endpoints',
                'page' => 'groundhogg_marketing_settings'
            ),
            array(
                'label' => 'Unsubscribe Page',
                'id' => 'gh_unsubscribe_page',
                'type' => 'page',
                'desc' => 'Page contacts see when they unsubscribe.',
                'section' => 'contact_endpoints',
                'page' => 'groundhogg_marketing_settings'
            ),
            array(
                'label' => 'Email Preferences Page',
                'id' => 'gh_email_preferences_page',
                'type' => 'page',
                'desc' => 'Page where contacts can manage their email preferences.',
                'section' => 'contact_endpoints',
                'page' => 'groundhogg_marketing_settings'
            ),
            array(
                'label' => 'Privacy Policy',
                'id' => 'gh_privacy_policy',
                'type' => 'page',
                'desc' => 'Link to your privacy policy.',
                'section' => 'compliance',
                'page' => 'groundhogg_marketing_settings'
            ),
            array(
                'label' => 'Terms & Conditions (Terms of Service)',
                'id' => 'gh_terms',
                'type' => 'page',
                'desc' => 'Link to your terms & conditions.',
                'section' => 'compliance',
                'page' => 'groundhogg_marketing_settings'
            ),
            array(
                'label' => 'Only send to confirmed emails.',
                'id' => 'gh_strict_confirmation',
                'type' => 'checkbox',
                'desc' => 'This will stop emails being sent to contacts who do not have confirmed emails outside of the below grace period.',
                'section' => 'compliance',
                'page' => 'groundhogg_marketing_settings',
                'options' => array(
                    'on' => 'Enable',
                ),
            ),
            array(
                'label' => 'Email confirmation grace Period',
                'id' => 'gh_confirmation_grace_period',
                'type' => 'number',
                'desc' => 'The number of days for which you can send an email to a contact after they are created but their email has not been confirmed. The default is 14 days.',
                'placeholder' => '14',
                'section' => 'compliance',
                'page' => 'groundhogg_marketing_settings',
            ),
            array(
                'label' => 'Enable GDPR features.',
                'id' => 'gh_enable_gdpr',
                'type' => 'checkbox',
                'desc' => 'This will add a consent box to your forms as well as a "Delete Everything" Button to your email preferences page.',
                'section' => 'compliance',
                'page' => 'groundhogg_marketing_settings',
                'options' => array(
                    'on' => 'Enable',
                ),
            ),
            array(
                'label' => 'Do not send email without consent.',
                'id' => 'gh_strict_gdpr',
                'type' => 'checkbox',
                'desc' => 'This will prevent your system from sending emails to contacts for which you do not have explicit consent. Only works if GDPR features are enabled.',
                'section' => 'compliance',
                'page' => 'groundhogg_marketing_settings',
                'options' => array(
                    'on' => 'Enable',
                ),
            ),
            array(
                'label' => 'Send mail with default SMTP provider or Groundhogg Mail',
                'id' => 'gh_mail_server',
                'type' => 'radio',
                'desc' => 'You may choose to send mail using your default provider (your own server) or you can use Groundhogg to send mail. 
                Groundhogg Mail is an inexpensive and monitored mail service designed to get your email to the inbox.',
                'section' => 'default_mail_settings',
                'page' => 'groundhogg_email_settings',
                'options' => array(
                    'groundhogg' => 'Groundhogg Mail',
                    'default' => 'Default Mail Service',
                ),
            ),
            array(
                'label' => 'Enable Recaptcha on forms',
                'id' => 'gh_enable_recaptcha',
                'type' => 'checkbox',
                'desc' => 'Add a google recaptcha to all your forms made with the [gh_form] shortcode',
                'section' => 'form_settings',
                'page' => 'groundhogg_marketing_settings',
                'options' => array(
                    'on' => 'Enable',
                ),
            ),
            array(
                'label' => 'Recaptcha Site Key',
                'id' => 'gh_recaptcha_site_key',
                'type' => 'text',
                'placeholder' => '',
                'desc' => 'This is the key which faces the users on the front-end',
                'section' => 'form_settings',
                'page' => 'groundhogg_marketing_settings',
            ),
            array(
                'label' => 'Recaptcha Secret Key',
                'id' => 'gh_recaptcha_secret_key',
                'type' => 'text',
                'desc' => 'Never ever ever share this with anyone!',
                'placeholder' => '',
                'section' => 'form_settings',
                'page' => 'groundhogg_marketing_settings',
            ),
            array(
                'label' => 'Bounce Inbox',
                'id' => 'gh_bounce_inbox',
                'type' => 'text',
                'placeholder' => 'bounce@' . ( ( substr( $_SERVER['SERVER_NAME'], 0, 4 ) == 'www.' ) ?  substr( $_SERVER['SERVER_NAME'], 4 ) : $_SERVER['SERVER_NAME'] ),
                'desc' => 'This is the inbox which emails will be sent to.',
                'section' => 'email_bounces',
                'page' => 'groundhogg_email_settings',
                'class' => 'regular-text'
            ),
            array(
                'label' => 'Bounce Inbox Password',
                'id' => 'gh_bounce_inbox_password',
                'type' => 'password',
                'placeholder' => '1234',
                'desc' => 'This password to access the inbox.',
                'section' => 'email_bounces',
                'page' => 'groundhogg_email_settings',
            ),

		);
		foreach( $fields as $field ){
			add_settings_field( $field['id'], $field['label'], array( $this, 'wpgh_field_callback' ), $field['page'] , $field['section'], $field );
			register_setting( $field['page'], $field['id'] );
		}
	}

	public function wpgh_field_callback( $field )
    {
		$value = get_option( $field['id'] );
		switch ( $field['type'] ) {
            case 'radio':
            case 'checkbox':
                if( ! empty ( $field['options'] ) && is_array( $field['options'] ) ) {
                    $options_markup = '';
                    $iterator = 0;

                    if ( ! is_array( $value ) ){
                        $value = array( $value );
                    }

                    foreach( $field['options'] as $key => $label ) {
                        $iterator++;
                        $options_markup.= sprintf('<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>',
                            $field['id'],
                            $field['type'],
                            $key,
                            checked( $value[array_search($key, $value, true)], $key, false ),
                            $label,
                            $iterator
                        );
                    }
                    printf( '<fieldset>%s</fieldset>',
                        $options_markup
                    );
                }
                break;
            case 'textarea':
                printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>',
                    $field['id'],
                    $field['placeholder'],
                    $value
                );
                break;
            case 'wysiwyg':
                wp_editor($value, $field['id']);
                break;
            case 'page':
                if ( $value ){ $args['selected'] = $value; }
                $args['name'] = $field['id'] ;
                $args['id'] = $field['id'];

                wp_dropdown_pages( $args );
                printf( '<script>jQuery(function($){$( "#%1$s" ).width(200);$( "#%1$s" ).select2()});</script>',
                    $field['id']
                );

                break;
			default:
				printf( '<input class="regular-text" name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />',
					$field['id'],
					$field['type'],
					$field['placeholder'],
					$value
				);
		}
		if( isset( $field['desc'] ) && $desc = $field['desc'] ) {
			printf( '<p class="description">%s </p>', $desc );
		}
	}
}