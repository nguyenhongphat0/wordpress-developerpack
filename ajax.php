<?php
add_action( 'wp_ajax_developerpack_test', 'developerpack_test' );

function developerpack_test() {
	$value = $_POST['value'];
	echo $value;
	wp_die();
}
