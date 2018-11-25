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

include_once( 'ajax.php' );

add_action( 'admin_menu', 'developerpack_menu' );
function developerpack_menu() {
	add_menu_page( 'Developer Pack Settings', 'Developer Pack', 'administrator', __FILE__, 'developerpack_settings_page' , 'dashicons-editor-code' );
}

function developerpack_settings_page() {
	ob_start();
	phpinfo();
	$phpinfo = ob_get_contents();
	ob_end_clean();
?>
	<div class="wrap">
		<h1>Developer Pack</h1>
		<div>
			<p>Welcome to Developer Pack. With this plugin you can view system information, download source code with advance options and live editing your website with a powerful code editor.</p>
			<div>
				<button onclick="phpinfo()" class="button button-primary">PHP Info</button>
				<a href="https://codex.wordpress.org/WordPress_Coding_Standards" target="_blank" class="button">Coding standard</a>
				<a href="https://codex.wordpress.org/Plugin_API" target="_blank" class="button">Plugin API</a>
				<a href="https://codex.wordpress.org/Theme_Development" target="_blank" class="button">Theme Development</a>
				<a href="https://codex.wordpress.org/AJAX_in_Plugins" target="_blank" class="button">AJAX</a>
			</div>
		</div>
	</div>
	<div>
		<h2>Download source code</h2>
		<table class="wp-list-table widefat fixed striped comments">
			<thead>
				<tr>
					<th scope="col" class="manage-column column-primary">File name</th>
					<th scope="col" class="manage-column" width="100">Action</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>minimal.zip</td>
					<td><a>Delete</a></td>
				</tr>
			</tbody>
			<tbody id="the-extra-comment-list" data-wp-lists="list:comment">
				<tr class="no-items"><td class="colspanchange" colspan="2">No zipped files found</td></tr>
			</tbody>
			<tfoot>
				<tr>
					<th></th>
					<th scope="col">
						<a>Clean all</a>
					</th>
				</tr>
			</tfoot>
		</table>
	</div>
	<script>
		function phpinfo() {
			var win = window.open();
			win.document.body.innerHTML = `<?php echo addslashes( $phpinfo ); ?>`;
		}
	</script>
<?php }

function developerpack_js() {
	wp_enqueue_script( 'developerpack',  plugins_url( '/developerpack.js', __FILE__ ));
}
add_action( 'admin_enqueue_scripts', 'developerpack_js' );
