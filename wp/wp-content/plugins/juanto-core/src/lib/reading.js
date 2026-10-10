/**
 * Calculate reading progress for an element.
 *
 * @param {Object} root0          Progress parameters.
 * @param {number} root0.top      Element top relative to viewport (negative once scrolled past).
 * @param {number} root0.height   Element height.
 * @param {number} root0.viewport Viewport height.
 * @return {number} Progress from 0 (top) to 1 (bottom), clamped.
 */
export const readingProgress = ( { top, height, viewport } ) => {
	const span = height - viewport;
	if ( span <= 0 ) {
		return 1;
	}
	return Math.min( 1, Math.max( 0, -top / span ) );
};

/**
 * Index of the last section whose top is at or above the reading line.
 *
 * @param {number[]} tops Array of section top positions.
 * @param {number}   line Reading line position.
 * @return {number} Index of active section.
 */
export const activeSection = ( tops, line ) => {
	let active = 0;
	tops.forEach( ( t, i ) => {
		if ( t <= line ) {
			active = i;
		}
	} );
	return active;
};
