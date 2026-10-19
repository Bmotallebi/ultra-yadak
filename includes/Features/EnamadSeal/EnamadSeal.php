<?php
namespace UltraYadak\Features\EnamadSeal;

use UltraYadak\Support\Assets;

defined( 'ABSPATH' ) || exit;

/**
 * نماد اعتماد الکترونیکی (ای‌نماد) را در فوتر سایت نمایش می‌دهد.
 *
 * ای‌نماد برای تأیید، دقیقاً همین لنگر/تصویر را با شناسه و کد مربوط به
 * همین دامنه روی صفحه می‌خواهد؛ پس مارک‌آپ عیناً با همان attributeها
 * (از جمله code و referrerpolicy) خروجی می‌شود و نباید دست‌کاری/escape
 * شود. شناسه و کد به‌صورت ثابت نگه‌داری می‌شوند تا بعداً بدون تغییر در
 * مارک‌آپ قابل به‌روزرسانی باشند.
 */
class EnamadSeal {

	/** شناسه‌ی ای‌نماد این دامنه. */
	private const ID = '8257680';

	/** کد اختصاصی ای‌نماد این دامنه. */
	private const CODE = 'bpASQUq9c3v1mNzjitXxnn5wdT40S4Sn';

	public function __construct() {
		add_action( 'wp_footer', array( $this, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/** لنگر/تصویر رسمی ای‌نماد، پیش از بسته‌شدن body. */
	public function render(): void {
		$trust = 'https://trustseal.enamad.ir/?id=' . self::ID . '&Code=' . self::CODE;
		$logo  = 'https://trustseal.enamad.ir/logo.aspx?id=' . self::ID . '&Code=' . self::CODE;

		printf(
			'<div class="uy-enamad"><a referrerpolicy="origin" target="_blank" href="%1$s"><img referrerpolicy="origin" src="%2$s" alt="" style="cursor:pointer" code="%3$s"></a></div>',
			esc_url( $trust ),
			esc_url( $logo ),
			esc_attr( self::CODE )
		);
	}

	public function enqueue_assets(): void {
		wp_enqueue_style(
			'uy-enamad-seal',
			ULTRA_YADAK_URL . 'assets/css/enamad-seal.css',
			array(),
			Assets::version( 'assets/css/enamad-seal.css' )
		);
	}
}
