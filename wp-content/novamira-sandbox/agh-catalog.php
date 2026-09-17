<?php
/**
 * Aviation Gear Hub — catalog rendering + mobile category dropdown.
 * Shortcodes: [agh_catalog]  and  [agh_product slug="..."]
 *
 * The catalog is a client-side filterable grid: search box + Search button,
 * category chips, Brand / Price / Sort-by dropdowns and an Apply-filters button.
 * All controls filter the products that are already printed server-side, so it
 * works with full-page caching and needs no AJAX. Header category links can
 * deep-link into a filtered view via ?cat=, ?q=, ?brand=, ?price=, ?sort=.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function agh_quote_url() { return home_url( '/contact/' ); }
function agh_product_url( $slug ) {
	$p = get_page_by_path( 'parts-gear/' . $slug );
	if ( $p ) { return get_permalink( $p ); }
	return home_url( '/parts-gear/' . $slug . '/' );
}
function agh_find_product( $slug ) {
	foreach ( agh_products_data() as $p ) { if ( $p['slug'] === $slug ) { return $p; } }
	return null;
}
function agh_cat_label( $cat ) {
	$c = agh_categories_data();
	return isset( $c[ $cat ] ) ? $c[ $cat ]['label'] : ucfirst( $cat );
}
function agh_img( $media, $size = 'medium_large', $alt = '' ) {
	if ( $media && wp_get_attachment_image_url( $media, $size ) ) {
		return wp_get_attachment_image( $media, $size, false, array( 'loading' => 'lazy', 'alt' => $alt, 'decoding' => 'async' ) );
	}
	return '<span class="agh-noimg" aria-hidden="true"></span>';
}

/** Parse the low end of a price range string ("$950 – $1,150") into an int (950). 0 = no price. */
function agh_price_value( $slug ) {
	$pr = function_exists( 'agh_price' ) ? agh_price( $slug ) : '';
	if ( ! $pr ) { return 0; }
	if ( preg_match( '/([0-9][0-9,]*)/', $pr, $m ) ) {
		return (int) str_replace( ',', '', $m[1] );
	}
	return 0;
}

/** Distinct brands present in the catalog, alphabetically. */
function agh_brand_list() {
	$brands = array();
	foreach ( agh_products_data() as $p ) {
		$b = trim( $p['brand'] );
		if ( $b !== '' ) { $brands[ $b ] = true; }
	}
	$brands = array_keys( $brands );
	sort( $brands, SORT_FLAG_CASE | SORT_STRING );
	return $brands;
}

function agh_styles() {
	static $done = false; if ( $done ) { return ''; } $done = true;
	return '<style id="agh-catalog-css">
	.agh-scope{--o:#C2410C;--oh:#9A3412;--dk:#1C1917;--am:#F59E0B;--cream:#FAFAF9;--lt:#F5F5F4;--raised:#E7E5E4;--bd:#D6D3D1;--mut:#78716C;--t2:#57534E;--wh:#fff;--serif:"Playfair Display",Georgia,serif;--sans:"Source Sans 3",system-ui,-apple-system,Segoe UI,Roboto,sans-serif;font-family:var(--sans);color:var(--dk);}
	.agh-scope *{box-sizing:border-box;}
	.agh-wrap{max-width:1240px;margin:0 auto;padding:0 20px;}
	.agh-eyebrow{font-size:12px;letter-spacing:.14em;text-transform:uppercase;font-weight:700;color:var(--o);margin:0 0 6px;}
	.agh-h2{font-family:var(--serif);font-weight:700;letter-spacing:-.5px;font-size:clamp(26px,4vw,40px);line-height:1.12;margin:0 0 10px;color:var(--dk);}
	.agh-sub{color:var(--t2);font-size:16px;line-height:1.6;max-width:60ch;margin:0 0 4px;}
	.agh-controls{display:flex;flex-wrap:wrap;gap:14px;align-items:center;justify-content:space-between;margin:26px 0 8px;}
	.agh-searchbar{display:flex;gap:8px;flex:1 1 300px;max-width:420px;}
	.agh-search{position:relative;flex:1 1 auto;}
	.agh-search input{width:100%;padding:12px 14px 12px 40px;border:1px solid var(--bd);border-radius:10px;background:var(--wh);font:inherit;font-size:15px;color:var(--dk);}
	.agh-search input:focus{outline:none;border-color:var(--o);box-shadow:0 0 0 3px rgba(194,65,12,.12);}
	.agh-search svg{position:absolute;left:13px;top:50%;transform:translateY(-50%);width:17px;height:17px;color:var(--mut);}
	.agh-search-btn{flex:0 0 auto;border:1px solid var(--o);background:var(--o);color:#fff;font:inherit;font-weight:700;font-size:14px;padding:0 18px;border-radius:10px;cursor:pointer;transition:.15s;}
	.agh-search-btn:hover{background:var(--oh);border-color:var(--oh);}
	.agh-chips{display:flex;flex-wrap:wrap;gap:8px;}
	.agh-chip{border:1px solid var(--bd);background:var(--wh);color:var(--t2);padding:9px 15px;border-radius:999px;font:inherit;font-size:14px;font-weight:600;cursor:pointer;transition:.15s;}
	.agh-chip:hover{border-color:var(--o);color:var(--o);}
	.agh-chip.is-active{background:var(--dk);border-color:var(--dk);color:#fff;}
	.agh-select{display:none;width:100%;padding:12px 14px;border:1px solid var(--bd);border-radius:10px;background:var(--wh);-webkit-appearance:none;appearance:none;font:inherit;font-size:15px;font-weight:600;color:var(--dk);}
	.agh-filters{display:flex;flex-wrap:wrap;gap:14px;align-items:flex-end;background:var(--cream);border:1px solid var(--bd);border-radius:12px;padding:16px 18px;margin:14px 0 4px;}
	.agh-field{display:flex;flex-direction:column;gap:6px;flex:1 1 180px;min-width:150px;}
	.agh-field label{font-size:11px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:var(--mut);}
	.agh-field select{width:100%;padding:11px 34px 11px 13px;border:1px solid var(--bd);border-radius:9px;background:var(--wh) url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'14\' height=\'14\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%2378716C\' stroke-width=\'2\'%3E%3Cpath d=\'M6 9l6 6 6-6\'/%3E%3C/svg%3E") no-repeat right 12px center;-webkit-appearance:none;appearance:none;font:inherit;font-size:14.5px;font-weight:600;color:var(--dk);cursor:pointer;}
	.agh-field select:focus{outline:none;border-color:var(--o);box-shadow:0 0 0 3px rgba(194,65,12,.12);}
	.agh-apply{flex:0 0 auto;align-self:flex-end;border:1px solid var(--o);background:var(--o);color:#fff;font:inherit;font-weight:700;font-size:14.5px;padding:12px 22px;border-radius:9px;cursor:pointer;transition:.15s;height:44px;}
	.agh-apply:hover{background:var(--oh);border-color:var(--oh);}
	.agh-tiles{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin:22px 0 6px;}
	.agh-tile{border:1px solid var(--bd);border-radius:12px;background:var(--wh);padding:16px;cursor:pointer;text-align:left;transition:.15s;font:inherit;}
	.agh-tile:hover{border-color:var(--o);box-shadow:0 6px 18px rgba(28,25,23,.07);transform:translateY(-2px);}
	.agh-tile b{font-family:var(--serif);font-size:17px;color:var(--dk);display:block;margin-bottom:3px;}
	.agh-tile span{font-size:12.5px;color:var(--mut);}
	.agh-count{color:var(--mut);font-size:14px;margin:18px 0 12px;}
	.agh-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(255px,1fr));gap:20px;}
	.agh-card{border:1px solid var(--bd);border-radius:14px;overflow:hidden;background:var(--wh);display:flex;flex-direction:column;transition:.18s;}
	.agh-card:hover{box-shadow:0 12px 30px rgba(28,25,23,.10);transform:translateY(-3px);border-color:var(--raised);}
	.agh-thumb{aspect-ratio:4/3;background:#fff;display:flex;align-items:center;justify-content:center;padding:16px;border-bottom:1px solid var(--bd);}
	.agh-thumb img{max-width:100%;max-height:100%;width:auto;height:auto;object-fit:contain;}
	.agh-body{padding:16px 16px 18px;display:flex;flex-direction:column;flex:1;}
	.agh-cat{font-size:11px;letter-spacing:.1em;text-transform:uppercase;font-weight:700;color:var(--o);margin-bottom:6px;}
	.agh-name{font-family:var(--serif);font-weight:700;font-size:19px;line-height:1.2;margin:0 0 6px;color:var(--dk);}
	.agh-brand{font-size:12.5px;color:var(--mut);margin:-2px 0 8px;font-weight:600;}
	.agh-tag{font-size:14px;color:var(--t2);line-height:1.5;margin:0 0 14px;flex:1;}
	.agh-price{font-size:15px;font-weight:700;color:var(--dk);margin-bottom:12px;}
	.agh-price-note{font-size:11px;font-weight:600;color:var(--mut);text-transform:uppercase;letter-spacing:.06em;margin-left:5px;}
	.agh-price-label{font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--mut);display:block;margin-bottom:5px;}
	.agh-price-range{font-family:var(--serif);font-size:27px;font-weight:700;color:var(--dk);margin-bottom:8px;line-height:1;}
	.agh-actions{display:flex;gap:8px;flex-wrap:wrap;}
	.agh-btn{display:inline-block;padding:10px 15px;border-radius:9px;font:inherit;font-size:14px;font-weight:600;text-decoration:none !important;text-align:center;white-space:nowrap;cursor:pointer;border:1px solid transparent;transition:.15s;}
	.agh-scope a.agh-btn-primary,.agh-btn-primary{background:var(--o) !important;color:#fff !important;flex:1;}
	.agh-scope a.agh-btn-primary:hover,.agh-btn-primary:hover{background:var(--oh) !important;color:#fff !important;}
	.agh-scope a.agh-btn-ghost,.agh-btn-ghost{background:var(--wh) !important;color:var(--dk) !important;border-color:var(--bd);}
	.agh-scope a.agh-btn-ghost:hover,.agh-btn-ghost:hover{border-color:var(--dk) !important;color:var(--dk) !important;}
	.agh-empty{display:none;padding:40px 0;color:var(--mut);text-align:center;font-size:16px;}
	.agh-note{margin:26px 0 0;color:var(--mut);font-size:13px;line-height:1.55;border-top:1px solid var(--bd);padding-top:16px;}
	.agh-crumb{font-size:13px;color:var(--mut);margin:0 0 22px;}
	.agh-crumb a{color:var(--mut);text-decoration:none;} .agh-crumb a:hover{color:var(--o);}
	.agh-crumb span{color:var(--dk);}
	.agh-pd{display:grid;grid-template-columns:minmax(0,1.05fr) minmax(0,1fr);gap:44px;align-items:start;}
	.agh-pd-img{border:1px solid var(--bd);border-radius:16px;background:#fff;aspect-ratio:1/1;display:flex;align-items:center;justify-content:center;padding:30px;}
	.agh-pd-img img{max-width:100%;max-height:100%;width:auto;object-fit:contain;}
	.agh-pd h1{font-family:var(--serif);font-weight:700;letter-spacing:-.6px;font-size:clamp(28px,4.4vw,44px);line-height:1.08;margin:6px 0 8px;color:var(--dk);}
	.agh-pd-brand{font-size:14px;font-weight:600;color:var(--mut);margin-bottom:18px;}
	.agh-pd-tag{font-size:18px;color:var(--dk);line-height:1.5;font-weight:600;margin:0 0 16px;}
	.agh-pd-desc{font-size:16px;color:var(--t2);line-height:1.7;margin:0 0 22px;}
	.agh-specs{width:100%;border-collapse:collapse;margin:0 0 24px;border-top:1px solid var(--bd);}
	.agh-specs tr{border-bottom:1px solid var(--bd);}
	.agh-specs th{text-align:left;font-weight:600;color:var(--mut);font-size:13.5px;padding:11px 0;width:40%;vertical-align:top;}
	.agh-specs td{padding:11px 0;font-size:14.5px;color:var(--dk);}
	.agh-pricebox{background:var(--cream);border:1px solid var(--bd);border-radius:14px;padding:18px 20px;margin:0 0 18px;}
	.agh-pricebox b{display:block;font-size:15px;color:var(--dk);margin-bottom:3px;}
	.agh-pricebox p{margin:0;color:var(--t2);font-size:13.5px;line-height:1.5;}
	.agh-pd-actions{display:flex;gap:10px;flex-wrap:wrap;}
	.agh-pd-actions .agh-btn{padding:13px 22px;font-size:15px;}
	.agh-related{margin-top:56px;}
	.agh-related h2{font-family:var(--serif);font-weight:700;font-size:26px;margin:0 0 18px;color:var(--dk);}
	@media (max-width:900px){ .agh-pd{grid-template-columns:1fr;gap:26px;} .agh-pd-img{aspect-ratio:4/3;} }
	@media (max-width:680px){
		.agh-chips{display:none;} .agh-select{display:block;} .agh-controls{flex-direction:column;align-items:stretch;}
		.agh-searchbar{max-width:none;flex:0 0 auto;} .agh-grid{grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;}
		.agh-name{font-size:17px;} .agh-body{padding:13px;}
		.agh-field{flex:1 1 100%;} .agh-apply{width:100%;}
	}
	.agh-catbar-toggle{display:none;}
	@media (max-width:768px){
		.elementor-element-62c2c1d .xpro-elementor-horizontal-navbar-wrapper{position:relative;}
		.agh-catbar-toggle{display:flex;align-items:center;justify-content:space-between;gap:10px;width:100%;padding:11px 16px;border:1px solid rgba(255,255,255,.28);border-radius:10px;background:rgba(0,0,0,.18);color:#fff;font-family:"Source Sans 3",system-ui,sans-serif;font-size:14px;font-weight:700;letter-spacing:.02em;cursor:pointer;}
		.agh-catbar-toggle .agh-caret{transition:transform .2s;font-size:12px;}
		.agh-catbar-toggle[aria-expanded="true"] .agh-caret{transform:rotate(180deg);}
		.elementor-element-62c2c1d .xpro-elementor-horizontal-navbar{position:absolute;left:0;right:0;top:calc(100% + 8px);z-index:120;flex-direction:column;background:#1C1917;border:1px solid #3D3327;border-radius:12px;padding:6px;box-shadow:0 18px 40px rgba(0,0,0,.35);display:none;max-height:70vh;overflow:auto;}
		.elementor-element-62c2c1d.agh-catbar-open .xpro-elementor-horizontal-navbar{display:flex;}
		.elementor-element-62c2c1d .xpro-elementor-horizontal-navbar>li{width:100%;border-bottom:1px solid rgba(255,255,255,.06);}
		.elementor-element-62c2c1d .xpro-elementor-horizontal-navbar>li:last-child{border-bottom:0;}
		.elementor-element-62c2c1d .xpro-elementor-horizontal-navbar>li>a{display:block;padding:13px 14px;color:#fff !important;font-size:15px;}
	}
	</style>';
}

function agh_catalog_shortcode() {
	$GLOBALS['agh_needs_catalog_js'] = true;
	$cats  = agh_categories_data();
	$prods = agh_products_data();
	$brands = agh_brand_list();
	ob_start();
	echo agh_styles();
	?>
	<div class="agh-scope" id="agh-catalog">
	<div class="agh-wrap">

		<div class="agh-tiles" role="list">
			<?php foreach ( $cats as $slug => $c ) : ?>
				<button type="button" class="agh-tile" role="listitem" data-filter="<?php echo esc_attr( $slug ); ?>"><b><?php echo esc_html( $c['label'] ); ?></b><span><?php echo esc_html( wp_trim_words( $c['blurb'], 12, '…' ) ); ?></span></button>
			<?php endforeach; ?>
		</div>

		<div class="agh-controls">
			<form class="agh-searchbar" id="agh-searchbar" role="search">
				<div class="agh-search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg><input type="search" id="agh-q" placeholder="Search the catalog…" aria-label="Search products"></div>
				<button type="submit" class="agh-search-btn">Search</button>
			</form>
			<div class="agh-chips" id="agh-chips"><button type="button" class="agh-chip is-active" data-filter="all">All</button>
				<?php foreach ( $cats as $slug => $c ) : ?><button type="button" class="agh-chip" data-filter="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $c['label'] ); ?></button><?php endforeach; ?>
			</div>
			<select class="agh-select" id="agh-select" aria-label="Filter by category"><option value="all">All categories</option>
				<?php foreach ( $cats as $slug => $c ) : ?><option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $c['label'] ); ?></option><?php endforeach; ?>
			</select>
		</div>

		<div class="agh-filters">
			<div class="agh-field"><label for="agh-brand">Brand</label>
				<select id="agh-brand"><option value="all">All brands</option>
					<?php foreach ( $brands as $b ) : ?><option value="<?php echo esc_attr( strtolower( $b ) ); ?>"><?php echo esc_html( $b ); ?></option><?php endforeach; ?>
				</select>
			</div>
			<div class="agh-field"><label for="agh-price">Price</label>
				<select id="agh-price">
					<option value="all">Any price</option>
					<option value="0-100">Under $100</option>
					<option value="100-500">$100 – $500</option>
					<option value="500-1000">$500 – $1,000</option>
					<option value="1000-0">$1,000+</option>
				</select>
			</div>
			<div class="agh-field"><label for="agh-sort">Sort by</label>
				<select id="agh-sort">
					<option value="price-asc">Price: low to high</option>
					<option value="price-desc">Price: high to low</option>
					<option value="name-asc">Name: A to Z</option>
				</select>
			</div>
			<button type="button" class="agh-apply" id="agh-apply">Apply filters</button>
		</div>

		<p class="agh-count" id="agh-count"></p>
		<div class="agh-grid" id="agh-grid">
			<?php foreach ( $prods as $p ) :
				$url = agh_product_url( $p['slug'] );
				$hay = strtolower( $p['title'] . ' ' . $p['brand'] . ' ' . agh_cat_label( $p['cat'] ) . ' ' . $p['tagline'] );
				$pval = agh_price_value( $p['slug'] );
				?>
				<article class="agh-card" data-cat="<?php echo esc_attr( $p['cat'] ); ?>" data-brand="<?php echo esc_attr( strtolower( $p['brand'] ) ); ?>" data-price="<?php echo esc_attr( $pval ); ?>" data-name="<?php echo esc_attr( strtolower( $p['title'] ) ); ?>" data-text="<?php echo esc_attr( $hay ); ?>">
					<a class="agh-thumb" href="<?php echo esc_url( $url ); ?>"><?php echo agh_img( $p['media'], 'medium_large', $p['title'] ); ?></a>
					<div class="agh-body">
						<div class="agh-cat"><?php echo esc_html( agh_cat_label( $p['cat'] ) ); ?></div>
						<h3 class="agh-name"><?php echo esc_html( $p['title'] ); ?></h3>
						<div class="agh-brand"><?php echo esc_html( $p['brand'] ); ?></div>
						<p class="agh-tag"><?php echo esc_html( $p['tagline'] ); ?></p>
						<div class="agh-price"><?php $pr = function_exists( 'agh_price' ) ? agh_price( $p['slug'] ) : ''; echo $pr ? esc_html( $pr ) . '<span class="agh-price-note">used</span>' : 'Price on request'; ?></div>
						<div class="agh-actions"><a class="agh-btn agh-btn-primary" href="<?php echo esc_url( $url ); ?>">View product</a><a class="agh-btn agh-btn-ghost" href="<?php echo esc_url( agh_quote_url() ); ?>">Request quote</a></div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		<div class="agh-empty" id="agh-empty">No products match your filters. Try another term or <a href="<?php echo esc_url( agh_quote_url() ); ?>">ask us directly</a>.</div>
		<p class="agh-note">Products shown are representative of current inventory. Part numbers, fitment, approvals and price are confirmed against your aircraft&rsquo;s documentation before anything is quoted. Nothing on this page is an approval or certification claim.</p>
	</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'agh_catalog', 'agh_catalog_shortcode' );

add_action( 'wp_footer', 'agh_catalog_footer_js', 98 );
function agh_catalog_footer_js() {
	if ( empty( $GLOBALS['agh_needs_catalog_js'] ) ) { return; }
	?>
	<script id="agh-catalog-js">
	(function(){
		var grid=document.getElementById('agh-grid');if(!grid)return;
		var cards=[].slice.call(grid.querySelectorAll('.agh-card'));
		var chips=[].slice.call(document.querySelectorAll('#agh-chips .agh-chip'));
		var sel=document.getElementById('agh-select');
		var q=document.getElementById('agh-q');
		var form=document.getElementById('agh-searchbar');
		var brand=document.getElementById('agh-brand');
		var price=document.getElementById('agh-price');
		var sort=document.getElementById('agh-sort');
		var applyBtn=document.getElementById('agh-apply');
		var count=document.getElementById('agh-count');
		var empty=document.getElementById('agh-empty');
		var tiles=[].slice.call(document.querySelectorAll('.agh-tile'));
		var cur='all';
		function priceOk(v){
			if(!price)return true;var b=price.value;if(b==='all')return true;
			var parts=b.split('-');var lo=parseInt(parts[0],10)||0;var hi=parseInt(parts[1],10)||0;
			if(hi===0)return v>=lo;         // open-ended top bucket
			return v>=lo&&v<hi;
		}
		function chipLabel(f){var el=document.querySelector('#agh-chips .agh-chip[data-filter="'+f+'"]');return el?el.textContent:'';}
		function sortCards(){
			if(!sort)return;var mode=sort.value;
			var sorted=cards.slice().sort(function(a,b){
				if(mode==='name-asc'){return (a.getAttribute('data-name')||'').localeCompare(b.getAttribute('data-name')||'');}
				var pa=parseInt(a.getAttribute('data-price'),10)||0,pb=parseInt(b.getAttribute('data-price'),10)||0;
				// items with no price sink to the bottom regardless of direction
				if(pa===0&&pb!==0)return 1; if(pb===0&&pa!==0)return -1;
				return mode==='price-desc'?(pb-pa):(pa-pb);
			});
			sorted.forEach(function(c){grid.appendChild(c);});
		}
		function apply(){
			var term=(q&&q.value||'').trim().toLowerCase();
			var br=brand?brand.value:'all';
			var n=0;
			sortCards();
			cards.forEach(function(c){
				var okCat=cur==='all'||c.getAttribute('data-cat')===cur;
				var okTxt=!term||c.getAttribute('data-text').indexOf(term)>-1;
				var okBrand=br==='all'||c.getAttribute('data-brand')===br;
				var okPrice=priceOk(parseInt(c.getAttribute('data-price'),10)||0);
				var show=okCat&&okTxt&&okBrand&&okPrice;
				c.style.display=show?'':'none';if(show)n++;
			});
			count.textContent='Showing '+n+' of '+cards.length+' products'+(cur!=='all'?' · '+chipLabel(cur):'');
			empty.style.display=n?'none':'block';
		}
		function setCat(f){cur=f;chips.forEach(function(ch){ch.classList.toggle('is-active',ch.getAttribute('data-filter')===f);});if(sel)sel.value=f;apply();}
		chips.forEach(function(ch){ch.addEventListener('click',function(){setCat(ch.getAttribute('data-filter'));});});
		tiles.forEach(function(t){t.addEventListener('click',function(){setCat(t.getAttribute('data-filter'));document.getElementById('agh-catalog').scrollIntoView({behavior:'smooth',block:'start'});});});
		if(sel)sel.addEventListener('change',function(){setCat(sel.value);});
		if(q)q.addEventListener('input',apply);
		if(form)form.addEventListener('submit',function(e){e.preventDefault();apply();});
		if(brand)brand.addEventListener('change',apply);
		if(price)price.addEventListener('change',apply);
		if(sort)sort.addEventListener('change',apply);
		if(applyBtn)applyBtn.addEventListener('click',apply);
		// Deep-link support: /parts-gear/?cat=avionics&q=...&brand=...&price=...&sort=...
		try{
			var params=new URLSearchParams(window.location.search);
			var pCat=params.get('cat');
			if(pCat){pCat=pCat.toLowerCase();if(document.querySelector('#agh-chips .agh-chip[data-filter="'+pCat+'"]')){cur=pCat;chips.forEach(function(ch){ch.classList.toggle('is-active',ch.getAttribute('data-filter')===pCat);});if(sel)sel.value=pCat;}}
			var pQ=params.get('q');if(pQ&&q)q.value=pQ;
			var pBrand=params.get('brand');if(pBrand&&brand){var bl=pBrand.toLowerCase();for(var i=0;i<brand.options.length;i++){if(brand.options[i].value===bl){brand.value=bl;break;}}}
			var pPrice=params.get('price');if(pPrice&&price){for(var j=0;j<price.options.length;j++){if(price.options[j].value===pPrice){price.value=pPrice;break;}}}
			var pSort=params.get('sort');if(pSort&&sort){for(var k=0;k<sort.options.length;k++){if(sort.options[k].value===pSort){sort.value=pSort;break;}}}
			if(pCat&&window.location.hash!=='#agh-catalog'){setTimeout(function(){document.getElementById('agh-catalog').scrollIntoView({behavior:'smooth',block:'start'});},250);}
		}catch(e){}
		apply();
	})();
	</script>
	<?php
}

function agh_product_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'slug' => '' ), $atts );
	$p = agh_find_product( $atts['slug'] );
	if ( ! $p ) { return ''; }
	ob_start();
	echo agh_styles();
	?>
	<div class="agh-scope">
	<div class="agh-wrap" style="padding-top:34px;padding-bottom:20px;">
		<nav class="agh-crumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> / <a href="<?php echo esc_url( home_url( '/parts-gear/' ) ); ?>">Parts &amp; gear</a> / <a href="<?php echo esc_url( home_url( '/parts-gear/?cat=' . $p['cat'] . '#agh-catalog' ) ); ?>"><?php echo esc_html( agh_cat_label( $p['cat'] ) ); ?></a> / <span><?php echo esc_html( $p['title'] ); ?></span></nav>
		<div class="agh-pd">
			<div class="agh-pd-img"><?php echo agh_img( $p['media'], 'large', $p['title'] ); ?></div>
			<div class="agh-pd-info">
				<h1><?php echo esc_html( $p['title'] ); ?></h1>
				<div class="agh-pd-brand"><?php echo esc_html( $p['brand'] ); ?></div>
				<p class="agh-pd-tag"><?php echo esc_html( $p['tagline'] ); ?></p>
				<p class="agh-pd-desc"><?php echo esc_html( $p['desc'] ); ?></p>
				<?php if ( ! empty( $p['specs'] ) ) : ?>
				<table class="agh-specs"><tbody>
					<?php foreach ( $p['specs'] as $k => $v ) : ?><tr><th><?php echo esc_html( $k ); ?></th><td><?php echo esc_html( $v ); ?></td></tr><?php endforeach; ?>
				</tbody></table>
				<?php endif; ?>
				<div class="agh-pricebox"><?php $pr = function_exists( 'agh_price' ) ? agh_price( $p['slug'] ) : ''; if ( $pr ) : ?><span class="agh-price-label">Estimated used price · USD</span><div class="agh-price-range"><?php echo esc_html( $pr ); ?></div><p>Indicative used / resale range. Final price depends on condition, configuration and availability. Request a quote to confirm the exact price for your aircraft.</p><?php else : ?><b>Price on request</b><p>We work on a quote basis so you get current pricing and the correct configuration for your aircraft.</p><?php endif; ?></div>
				<div class="agh-pd-actions"><a class="agh-btn agh-btn-primary" href="<?php echo esc_url( agh_quote_url() ); ?>">Request a quote</a><a class="agh-btn agh-btn-ghost" href="<?php echo esc_url( home_url( '/parts-gear/#agh-catalog' ) ); ?>">Back to catalog</a></div>
			</div>
		</div>
		<?php
		$rel = array();
		foreach ( agh_products_data() as $r ) { if ( $r['cat'] === $p['cat'] && $r['slug'] !== $p['slug'] ) { $rel[] = $r; } }
		$rel = array_slice( $rel, 0, 4 );
		if ( $rel ) : ?>
		<div class="agh-related">
			<h2>More in <?php echo esc_html( strtolower( agh_cat_label( $p['cat'] ) ) ); ?></h2>
			<div class="agh-grid">
				<?php foreach ( $rel as $r ) : $ru = agh_product_url( $r['slug'] ); ?>
					<article class="agh-card"><a class="agh-thumb" href="<?php echo esc_url( $ru ); ?>"><?php echo agh_img( $r['media'], 'medium_large', $r['title'] ); ?></a>
						<div class="agh-body"><div class="agh-cat"><?php echo esc_html( $r['brand'] ); ?></div><h3 class="agh-name"><?php echo esc_html( $r['title'] ); ?></h3><p class="agh-tag"><?php echo esc_html( $r['tagline'] ); ?></p><div class="agh-actions"><a class="agh-btn agh-btn-primary" href="<?php echo esc_url( $ru ); ?>">View product</a></div></div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>
		<p class="agh-note">Fitment, part numbers, approvals and price are confirmed against your aircraft&rsquo;s documentation before anything is quoted. Nothing on this page is an approval or certification claim.</p>
	</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'agh_product', 'agh_product_shortcode' );

add_action( 'wp_footer', 'agh_catbar_script', 99 );
function agh_catbar_script() {
	?>
	<script id="agh-catbar-js">
	(function(){
		function init(){
			var el=document.querySelector('.elementor-element-62c2c1d');if(!el)return;
			var nav=el.querySelector('.xpro-elementor-horizontal-navbar');
			var wrap=el.querySelector('.xpro-elementor-horizontal-navbar-wrapper');
			if(!nav||!wrap||el.querySelector('.agh-catbar-toggle'))return;
			var btn=document.createElement('button');
			btn.className='agh-catbar-toggle';btn.type='button';btn.setAttribute('aria-expanded','false');
			btn.innerHTML='<span>Browse categories</span><span class="agh-caret">▾</span>';
			wrap.insertBefore(btn,nav);
			btn.addEventListener('click',function(e){e.stopPropagation();var open=el.classList.toggle('agh-catbar-open');btn.setAttribute('aria-expanded',open?'true':'false');});
			document.addEventListener('click',function(ev){if(el.classList.contains('agh-catbar-open')&&!el.contains(ev.target)){el.classList.remove('agh-catbar-open');btn.setAttribute('aria-expanded','false');}});
		}
		if(document.readyState!=='loading')init();else document.addEventListener('DOMContentLoaded',init);
	})();
	</script>
	<?php
}
