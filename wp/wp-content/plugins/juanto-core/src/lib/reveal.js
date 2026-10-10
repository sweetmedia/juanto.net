/**
 * Whether an element's vertical bounds already overlap the viewport, i.e. it's
 * visible without scrolling. Used to skip the scroll-triggered reveal for an
 * element that's already on screen at load -- arming it would clip it until
 * the observer fires and the reveal transition finishes, which is costly when
 * that element is also the page's LCP candidate.
 *
 * @param {Object} root0          Rect parameters.
 * @param {number} root0.top      Element top relative to the viewport.
 * @param {number} root0.bottom   Element bottom relative to the viewport.
 * @param {number} root0.viewport Viewport height.
 * @return {boolean} True if any part of the element is already visible.
 */
export const isInInitialViewport = ( { top, bottom, viewport } ) =>
	bottom > 0 && top < viewport;
