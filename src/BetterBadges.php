<?php

namespace XD\BetterBadges;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Environment;

/**
 * Configuration for better-badges.
 *
 * By default the CMS renders page-tree status/locale badges absolutely positioned and
 * touching, so down a long tree they read as one solid vertical bar. This module gives
 * them a little vertical breathing room and a choice of styles.
 *
 * Pick a style from `app/_config.php` or YAML, e.g.:
 *
 *     use XD\BetterBadges\BetterBadges;
 *     use SilverStripe\Core\Config\Config;
 *     Config::modify()->set(BetterBadges::class, 'style', 'pill');
 *
 * Or in YAML:
 *
 *     XD\BetterBadges\BetterBadges:
 *       style: compact
 *
 * Set `style: off` (or `none`) to leave the CMS badges completely unaltered — the module then
 * loads no stylesheet and the native badges render as they would without it.
 *
 * The style can also be overridden per environment from `.env`, which takes precedence over YAML
 * (handy to e.g. turn the module off on one environment):
 *
 *     SS_BETTER_BADGES_STYLE="off"
 */
class BetterBadges
{
    use Configurable;

    /**
     * Badge style loaded into the CMS. One of:
     *  - 'outline' (default): compact + a light outline treatment, every state softened
     *     so native status badges and Fluent locale badges read on their own row.
     *  - 'outline-pill': the outline treatment with fully rounded pill badges.
     *  - 'compact': compact rounded rectangle, keeps the native solid fills.
     *  - 'pill': compact, fully rounded, keeps the native solid fills.
     *  - 'off' (alias 'none'): disabled — load no stylesheet, native badges unchanged.
     *
     * An unknown value falls back to 'outline'.
     *
     * @config
     */
    private static string $style = 'outline';

    /**
     * Environment variable that overrides the `style` config when set (e.g. in `.env`).
     */
    public const STYLE_ENV_VAR = 'SS_BETTER_BADGES_STYLE';

    /**
     * Values of `style` that disable the module (load no stylesheet).
     */
    private const OFF = ['off', 'none'];

    /**
     * Stylesheets per style, as module-relative Requirements paths.
     */
    private const STYLES = [
        'outline' => 'xddesigners/silverstripe-better-badges:client/css/better-badges-outline.css',
        'outline-pill' => 'xddesigners/silverstripe-better-badges:client/css/better-badges-outline-pill.css',
        'compact' => 'xddesigners/silverstripe-better-badges:client/css/better-badges-compact.css',
        'pill' => 'xddesigners/silverstripe-better-badges:client/css/better-badges-pill.css',
    ];

    /**
     * Opt-in: show a small country flag inside each Fluent locale badge (needs silverstripe-fluent).
     * A circular flag (flag-icons 1x1) is placed on the left of the badge, derived from the locale's
     * region, with the `xx_YY` code kept beside it. Enable in YAML (`locale_flags: true`) or per
     * environment via `SS_BETTER_BADGES_LOCALE_FLAGS`.
     *
     * @config
     */
    private static bool $locale_flags = false;

    /**
     * Override the flag for a locale or language, e.g. `['en' => 'gb', 'en_US' => 'us', 'eu' => 'eu']`.
     * Looked up by full locale first, then language; otherwise the locale's region is used.
     *
     * @config
     */
    private static array $locale_flags_map = [];

    /**
     * Show only the flag, hiding the `xx_YY` code. Default: keep the code beside the flag.
     *
     * @config
     */
    private static bool $locale_flags_hide_code = false;

    /**
     * Flag shape: 'auto' (default — a circle for the rounded pill styles, a tiny-rounded square for
     * the others), or force 'circle' / 'square'.
     *
     * @config
     */
    private static string $locale_flags_shape = 'auto';

    /**
     * Environment variable that enables locale flags when set truthy (overrides the `locale_flags` config).
     */
    public const LOCALE_FLAGS_ENV_VAR = 'SS_BETTER_BADGES_LOCALE_FLAGS';

    /**
     * Stylesheet that renders the locale flags.
     */
    private const LOCALE_FLAGS_STYLESHEET =
        'xddesigners/silverstripe-better-badges:client/css/better-badges-flags.css';

    /**
     * Opt-in high-contrast mode: pushes the Fluent locale badges to WCAG AAA (darker text on a light
     * tint). Loaded in addition to the active style. Enable in YAML (`high_contrast: true`) or per
     * environment via `SS_BETTER_BADGES_HIGH_CONTRAST`.
     *
     * @config
     */
    private static bool $high_contrast = false;

    /**
     * Environment variable that enables high-contrast mode when set truthy (overrides the config).
     */
    public const HIGH_CONTRAST_ENV_VAR = 'SS_BETTER_BADGES_HIGH_CONTRAST';

    /**
     * The high-contrast overlay stylesheet.
     */
    private const HIGH_CONTRAST_STYLESHEET =
        'xddesigners/silverstripe-better-badges:client/css/better-badges-high-contrast.css';

    /**
     * The effective style: the `SS_BETTER_BADGES_STYLE` env var when set, otherwise the `style`
     * config. Lower-cased and trimmed.
     */
    public static function style(): string
    {
        $env = Environment::getEnv(self::STYLE_ENV_VAR);
        $style = ($env !== false && trim((string) $env) !== '')
            ? $env
            : static::config()->get('style');

        // Guard the YAML footgun where an unquoted `style: off` is read as boolean false.
        if ($style === false) {
            return 'off';
        }

        return strtolower(trim((string) $style));
    }

    /**
     * Whether the module is enabled (i.e. not set to 'off'/'none').
     */
    public static function isEnabled(): bool
    {
        return !in_array(self::style(), self::OFF, true);
    }

    /**
     * Resolve the stylesheet to load for the effective style, or '' when disabled ('off'/'none').
     */
    public static function stylesheet(): string
    {
        if (!self::isEnabled()) {
            return '';
        }
        return self::STYLES[self::style()] ?? self::STYLES['outline'];
    }

    /**
     * Whether locale flags are enabled: `SS_BETTER_BADGES_LOCALE_FLAGS` when set, otherwise the
     * `locale_flags` config.
     */
    public static function localeFlagsEnabled(): bool
    {
        $env = Environment::getEnv(self::LOCALE_FLAGS_ENV_VAR);
        if ($env !== false && trim((string) $env) !== '') {
            return in_array(strtolower(trim((string) $env)), ['1', 'true', 'on', 'yes'], true);
        }
        return (bool) static::config()->get('locale_flags');
    }

    /**
     * The stylesheet that renders the locale flags.
     */
    public static function localeFlagStylesheet(): string
    {
        return self::LOCALE_FLAGS_STYLESHEET;
    }

    /**
     * Whether high-contrast mode is enabled: `SS_BETTER_BADGES_HIGH_CONTRAST` when set, otherwise the
     * `high_contrast` config.
     */
    public static function highContrastEnabled(): bool
    {
        $env = Environment::getEnv(self::HIGH_CONTRAST_ENV_VAR);
        if ($env !== false && trim((string) $env) !== '') {
            return in_array(strtolower(trim((string) $env)), ['1', 'true', 'on', 'yes'], true);
        }
        return (bool) static::config()->get('high_contrast');
    }

    /**
     * The high-contrast overlay stylesheet.
     */
    public static function highContrastStylesheet(): string
    {
        return self::HIGH_CONTRAST_STYLESHEET;
    }

    /**
     * Whether the flag should be a (tiny-rounded) square rather than a circle. Driven by the
     * `locale_flags_shape` config: 'square' / 'circle' force it; 'auto' (default) uses a circle for the
     * rounded pill styles ('pill', 'outline-pill') and a square for the others.
     */
    public static function localeFlagSquare(): bool
    {
        $shape = strtolower(trim((string) static::config()->get('locale_flags_shape')));
        if ($shape === 'square') {
            return true;
        }
        if ($shape === 'circle') {
            return false;
        }
        return !in_array(self::style(), ['pill', 'outline-pill'], true);
    }

    /**
     * Whether to hide the `xx_YY` code and show only the flag.
     */
    public static function localeFlagsHideCode(): bool
    {
        return (bool) static::config()->get('locale_flags_hide_code');
    }

    /**
     * Resolve a locale code (e.g. "nl_NL") to a bundled flag code, honouring the override map and
     * falling back to the locale's region. Returns null when no bundled flag matches.
     */
    public static function flagCodeForLocale(string $locale): ?string
    {
        $locale = trim($locale);
        if ($locale === '') {
            return null;
        }

        $lookup = [];
        foreach ((array) static::config()->get('locale_flags_map') as $key => $value) {
            $lookup[strtolower((string) $key)] = strtolower(trim((string) $value));
        }

        $lc = strtolower($locale);
        $cc = $lookup[$lc]
            ?? $lookup[substr($lc, 0, 2)]
            ?? self::regionOf($locale);

        return ($cc && in_array($cc, self::availableFlags(), true)) ? $cc : null;
    }

    /**
     * The region/country part of a locale, e.g. "nl_NL" -> "nl", "en-GB" -> "gb". Null if none.
     */
    private static function regionOf(string $locale): ?string
    {
        return preg_match('/[_-]([A-Za-z]{2})$/', $locale, $m) ? strtolower($m[1]) : null;
    }

    /**
     * The set of bundled flag codes (SVG filenames in client/flags/1x1), cached per request.
     *
     * @return string[]
     */
    public static function availableFlags(): array
    {
        static $codes = null;
        if ($codes === null) {
            $codes = [];
            foreach (glob(dirname(__DIR__) . '/client/flags/1x1/*.svg') ?: [] as $file) {
                $codes[] = strtolower(basename($file, '.svg'));
            }
        }
        return $codes;
    }
}
