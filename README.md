# Silverstripe Better Badges

Stop the CMS page-tree status/locale badges from merging into one solid vertical bar.

By default Silverstripe renders the page-tree badges (`Draft`, `Modified`, and — with
[Fluent](https://github.com/tractorcow-farm/silverstripe-fluent) — the locale badges)
absolutely positioned and touching, so down a longer tree they read as one continuous
coloured bar instead of a per-row status. This module gives them a little vertical
breathing room and a choice of styles, consistently across the page tree, the edit-view
header/breadcrumb and the list/gridfield views.

![Badge styling options](docs/images/overview.png)

## Requirements

- `silverstripe/framework` ^6.0
- `silverstripe/admin` ^3.0

Works with or without [Fluent](https://github.com/tractorcow-farm/silverstripe-fluent) —
it styles the native status badges on their own, and softens the Fluent locale badges too
when Fluent is installed.

## Installation

```sh
composer require xddesigners/silverstripe-better-badges
```

No build step and no `dev/build` needed — it only ships CSS that is loaded into the CMS.

## Configuration

Pick a style (default `outline`). In YAML:

```yaml
XD\BetterBadges\BetterBadges:
  style: outline   # outline | outline-pill | compact | pill | off
```

Or in `app/_config.php`:

```php
use XD\BetterBadges\BetterBadges;
use SilverStripe\Core\Config\Config;

Config::modify()->set(BetterBadges::class, 'style', 'pill');
```

### Styles

| Style          | Looks like                                                                                      |
| -------------- | ----------------------------------------------------------------------------------------------- |
| `outline`      | **Default.** Compact + a light outline; every state softened to a tinted chip with a coloured border. |
| `outline-pill` | The outline treatment with fully rounded pill badges.                                           |
| `compact`      | Compact rounded rectangle, keeps the native solid fills.                                         |
| `pill`         | Compact, fully rounded, keeps the native solid fills.                                            |
| `off`          | Alias `none`. **Disabled** — loads no stylesheet; the CMS badges stay exactly as Silverstripe renders them (the first panel above). |

Every style except `off` adds the vertical spacing that breaks up the bar; they only differ in
the look. `off` leaves the native badges completely untouched — useful to compare, or to disable
the module on a specific environment (see below).

## Environment override

The style can also be set from `.env`, which **takes precedence over the YAML/PHP config**. This is
handy to override it per environment — e.g. turn the module off on one site — without changing config:

```
SS_BETTER_BADGES_STYLE="off"
```

Any value works (`outline`, `pill`, `off`, …). When the variable is set it wins; otherwise the
`style` config is used.

## License

BSD-3-Clause.
