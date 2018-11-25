<?php
/*
 * Plugin Name:  Developer Pack
 * Description:  This module contain everything a wordpress developer need.
 * Author:       nguyenhongphat0
 * Author URI:   https://nguyenhongphat0.github.io
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-3.0.en.html
 * Text Domain:  developerpack
 */

add_action( 'init', 'developerpack_ajax' );
function developerpack_ajax() {
	$is_admin = current_user_can( 'administrator' );
	if ( $is_admin ) {
		require_once( 'ajax.php' );
	}
}

add_action( 'admin_menu', 'developerpack_menu' );
function developerpack_menu() {
	add_menu_page( 'Developer Pack Settings', 'Developer Pack', 'administrator', __FILE__, 'developerpack_settings_page' , 'dashicons-editor-code' );
}

function developerpack_settings_page() {
	ob_start();
	phpinfo();
	$phpinfo = ob_get_contents();
	ob_end_clean();
	require_once( 'template.php' );
}
