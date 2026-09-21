<?php
/**
 * Plugin Name:       اولترا یدک — استعلام قیمت
 * Plugin URI:        https://ultra-yadak.ir
 * Description:       هر محصولی که فیلد قیمتش خالی باشد، به‌جای دکمه خرید باکس «استعلام قیمت» با دکمه تماس و واتساپ نمایش می‌دهد. محصولاتی که قیمت دارند دست‌نخورده باقی می‌مانند.
 * Version:           1.3.0
 * Author:            Ultra Yadak
 * Text Domain:       uy-cfp
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 7.0
 */

defined( 'ABSPATH' ) || exit;

define( 'ULTRA_YADAK_VERSION', '1.3.0' );
define( 'ULTRA_YADAK_FILE', __FILE__ );
define( 'ULTRA_YADAK_DIR', plugin_dir_path( __FILE__ ) );
define( 'ULTRA_YADAK_URL', plugin_dir_url( __FILE__ ) );
define( 'ULTRA_YADAK_BASENAME', plugin_basename( __FILE__ ) );

require_once ULTRA_YADAK_DIR . 'includes/Autoloader.php';
\UltraYadak\Autoloader::register( ULTRA_YADAK_DIR . 'includes' );

add_action( 'plugins_loaded', function () {
	\UltraYadak\Plugin::instance();
} );
