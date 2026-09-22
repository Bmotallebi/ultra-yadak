<?php
namespace UltraYadak;

use UltraYadak\Features\QuotePrice\QuotePrice;
use UltraYadak\Features\QuotePrice\Settings;
use UltraYadak\Features\LatinNumerals\LatinNumerals;

defined( 'ABSPATH' ) || exit;

/**
 * نقطه‌ی ورود اصلی افزونه؛ فیچرها اینجا رجیستر می‌شوند.
 * فیچر جدید = یک پوشه‌ی تازه زیر includes/Features + یک سطر اینجا.
 */
final class Plugin {

	private static ?Plugin $instance = null;

	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->load_features();
	}

	private function load_features(): void {
		new QuotePrice();
		new LatinNumerals();

		if ( is_admin() ) {
			new Settings();
		}
	}
}
