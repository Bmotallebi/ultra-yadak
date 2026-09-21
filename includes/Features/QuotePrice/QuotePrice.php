<?php
namespace UltraYadak\Features\QuotePrice;

defined( 'ABSPATH' ) || exit;

/**
 * هر محصولی که فیلد قیمتش خالی (یا صفر) باشد، به‌جای دکمه خرید باکس
 * «استعلام قیمت» با دکمه تماس و واتساپ نمایش می‌دهد.
 */
class QuotePrice {

	const OPTION_NAME = 'uy_cfp_settings';

	public function __construct() {
		add_filter( 'woocommerce_is_purchasable', array( $this, 'filter_is_purchasable' ), 10, 2 );
		add_filter( 'woocommerce_get_price_html', array( $this, 'filter_price_html' ), 10, 2 );
		add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'filter_loop_add_to_cart_link' ), 10, 2 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'render_contact_box' ), 30 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/* =========================================================================
	 * تنظیمات
	 * ====================================================================== */

	public static function defaults(): array {
		return array(
			'enabled'  => 'yes',
			'zero'     => 'yes',
			'phone'    => '',
			'whatsapp' => '',
			'label'    => 'تماس برای استعلام قیمت',
			'note'     => 'قیمت این محصول بر اساس نرخ روز ارز و موجودی انبار اعلام می‌شود. برای استعلام قیمت و ثبت سفارش تماس بگیرید.',
			'color'    => '#1f3a93',
		);
	}

	public static function get( string $key ) {
		$opts = wp_parse_args( (array) get_option( self::OPTION_NAME, array() ), self::defaults() );
		return $opts[ $key ] ?? '';
	}

	/* =========================================================================
	 * منطق اصلی
	 * ====================================================================== */

	/** آیا این محصول قیمت ندارد؟ */
	public function is_quote_only( $product ): bool {
		if ( 'yes' !== self::get( 'enabled' ) ) {
			return false;
		}
		if ( ! $product instanceof \WC_Product ) {
			return false;
		}
		$price = $product->get_price();

		// قیمت خالی
		if ( '' === $price || null === $price ) {
			return true;
		}

		// قیمت صفر (یا منفی) — اگر در تنظیمات فعال باشد
		if ( 'yes' === self::get( 'zero' ) && (float) $price <= 0 ) {
			return true;
		}

		return false;
	}

	/** ۱) محصول بدون قیمت قابل خرید نیست؛ ووکامرس خودش فرم سبد خرید را حذف می‌کند. */
	public function filter_is_purchasable( $purchasable, $product ) {
		return $this->is_quote_only( $product ) ? false : $purchasable;
	}

	/** ۲) به‌جای قیمت، متن دلخواه. */
	public function filter_price_html( $price_html, $product ) {
		if ( ! $this->is_quote_only( $product ) ) {
			return $price_html;
		}

		$html = '<span class="uy-cfp-label">' . esc_html( self::get( 'label' ) ) . '</span>';

		if ( function_exists( 'is_product' ) && is_product() ) {
			$html .= $this->render_phones_html();
		}

		return $html;
	}

	/** لیست شماره‌های تماس زیر عنوان استعلام قیمت (فقط در PDP). */
	private function render_phones_html(): string {
		$phones = array(
			array( 'number' => '+989143248680', 'label' => '0914 324 8680' ),
			array( 'number' => '+989121077173', 'label' => '0912 107 7173' ),
		);

		$icon = '<svg class="uy-cfp-phones__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M6.6 10.8c1.4 2.8 3.8 5.2 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.4c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.4 0 .8-.2 1L6.6 10.8z" fill="currentColor"/></svg>';

		$links = array_map(
			function ( $phone ) use ( $icon ) {
				return sprintf(
					'<a class="uy-cfp-phones__link" href="tel:%s">%s<span dir="ltr">%s</span></a>',
					esc_attr( $phone['number'] ),
					$icon,
					esc_html( $phone['label'] )
				);
			},
			$phones
		);

		return '<div class="uy-cfp-phones">' . implode( '', $links ) . '</div>';
	}

	/** ۳) در لیست فروشگاه، دکمه «افزودن به سبد» → «مشاهده و استعلام قیمت». */
	public function filter_loop_add_to_cart_link( $html, $product ) {
		if ( $this->is_quote_only( $product ) ) {
			return sprintf(
				'<a href="%s" class="button uy-cfp-button">%s</a>',
				esc_url( get_permalink( $product->get_id() ) ),
				esc_html__( 'مشاهده و استعلام قیمت', 'uy-cfp' )
			);
		}
		return $html;
	}

	/** ۴) باکس تماس در صفحه محصول، دقیقاً جای دکمه خرید. */
	public function render_contact_box(): void {
		global $product;

		if ( ! $this->is_quote_only( $product ) ) {
			return;
		}

		$phone = preg_replace( '/[^0-9+]/', '', self::get( 'phone' ) );
		$wa    = preg_replace( '/[^0-9]/', '', self::get( 'whatsapp' ) );
		$note  = self::get( 'note' );

		if ( ! $phone && ! $wa ) {
			echo '<div class="uy-cfp-box"><p class="uy-cfp-note">'
				. esc_html__( 'شماره تماس در تنظیمات افزونه وارد نشده است.', 'uy-cfp' )
				. '</p></div>';
			return;
		}

		$sku     = $product->get_sku();
		$wa_text = rawurlencode( sprintf(
			'سلام، قیمت و موجودی این محصول را می‌خواستم: %s%s',
			$product->get_name(),
			$sku ? ' (کد: ' . $sku . ')' : ''
		) );
		?>
		<div class="uy-cfp-box">
			<?php if ( $note ) : ?>
				<p class="uy-cfp-note"><?php echo esc_html( $note ); ?></p>
			<?php endif; ?>
			<div class="uy-cfp-actions">
				<?php if ( $phone ) : ?>
					<a class="uy-cfp-btn uy-cfp-btn--phone" href="tel:<?php echo esc_attr( $phone ); ?>">
						<?php esc_html_e( 'تماس تلفنی', 'uy-cfp' ); ?>
					</a>
				<?php endif; ?>
				<?php if ( $wa ) : ?>
					<a class="uy-cfp-btn uy-cfp-btn--wa"
					   href="https://wa.me/<?php echo esc_attr( $wa ); ?>?text=<?php echo $wa_text; ?>"
					   target="_blank" rel="noopener nofollow">
						<?php esc_html_e( 'استعلام در واتساپ', 'uy-cfp' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/** ۵) استایل — فقط در صفحاتی که واقعاً لازم است. */
	public function enqueue_assets(): void {
		if ( ! function_exists( 'is_product' ) ) {
			return;
		}
		if ( ! is_product() && ! is_shop() && ! is_product_category() && ! is_product_tag() ) {
			return;
		}

		wp_enqueue_style(
			'uy-cfp-quote-price',
			ULTRA_YADAK_URL . 'assets/css/quote-price.css',
			array(),
			ULTRA_YADAK_VERSION
		);

		$color = self::get( 'color' ) ?: '#1f3a93';
		wp_add_inline_style(
			'uy-cfp-quote-price',
			':root{--uy-cfp-color:' . esc_attr( $color ) . '}'
		);
	}
}
