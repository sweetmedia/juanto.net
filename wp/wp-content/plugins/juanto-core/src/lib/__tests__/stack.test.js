import { nextOrder, prevOrder, bringToTop, layerTransform } from '../stack';

test( 'next sends the top print to the bottom', () => {
	expect( nextOrder( [ 0, 1, 2 ] ) ).toEqual( [ 1, 2, 0 ] );
} );
test( 'prev brings the bottom print back', () => {
	expect( prevOrder( [ 0, 1, 2 ] ) ).toEqual( [ 2, 0, 1 ] );
} );
test( 'bringToTop rotates without losing order', () => {
	expect( bringToTop( [ 3, 4, 5, 6 ], 5 ) ).toEqual( [ 5, 6, 3, 4 ] );
	expect( bringToTop( [ 3, 4 ], 9 ) ).toEqual( [ 3, 4 ] );
} );
test( 'layers fan out and cap at depth 4', () => {
	expect( layerTransform( 0, 4 ) ).toEqual( {
		x: 0,
		y: 0,
		rotate: 1.4,
		scale: 1,
	} );
	expect( layerTransform( 2, -3 ) ).toEqual( {
		x: 18,
		y: 26,
		rotate: -3.9,
		scale: 0.95,
	} );
	expect( layerTransform( 9, 1 ) ).toEqual( layerTransform( 4, 1 ) );
} );
