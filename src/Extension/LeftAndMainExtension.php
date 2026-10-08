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

        // Locale flags (opt-in) render via their own stylesheet, independent of the chosen style.
        if (BetterBadges::localeFlagsEnabled()) {
            Requirements::css(BetterBadges::localeFlagStylesheet());
        }

        // High-contrast overlay (opt-in) — loaded last so it refines the active style's badge colours.
        if (BetterBadges::isEnabled() && BetterBadges::highContrastEnabled()) {
            Requirements::css(BetterBadges::highContrastStylesheet());
        }
    }
}
