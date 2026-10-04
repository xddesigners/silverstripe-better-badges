<?php

namespace XD\BetterBadges;

use SilverStripe\Core\Config\Configurable;

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
     *
     * An unknown value falls back to 'outline'.
     *
     * @config
     */
    private static string $style = 'outline';

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
     * Resolve the stylesheet to load for the configured style.
     */
    public static function stylesheet(): string
    {
        $style = strtolower(trim((string) static::config()->get('style')));
        return self::STYLES[$style] ?? self::STYLES['outline'];
    }
}
