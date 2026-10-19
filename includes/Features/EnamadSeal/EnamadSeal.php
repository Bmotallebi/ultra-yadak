<?php
namespace UltraYadak\Features\EnamadSeal;

use UltraYadak\Support\Assets;

defined( 'ABSPATH' ) || exit;

/**
 * نماد اعتماد الکترونیکی (ای‌نماد) را در فوتر سایت، زیر ویجت «درگاه‌های
 * پرداخت ایرانی» نمایش می‌دهد.
 *
 * ای‌نماد برای تأیید، دقیقاً همین لنگر/تصویر را با شناسه و کد مربوط به
 * همین دامنه روی صفحه می‌خواهد؛ پس مارک‌آپ عیناً با همان attributeها
 * (از جمله code و referrerpolicy) خروجی می‌شود. اگر ویجت درگاه‌ها روزی
 * حذف/عوض شود، نماد در انتهای صفحه چاپ می‌شود تا تأیید ای‌نماد از دست نرود.
 */
class EnamadSeal {

	/** شناسه‌ی ای‌نماد این دامنه. */
	private const ID = '8257680';

	/** کد اختصاصی ای‌نماد این دامنه. */
	private const CODE = 'bpASQUq9c3v1mNzjitXxnn5wdT40S4Sn';

	/** ویجت «درگاه‌های پرداخت ایرانی» در ستون چهارم فوتر قالب. */
	private const WIDGET_ID = 'media_image-2';

	private bool $rendered = false;

	public function __construct() {
		add_filter( 'dynamic_sidebar_params', array( $this, 'attach_to_widget' ) );
		add_action( 'wp_footer', array( $this, 'render_fallback' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/** نماد را داخل همان ویجت، درست بعد از تصویر درگاه‌ها می‌گذارد. */
	public function attach_to_widget( array $params ): array {
		if ( is_admin() || self::WIDGET_ID !== ( $params[0]['widget_id'] ?? '' ) ) {
			return $params;
		}

		$params[0]['after_widget'] = $this->markup() . ( $params[0]['after_widget'] ?? '' );
		$this->rendered            = true;

		return $params;
	}

	/** اگر ویجت درگاه‌ها در صفحه نبود، نماد پیش از بسته‌شدن body. */
	public function render_fallback(): void {
		if ( ! $this->rendered ) {
			echo $this->markup( 'uy-enamad--fallback' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in markup().
		}
	}

	private function markup( string $modifier = '' ): string {
		$trust = 'https://trustseal.enamad.ir/?id=' . self::ID . '&Code=' . self::CODE;
		$logo  = 'https://trustseal.enamad.ir/logo.aspx?id=' . self::ID . '&Code=' . self::CODE;

		return sprintf(
			'<div class="uy-enamad %4$s"><a referrerpolicy="origin" target="_blank" href="%1$s"><img referrerpolicy="origin" src="%2$s" alt="" style="cursor:pointer" code="%3$s"></a></div>',
			esc_url( $trust ),
			esc_url( $logo ),
			esc_attr( self::CODE ),
			esc_attr( $modifier )
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
