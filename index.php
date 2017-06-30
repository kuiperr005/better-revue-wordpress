<?php
/*
Plugin Name: RKMD Revue Wordpress
Description: Modified original Revue plugin that allows you to quickly add a signup form for your Revue list.
Version: 1.1.0
Author: RK Mediadesign
Author URI: https://rkmediadesign.nl
*/

define( 'RKMD_REVUE_TRANS_DOMAIN', 'revue' );
$_revue_printed_forms = 0;

include_once 'widget.php';

function rkmd_revue_ajaxurl() {
	?>
	<script type="text/javascript">
		var rkmd_revue_ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
	</script>
	<?php
}

add_action( 'wp_head', 'rkmd_revue_ajaxurl' );

function rkmd_revue_admin_settings() {
	?>
	<div class="wrap wrap-revue">
		<img src="<?php echo plugins_url( 'images/logo.png', __FILE__ ); ?>" style="margin-top: 20px; width: 100px;"/>

		<p style="font-size: 18px; line-height: 28px;margin-bottom: 40px;">
			Om verbinding te maken met Revue, moet je je API key invullen. Je kunt je key vinden onderaan de <a target="_blank" style="color: #E15718;" href="https://www.getrevue.co/app/integrations">Integrations page.</a>
		</p>

		<form method="post" action="options.php">
			<?php
			// This prints out all hidden setting fields
			settings_fields( 'rkmd_revue_general' );
			do_settings_sections( 'rkmd-revue-settings' );
			submit_button();
			?>
		</form>

		<p style="color: #999;font-size: 12px;margin-top: 20px;">Hulp nodig? <a style="color: #999;" href="mailto:support@getrevue.co">stuur
				ons een e-mail</a>.</p>
	</div>
	<?php
}

function rkmd_revue_admin_menu() {
	add_options_page(
		'Revue',
		'Revue',
		'manage_options',
		'rkmd-revue-settings',
		'rkmd_revue_admin_settings'
	);
}

add_action( 'admin_menu', 'rkmd_revue_admin_menu' );

function rkmd_revue_api_key_callback() {
	$options = get_option( 'rkmd_revue_general' );
	printf(
		'<input type="text" id="api_key" name="rkmd_revue_general[api_key]" value="%s" />',
		isset( $options['api_key'] ) ? esc_attr( $options['api_key'] ) : ''
	);
}

function rkmd_revue_page_init() {

	register_setting(
		'rkmd_revue_general',
		'rkmd_revue_general'
	);

	add_settings_section(
		'rkmd_revue_api_settings', // ID
		__( 'API Settings', RKMD_REVUE_TRANS_DOMAIN ), // Title
		null,
		'rkmd-revue-settings' // Page
	);

	add_settings_field(
		'api_key', // ID
		__( 'Vul hier je API key in:', RKMD_REVUE_TRANS_DOMAIN ),
		'rkmd_revue_api_key_callback',
		'rkmd-revue-settings', // Page
		'rkmd_revue_api_settings' // Section
	);
}

add_action( 'admin_init', 'rkmd_revue_page_init' );

function rkmd_revue_enqueue_scripts() {
	wp_enqueue_script( 'revue', plugin_dir_url( __FILE__ ) . 'revue.js', array( 'jquery' ), '1.1.0', true );
}

add_action( 'wp_enqueue_scripts', 'rkmd_revue_enqueue_scripts' );

function rkmd_revue_subscribe_callback() {
	// revue_subscribe( $_POST['email'], $_POST['first_name'], $_POST['last_name'] );
	rkmd_revue_subscribe( $_POST['email'] );

	header( 'Content-Type: application/json' );

	echo json_encode( array(
		'thank_you' => sprintf(
			'<p>Bedankt voor je inschrijving! Je kunt oudere nieuwsbrieven <a href="%s">hier</a> vinden.</p>',
			rkmd_revue_get_profile_url()
		)
	) );

	wp_die();
}

add_action( 'wp_ajax_rkmd_revue_subscribe', 'rkmd_revue_subscribe_callback' );
add_action( 'wp_ajax_nopriv_rkmd_revue_subscribe', 'rkmd_revue_subscribe_callback' );

function rkmd_revue_subscribe_form() {
	global $_revue_printed_forms;
	$_revue_printed_forms ++;

	if ( ! _rkmd_revue_key_provided() ) {
		return 'Je moet nog een API key invullen op de Revue settings page';
	}

	$res = '';

	$res .= '<div class="revue-subscribe">';
	$res .= '<form class="revue-subscribeform" method="post" id="revue-subscribeform-' . $_revue_printed_forms . '">';
	$res .= _rkmd_revue_print_field( 'E-mailadres', 'revue_email', 'email' );
	$res .= '<button type="submit">' . __( 'Inschrijven', RKMD_REVUE_TRANS_DOMAIN ) . '</button>';
	$res .= '<img class="revue-ajax-loader" src="' . plugin_dir_url( __FILE__ ) . 'images/ajax-loader.gif" style="display:none; margin: 10px;" />';
	$res .= '</form></div>';
	return $res;
}

add_shortcode( 'rkmd_revue_subscribe', 'rkmd_revue_subscribe_form' );

function _rkmd_revue_print_field( $label, $name, $type ) {
	global $_revue_printed_forms;

	$id = $name . '_' . $_revue_printed_forms;

	$res = '';

	// $res .= '<p>';
	// $res .= '<label for="' . $id . '">' . __( $label, RKMD_REVUE_TRANS_DOMAIN ) . '</label><br>';
	$res .= '<input placeholder="' . $label . '" type="' . $type . '" name="' . $name . '" id="' . $id . '" />';
	// $res .= '</p>';

	return $res;
}

function _rkmd_revue_key_provided() {
	$options = get_option( 'rkmd_revue_general' );

	return ! empty( $options['api_key'] );
}

function rkmd_revue_subscribe( $email, $first_name = null, $last_name = null ) {
	$options = get_option( 'rkmd_revue_general' );

	$body = array(
		'email' => $email,
	);

	// if ( ! empty( $first_name ) ) {
	// 	$body['first_name'] = $first_name;
	// }

	// if ( ! empty( $last_name ) ) {
	// 	$body['last_name'] = $last_name;
	// }

	wp_remote_post( 'https://www.getrevue.co/api/v2/subscribers', array(
		'headers' => array(
			'Authorization' => 'Token token="' . $options['api_key'] . '"',
		),
		'body'    => $body,
	) );
}


function rkmd_revue_admin_styles() {
	echo '<style>
		.wrap-revue #submit {
			background: transparent;
			border: 0;
			text-shadow: none;
			box-shadow: none;
			-webkit-box-shadow: 0;
			background-color: #E15718;
			border-radius: 18px;
			padding: 0 40px;
			height: 36px;
		}

		.wrap-revue h2 {
			display: none;
		}

		.wrap-revue th, .wrap-revue td {
			width: 100%;
			display: block;
			padding-top: 0;
			padding-left: 0;
			padding-bottom: 10px;
		}

		.wrap-revue td {
			padding-bottom: 0;
		}

		.wrap-revue #api_key {
			width: 50%;
			padding: 10px;
		}

		.wrap-revue p.submit {
			padding-top: 20px !important;
			margin-top: 0;
		}
	</style>';
}

add_action( 'admin_head', 'rkmd_revue_admin_styles' );

function rkmd_revue_admin_placeholder() {
	echo '<script type="text/javascript">';
	echo 'jQuery(function($) { $(".wrap-revue #api_key").attr("placeholder", "Jouw API key"); });';
	echo '</script>';
}

add_action( 'admin_footer', 'rkmd_revue_admin_placeholder' );

function rkmd_revue_get_profile_url() {
	if ( false === ( $profileUrl = get_transient( 'revue_profile_url' ) ) ) {
		$options = get_option( 'rkmd_revue_general' );
		$resp    = wp_remote_get( 'https://www.getrevue.co/api/v2/accounts/me', array(
			'headers' => array(
				'Authorization' => 'Token token="' . $options['api_key'] . '"',
			),
		) );

		$data = json_decode( $resp['body'], true );

		if ( ! empty( $data['profile_url'] ) ) {
			$profileUrl = $data['profile_url'];
			set_transient( 'revue_profile_url', $profileUrl, 24 * HOUR_IN_SECONDS );
		} else {
			$profileUrl = '';
		}
	}

	return $profileUrl;
}