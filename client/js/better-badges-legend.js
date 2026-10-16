/* better-badges — locale-icon legend (opt-in).
 *
 * Builds a small key from the server-provided window.__betterBadgesLegend ({ariaLabel, closeLabel,
 * showLabel, items:[{cls,label}]}) and drops it at the foot of the edit-view tree sidebar (where the
 * badges are icon-only and need a key). Each swatch is a real badge chip (`badge bb-locale-icon <state>`)
 * so the glyph + colour come from the badge stylesheets — the legend always matches the tree.
 *
 * The user can dismiss it with the × ; that is remembered for the browser session and replaced by a small
 * "show legend" toggle so there is always a way back. Re-applies after CMS PJAX navigations; de-dupes.
 *
 * The swatches are decorative (aria-hidden) — the visible text labels carry the meaning. */
(function () {
    var STORAGE_KEY = 'bbLocaleLegendHidden';

    function isHidden() {
        try {
            return window.sessionStorage.getItem(STORAGE_KEY) === '1';
        } catch (e) {
            return false;
        }
    }

    function setHidden(hidden) {
        try {
            if (hidden) {
                window.sessionStorage.setItem(STORAGE_KEY, '1');
            } else {
                window.sessionStorage.removeItem(STORAGE_KEY);
            }
        } catch (e) {
            /* storage may be unavailable (private mode) — the choice just won't persist */
        }
    }

    function buildLegend(data) {
        var wrap = document.createElement('div');
        wrap.className = 'bb-locale-legend';
        // Group the key under one accessible name (the swatches are decorative; the labels carry meaning).
        wrap.setAttribute('role', 'group');
        if (data.ariaLabel) {
            wrap.setAttribute('aria-label', data.ariaLabel);
        }

        var close = document.createElement('button');
        close.type = 'button';
        close.className = 'bb-locale-legend__close';
        close.setAttribute('aria-label', data.closeLabel || 'Hide legend');
        close.addEventListener('click', function () {
            setHidden(true);
            render();
        });
        wrap.appendChild(close);

        (data.items || []).forEach(function (it) {
            if (!it || !it.cls) {
                return;
            }
            var item = document.createElement('span');
            item.className = 'bb-locale-legend__item';

            var chip = document.createElement('span');
            chip.className = 'badge bb-locale-icon ' + it.cls;
            chip.setAttribute('aria-hidden', 'true');

            var label = document.createElement('span');
            label.className = 'bb-locale-legend__label';
            label.textContent = it.label || '';

            item.appendChild(chip);
            item.appendChild(label);
            wrap.appendChild(item);
        });

        return wrap;
    }

    function buildToggle(data) {
        // A full-width bar (its border-top matches the legend's, spanning the panel) holding the button.
        var bar = document.createElement('div');
        bar.className = 'bb-locale-legend-toggle-bar';

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'bb-locale-legend-toggle';
        btn.textContent = data.showLabelShort || 'Legend';
        btn.setAttribute('aria-label', data.showLabel || 'Show legend');
        btn.addEventListener('click', function () {
            setHidden(false);
            render();
        });

        bar.appendChild(btn);
        return bar;
    }

    function placeAtFoot(host, el) {
        // Sit just above the panel's own collapse/south toolbar (so that toolbar stays in the corner).
        var south = host.querySelector(':scope > .toolbar--south');
        if (south) {
            host.insertBefore(el, south);
        } else {
            host.appendChild(el);
        }
    }

    function render() {
        var data = window.__betterBadgesLegend;
        if (!data || !data.items || !data.items.length) {
            return;
        }
        // Pages section only — the tree badges live here (not Files/Security/etc.).
        if (!/\/pages(\/|$)/.test(window.location.pathname)) {
            return;
        }
        // Only in the edit-view tree sidebar, where the badges are icon-only. The full-width list/tree
        // screen shows full text badges, so it needs no legend.
        var host = document.querySelector('.cms-content-tools.cms-panel');
        if (!host) {
            return;
        }
        var legend = host.querySelector(':scope > .bb-locale-legend');
        var toggleBar = host.querySelector(':scope > .bb-locale-legend-toggle-bar');

        if (isHidden()) {
            if (legend) {
                legend.remove();
            }
            if (!toggleBar) {
                placeAtFoot(host, buildToggle(data));
            }
        } else {
            if (toggleBar) {
                toggleBar.remove();
            }
            if (!legend) {
                placeAtFoot(host, buildLegend(data));
            }
        }
    }

    if (window.jQuery) {
        window.jQuery(function () { render(); });
        // The CMS swaps panels via PJAX; re-check once the DOM has settled.
        window.jQuery(document).on('ajaxComplete', function () { window.setTimeout(render, 0); });
    } else {
        document.addEventListener('DOMContentLoaded', render);
    }
})();
