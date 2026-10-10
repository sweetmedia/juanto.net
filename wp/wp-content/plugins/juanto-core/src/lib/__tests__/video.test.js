/* eslint-disable jsdoc/check-tag-names -- the jest docblock pragma is not a JSDoc tag. */
/**
 * @jest-environment jsdom
 */
import { mountVideo } from '../video';

test( 'clicking the facade swaps in a privacy-enhanced iframe', () => {
	document.body.innerHTML =
		'<div class="juanto-video" data-video="MLpWrANjFbI" data-title="Elementor downloads"><button type="button" class="juanto-video__play">Play</button></div>';
	const wrap = document.querySelector( '.juanto-video' );
	expect( mountVideo( wrap ) ).toBe( true );
	wrap.querySelector( 'button' ).click();
	const iframe = wrap.querySelector( 'iframe' );
	expect( iframe.src ).toBe(
		'https://www.youtube-nocookie.com/embed/MLpWrANjFbI?autoplay=1&rel=0'
	);
	expect( iframe.title ).toBe( 'Elementor downloads' );
	expect( wrap.querySelector( 'button' ) ).toBeNull();
	expect( wrap.classList.contains( 'is-playing' ) ).toBe( true );
} );

test( 'a bad ID mounts nothing', () => {
	document.body.innerHTML =
		'<div class="juanto-video" data-video="x&quot;onload"><button type="button">Play</button></div>';
	expect( mountVideo( document.querySelector( '.juanto-video' ) ) ).toBe(
		false
	);
} );
