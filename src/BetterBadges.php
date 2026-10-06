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
}
