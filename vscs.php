<?php
/**
 * Plugin Name: Very Simple Custom Style
 * Description: This is a very simple plugin to add Custom Style (CSS) to change your theme or plugin layout. For more info please check readme file.
 * Version: 1.2
 * Author: Guido van der Leest
 * Author URI: http://www.guidovanderleest.nl
 * License: GNU General Public License v3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: customstyle
 * Domain Path: translation
 */


// Load the plugin's text domain
function vscs_init() { 
	load_plugin_textdomain( 'customstyle', false, dirname( plugin_basename( __FILE__ ) ) . '/translation' );
}
add_action('plugins_loaded', 'vscs_init');


// Check data before saving it in database 
// Same as sanitize_text_field function but line breaks are allowed 
function vscs_sanitize_text_field($str) {
	$filtered = wp_check_invalid_utf8( $str );

	if ( strpos($filtered, '<') !== false ) {
		$filtered = wp_pre_kses_less_than( $filtered );
		$filtered = wp_strip_all_tags( $filtered, false );
	} else {
		$filtered = trim( preg_replace('/[\t ]+/', ' ', $filtered) );
	}

	$found = false;
	while ( preg_match('/%[a-f0-9]{2}/i', $filtered, $match) ) {
		$filtered = str_replace($match[0], '', $filtered);
		$found = true;
	}

	if ( $found ) {
		$filtered = trim( preg_replace('/ +/', ' ', $filtered) );
	}
	return apply_filters( 'vscs_sanitize_text_field', $filtered, $str );
}


// Add the admin options page
function vscs_menu_page() {
    add_options_page( __( 'VSCS Custom Style', 'customstyle' ), __( 'VSCS Custom Style', 'customstyle' ), 'manage_options', 'vscs', 'vscs_options_page' );
}
add_action( 'admin_menu', 'vscs_menu_page' );


// Add the admin settings and such 
function vscs_admin_init() {
    register_setting( 'vscs-options', 'vscs-setting', 'vscs_sanitize_text_field' );
    add_settings_section( 'vscs-section', __( 'Description', 'customstyle' ), 'vscs_section_callback', 'vscs' );
    add_settings_field( 'vscs-field', __( 'Custom Style', 'customstyle' ), 'vscs_field_callback', 'vscs', 'vscs-section' );
}
add_action( 'admin_init', 'vscs_admin_init' );


function vscs_section_callback() {
    echo __( 'On this page you can add Custom Style (CSS) to change the layout of your theme or plugin.', 'customstyle' ); 
}


function vscs_field_callback() {
    $vscs_setting = esc_textarea( get_option( 'vscs-setting' ) );
    echo "<textarea name='vscs-setting' rows='15' cols='60' maxlength='2000'>$vscs_setting</textarea>";
}


// Display the admin options page
function vscs_options_page() {
?>
<div class="wrap"> 
	<div id="icon-plugins" class="icon32"></div> 
	<h1><?php _e( 'Very Simple Custom Style', 'customstyle' ); ?></h1> 
	<form action="options.php" method="POST">
	<?php settings_fields( 'vscs-options' ); ?>
	<?php do_settings_sections( 'vscs' ); ?>
	<?php submit_button(__('Save Style', 'customstyle')); ?>
	</form>
	<p><?php _e( 'If you want to change the layout of your theme or plugin you should first look for the div or class of the element you want to change.', 'customstyle' ); ?></p>
	<p><?php _e( 'So first you should inspect your theme or plugin.', 'customstyle' ); ?></p>
</div>
<?php
}


// Include custom CSS in header 
function vscs_custom_css() {
	$vscs_css = esc_textarea( get_option( 'vscs-setting' ) );
	if (!empty($vscs_css)) {
		echo '<style type="text/css">' . "\n"; 
		echo $vscs_css . "\n";
		echo '</style>' . "\n"; 
	}
}
add_action( 'wp_head', 'vscs_custom_css' );

?>