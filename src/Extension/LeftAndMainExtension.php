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
    }
}
