<?php
/**
 * Aviation Gear Hub — catalog rendering + mobile category dropdown.
 * Shortcodes: [agh_catalog]  and  [agh_product slug="..."]
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
function agh_price_low( $slug ) {
	$pr = function_exists( 'agh_price' ) ? agh_price( $slug ) : '';
	if ( ! $pr ) { return ''; }
	if ( preg_match( '/([0-9][0-9,]*)/', $pr, $m ) ) { return (int) str_replace( ',', '', $m[1] ); }
	return '';
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
	.agh-search{position:relative;display:flex;align-items:center;flex:1 1 300px;max-width:460px;background:var(--wh);border:1px solid var(--bd);border-radius:10px;overflow:hidden;}
	.agh-search svg{flex:0 0 auto;width:17px;height:17px;color:var(--mut);margin-left:13px;}
	.agh-search input{flex:1;min-width:0;border:0;background:transparent;padding:12px 12px;font:inherit;font-size:15px;color:var(--dk);}
	.agh-search input:focus{outline:none;}
	.agh-search:focus-within{border-color:var(--o);box-shadow:0 0 0 3px rgba(194,65,12,.12);}
	.agh-search .agh-search-go{flex:0 0 auto;border:0;background:var(--o);color:#fff;font:inherit;font-weight:700;font-size:13px;letter-spacing:.02em;padding:0 18px;align-self:stretch;cursor:pointer;transition:background .15s;}
	.agh-search .agh-search-go:hover{background:var(--oh);}
	.agh-chips{display:flex;flex-wrap:wrap;gap:8px;}
	.agh-chip{border:1px solid var(--bd);background:var(--wh);color:var(--t2);padding:9px 15px;border-radius:999px;font:inherit;font-size:14px;font-weight:600;cursor:pointer;transition:.15s;}
	.agh-chip:hover{border-color:var(--o);color:var(--o);}
	.agh-chip.is-active{background:var(--dk);border-color:var(--dk);color:#fff;}
	.agh-subchips{display:flex;flex-wrap:wrap;gap:8px;width:100%;margin:2px 0 0;padding:13px 0 2px;border-top:1px dashed var(--bd);}
	.agh-subchips[hidden]{display:none;}
	.agh-subchip{border:1px solid var(--bd);background:var(--wh);color:var(--t2);padding:7px 13px;border-radius:999px;font:inherit;font-size:13px;font-weight:600;cursor:pointer;transition:.15s;}
	.agh-subchip:hover{border-color:var(--o);color:var(--o);}
	.agh-subchip.is-active{background:var(--o);border-color:var(--o);color:#fff;}
	.agh-select{display:none;width:100%;padding:12px 14px;border:1px solid var(--bd);border-radius:10px;background:var(--wh);-webkit-appearance:none;appearance:none;font:inherit;font-size:15px;font-weight:600;color:var(--dk);}
	.agh-acfilters{display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;margin:14px 0 4px;}
	.agh-acfilters .agh-field{display:flex;flex-direction:column;gap:4px;flex:1 1 175px;min-width:150px;}
	.agh-acfilters label{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--mut);}
	.agh-acfilters select{width:100%;padding:11px 13px;border:1px solid var(--bd);border-radius:10px;background:var(--wh);-webkit-appearance:none;appearance:none;font:inherit;font-size:14.5px;font-weight:600;color:var(--dk);background-image:url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'8\' viewBox=\'0 0 12 8\' fill=\'none\'%3E%3Cpath d=\'M1 1l5 5 5-5\' stroke=\'%2378716C\' stroke-width=\'2\' stroke-linecap=\'round\'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 13px center;padding-right:34px;}
	.agh-acfilters select:focus{outline:none;border-color:var(--o);box-shadow:0 0 0 3px rgba(194,65,12,.12);}
	.agh-acfilters .agh-apply-field{flex:0 0 auto;}
	.agh-apply-btn{padding:0 22px;height:43px;border-radius:10px;font-size:14px;font-weight:700;white-space:nowrap;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;}
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
	.agh-scope a.agh-btn-primary,.agh-btn-primary{background:var(--o) !important;color:#fff !important;flex:1;}
	.agh-scope a.agh-btn-primary:hover,.agh-btn-primary:hover{background:var(--oh) !important;color:#fff !important;}
	.agh-scope a.agh-btn-ghost,.agh-btn-ghost{background:var(--wh) !important;color:var(--dk) !important;border-color:var(--bd);}
	.agh-scope a.agh-btn-ghost:hover,.agh-btn-ghost:hover{border-color:var(--dk) !important;color:var(--dk) !important;}
	.agh-btn{display:inline-block;padding:10px 15px;border-radius:9px;font:inherit;font-size:14px;font-weight:600;text-decoration:none !important;text-align:center;white-space:nowrap;cursor:pointer;border:1px solid transparent;transition:.15s;}
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
		.agh-search{max-width:none;flex:0 0 auto;} .agh-grid{grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;}
		.agh-name{font-size:17px;} .agh-body{padding:13px;}
		.agh-acfilters .agh-field{flex:1 1 47%;min-width:0;}
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
	$cats = agh_categories_data();
	$prods = agh_products_data();
	$subcats = function_exists( 'agh_subcategories_data' ) ? agh_subcategories_data() : array();
	$brands = array();
	foreach ( $prods as $p ) { if ( ! empty( $p['brand'] ) ) { $brands[ $p['brand'] ] = true; } }
	$brands = array_keys( $brands ); sort( $brands );
	ob_start();
	echo agh_styles();
	?>
	<div class="agh-scope" id="agh-catalog">
	<div class="agh-wrap">
		
		
		
		<div class="agh-tiles" role="list">
			<?php foreach ( $cats as $slug => $c ) : ?>
				<button class="agh-tile" role="listitem" data-filter="<?php echo esc_attr( $slug ); ?>"><b><?php echo esc_html( $c['label'] ); ?></b><span><?php echo esc_html( wp_trim_words( $c['blurb'], 12, '…' ) ); ?></span></button>
			<?php endforeach; ?>
		</div>
		<div class="agh-controls">
			<div class="agh-search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg><input type="search" id="agh-q" placeholder="Search the catalog…" aria-label="Search products"><button type="button" class="agh-search-go" id="agh-go">Search</button></div>
			<div class="agh-chips" id="agh-chips"><button class="agh-chip is-active" data-filter="all">All</button>
				<?php foreach ( $cats as $slug => $c ) : ?><button class="agh-chip" data-filter="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $c['label'] ); ?></button><?php endforeach; ?>
			</div>
			<select class="agh-select" id="agh-select" aria-label="Filter by category"><option value="all">All categories</option>
				<?php foreach ( $cats as $slug => $c ) : ?><option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $c['label'] ); ?></option><?php endforeach; ?>
			</select>
		</div>
		<?php foreach ( $subcats as $pcat => $subs ) : ?>
		<div class="agh-subchips" data-parent="<?php echo esc_attr( $pcat ); ?>" role="group" aria-label="<?php echo esc_attr( agh_cat_label( $pcat ) ); ?> subcategories" hidden>
			<button type="button" class="agh-subchip is-active" data-sub="all">All <?php echo esc_html( strtolower( agh_cat_label( $pcat ) ) ); ?></button>
			<?php foreach ( $subs as $sslug => $slabel ) : ?><button type="button" class="agh-subchip" data-sub="<?php echo esc_attr( $sslug ); ?>"><?php echo esc_html( $slabel ); ?></button><?php endforeach; ?>
		</div>
		<?php endforeach; ?>
		<div class="agh-acfilters">
			<div class="agh-field"><label for="agh-brand">Brand</label>
				<select id="agh-brand"><option value="all">All brands</option>
					<?php foreach ( $brands as $b ) : ?><option value="<?php echo esc_attr( $b ); ?>"><?php echo esc_html( $b ); ?></option><?php endforeach; ?>
				</select>
			</div>
			<div class="agh-field"><label for="agh-price">Price</label>
				<select id="agh-price">
					<option value="all">Any price</option>
					<option value="0-99">Under $100</option>
					<option value="100-500">$100 &ndash; $500</option>
					<option value="500-1000">$500 &ndash; $1,000</option>
					<option value="1000-99999999">$1,000 and up</option>
				</select>
			</div>
			<div class="agh-field"><label for="agh-sort">Sort by</label>
				<select id="agh-sort">
					<option value="featured">Featured</option>
					<option value="price-asc">Price: low to high</option>
					<option value="price-desc">Price: high to low</option>
					<option value="name-asc">Name: A to Z</option>
				</select>
			</div>
			<div class="agh-field agh-apply-field"><label aria-hidden="true">&nbsp;</label><button type="button" class="agh-btn agh-btn-primary agh-apply-btn" id="agh-apply">Apply filters</button></div>
		</div>
		<p class="agh-count" id="agh-count"></p>
		<div class="agh-grid" id="agh-grid">
			<?php $i = 0; foreach ( $prods as $p ) :
				$url = agh_product_url( $p['slug'] );
				$hay = strtolower( $p['title'] . ' ' . $p['brand'] . ' ' . agh_cat_label( $p['cat'] ) . ' ' . $p['tagline'] );
				?>
				<article class="agh-card" data-cat="<?php echo esc_attr( $p['cat'] ); ?>" data-brand="<?php echo esc_attr( $p['brand'] ); ?>" data-price="<?php echo esc_attr( agh_price_low( $p['slug'] ) ); ?>" data-name="<?php echo esc_attr( strtolower( $p['title'] ) ); ?>" data-subcat="<?php echo esc_attr( isset( $p['subcat'] ) ? $p['subcat'] : '' ); ?>" data-i="<?php echo $i; ?>" data-text="<?php echo esc_attr( $hay ); ?>">
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
			<?php $i++; endforeach; ?>
		</div>
		<div class="agh-empty" id="agh-empty">No products match that search. Try another term or <a href="<?php echo esc_url( agh_quote_url() ); ?>">ask us directly</a>.</div>
		<p class="agh-note">Products shown are representative of current inventory. Part numbers, fitment, approvals and price are confirmed against your aircraft&rsquo;s documentation before anything is quoted. Nothing on this page is an approval or certification claim.</p>
	</div>
	</div>
	<?php
	$GLOBALS['agh_catalog_needs_js'] = true;
	return ob_get_clean();
}
add_shortcode( 'agh_catalog', 'agh_catalog_shortcode' );

/**
 * Output the catalog's interactive JS from wp_footer (direct echo) instead of
 * returning it inside the shortcode string. Returning it as shortcode content
 * runs it through the_content/wptexturize, which HTML-encodes the "&&" logical
 * operators ("&&" -> "&#038;&#038;") and breaks the whole script with a syntax
 * error. Echoing from wp_footer bypasses those content filters.
 */
add_action( 'wp_footer', 'agh_catalog_footer_script', 98 );
function agh_catalog_footer_script() {
	if ( empty( $GLOBALS['agh_catalog_needs_js'] ) ) { return; }
	?>
	<script id="agh-catalog-js" data-no-optimize="1" data-no-defer="1" data-no-minify="1">
	(function(){
		function init(){
		var grid=document.getElementById('agh-grid');if(!grid)return;
		var cards=[].slice.call(grid.querySelectorAll('.agh-card'));
		var chips=[].slice.call(document.querySelectorAll('#agh-chips .agh-chip'));
		var sel=document.getElementById('agh-select');
		var q=document.getElementById('agh-q');
		var brand=document.getElementById('agh-brand');
		var price=document.getElementById('agh-price');
		var sort=document.getElementById('agh-sort');
		var count=document.getElementById('agh-count');
		var empty=document.getElementById('agh-empty');
		var tiles=[].slice.call(document.querySelectorAll('.agh-tile'));
		var cur='all';var curSub='all';
		var subwraps=[].slice.call(document.querySelectorAll('.agh-subchips'));
		function priceBand(){var v=price?price.value:'all';if(v==='all')return null;var p=v.split('-');return[parseInt(p[0],10),parseInt(p[1],10)];}
		function apply(){
			var term=(q.value||'').trim().toLowerCase();
			var band=priceBand();
			var br=brand?brand.value:'all';
			var n=0;var visible=[];
			cards.forEach(function(c){
				var okCat=cur==='all'||c.getAttribute('data-cat')===cur;
				var okTxt=!term||c.getAttribute('data-text').indexOf(term)>-1;
				var okBr=br==='all'||c.getAttribute('data-brand')===br;
				var praw=c.getAttribute('data-price');var pr=praw===''?null:parseInt(praw,10);
				var okPr=!band||(pr!==null&&pr>=band[0]&&pr<=band[1]);
				var okSub=curSub==='all'||c.getAttribute('data-subcat')===curSub;
				var show=okCat&&okTxt&&okBr&&okPr&&okSub;
				c.style.display=show?'':'none';
				if(show){n++;visible.push(c);}
			});
			var s=sort?sort.value:'featured';
			if(s!=='featured'){
				visible.sort(function(a,b){
					var pa=a.getAttribute('data-price'),pb=b.getAttribute('data-price');
					pa=pa===''?null:parseInt(pa,10);pb=pb===''?null:parseInt(pb,10);
					if(s==='price-asc'){if(pa===null)return 1;if(pb===null)return -1;return pa-pb;}
					if(s==='price-desc'){if(pa===null)return 1;if(pb===null)return -1;return pb-pa;}
					if(s==='name-asc')return a.dataset.name<b.dataset.name?-1:(a.dataset.name>b.dataset.name?1:0);
					return 0;
				});
			}else{
				visible.sort(function(a,b){return (+a.dataset.i)-(+b.dataset.i);});
			}
			visible.forEach(function(c){grid.appendChild(c);});
			count.textContent='Showing '+n+' of '+cards.length+' products'+(cur!=='all'?' · '+chipLabel(cur):'');
			empty.style.display=n?'none':'block';
		}
		function chipLabel(f){var el=document.querySelector('#agh-chips .agh-chip[data-filter="'+f+'"]');return el?el.textContent:'';}
		function activeSubwrap(){var w=null;subwraps.forEach(function(s){if(s.getAttribute('data-parent')===cur){w=s;s.hidden=false;}else{s.hidden=true;}});return w;}
		function setCat(f){cur=f;curSub='all';chips.forEach(function(ch){ch.classList.toggle('is-active',ch.getAttribute('data-filter')===f);});if(sel)sel.value=f;var w=activeSubwrap();if(w){[].slice.call(w.querySelectorAll('.agh-subchip')).forEach(function(sc){sc.classList.toggle('is-active',sc.getAttribute('data-sub')==='all');});}apply();}
		function setSub(s){curSub=s;var w=activeSubwrap();if(w){[].slice.call(w.querySelectorAll('.agh-subchip')).forEach(function(sc){sc.classList.toggle('is-active',sc.getAttribute('data-sub')===s);});}apply();}
		chips.forEach(function(ch){ch.addEventListener('click',function(){setCat(ch.getAttribute('data-filter'));});});
		tiles.forEach(function(t){t.addEventListener('click',function(){setCat(t.getAttribute('data-filter'));document.getElementById('agh-catalog').scrollIntoView({behavior:'smooth',block:'start'});});});
		if(sel)sel.addEventListener('change',function(){setCat(sel.value);});
		var go=document.getElementById('agh-go');
		var applyBtn=document.getElementById('agh-apply');
		function commit(){apply();var t=document.getElementById('agh-catalog');if(t)t.scrollIntoView({behavior:'smooth',block:'start'});}
		if(go)go.addEventListener('click',commit);
		if(applyBtn)applyBtn.addEventListener('click',commit);
		if(q)q.addEventListener('keydown',function(e){if(e.key==='Enter'){e.preventDefault();commit();}});
		subwraps.forEach(function(w){[].slice.call(w.querySelectorAll('.agh-subchip')).forEach(function(sc){sc.addEventListener('click',function(){setSub(sc.getAttribute('data-sub'));var t=document.getElementById('agh-catalog');if(t)t.scrollIntoView({behavior:'smooth',block:'start'});});});});
		(function(){var qs=new URLSearchParams(window.location.search||'');var c=qs.get('agh_cat');var s=qs.get('agh_sub');if(!c&&window.location.hash.indexOf('=')>-1){var hp=new URLSearchParams(window.location.hash.substring(1));c=hp.get('cat');s=hp.get('sub');}if(c){var ok=chips.some(function(ch){return ch.getAttribute('data-filter')===c;});if(ok){setCat(c);if(s)setSub(s);var t=document.getElementById('agh-catalog');if(t)setTimeout(function(){t.scrollIntoView({behavior:'smooth',block:'start'});},80);return;}}apply();})();
		}
		if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',init);}else{init();}
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
		<nav class="agh-crumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> / <a href="<?php echo esc_url( home_url( '/parts-gear/' ) ); ?>">Parts &amp; gear</a> / <a href="<?php echo esc_url( home_url( '/parts-gear/#agh-catalog' ) ); ?>"><?php echo esc_html( agh_cat_label( $p['cat'] ) ); ?></a> / <span><?php echo esc_html( $p['title'] ); ?></span></nav>
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
