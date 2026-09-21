# Ultra Yadak Plugin — Project Rules

## Architecture
- OOP only. New logic goes into a class under `includes/`, not into procedural functions or closures hung directly on `add_action`/`add_filter` in the bootstrap file.
- One feature = one folder under `includes/Features/<FeatureName>/`, self-contained (its own hook registration, its own settings class if it needs one).
- Namespace root is `UltraYadak\`, autoloaded via `includes/Autoloader.php` (PSR-4-style: `UltraYadak\Foo\Bar` → `includes/Foo/Bar.php`). No new files should be `require`d by hand in the bootstrap.
- The bootstrap file (`ultra-yadak.php`) stays a thin loader: plugin header, constants, autoloader registration, `Plugin::instance()`. No feature logic there.

## DRY
- Before adding a helper, a settings getter, a sanitizer, or markup, check whether something equivalent already exists (e.g. `QuotePrice::get()` for options) and reuse/extend it instead of duplicating.
- Shared behavior used by more than one feature belongs in a shared class/trait, not copy-pasted across feature folders.
- CSS lives in `assets/css/`, enqueued via `wp_enqueue_style` + `wp_add_inline_style` for dynamic values (e.g. brand color via CSS custom properties). Don't echo raw `<style>` blocks in hooks.

## When adding a new feature
1. Create `includes/Features/<FeatureName>/<FeatureName>.php` with hook registration in the constructor.
2. Add a `Settings.php` in the same folder only if the feature needs admin config.
3. Register the feature in `includes/Plugin.php::load_features()`.
4. Add any CSS/JS under `assets/`.
