<?php
namespace UltraYadak\Support;

defined( 'ABSPATH' ) || exit;

/**
 * نسخه‌ی cache-busting بر اساس زمان آخرین تغییر خود فایل، نه ثابت
 * دستی پلاگین — تا هر بار محتوای CSS/JS عوض شد، مرورگر خودکار
 * نسخه‌ی جدید را بگیرد و نیازی به یادآوری بالا بردن ورژن نباشد.
 */
class Assets {

	public static function version( string $relative_path ): string {
		$file = ULTRA_YADAK_DIR . ltrim( $relative_path, '/' );
		return is_file( $file ) ? (string) filemtime( $file ) : ULTRA_YADAK_VERSION;
	}
}
