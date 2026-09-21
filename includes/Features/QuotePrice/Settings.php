<?php
namespace UltraYadak\Features\QuotePrice;

defined( 'ABSPATH' ) || exit;

/**
 * صفحه تنظیمات — ووکامرس ← استعلام قیمت
 */
class Settings {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_setting' ) );
		add_filter( 'plugin_action_links_' . ULTRA_YADAK_BASENAME, array( $this, 'add_action_links' ) );
	}

	public function register_menu(): void {
		add_submenu_page(
			'woocommerce',
			__( 'استعلام قیمت', 'uy-cfp' ),
			__( 'استعلام قیمت', 'uy-cfp' ),
			'manage_woocommerce',
			'uy-cfp',
			array( $this, 'render_page' )
		);
	}

	public function register_setting(): void {
		register_setting(
			'uy_cfp_group',
			QuotePrice::OPTION_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => QuotePrice::defaults(),
			)
		);
	}

	public function sanitize( $input ): array {
		$out             = QuotePrice::defaults();
		$out['enabled']  = ( isset( $input['enabled'] ) && 'yes' === $input['enabled'] ) ? 'yes' : 'no';
		$out['zero']     = ( isset( $input['zero'] ) && 'yes' === $input['zero'] ) ? 'yes' : 'no';
		$out['phone']    = isset( $input['phone'] ) ? sanitize_text_field( $input['phone'] ) : '';
		$out['whatsapp'] = isset( $input['whatsapp'] ) ? preg_replace( '/[^0-9]/', '', $input['whatsapp'] ) : '';
		$out['label']    = isset( $input['label'] ) ? sanitize_text_field( $input['label'] ) : '';
		$out['note']     = isset( $input['note'] ) ? sanitize_textarea_field( $input['note'] ) : '';
		$out['color']    = isset( $input['color'] ) ? sanitize_hex_color( $input['color'] ) : '#1f3a93';
		return $out;
	}

	public function render_page(): void {
		$o = wp_parse_args( (array) get_option( QuotePrice::OPTION_NAME, array() ), QuotePrice::defaults() );
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
								<input type="checkbox" name="<?php echo esc_attr( QuotePrice::OPTION_NAME ); ?>[enabled]"
								       value="yes" <?php checked( 'yes', $o['enabled'] ); ?>>
								<?php esc_html_e( 'نمایش باکس استعلام برای محصولات بدون قیمت', 'uy-cfp' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'قیمت صفر', 'uy-cfp' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( QuotePrice::OPTION_NAME ); ?>[zero]"
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
							       name="<?php echo esc_attr( QuotePrice::OPTION_NAME ); ?>[phone]"
							       value="<?php echo esc_attr( $o['phone'] ); ?>" placeholder="02112345678">
							<p class="description"><?php esc_html_e( 'شماره ثابت یا موبایل فروشگاه. روی موبایل کاربر مستقیم شماره‌گیری می‌شود.', 'uy-cfp' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="uy_wa"><?php esc_html_e( 'شماره واتساپ', 'uy-cfp' ); ?></label></th>
						<td>
							<input id="uy_wa" type="text" dir="ltr" class="regular-text"
							       name="<?php echo esc_attr( QuotePrice::OPTION_NAME ); ?>[whatsapp]"
							       value="<?php echo esc_attr( $o['whatsapp'] ); ?>" placeholder="989121234567">
							<p class="description"><?php esc_html_e( 'با کد کشور، بدون علامت + و بدون صفر ابتدایی. مثال: 989121234567', 'uy-cfp' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="uy_label"><?php esc_html_e( 'متن جایگزین قیمت', 'uy-cfp' ); ?></label></th>
						<td>
							<input id="uy_label" type="text" class="regular-text"
							       name="<?php echo esc_attr( QuotePrice::OPTION_NAME ); ?>[label]"
							       value="<?php echo esc_attr( $o['label'] ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="uy_note"><?php esc_html_e( 'توضیح داخل باکس', 'uy-cfp' ); ?></label></th>
						<td>
							<textarea id="uy_note" rows="3" class="large-text"
							          name="<?php echo esc_attr( QuotePrice::OPTION_NAME ); ?>[note]"><?php echo esc_textarea( $o['note'] ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="uy_color"><?php esc_html_e( 'رنگ دکمه تماس', 'uy-cfp' ); ?></label></th>
						<td>
							<input id="uy_color" type="color"
							       name="<?php echo esc_attr( QuotePrice::OPTION_NAME ); ?>[color]"
							       value="<?php echo esc_attr( $o['color'] ); ?>">
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'ذخیره تنظیمات', 'uy-cfp' ) ); ?>
			</form>
		</div>
		<?php
	}

	/** لینک «تنظیمات» کنار نام افزونه در لیست افزونه‌ها */
	public function add_action_links( array $links ): array {
		$url = admin_url( 'admin.php?page=uy-cfp' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'تنظیمات', 'uy-cfp' ) . '</a>' );
		return $links;
	}
}
