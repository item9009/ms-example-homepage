<?php
/**
 * MS Store functions and definitions.
 *
 * @package ms-store
 */

if ( ! defined( 'MS_STORE_FONT_URL' ) ) {
	define( 'MS_STORE_FONT_URL', 'https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css' );
}

/*
 * Kakao Developers > 내 애플리케이션 > 앱 키의 JavaScript 키.
 * wp-config.php에서 미리 정의하면 그 값이 우선 적용됩니다.
 */
if ( ! defined( 'MS_STORE_KAKAO_MAP_APP_KEY' ) ) {
	define( 'MS_STORE_KAKAO_MAP_APP_KEY', '' );
}

/**
 * Loads the Kakao Maps API and renders every `.ms-map` element on the front page.
 */
function ms_store_kakao_map_script() {
	if ( ! is_front_page() ) {
		return;
	}

	if ( '' === MS_STORE_KAKAO_MAP_APP_KEY ) {
		wp_register_script( 'ms-store-kakao-map', false, array(), null, true );
		wp_enqueue_script( 'ms-store-kakao-map' );
		wp_add_inline_script(
			'ms-store-kakao-map',
			"document.querySelectorAll( '.ms-map__fallback' ).forEach( function ( el ) { el.textContent = '카카오맵 JavaScript 키를 설정하면 지도가 표시됩니다.'; } );"
		);
		return;
	}

	wp_enqueue_script(
		'ms-store-kakao-map',
		'https://dapi.kakao.com/v2/maps/sdk.js?autoload=false&appkey=' . rawurlencode( MS_STORE_KAKAO_MAP_APP_KEY ),
		array(),
		null,
		true
	);

	wp_add_inline_script(
		'ms-store-kakao-map',
		<<<'JS'
( function () {
	var maps = document.querySelectorAll( '.ms-map' );

	if ( ! window.kakao || ! kakao.maps ) {
		maps.forEach( function ( el ) {
			el.innerHTML = '<p class="ms-map__fallback">카카오맵을 불러오지 못했습니다. JavaScript 키와 사이트 도메인 등록을 확인해 주세요.</p>';
		} );
		return;
	}

	kakao.maps.load( function () {
		maps.forEach( function ( el ) {
			var position = new kakao.maps.LatLng( parseFloat( el.dataset.lat ), parseFloat( el.dataset.lng ) );
			el.innerHTML = '';
			el.classList.add( 'is-loaded' );

			var map = new kakao.maps.Map( el, {
				center: position,
				level: parseInt( el.dataset.level, 10 ) || 3
			} );
			map.setZoomable( false );
			map.addControl( new kakao.maps.ZoomControl(), kakao.maps.ControlPosition.RIGHT );

			var marker = new kakao.maps.Marker( { position: position, map: map } );

			if ( el.dataset.title ) {
				var info = new kakao.maps.InfoWindow( {
					content: '<div style="padding:8px 12px;font-weight:700;white-space:nowrap;">' + el.dataset.title + '</div>'
				} );
				var isOpen = false;
				kakao.maps.event.addListener( marker, 'click', function () {
					isOpen ? info.close() : info.open( map, marker );
					isOpen = ! isOpen;
				} );
			}
		} );
	} );
} )();
JS
	);
}
add_action( 'wp_enqueue_scripts', 'ms_store_kakao_map_script' );

/**
 * Loads the font and the theme stylesheet on the front end.
 */
function ms_store_enqueue_styles() {
	wp_enqueue_style( 'ms-store-font', MS_STORE_FONT_URL, array(), '1.3.9' );
	wp_enqueue_style( 'ms-store-style', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'ms_store_enqueue_styles' );

/**
 * Loads the same styles in the block editor.
 */
function ms_store_editor_style() {
	add_editor_style( array( MS_STORE_FONT_URL, 'style.css' ) );
}
add_action( 'after_setup_theme', 'ms_store_editor_style' );

/**
 * Fades in elements with the `ms-reveal` class as they scroll into view.
 */
function ms_store_reveal_script() {
	?>
	<script>
	( function () {
		if ( ! ( 'IntersectionObserver' in window ) ) {
			return;
		}
		document.documentElement.classList.add( 'ms-js' );
		var observer = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-visible' );
					observer.unobserve( entry.target );
				}
			} );
		}, { threshold: 0.15 } );
		document.addEventListener( 'DOMContentLoaded', function () {
			document.querySelectorAll( '.ms-reveal' ).forEach( function ( el ) {
				observer.observe( el );
			} );
		} );
	} )();
	</script>
	<?php
}
add_action( 'wp_head', 'ms_store_reveal_script' );
