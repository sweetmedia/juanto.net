/**
 * Move the top print to the bottom.
 *
 * @param {Array} order Array of print identifiers.
 * @return {Array} New order with top print moved to bottom.
 */
export const nextOrder = ( order ) => [ ...order.slice( 1 ), order[ 0 ] ];

/**
 * Move the bottom print to the top.
 *
 * @param {Array} order Array of print identifiers.
 * @return {Array} New order with bottom print moved to top.
 */
export const prevOrder = ( order ) => [
	order[ order.length - 1 ],
	...order.slice( 0, -1 ),
];

/**
 * Bring a specific print to the top.
 *
 * @param {Array} order Array of print identifiers.
 * @param {*}     id    Print identifier to bring to top.
 * @return {Array} New order with specified print at top, or original if not found.
 */
export const bringToTop = ( order, id ) => {
	const at = order.indexOf( id );
	return at < 0 ? order : [ ...order.slice( at ), ...order.slice( 0, at ) ];
};

const round = ( n ) => Math.round( n * 1000 ) / 1000;

/**
 * Depth 0 is the top print. Lower prints fan out, capped at 4 levels.
 *
 * @param {number} depth Print depth (0 = top).
 * @param {number} rot   Rotation in degrees.
 * @return {Object} Transform with x, y, rotate, scale properties.
 */
export const layerTransform = ( depth, rot ) => {
	const d = Math.min( depth, 4 );
	return {
		x: d * 9,
		y: d * 13,
		rotate: round( depth === 0 ? rot * 0.35 : rot * 1.3 ),
		scale: round( 1 - d * 0.025 ),
	};
};
