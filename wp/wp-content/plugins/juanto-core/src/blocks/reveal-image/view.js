/* global matchMedia, IntersectionObserver, innerHeight */
import { isInInitialViewport } from '../../lib/reveal';
// Arms every reveal image below the fold, then reveals it once it scrolls
// into view. Anything already on screen at load is left alone (no [data-armed],
// no clip, no transition) -- arming it would clip it until the observer fires
// and the 1.1s transition finishes, which is costly when that image is also
// the page's LCP candidate.
const figures = document.querySelectorAll( '.juanto-reveal' );
if (
	figures.length &&
	! matchMedia( '(prefers-reduced-motion: reduce)' ).matches &&
	'IntersectionObserver' in window
) {
	const io = new IntersectionObserver(
		( entries ) =>
			entries.forEach( ( entry ) => {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-in' );
					io.unobserve( entry.target );
				}
			} ),
		{ rootMargin: '0px 0px -12% 0px' }
	);
	figures.forEach( ( figure ) => {
		const rect = figure.getBoundingClientRect();
		if (
			isInInitialViewport( {
				top: rect.top,
				bottom: rect.bottom,
				viewport: innerHeight,
			} )
		) {
			return;
		}
		figure.dataset.armed = '';
		io.observe( figure );
	} );
}
