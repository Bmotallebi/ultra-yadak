<?php
namespace UltraYadak\Features\SliderRedraw;

use UltraYadak\Support\Assets;

defined( 'ABSPATH' ) || exit;

/**
 * اسلایدر رولوشن (fullwidth) در صفحه‌ی RTL عرض/موقعیتش را قبل از
 * نشستن کامل لایوت (فونت، اسکرول‌بار، تصاویر) محاسبه می‌کند و از سمت
 * اشتباه جابه‌جا می‌شود؛ با ریسایز دستی درست می‌شد. این فیچر بعد از
 * لود کامل صفحه اسلایدر را وادار به محاسبه‌ی مجدد می‌کند.
 */
class SliderRedraw {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function enqueue_assets(): void {
		wp_enqueue_script(
			'uy-slider-redraw',
			ULTRA_YADAK_URL . 'assets/js/slider-redraw.js',
			array(),
			Assets::version( 'assets/js/slider-redraw.js' ),
			true
		);
	}
}
