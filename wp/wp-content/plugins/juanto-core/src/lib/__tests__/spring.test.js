import { springStep, isSettled } from '../spring';

test( 'moves 16% of the remaining distance per frame', () => {
	expect( springStep( 0, 100 ) ).toBeCloseTo( 16 );
	expect( springStep( 84, 100 ) ).toBeCloseTo( 86.56 );
} );
test( 'settles within 0.3px', () => {
	expect( isSettled( 99.8, 100 ) ).toBe( true );
	expect( isSettled( 99, 100 ) ).toBe( false );
} );
