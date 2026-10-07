<?php

namespace XD\BetterBadges\Extension;

use SilverStripe\Core\Extension;
use XD\BetterBadges\BetterBadges;

/**
 * Stamps a country-flag class onto each Fluent locale status flag so the stylesheet can render a
 * small flag inside the badge. Runs after Fluent (see the `After: ['#fluentcms-pages']` config), so
 * the fluent flags exist in `$flags` to decorate.
 *
 * The flag's class (the flag-array key) is rendered unescaped, unlike the flag text — so adding
 * `bb-flag bb-flag-{cc}` to the key is the reliable, server-side, JS-free way to attach the flag.
 * Applies in every surface that renders the badge: the page list, the edit-view tree and the header.
 *
 * @extends Extension<\SilverStripe\CMS\Model\SiteTree>
 */
class SiteTreeLocaleFlagExtension extends Extension
{
    public function updateStatusFlags(array &$flags)
    {
        if (!BetterBadges::localeFlagsEnabled()) {
            return;
        }

        $extra = [];
        if (BetterBadges::localeFlagSquare()) {
            $extra[] = 'bb-flag-square';
        }
        if (BetterBadges::localeFlagsHideCode()) {
            $extra[] = 'bb-flag-nocode';
        }

        // Collect key rewrites first, then apply — don't mutate $flags while iterating it.
        $rewrites = [];
        foreach ($flags as $key => $data) {
            if (strpos((string) $key, 'fluent-badge') === false) {
                continue;
            }
            $text = (is_array($data) && isset($data['text'])) ? (string) $data['text'] : '';
            $cc = BetterBadges::flagCodeForLocale($text);
            if ($cc === null) {
                continue;
            }
            $classes = array_merge(['bb-flag', 'bb-flag-' . $cc], $extra);
            $rewrites[$key] = $key . ' ' . implode(' ', $classes);
        }

        foreach ($rewrites as $old => $new) {
            $flags[$new] = $flags[$old];
            unset($flags[$old]);
        }
    }
}
