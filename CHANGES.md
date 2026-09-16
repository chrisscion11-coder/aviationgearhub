# Aero Gear Hub — catalog & aircraft fixes

This change fixes the reported problems on aerogearhub.ltd (a WordPress /
Elementor site). The site's custom catalog logic lives in the Novamira
sandbox (`wp-content/novamira-sandbox/`), which is auto-loaded by a
mu-plugin on every request. The files in this repo mirror the deployed
sandbox files so the code is reviewable in git.

## What was broken

1. **Header / mega / footer category links did nothing useful.** Every
   category link (Headsets, Avionics, Aircraft parts, Pilot gear, Flight
   accessories) pointed at the bare `/parts-gear/` page, so clicking a
   category always showed the full catalog instead of that category.
2. **Catalog controls did not match / did not work.** Users were seeing a
   stale full-page-cached version with a Search button, Brand / Price /
   Sort-by dropdowns and an Apply-filters button that no longer matched the
   deployed code, so those controls appeared dead.
3. **Aircraft page showed placeholders.** It rendered six hardcoded
   "Sample listing" Cessna cards even though ~86 real Cessna aircraft photos
   had been uploaded to the media library.
4. **Parts & Gear could render blank** for users being served the stale
   cached page.

## What changed

### `wp-content/novamira-sandbox/agh-catalog.php` (rewritten)
- Rebuilt the `[agh_catalog]` control bar to the intended design and made
  every control work client-side over the server-rendered product grid:
  - Search box **and** a Search button (live-as-you-type + submit).
  - Category chips (desktop) / category `<select>` (mobile).
  - **Brand** dropdown (built dynamically from the products).
  - **Price** dropdown (Any / Under $100 / $100–500 / $500–1,000 / $1,000+),
    parsed from each product's indicative price range.
  - **Sort by** (Price low→high / high→low / Name A–Z).
  - **Apply filters** button.
- Added deep-link support: `/parts-gear/?cat=&q=&brand=&price=&sort=` is read
  on load, the matching controls are set, and the page scrolls to the catalog.
  This is what makes the header category links land on a filtered view.
- Product detail breadcrumb now links back to the filtered category.
- No AJAX — filtering runs on the already-printed grid, so it is compatible
  with full-page caching.

### `wp-content/novamira-sandbox/agh-aircraft.php` (new)
- New `[agh_aircraft]` shortcode + `agh_aircraft_data()` that builds real
  aircraft listings from the uploaded media. Any attachment named
  `"<year>-cessna-<model>-piston-single|piston-twin|light-sport-aircraft_<id>"`
  becomes a listing; duplicate re-uploads (same photo-id token) are merged
  (86 unique listings). Year / model / engine type are parsed from the
  filename and grouped into model families (150/152, 172 Skyhawk, 182 Skylane,
  210 Centurion, 310, 337 Skymaster, 421 Golden Eagle, …).
- Catalog UI matches the parts catalog: search + Search button, engine-type
  chips (Single / Twin / Light sport), a Model dropdown, a Sort-by dropdown
  (Year newest/oldest, Model A–Z) and Apply filters. Supports
  `/aircraft/?type=&family=&q=` deep links.

## Database-side edits (not files — applied via the WP API)

- **Aircraft page (page ID 40):** replaced the hardcoded sample-listing
  Elementor block with the `[agh_aircraft]` shortcode and updated the hero
  intro copy to drop the "sample entries" wording.
- **Menus:** repointed the category/aircraft items in the *Primary menu*
  (the header nav dropdown), *Category bar*, *Mega – Parts and gear*,
  *Mega – Aircraft* and *Footer – Parts and gear* menus to filtered URLs,
  e.g. `/parts-gear/?cat=headsets#agh-catalog` and
  `/aircraft/?type=single#agh-aircraft`. (The header dropdown's submenu items
  live in the Primary menu — fixed in a follow-up so every category link,
  including the dropdown, deep-links to its filtered view.)
- Flushed LiteSpeed full-page cache, Elementor CSS cache and the WP object
  cache so visitors stop getting the stale catalog.

## Follow-up: blank pages + dead filters (root cause)

Diagnosed live with a headless browser. Two real defects were found:

1. **Inline script corruption → dead filters.** When the catalog / aircraft
   JavaScript was returned as part of the shortcode output, WordPress/Elementor
   HTML-encoded the `&&` operators to `&#038;&#038;`, producing
   `Invalid or unexpected token` and aborting the whole script — so search,
   chips, brand/price/sort and Apply did nothing. Fixed by emitting both
   scripts as **raw `wp_footer` output** (the same path the working category-bar
   script already used), so they are never texturized. The shortcodes now set a
   `$GLOBALS` flag and `agh_catalog_footer_js()` / `agh_aircraft_footer_js()`
   print the scripts in the footer.
2. **Blank pages.** `agh-enhance.php` sets sections and product cards to
   `opacity:0` and reveals them on scroll; when the reveal never fired, whole
   pages stayed invisible. Added a **failsafe** in `agh-enhance.php` that
   force-reveals every pending element after a short timeout (and on
   `window.load`), so content can never remain hidden.

Verified in a headless browser against the live site: Parts & Gear shows
98/98 products (clicking *Avionics* → "Showing 2 of 98"), Aircraft shows
86/86 real Cessna listings (clicking *Twin-engine* → "Showing 11 of 86"),
with no page JS errors.
