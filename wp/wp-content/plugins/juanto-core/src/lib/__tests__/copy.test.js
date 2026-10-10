/* eslint-disable jsdoc/check-tag-names -- the jest docblock pragma is not a JSDoc tag. */
/**
 * @jest-environment jsdom
 */
import { enhanceCodeBlocks } from '../copy';

const markup =
	'<div class="juanto-code"><pre class="wp-block-code"><code>download|true</code></pre></div>';

beforeEach( () => {
	document.body.innerHTML = markup;
} );

test( 'no Clipboard API, no button', () => {
	expect( enhanceCodeBlocks( document, undefined ) ).toBe( 0 );
	expect( document.querySelector( '.juanto-code__copy' ) ).toBeNull();
} );

test( 'copies the code, announces it and resets after 2 seconds', async () => {
	const writeText = jest.fn().mockResolvedValue();
	let reset;
	let delay;
	expect(
		enhanceCodeBlocks( document, { writeText }, ( fn, ms ) => {
			reset = fn;
			delay = ms;
		} )
	).toBe( 1 );
	const button = document.querySelector( '.juanto-code__copy' );
	expect( button.type ).toBe( 'button' );
	button.click();
	await Promise.resolve();
	await Promise.resolve();
	expect( writeText ).toHaveBeenCalledWith( 'download|true' );
	expect( button.textContent ).toBe( 'Copied' );
	expect( document.querySelector( '[aria-live="polite"]' ).textContent ).toBe(
		'Copied'
	);
	expect( delay ).toBe( 2000 );
	reset();
	expect( button.textContent ).toBe( 'Copy' );
} );

test( 'running twice adds one button', () => {
	const clip = { writeText: jest.fn() };
	enhanceCodeBlocks( document, clip );
	expect( enhanceCodeBlocks( document, clip ) ).toBe( 0 );
	expect( document.querySelectorAll( '.juanto-code__copy' ) ).toHaveLength(
		1
	);
} );

test( 'the button has an accessible name: Copy code by default', () => {
	enhanceCodeBlocks( document, { writeText: jest.fn() } );
	const button = document.querySelector( '.juanto-code__copy' );
	expect( button.getAttribute( 'aria-label' ) ).toBe( 'Copy code' );
	expect( button.textContent ).toBe( 'Copy' );
} );

test( 'the accessible name carries the file name when there is a file tab', () => {
	document.body.innerHTML =
		'<div class="juanto-code has-file"><p class="juanto-code__file"> functions.php </p><pre><code>x</code></pre></div>';
	enhanceCodeBlocks( document, { writeText: jest.fn() } );
	expect(
		document
			.querySelector( '.juanto-code__copy' )
			.getAttribute( 'aria-label' )
	).toBe( 'Copy functions.php' );
} );
