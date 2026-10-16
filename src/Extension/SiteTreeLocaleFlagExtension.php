<?php

namespace XD\BetterBadges\Extension;

use SilverStripe\Core\Extension;
use TractorCow\Fluent\Extension\Traits\FluentBadgeTrait;
use TractorCow\Fluent\Model\Locale;
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
        // Correct the inherited-page tooltip regardless of the marker feature (see below).
        $this->fixInheritedTitles($flags);

        // Opt-in locale icon replaces the code text; it takes precedence over the country flag.
        if (BetterBadges::localeIconEnabled()) {
            $this->applyLocaleIcon($flags);
            return;
        }

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

    /**
     * Icon mode: stamp `bb-locale-icon` onto the badges the stylesheet turns into glyphs — the Fluent
     * locale badge and its "No source" flag (both → translate icon), plus the native draft and modified
     * status badges (→ eye-off and pen). The stylesheet picks the glyph per the badge's own status
     * class and tints it to the badge colour. The flag-array key is rendered unescaped, so adding a
     * class to it is the reliable, server-side, JS-free way to decorate the badge. (We don't add the
     * CMS's own `font-icon-translatable` class: the key also lands on the tree <li>, whose ::before
     * would then paint a stray glyph on the whole row — so the stylesheet carries every glyph.)
     */
    private function applyLocaleIcon(array &$flags): void
    {
        // Flag-key fragments that get an icon: the Fluent locale badge + "No source", and the native
        // draft / modified status badges.
        $iconKeys = ['fluent-badge', 'removedfromdraft', 'addedtodraft', 'modified'];

        // Rebuild in place: preserve order (the edit-view sidebar shows only the FIRST badge, so order
        // matters), tag the iconable badges, and drop Fluent's empty "invisible" flag — otherwise it
        // can sit first in that sidebar and hide every real icon badge, leaving the row blank.
        $rebuilt = [];
        foreach ($flags as $key => $data) {
            $k = (string) $key;
            if (strpos($k, 'fluentinvisible') !== false) {
                continue;
            }
            $newKey = $k;
            foreach ($iconKeys as $needle) {
                if (strpos($k, $needle) !== false) {
                    $newKey = $k . ' bb-locale-icon';
                    break;
                }
            }
            $rebuilt[$newKey] = $data;
        }

        // Keep the Fluent locale badge last, so the language icon always sits on the right, after the
        // status icons (draft / modified / no source).
        $status = [];
        $locale = [];
        foreach ($rebuilt as $key => $data) {
            if (strpos((string) $key, 'fluent-badge') !== false) {
                $locale[$key] = $data;
            } else {
                $status[$key] = $data;
            }
        }
        $flags = $status + $locale;
    }

    /**
     * Fluent labels the tree badge of a page that merely inherits its content with "Localised in
     * {source locale}" — the same text as a truly localised page. Use Fluent's own "Inherited from
     * {locale}" string (the one its header/grid badges use) so the tooltip tells the two states apart.
     */
    private function fixInheritedTitles(array &$flags): void
    {
        foreach ($flags as $key => &$data) {
            if (strpos((string) $key, 'fluent-badge--localised') === false
                || !is_array($data)
                || empty($data['text'])
            ) {
                continue;
            }

            $source = Locale::getByLocale($data['text']);
            $data['title'] = _t(
                FluentBadgeTrait::class . '.BadgeInherited',
                'Inherited from {locale}',
                ['locale' => $source ? $source->getTitle() : $data['text']]
            );
        }
    }
}
