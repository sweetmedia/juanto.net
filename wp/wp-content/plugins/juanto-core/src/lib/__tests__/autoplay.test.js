/* eslint-disable jsdoc/check-tag-names -- the jest docblock pragma is not a JSDoc tag. */
/**
 * @jest-environment jsdom
 */
import { armAutoplay } from '../autoplay';

function fakeObserver() {
	const observer = { targets: [], callback: null, options: null };
	observer.Ctor = function ( callback, options ) {
		observer.callback = callback;
		observer.options = options;
		this.observe = ( el ) => observer.targets.push( el );
	};
	observer.fire = ( target, isIntersecting ) =>
		observer.callback( [ { target, isIntersecting } ] );
	return observer;
}

function setup() {
	document.body.innerHTML =
		'<video data-juanto-autoplay controls preload="none" muted loop playsinline src="clip.mp4"></video>';
	const video = document.querySelector( 'video' );
	video.play = jest.fn( () => Promise.resolve() );
	video.pause = jest.fn();
	return video;
}

test( 'plays only once the video nears the viewport, and pauses when it leaves', () => {
	const video = setup();
	const io = fakeObserver();
	expect(
		armAutoplay( document, { Observer: io.Ctor, reducedMotion: false } )
	).toBe( 1 );
	expect( video.controls ).toBe( false );
	expect( video.play ).not.toHaveBeenCalled();
	expect( io.targets ).toEqual( [ video ] );
	expect( io.options.rootMargin ).toBe( '200px 0px' );

	io.fire( video, true );
	expect( video.preload ).toBe( 'auto' );
	expect( video.play ).toHaveBeenCalledTimes( 1 );

	io.fire( video, false );
	expect( video.pause ).toHaveBeenCalledTimes( 1 );
} );

test( 'reduced motion keeps the controls and never autoplays', () => {
	const video = setup();
	const io = fakeObserver();
	expect(
		armAutoplay( document, { Observer: io.Ctor, reducedMotion: true } )
	).toBe( 0 );
	expect( video.controls ).toBe( true );
	expect( io.targets ).toEqual( [] );
	expect( video.play ).not.toHaveBeenCalled();
} );

test( 'a blocked play() brings the controls back', async () => {
	const video = setup();
	video.play = jest.fn( () => Promise.reject( new Error( 'NotAllowed' ) ) );
	const io = fakeObserver();
	armAutoplay( document, { Observer: io.Ctor, reducedMotion: false } );
	io.fire( video, true );
	await Promise.resolve();
	await Promise.resolve();
	expect( video.controls ).toBe( true );
} );

test( 'without IntersectionObserver the video keeps its controls', () => {
	const video = setup();
	expect(
		armAutoplay( document, { Observer: undefined, reducedMotion: false } )
	).toBe( 0 );
	expect( video.controls ).toBe( true );
} );
