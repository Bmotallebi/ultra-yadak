<?php
namespace UltraYadak;

defined( 'ABSPATH' ) || exit;

/**
 * PSR-4-ish autoloader: UltraYadak\Foo\Bar -> includes/Foo/Bar.php
 */
class Autoloader {

	private const PREFIX = __NAMESPACE__ . '\\';

	private static string $base_dir;

	public static function register( string $base_dir ): void {
		self::$base_dir = rtrim( $base_dir, '/\\' ) . '/';
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	private static function autoload( string $class ): void {
		if ( 0 !== strpos( $class, self::PREFIX ) ) {
			return;
		}

		$relative = substr( $class, strlen( self::PREFIX ) );
		$relative = str_replace( '\\', '/', $relative );
		$file     = self::$base_dir . $relative . '.php';

		if ( is_file( $file ) ) {
			require $file;
		}
	}
}
