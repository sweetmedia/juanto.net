import { stepNumber, isTutorialPost } from '../tutorial';

test( 'a step is numbered by its position among steps', () => {
	expect( stepNumber( [ 'a', 'b', 'c' ], 'c' ) ).toBe( 3 );
	expect( stepNumber( [ 'a' ], 'a' ) ).toBe( 1 );
	expect( stepNumber( [], 'x' ) ).toBe( 1 );
} );

test( 'the panel shows only when the Tutorials category is checked', () => {
	expect( isTutorialPost( [ 1, 7 ], 7 ) ).toBe( true );
	expect( isTutorialPost( [ 1 ], 7 ) ).toBe( false );
	expect( isTutorialPost( [ 7 ], 0 ) ).toBe( false );
	expect( isTutorialPost( undefined, 7 ) ).toBe( false );
} );
