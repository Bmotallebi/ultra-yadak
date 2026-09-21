<?php
/**
 * Plugin Name:       اولترا یدک — استعلام قیمت
 * Plugin URI:        https://ultra-yadak.ir
 * Description:       هر محصولی که فیلد قیمتش خالی باشد، به‌جای دکمه خرید باکس «استعلام قیمت» با دکمه تماس و واتساپ نمایش می‌دهد. محصولاتی که قیمت دارند دست‌نخورده باقی می‌مانند.
 * Version:           1.2.0
 * Author:            Ultra Yadak
 * Text Domain:       uy-cfp
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 7.0
 */

defined( 'ABSPATH' ) || exit;

define( 'UY_CFP_OPTION', 'uy_cfp_settings' );

/* =========================================================================
 * تنظیمات پیش‌فرض
 * ====================================================================== */
function uy_cfp_defaults() {
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

function uy_cfp_get( $key ) {
	$opts = wp_parse_args( (array) get_option( UY_CFP_OPTION, array() ), uy_cfp_defaults() );
	return isset( $opts[ $key ] ) ? $opts[ $key ] : '';
}

/* =========================================================================
 * منطق اصلی
 * ====================================================================== */

/** آیا این محصول قیمت ندارد؟ */
function uy_cfp_is_quote_only( $product ) {
	if ( 'yes' !== uy_cfp_get( 'enabled' ) ) {
		return false;
	}
	if ( ! $product instanceof WC_Product ) {
		return false;
	}
	$price = $product->get_price();

	// قیمت خالی
	if ( '' === $price || null === $price ) {
		return true;
	}

	// قیمت صفر (یا منفی) — اگر در تنظیمات فعال باشد
	if ( 'yes' === uy_cfp_get( 'zero' ) && (float) $price <= 0 ) {
		return true;
	}

	return false;
}

/** ۱) محصول بدون قیمت قابل خرید نیست؛ ووکامرس خودش فرم سبد خرید را حذف می‌کند. */
add_filter( 'woocommerce_is_purchasable', function ( $purchasable, $product ) {
	return uy_cfp_is_quote_only( $product ) ? false : $purchasable;
}, 10, 2 );

/** ۲) به‌جای قیمت، متن دلخواه. */
add_filter( 'woocommerce_get_price_html', function ( $price_html, $product ) {
	if ( uy_cfp_is_quote_only( $product ) ) {
		$html = '<span class="uy-cfp-label">' . esc_html( uy_cfp_get( 'label' ) ) . '</span>';

		if ( function_exists( 'is_product' ) && is_product() ) {
			$icon    = '<svg class="uy-cfp-phones__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M6.6 10.8c1.4 2.8 3.8 5.2 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.4c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.4 0 .8-.2 1L6.6 10.8z" fill="currentColor"/></svg>';
			$html   .= '<div class="uy-cfp-phones">'
				. '<a class="uy-cfp-phones__link" href="tel:+989143248680">' . $icon . '<span dir="ltr">0914 324 8680</span></a>'
				. '<a class="uy-cfp-phones__link" href="tel:+989121077173">' . $icon . '<span dir="ltr">0912 107 7173</span></a>'
				. '</div>';
		}

		return $html;
	}
	return $price_html;
}, 10, 2 );

/** ۳) در لیست فروشگاه، دکمه «افزودن به سبد» → «مشاهده و استعلام قیمت». */
add_filter( 'woocommerce_loop_add_to_cart_link', function ( $html, $product ) {
	if ( uy_cfp_is_quote_only( $product ) ) {
		return sprintf(
			'<a href="%s" class="button uy-cfp-button">%s</a>',
			esc_url( get_permalink( $product->get_id() ) ),
			esc_html__( 'مشاهده و استعلام قیمت', 'uy-cfp' )
		);
	}
	return $html;
}, 10, 2 );

/** ۴) باکس تماس در صفحه محصول، دقیقاً جای دکمه خرید. */
add_action( 'woocommerce_single_product_summary', function () {
	global $product;

	if ( ! uy_cfp_is_quote_only( $product ) ) {
		return;
	}

	$phone = preg_replace( '/[^0-9+]/', '', uy_cfp_get( 'phone' ) );
	$wa    = preg_replace( '/[^0-9]/', '', uy_cfp_get( 'whatsapp' ) );
	$note  = uy_cfp_get( 'note' );

	if ( ! $phone && ! $wa ) {
		echo '<div class="uy-cfp-box"><p class="uy-cfp-note">'
			. esc_html__( 'شماره تماس در تنظیمات افزونه وارد نشده است.', 'uy-cfp' )
			. '</p></div>';
		return;
	}

	$sku      = $product->get_sku();
	$wa_text  = rawurlencode( sprintf(
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
}, 30 );

/** ۵) استایل. */
add_action( 'wp_head', function () {
	if ( ! function_exists( 'is_product' ) ) {
		return;
	}
	if ( ! is_product() && ! is_shop() && ! is_product_category() && ! is_product_tag() ) {
		return;
	}
	$color = uy_cfp_get( 'color' ) ?: '#1f3a93';
	?>
	<style>
		.uy-cfp-label{display:inline-block;font-weight:700;font-size:1.05em;color:<?php echo esc_attr( $color ); ?>}
		.uy-cfp-phones{display:flex;flex-wrap:wrap;gap:10px;margin-top:12px}
		.uy-cfp-phones__link{
			display:inline-flex;align-items:center;gap:8px;
			padding:10px 18px;border-radius:999px;
			background:#fff;border:1.5px solid <?php echo esc_attr( $color ); ?>;
			color:<?php echo esc_attr( $color ); ?>;font-weight:700;font-size:.95em;
			text-decoration:none;line-height:1;letter-spacing:.02em;
			transition:background-color .15s ease,color .15s ease,box-shadow .15s ease,transform .15s ease;
		}
		.uy-cfp-phones__link:hover,.uy-cfp-phones__link:focus-visible{
			background:<?php echo esc_attr( $color ); ?>;color:#fff;text-decoration:none;
			box-shadow:0 4px 14px rgba(0,0,0,.12);transform:translateY(-1px);
		}
		.uy-cfp-phones__icon{flex:0 0 auto}
		.uy-cfp-phones__link span{direction:ltr}
		.uy-cfp-box{margin:18px 0 24px;padding:18px;border:1px solid #e2e6ee;border-radius:10px;background:#f7f9fc}
		.uy-cfp-note{margin:0 0 14px;font-size:.92em;line-height:1.9;color:#48506b}
		.uy-cfp-actions{display:flex;flex-wrap:wrap;gap:10px}
		.uy-cfp-btn{flex:1 1 180px;text-align:center;padding:13px 18px;border-radius:8px;font-weight:700;text-decoration:none;transition:opacity .15s ease}
		.uy-cfp-btn:hover{opacity:.88;text-decoration:none}
		.uy-cfp-btn--phone{background:<?php echo esc_attr( $color ); ?>;color:#fff}
		.uy-cfp-btn--wa{background:#25d366;color:#fff}
	</style>
	<?php
} );

/* =========================================================================
 * صفحه تنظیمات — ووکامرس ← استعلام قیمت
 * ====================================================================== */
add_action( 'admin_menu', function () {
	add_submenu_page(
		'woocommerce',
		__( 'استعلام قیمت', 'uy-cfp' ),
		__( 'استعلام قیمت', 'uy-cfp' ),
		'manage_woocommerce',
		'uy-cfp',
		'uy_cfp_settings_page'
	);
} );

add_action( 'admin_init', function () {
	register_setting( 'uy_cfp_group', UY_CFP_OPTION, array(
		'sanitize_callback' => 'uy_cfp_sanitize',
		'default'           => uy_cfp_defaults(),
	) );
} );

function uy_cfp_sanitize( $input ) {
	$out             = uy_cfp_defaults();
	$out['enabled']  = ( isset( $input['enabled'] ) && 'yes' === $input['enabled'] ) ? 'yes' : 'no';
	$out['zero']     = ( isset( $input['zero'] ) && 'yes' === $input['zero'] ) ? 'yes' : 'no';
	$out['phone']    = isset( $input['phone'] ) ? sanitize_text_field( $input['phone'] ) : '';
	$out['whatsapp'] = isset( $input['whatsapp'] ) ? preg_replace( '/[^0-9]/', '', $input['whatsapp'] ) : '';
	$out['label']    = isset( $input['label'] ) ? sanitize_text_field( $input['label'] ) : '';
	$out['note']     = isset( $input['note'] ) ? sanitize_textarea_field( $input['note'] ) : '';
	$out['color']    = isset( $input['color'] ) ? sanitize_hex_color( $input['color'] ) : '#1f3a93';
	return $out;
}

function uy_cfp_settings_page() {
	$o = wp_parse_args( (array) get_option( UY_CFP_OPTION, array() ), uy_cfp_defaults() );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'استعلام قیمت — اولترا یدک', 'uy-cfp' ); ?></h1>
		<p style="max-width:760px;line-height:2">
			هر محصولی که فیلد <strong>قیمت اصلی</strong> آن خالی باشد، به‌جای دکمه «افزودن به سبد»،
			باکس استعلام قیمت با دکمه تماس و واتساپ نمایش می‌دهد. به‌محض اینکه برای محصولی قیمت
			وارد کنی، آن محصول خودکار به حالت فروش عادی برمی‌گردد. نیازی به تنظیم محصول‌به‌محصول نیست.
		</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'uy_cfp_group' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'فعال باشد', 'uy-cfp' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( UY_CFP_OPTION ); ?>[enabled]"
							       value="yes" <?php checked( 'yes', $o['enabled'] ); ?>>
							<?php esc_html_e( 'نمایش باکس استعلام برای محصولات بدون قیمت', 'uy-cfp' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'قیمت صفر', 'uy-cfp' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( UY_CFP_OPTION ); ?>[zero]"
							       value="yes" <?php checked( 'yes', $o['zero'] ); ?>>
							<?php esc_html_e( 'محصولاتی که قیمتشان صفر است را هم استعلامی در نظر بگیر', 'uy-cfp' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'توصیه می‌شود فعال بماند تا محصولی با قیمت صفر به‌اشتباه قابل سفارش نشود.', 'uy-cfp' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="uy_phone"><?php esc_html_e( 'شماره تماس', 'uy-cfp' ); ?></label></th>
					<td>
						<input id="uy_phone" type="text" dir="ltr" class="regular-text"
						       name="<?php echo esc_attr( UY_CFP_OPTION ); ?>[phone]"
						       value="<?php echo esc_attr( $o['phone'] ); ?>" placeholder="02112345678">
						<p class="description"><?php esc_html_e( 'شماره ثابت یا موبایل فروشگاه. روی موبایل کاربر مستقیم شماره‌گیری می‌شود.', 'uy-cfp' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="uy_wa"><?php esc_html_e( 'شماره واتساپ', 'uy-cfp' ); ?></label></th>
					<td>
						<input id="uy_wa" type="text" dir="ltr" class="regular-text"
						       name="<?php echo esc_attr( UY_CFP_OPTION ); ?>[whatsapp]"
						       value="<?php echo esc_attr( $o['whatsapp'] ); ?>" placeholder="989121234567">
						<p class="description"><?php esc_html_e( 'با کد کشور، بدون علامت + و بدون صفر ابتدایی. مثال: 989121234567', 'uy-cfp' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="uy_label"><?php esc_html_e( 'متن جایگزین قیمت', 'uy-cfp' ); ?></label></th>
					<td>
						<input id="uy_label" type="text" class="regular-text"
						       name="<?php echo esc_attr( UY_CFP_OPTION ); ?>[label]"
						       value="<?php echo esc_attr( $o['label'] ); ?>">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="uy_note"><?php esc_html_e( 'توضیح داخل باکس', 'uy-cfp' ); ?></label></th>
					<td>
						<textarea id="uy_note" rows="3" class="large-text"
						          name="<?php echo esc_attr( UY_CFP_OPTION ); ?>[note]"><?php echo esc_textarea( $o['note'] ); ?></textarea>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="uy_color"><?php esc_html_e( 'رنگ دکمه تماس', 'uy-cfp' ); ?></label></th>
					<td>
						<input id="uy_color" type="color"
						       name="<?php echo esc_attr( UY_CFP_OPTION ); ?>[color]"
						       value="<?php echo esc_attr( $o['color'] ); ?>">
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'ذخیره تنظیمات', 'uy-cfp' ) ); ?>
		</form>
	</div>
	<?php
}

/* لینک «تنظیمات» کنار نام افزونه در لیست افزونه‌ها */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	$url = admin_url( 'admin.php?page=uy-cfp' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'تنظیمات', 'uy-cfp' ) . '</a>' );
	return $links;
} );
