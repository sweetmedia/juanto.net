import { readingProgress, activeSection } from '../reading';

test( 'progress is 0 at the top, 1 at the end, clamped', () => {
	expect( readingProgress( { top: 0, height: 3000, viewport: 800 } ) ).toBe(
		0
	);
	expect(
		readingProgress( { top: -1100, height: 3000, viewport: 800 } )
	).toBeCloseTo( 0.5 );
	expect(
		readingProgress( { top: -5000, height: 3000, viewport: 800 } )
	).toBe( 1 );
	expect( readingProgress( { top: 200, height: 3000, viewport: 800 } ) ).toBe(
		0
	);
} );
test( 'active section is the last one whose top crossed the reading line', () => {
	expect( activeSection( [ 100, 900, 1800 ], 300 ) ).toBe( 0 );
	expect( activeSection( [ -400, 250, 1800 ], 300 ) ).toBe( 1 );
	expect( activeSection( [ 500, 900 ], 300 ) ).toBe( 0 );
} );
