<?php
/**
 * Aviation Gear Hub — real aircraft catalog built from the uploaded Cessna photos.
 * Shortcode: [agh_aircraft]
 *
 * Listings are generated from the media library: any attachment whose filename
 * follows the "<year>-cessna-<model>-piston-single|piston-twin|light-sport-aircraft_<id>.jpg"
 * pattern becomes a listing. Duplicate re-uploads (same photo id token) are merged.
 * Reuses the .agh-* styles registered by agh-catalog.php (agh_styles()).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Pretty-print a model slug such as "172l-skyhawk" -> "172L Skyhawk". */
function agh_ac_pretty_model( $mid ) {
	$parts = explode( '-', $mid );
	$out = array();
	foreach ( $parts as $t ) {
		if ( $t === '' ) { continue; }
		if ( preg_match( '/[0-9]/', $t ) ) { $out[] = strtoupper( $t ); }
		elseif ( strlen( $t ) <= 2 ) { $out[] = strtoupper( $t ); }
		else { $out[] = ucfirst( $t ); }
	}
	return implode( ' ', $out );
}

/** Map a model slug to a family key/label/blurb. */
function agh_ac_family( $mid ) {
	$num = 0;
	if ( preg_match( '/([0-9]{2,3})/', $mid, $m ) ) { $num = (int) $m[1]; }
	$map = array(
		150 => array( '150-152', '150 / 152', 'Two-seat trainers that keep hourly costs low for schools and time-builders.' ),
		152 => array( '150-152', '150 / 152', 'Two-seat trainers that keep hourly costs low for schools and time-builders.' ),
		162 => array( '162', '162 Skycatcher', 'Light-sport two-seater for economical recreational and training flying.' ),
		165 => array( '165', 'C-165 Airmaster', 'Pre-war Cessna cabin single prized by collectors and enthusiasts.' ),
		170 => array( '170', '170', 'Tailwheel four-seat classic, a favourite for vintage and backcountry owners.' ),
		172 => array( '172', '172 Skyhawk', 'The default trainer of general aviation and an easy first aircraft to own.' ),
		177 => array( '177', '177 Cardinal', 'Wide-cabin single with a stabilator and excellent all-round visibility.' ),
		182 => array( '182', '182 Skylane', 'More load and cross-country capability than a trainer; a common step up.' ),
		185 => array( '185', '185 Skywagon', 'Powerful tailwheel utility single built for hauling into rough strips.' ),
		195 => array( '195', '195 Businessliner', 'Radial-engine post-war classic prized by vintage collectors.' ),
		205 => array( '205', '205', 'Fixed-gear six-seat utility single, forerunner of the 206 Stationair.' ),
		210 => array( '210', '210 Centurion', 'Retractable-gear performance for owners who cover distance.' ),
		310 => array( '310', '310', 'Classic light twin for owners who want two engines and more capability.' ),
		335 => array( '335', '335', 'Cabin-class piston twin, the non-pressurised sister of the 340.' ),
		337 => array( '337', '337 Skymaster', 'Centre-line push-pull twin with distinctive handling and safety appeal.' ),
		340 => array( '340', '340', 'Pressurised cabin-class twin for comfortable higher-altitude cross-country.' ),
		401 => array( '401-402', '401 / 402', 'Cabin-class piston twins built for light commuter and business roles.' ),
		402 => array( '401-402', '401 / 402', 'Cabin-class piston twins built for light commuter and business roles.' ),
		421 => array( '421', '421 Golden Eagle', 'Pressurised cabin-class twin at the top of Cessna\'s piston line.' ),
	);
	if ( isset( $map[ $num ] ) ) {
		return array( 'key' => $map[ $num ][0], 'label' => $map[ $num ][1], 'blurb' => $map[ $num ][2], 'num' => $num );
	}
	return array( 'key' => (string) $num, 'label' => $num ? (string) $num : 'Cessna', 'blurb' => 'Cessna piston aircraft — configuration confirmed against the aircraft documents.', 'num' => $num );
}

/** Build the aircraft listings from the media library (cached per request). */
function agh_aircraft_data() {
	static $cache = null;
	if ( $cache !== null ) { return $cache; }
	global $wpdb;
	$rows = $wpdb->get_results( "SELECT ID, guid FROM {$wpdb->posts} WHERE post_type='attachment' AND guid REGEXP '/[0-9]{4}-cessna-' ORDER BY ID ASC" );
	$seen = array();
	$list = array();
	foreach ( $rows as $r ) {
		$file = basename( $r->guid );
		$token = preg_match( '/_([0-9]{5,})/', $file, $tm ) ? $tm[1] : $file;
		if ( isset( $seen[ $token ] ) ) { continue; }
		$seen[ $token ] = true;
		$name = preg_replace( '/\.(jpg|jpeg|png|webp)$/i', '', $file );
		$name = preg_replace( '/-[0-9]+$/', '', $name );      // strip trailing -N re-upload suffix
		$core = preg_replace( '/_[0-9].*$/', '', $name );      // strip the _<id> token
		if ( ! preg_match( '/^([0-9]{4})-cessna-(.+)-aircraft$/', $core, $m ) ) { continue; }
		$year = (int) $m[1];
		$mid  = $m[2];
		if ( strpos( $mid, 'piston-twin' ) !== false )   { $type = 'twin';   $type_label = 'Twin-engine piston';   $mid = str_replace( '-piston-twin', '', $mid ); }
		elseif ( strpos( $mid, 'piston-single' ) !== false ) { $type = 'single'; $type_label = 'Single-engine piston'; $mid = str_replace( '-piston-single', '', $mid ); }
		elseif ( strpos( $mid, 'light-sport' ) !== false )   { $type = 'sport';  $type_label = 'Light sport';          $mid = str_replace( '-light-sport', '', $mid ); }
		else { $type = 'single'; $type_label = 'Single-engine piston'; }
		$model  = agh_ac_pretty_model( $mid );
		$fam    = agh_ac_family( $mid );
		$list[] = array(
			'id'          => (int) $r->ID,
			'year'        => $year,
			'type'        => $type,
			'type_label'  => $type_label,
			'model'       => $model,
			'family'      => $fam['key'],
			'family_num'  => $fam['num'],
			'family_label'=> $fam['label'],
			'title'       => $year . ' Cessna ' . $model,
			'tagline'     => $fam['blurb'],
		);
	}
	usort( $list, function ( $a, $b ) {
		if ( $a['year'] !== $b['year'] ) { return $b['year'] - $a['year']; }
		return strcmp( $a['model'], $b['model'] );
	} );
	$cache = $list;
	return $list;
}

/** Distinct model families present, ordered by model number. */
function agh_aircraft_families() {
	$fams = array();
	foreach ( agh_aircraft_data() as $a ) {
		if ( ! isset( $fams[ $a['family'] ] ) ) {
			$fams[ $a['family'] ] = array( 'label' => $a['family_label'], 'num' => $a['family_num'] );
		}
	}
	uasort( $fams, function ( $a, $b ) { return $a['num'] - $b['num']; } );
	return $fams;
}

function agh_aircraft_shortcode() {
	$GLOBALS['agh_needs_aircraft_js'] = true;
	$items = agh_aircraft_data();
	$fams  = agh_aircraft_families();
	$types = array( 'single' => 'Single-engine', 'twin' => 'Twin-engine', 'sport' => 'Light sport' );
	$contact = home_url( '/contact/' );
	ob_start();
	if ( function_exists( 'agh_styles' ) ) { echo agh_styles(); }
	?>
	<style id="agh-aircraft-css">
	.agh-scope .agh-thumb-cover{aspect-ratio:4/3;padding:0;}
	.agh-scope .agh-thumb-cover img{width:100%;height:100%;max-width:none;max-height:none;object-fit:cover;}
	.agh-ac-meta{font-size:12.5px;color:var(--mut);margin:-2px 0 10px;font-weight:600;}
	</style>
	<div class="agh-scope" id="agh-aircraft">
	<div class="agh-wrap">

		<div class="agh-controls">
			<form class="agh-searchbar" id="agh-ac-searchbar" role="search">
				<div class="agh-search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg><input type="search" id="agh-ac-q" placeholder="Search aircraft (year, model…)" aria-label="Search aircraft"></div>
				<button type="submit" class="agh-search-btn">Search</button>
			</form>
			<div class="agh-chips" id="agh-ac-chips"><button type="button" class="agh-chip is-active" data-filter="all">All</button>
				<?php foreach ( $types as $k => $label ) : ?><button type="button" class="agh-chip" data-filter="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></button><?php endforeach; ?>
			</div>
			<select class="agh-select" id="agh-ac-typesel" aria-label="Filter by engine type"><option value="all">All aircraft</option>
				<?php foreach ( $types as $k => $label ) : ?><option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></option><?php endforeach; ?>
			</select>
		</div>

		<div class="agh-filters">
			<div class="agh-field"><label for="agh-ac-model">Model</label>
				<select id="agh-ac-model"><option value="all">All models</option>
					<?php foreach ( $fams as $key => $f ) : ?><option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $f['label'] ); ?></option><?php endforeach; ?>
				</select>
			</div>
			<div class="agh-field"><label for="agh-ac-sort">Sort by</label>
				<select id="agh-ac-sort">
					<option value="year-desc">Year: newest first</option>
					<option value="year-asc">Year: oldest first</option>
					<option value="model-asc">Model: A to Z</option>
				</select>
			</div>
			<button type="button" class="agh-apply" id="agh-ac-apply">Apply filters</button>
		</div>

		<p class="agh-count" id="agh-ac-count"></p>
		<div class="agh-grid" id="agh-ac-grid">
			<?php foreach ( $items as $a ) :
				$hay = strtolower( $a['title'] . ' ' . $a['family_label'] . ' ' . $a['type_label'] . ' cessna' );
				?>
				<article class="agh-card" data-type="<?php echo esc_attr( $a['type'] ); ?>" data-family="<?php echo esc_attr( $a['family'] ); ?>" data-year="<?php echo esc_attr( $a['year'] ); ?>" data-model="<?php echo esc_attr( strtolower( $a['model'] ) ); ?>" data-text="<?php echo esc_attr( $hay ); ?>">
					<div class="agh-thumb agh-thumb-cover"><?php echo agh_img( $a['id'], 'medium_large', $a['title'] ); ?></div>
					<div class="agh-body">
						<div class="agh-cat"><?php echo esc_html( $a['type_label'] ); ?></div>
						<h3 class="agh-name"><?php echo esc_html( $a['title'] ); ?></h3>
						<div class="agh-ac-meta">Cessna · <?php echo esc_html( $a['family_label'] ); ?></div>
						<p class="agh-tag"><?php echo esc_html( $a['tagline'] ); ?></p>
						<div class="agh-price">Price on request</div>
						<div class="agh-actions"><a class="agh-btn agh-btn-primary" href="<?php echo esc_url( $contact ); ?>">Request information</a></div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		<div class="agh-empty" id="agh-ac-empty">No aircraft match your filters. <a href="<?php echo esc_url( $contact ); ?>">Send us your requirements</a> and we will work the search.</div>
		<p class="agh-note">Listings are drawn from current and recently available Cessna aircraft. Year, hours, equipment, condition, location and price are confirmed against the individual aircraft&rsquo;s documents when you request information. Nothing on this page is an offer, appraisal or airworthiness claim.</p>
	</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'agh_aircraft', 'agh_aircraft_shortcode' );

add_action( 'wp_footer', 'agh_aircraft_footer_js', 98 );
function agh_aircraft_footer_js() {
	if ( empty( $GLOBALS['agh_needs_aircraft_js'] ) ) { return; }
	?>
	<script id="agh-aircraft-js">
	(function(){
		var grid=document.getElementById('agh-ac-grid');if(!grid)return;
		var cards=[].slice.call(grid.querySelectorAll('.agh-card'));
		var chips=[].slice.call(document.querySelectorAll('#agh-ac-chips .agh-chip'));
		var typesel=document.getElementById('agh-ac-typesel');
		var q=document.getElementById('agh-ac-q');
		var form=document.getElementById('agh-ac-searchbar');
		var model=document.getElementById('agh-ac-model');
		var sort=document.getElementById('agh-ac-sort');
		var applyBtn=document.getElementById('agh-ac-apply');
		var count=document.getElementById('agh-ac-count');
		var empty=document.getElementById('agh-ac-empty');
		var cur='all';
		function chipLabel(f){var el=document.querySelector('#agh-ac-chips .agh-chip[data-filter="'+f+'"]');return el?el.textContent:'';}
		function sortCards(){
			if(!sort)return;var mode=sort.value;
			var sorted=cards.slice().sort(function(a,b){
				if(mode==='model-asc'){return (a.getAttribute('data-model')||'').localeCompare(b.getAttribute('data-model')||'');}
				var ya=parseInt(a.getAttribute('data-year'),10)||0,yb=parseInt(b.getAttribute('data-year'),10)||0;
				return mode==='year-asc'?(ya-yb):(yb-ya);
			});
			sorted.forEach(function(c){grid.appendChild(c);});
		}
		function apply(){
			var term=(q&&q.value||'').trim().toLowerCase();
			var md=model?model.value:'all';var n=0;
			sortCards();
			cards.forEach(function(c){
				var okType=cur==='all'||c.getAttribute('data-type')===cur;
				var okTxt=!term||c.getAttribute('data-text').indexOf(term)>-1;
				var okModel=md==='all'||c.getAttribute('data-family')===md;
				var show=okType&&okTxt&&okModel;
				c.style.display=show?'':'none';if(show)n++;
			});
			count.textContent='Showing '+n+' of '+cards.length+' aircraft'+(cur!=='all'?' · '+chipLabel(cur):'');
			empty.style.display=n?'none':'block';
		}
		function setType(f){cur=f;chips.forEach(function(ch){ch.classList.toggle('is-active',ch.getAttribute('data-filter')===f);});if(typesel)typesel.value=f;apply();}
		chips.forEach(function(ch){ch.addEventListener('click',function(){setType(ch.getAttribute('data-filter'));});});
		if(typesel)typesel.addEventListener('change',function(){setType(typesel.value);});
		if(q)q.addEventListener('input',apply);
		if(form)form.addEventListener('submit',function(e){e.preventDefault();apply();});
		if(model)model.addEventListener('change',apply);
		if(sort)sort.addEventListener('change',apply);
		if(applyBtn)applyBtn.addEventListener('click',apply);
		try{
			var params=new URLSearchParams(window.location.search);
			var pType=params.get('type');
			if(pType){pType=pType.toLowerCase();if(document.querySelector('#agh-ac-chips .agh-chip[data-filter="'+pType+'"]')){cur=pType;chips.forEach(function(ch){ch.classList.toggle('is-active',ch.getAttribute('data-filter')===pType);});if(typesel)typesel.value=pType;}}
			var pFam=params.get('family');if(pFam&&model){for(var i=0;i<model.options.length;i++){if(model.options[i].value===pFam){model.value=pFam;break;}}}
			var pQ=params.get('q');if(pQ&&q)q.value=pQ;
			if(pType||pFam||pQ){setTimeout(function(){document.getElementById('agh-aircraft').scrollIntoView({behavior:'smooth',block:'start'});},250);}
		}catch(e){}
		apply();
	})();
	</script>
	<?php
}
