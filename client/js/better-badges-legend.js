/* better-badges — locale-icon legend (opt-in).
 *
 * Builds a small key from the server-provided window.__betterBadgesLegend ({title, items:[{cls,label}]})
 * and inserts it under the Pages panel header. Each swatch is a real badge chip (class `badge
 * bb-locale-icon <state>`) so the glyph + colour come from the badge stylesheets — the legend always
 * matches what the tree/list shows. Re-inserts after CMS PJAX navigations; de-dupes itself.
 *
 * The swatches are decorative here (aria-hidden) — the visible text label carries the meaning. */
(function () {
    function build(data) {
        var wrap = document.createElement('div');
        wrap.className = 'bb-locale-legend';

        if (data.title) {
            var title = document.createElement('span');
            title.className = 'bb-locale-legend__title';
            title.textContent = data.title;
            wrap.appendChild(title);
        }

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

    function insert() {
        var data = window.__betterBadgesLegend;
        if (!data || !data.items || !data.items.length) {
            return;
        }
        // Only in the Pages section, where the tree/list badges live.
        var panel = document.querySelector('.cms-content.CMSMain');
        if (!panel || panel.querySelector(':scope > .bb-locale-legend')) {
            return;
        }
        var header = panel.querySelector(':scope > .cms-content-header');
        if (!header) {
            return;
        }
        header.insertAdjacentElement('afterend', build(data));
    }

    if (window.jQuery) {
        window.jQuery(function () { insert(); });
        // The CMS swaps panels via PJAX; re-check once the DOM has settled.
        window.jQuery(document).on('ajaxComplete', function () { window.setTimeout(insert, 0); });
    } else {
        document.addEventListener('DOMContentLoaded', insert);
    }
})();
