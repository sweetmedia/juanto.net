import { shouldThrow, throwDirection } from '../flick';

test( 'a slow drag past 22% of the width throws', () => {
	expect( shouldThrow( { dx: 120, velocity: 0.05, width: 500 } ) ).toBe(
		true
	);
} );
test( 'a quick flick throws even when short', () => {
	expect( shouldThrow( { dx: -30, velocity: -0.2, width: 500 } ) ).toBe(
		true
	);
} );
test( 'a tiny twitch or a slow short drag does not', () => {
	expect( shouldThrow( { dx: 15, velocity: 0.5, width: 500 } ) ).toBe(
		false
	);
	expect( shouldThrow( { dx: 80, velocity: 0.05, width: 500 } ) ).toBe(
		false
	);
} );
test( 'direction follows dx', () => {
	expect( throwDirection( -4 ) ).toBe( -1 );
	expect( throwDirection( 0 ) ).toBe( 1 );
} );
