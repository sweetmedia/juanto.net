/**
 * Turns a .juanto-video facade into a YouTube (no-cookie) player on click.
 *
 * @param {Element} wrap Facade wrapper with data-video and data-title.
 * @return {boolean} Whether the facade was wired.
 */
export function mountVideo( wrap ) {
	const id = wrap.dataset.video || '';
	const button = wrap.querySelector( 'button' );
	if ( ! /^[A-Za-z0-9_-]{11}$/.test( id ) || ! button ) {
		return false;
	}
	button.addEventListener( 'click', () => {
		const iframe = wrap.ownerDocument.createElement( 'iframe' );
		iframe.src = `https://www.youtube-nocookie.com/embed/${ id }?autoplay=1&rel=0`;
		iframe.title = wrap.dataset.title || 'Video';
		iframe.allow =
			'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture';
		iframe.allowFullscreen = true;
		wrap.classList.add( 'is-playing' );
		button.replaceWith( iframe );
		iframe.focus();
	} );
	return true;
}
