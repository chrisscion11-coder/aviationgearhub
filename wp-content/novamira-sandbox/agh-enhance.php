<?php
/**
 * Aviation Gear Hub — motion & polish enhancement layer
 * ----------------------------------------------------------------------------
 * Adds subtle, professional scroll-reveal motion sitewide and a few responsive
 * polish rules. Loaded from the Novamira sandbox (frontend only).
 *
 * Design contract:
 *  - Progressive enhancement: content is fully visible if JS never runs; the
 *    hidden start-state is applied only after JS marks <html class="agh-motion">.
 *  - Respects prefers-reduced-motion (CSS + JS both bail out).
 *  - Only animates opacity + transform (never layout properties).
 *  - Reveal classes are transient: removed after they finish so the site's
 *    existing card/tile/button hover states keep working untouched.
 *  - Never runs inside the Elementor editor or its preview.
 *  - Failsafe: a timeout force-reveals everything so content can never stay
 *    hidden if the IntersectionObserver never fires.
 *
 * To disable: rename to agh-enhance.php.disabled (novamira/disable-file).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function agh_enhance_is_editor() {
	if ( is_admin() ) { return true; }
	if ( isset( $_GET['elementor-preview'] ) && $_GET['elementor-preview'] !== '' ) { return true; }
	if ( class_exists( '\\Elementor\\Plugin' ) ) {
		$p = \Elementor\Plugin::$instance;
		if ( isset( $p->preview ) && method_exists( $p->preview, 'is_preview_mode' ) && $p->preview->is_preview_mode() ) { return true; }
		if ( isset( $p->editor ) && method_exists( $p->editor, 'is_edit_mode' ) && $p->editor->is_edit_mode() ) { return true; }
	}
	return false;
}

add_action( 'wp_head', function () {
	if ( agh_enhance_is_editor() ) { return; }
	?>
<style id="agh-enhance-css">
/* --- Scroll reveal: start-state only when JS has confirmed motion is allowed --- */
html.agh-motion [data-agh-reveal]{
	opacity:0;transform:translateY(16px);
	transition:opacity .6s cubic-bezier(.16,1,.3,1),transform .6s cubic-bezier(.16,1,.3,1);
	will-change:opacity,transform;
}
html.agh-motion [data-agh-reveal].agh-in{opacity:1;transform:none;}
/* Staggered children (product grids, category tiles, related items) */
html.agh-motion [data-agh-reveal-stagger]>*{
	opacity:0;transform:translateY(18px);
	transition:opacity .55s cubic-bezier(.16,1,.3,1),transform .55s cubic-bezier(.16,1,.3,1);
	will-change:opacity,transform;
}
html.agh-motion [data-agh-reveal-stagger].agh-in>*{opacity:1;transform:none;}
/* Gentler travel on small screens so nothing feels heavy or janky */
@media (max-width:767px){
	html.agh-motion [data-agh-reveal]{transform:translateY(11px);}
	html.agh-motion [data-agh-reveal-stagger]>*{transform:translateY(12px);}
}
/* Motion off: honour the visitor's system setting, no exceptions */
@media (prefers-reduced-motion:reduce){
	html.agh-motion [data-agh-reveal],
	html.agh-motion [data-agh-reveal].agh-in,
	html.agh-motion [data-agh-reveal-stagger]>*,
	html.agh-motion [data-agh-reveal-stagger].agh-in>*{
		opacity:1!important;transform:none!important;transition:none!important;
	}
}

/* --- Logo polish: keep the new horizontal lockup crisp and centred --- */
.elementor-widget-image img[src*="agh-logo-full"],
.elementor-widget-image img[src*="agh-logo-white"]{
	image-rendering:auto;height:auto;vertical-align:middle;
}
</style>
	<?php
}, 100 );

add_action( 'wp_footer', function () {
	if ( agh_enhance_is_editor() ) { return; }
	?>
<script id="agh-enhance-js">
(function(){
	"use strict";
	var doc = document.documentElement;
	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	// Mark motion allowed so the hidden start-state in CSS takes effect.
	doc.classList.add('agh-motion');
	// No IO or reduced motion: CSS keeps everything visible; nothing else to do.
	if (reduce || !('IntersectionObserver' in window)) { return; }

	var STAGGER_SEL = ['.agh-grid', '.agh-tiles', '.agh-related'];
	var STEP = 55, CAP = 8; // per-item delay (ms) and how many items still stagger

	function prepare(){
		var excluded = [];
		// 1) Grids/tiles: stagger their children, and exclude their section from a whole-section reveal.
		STAGGER_SEL.forEach(function(sel){
			document.querySelectorAll(sel).forEach(function(g){
				if (g.hasAttribute('data-agh-reveal-stagger')) { return; }
				g.setAttribute('data-agh-reveal-stagger','');
				var kids = g.children;
				for (var i = 0; i < kids.length; i++){
					kids[i].style.transitionDelay = (Math.min(i, CAP) * STEP) + 'ms';
				}
				var sec = g.closest('.e-con.e-parent');
				if (sec) { excluded.push(sec); }
				observe(g);
			});
		});
		// 2) Top-level Elementor sections that don't already contain a staggered grid.
		document.querySelectorAll('.e-con.e-parent').forEach(function(s){
			if (excluded.indexOf(s) !== -1) { return; }
			if (s.hasAttribute('data-agh-reveal') || s.hasAttribute('data-agh-reveal-stagger')) { return; }
			s.setAttribute('data-agh-reveal','');
			observe(s);
		});
	}

	var io = new IntersectionObserver(function(entries){
		entries.forEach(function(e){
			if (!e.isIntersecting) { return; }
			var el = e.target;
			io.unobserve(el);
			el.classList.add('agh-in');
			var cleaned = false;
			function cleanup(){
				if (cleaned) { return; }
				cleaned = true;
				el.classList.remove('agh-in');
				el.removeAttribute('data-agh-reveal');
				el.removeAttribute('data-agh-reveal-stagger');
				el.style.willChange = '';
				for (var i = 0; i < el.children.length; i++){ el.children[i].style.transitionDelay = ''; el.children[i].style.willChange = ''; }
				el.removeEventListener('transitionend', onEnd);
			}
			function onEnd(ev){ if (ev.target === el || ev.target.parentNode === el) { cleanup(); } }
			el.addEventListener('transitionend', onEnd);
			setTimeout(cleanup, 1400);
		});
	}, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

	function observe(el){ io.observe(el); }

	if (document.readyState === 'loading'){
		document.addEventListener('DOMContentLoaded', prepare);
	} else {
		prepare();
	}
	// Failsafe: never leave content invisible if the observer never fires
	// (e.g. on long pages, in some browsers, or if a later script errors).
	function aghRevealAll(){
		try{
			var els=document.querySelectorAll('[data-agh-reveal],[data-agh-reveal-stagger]');
			for(var i=0;i<els.length;i++){ try{io.unobserve(els[i]);}catch(e){} els[i].classList.add('agh-in'); }
		}catch(e){}
	}
	setTimeout(aghRevealAll, 1600);
	window.addEventListener('load', function(){ setTimeout(aghRevealAll, 500); });
})();
</script>
	<?php
}, 100 );
