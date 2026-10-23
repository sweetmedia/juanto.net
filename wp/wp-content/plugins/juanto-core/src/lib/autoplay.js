/**
 * Plays muted loop videos (marked data-juanto-autoplay by the server) only
 * while they are near the viewport. The server swaps `autoplay` for
 * preload="none" + controls, so nothing downloads at load and the visitor
 * can still play the clip without JS.
 *
 * @param {Document} doc                   Document to search.
 * @param {Object}   options
 * @param {Function} options.Observer      IntersectionObserver constructor, if any.
 * @param {boolean}  options.reducedMotion Whether the visitor prefers reduced motion.
 * @return {number} How many videos were armed.
 */
export function armAutoplay( doc, { Observer, reducedMotion } ) {
	const videos = doc.querySelectorAll( 'video[data-juanto-autoplay]' );
	if ( ! videos.length || reducedMotion || ! Observer ) {
		return 0;
	}
	const io = new Observer(
		( entries ) =>
			entries.forEach( ( { target, isIntersecting } ) => {
				if ( ! isIntersecting ) {
					target.pause();
					return;
				}
				target.preload = 'auto';
				const playing = target.play();
				if ( playing && playing.catch ) {
					playing.catch( () => {
						target.controls = true;
					} );
				}
			} ),
		{ rootMargin: '200px 0px' }
	);
	videos.forEach( ( video ) => {
		video.controls = false;
		io.observe( video );
	} );
	return videos.length;
}
