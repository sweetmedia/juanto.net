/**
 * Critically damped follow: move a fixed share of the remaining distance each frame.
 *
 * @param {number} current Current position.
 * @param {number} target  Target position.
 * @param {number} k       Spring constant (default 0.16).
 * @return {number} New position after one frame.
 */
export const springStep = ( current, target, k = 0.16 ) =>
	current + ( target - current ) * k;

/**
 * Check if current position has settled near the target.
 *
 * @param {number} current Current position.
 * @param {number} target  Target position.
 * @param {number} eps     Epsilon tolerance (default 0.3).
 * @return {boolean} True if settled within epsilon.
 */
export const isSettled = ( current, target, eps = 0.3 ) =>
	Math.abs( target - current ) <= eps;
