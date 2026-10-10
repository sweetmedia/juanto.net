import { stepSchedule } from '../steps';

const types = ( s ) =>
	s.map(
		( e ) =>
			e.type + ( e.step ?? '' ) + ( e.attempt ? '#' + e.attempt : '' )
	);

test( 'plain track completes every step 110ms apart', () => {
	const s = stepSchedule( { steps: 3, fails: {}, bracket: null } );
	expect( types( s ) ).toEqual( [ 'done1', 'done2', 'done3' ] );
	expect( s.map( ( e ) => e.at ) ).toEqual( [ 110, 220, 330 ] );
} );

test( 'a failing step fails n times before passing', () => {
	const s = stepSchedule( { steps: 2, fails: { 2: 2 }, bracket: null } );
	expect( types( s ) ).toEqual( [
		'done1',
		'fail2#1',
		'unfail2',
		'fail2#2',
		'unfail2',
		'done2',
	] );
	expect( s[ s.length - 1 ].at ).toBe( 110 + 260 + 240 + 260 + 240 + 260 );
} );

test( 'the bracket opens before its first step and its steps run faster', () => {
	const s = stepSchedule( { steps: 4, fails: {}, bracket: [ 2, 3 ] } );
	expect( types( s ) ).toEqual( [
		'done1',
		'brace',
		'done2',
		'done3',
		'done4',
	] );
	expect( s.map( ( e ) => e.at ) ).toEqual( [ 110, 310, 380, 450, 560 ] );
} );
