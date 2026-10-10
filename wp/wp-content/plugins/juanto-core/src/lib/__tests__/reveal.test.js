import { isInInitialViewport } from '../reveal';

test( 'an element already on screen is in the initial viewport', () => {
	expect(
		isInInitialViewport( { top: 100, bottom: 400, viewport: 800 } )
	).toBe( true );
	expect(
		isInInitialViewport( { top: -50, bottom: 50, viewport: 800 } )
	).toBe( true ); // straddling the top edge
	expect(
		isInInitialViewport( { top: 750, bottom: 900, viewport: 800 } )
	).toBe( true ); // straddling the bottom edge
} );

test( 'an element fully below or above the fold is not', () => {
	expect(
		isInInitialViewport( { top: 1000, bottom: 1300, viewport: 800 } )
	).toBe( false );
	expect(
		isInInitialViewport( { top: -300, bottom: -50, viewport: 800 } )
	).toBe( false );
} );
