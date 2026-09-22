<?php
namespace UltraYadak\Features\LatinNumerals;

use UltraYadak\Support\Assets;

defined( 'ABSPATH' ) || exit;

/**
 * فونت فارسی قالب (sc_iranyekan_fa) گلیف ارقام لاتین ۰-۹ رو با شکل فارسی
 * جایگزین می‌کند؛ کد کاراکتر همان رقم لاتین باقی می‌ماند، فقط شکل رسم‌شده
 * عوض می‌شود. این فیچر با unicode-range فقط گلیف رقم‌ها را روی یک فونت
 * لاتین‌امن force می‌کند — حروف فارسی و بقیه‌ی تایپوگرافی دست‌نخورده می‌ماند.
 */
class LatinNumerals {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function enqueue_assets(): void {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		wp_enqueue_style(
			'uy-latin-numerals',
			ULTRA_YADAK_URL . 'assets/css/latin-numerals.css',
			array(),
			Assets::version( 'assets/css/latin-numerals.css' )
		);
	}
}
