<?php

namespace XD\BetterBadges\Extension;

use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;
use XD\BetterBadges\BetterBadges;

/**
 * Loads the configured badge stylesheet into the CMS.
 *
 * @extends Extension<\SilverStripe\Admin\LeftAndMain>
 */
class LeftAndMainExtension extends Extension
{
    protected function onAfterInit(): void
    {
        $stylesheet = BetterBadges::stylesheet();
        if ($stylesheet !== '') {
            Requirements::css($stylesheet);
        }

        // Locale marker (opt-in), independent of the chosen style: either the translatable icon or a
        // country flag inside the Fluent locale badge. The icon wins if both are enabled.
        if (BetterBadges::localeIconEnabled()) {
            Requirements::css(BetterBadges::localeIconStylesheet());
        } elseif (BetterBadges::localeFlagsEnabled()) {
            Requirements::css(BetterBadges::localeFlagStylesheet());
        }

        // High-contrast overlay (opt-in) — loaded last so it refines the active style's badge colours.
        if (BetterBadges::isEnabled() && BetterBadges::highContrastEnabled()) {
            Requirements::css(BetterBadges::highContrastStylesheet());
        }
    }
}
