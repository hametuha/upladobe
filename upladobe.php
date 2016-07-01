<?php
/*
Plugin Name: Upladobe
Description: A WordPress plugin which enable to create thumbnails for .pds, .ai and .pdf.
Version: 1.0.0
Plugin URI: https://github.com/hametuha/upladboe
Author: Takahashi_Fumiki
Author URI: https://hametuha.co.jp
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: upladobe
Domain Path: /languages
*/

// Avoid direct load.
defined( 'ABSPATH' ) or die( 'Do not load directly' );

/**
 * Post meta key name.
 */
define( 'UPLADOBE_KEY', '_upladobe_thumbnail_key' );

// Register text domain
load_plugin_textdomain( 'upladobe', false, 'upladobe/languages' );

// Requires PHP 5.3 or Imagic
if ( version_compare( '5.3.*', phpversion(), '<=' ) ) {
	require __DIR__ .'/functions.php';
	require __DIR__ .'/hooks.php';
} else {
	add_action( 'admin_notices', '_upladobe_version' );
	/**
	 * Notice
	 *
	 * @ignore
	 */
	function _upladobe_version() {
		printf(
			'<div class="error"><p><strong>Uploadobe: </strong>%s</p></div>',
			sprintf( __( 'Upladobe requires PHP 5.3 or later. You PHP version is %s.', 'upladobe' ), phpversion() )
		);
	}
}

