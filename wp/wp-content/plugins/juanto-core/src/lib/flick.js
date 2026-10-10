/**
 * A print is thrown by distance (slow drag) or by velocity (quick flick).
 *
 * @param {Object} root0          Flick parameters.
 * @param {number} root0.dx       Horizontal drag distance.
 * @param {number} root0.velocity Horizontal velocity.
 * @param {number} root0.width    Container width.
 * @return {boolean} True if the flick should throw the print.
 */
export const shouldThrow = ( { dx, velocity, width } ) =>
	Math.abs( dx ) > width * 0.22 ||
	( Math.abs( velocity ) > 0.11 && Math.abs( dx ) > 20 );

/**
 * Determine throw direction from drag distance.
 *
 * @param {number} dx Horizontal drag distance.
 * @return {number} Direction: -1 for left, 1 for right.
 */
export const throwDirection = ( dx ) => ( dx < 0 ? -1 : 1 );
