/**
 * Adds a Copy button to every .juanto-code wrapper, only when the Clipboard API exists.
 *
 * @param {Document|Element} root      Where to look (usually document).
 * @param {Object}           clipboard navigator.clipboard, or undefined.
 * @param {Function}         later     setTimeout-compatible scheduler.
 * @return {number} How many buttons were added.
 */
export function enhanceCodeBlocks( root, clipboard, later = setTimeout ) {
	if ( ! clipboard || typeof clipboard.writeText !== 'function' ) {
		return 0;
	}
	let added = 0;
	root.querySelectorAll( '.juanto-code' ).forEach( ( wrap ) => {
		const code = wrap.querySelector( 'code' );
		if ( ! code || wrap.querySelector( '.juanto-code__copy' ) ) {
			return;
		}
		const doc = wrap.ownerDocument;
		const button = doc.createElement( 'button' );
		button.type = 'button';
		button.className = 'juanto-code__copy';
		button.textContent = 'Copy';
		const file = wrap.querySelector( '.juanto-code__file' );
		const fileName = file ? file.textContent.trim() : '';
		button.setAttribute(
			'aria-label',
			fileName ? `Copy ${ fileName }` : 'Copy code'
		);
		const status = doc.createElement( 'span' );
		status.className = 'screen-reader-text';
		status.setAttribute( 'aria-live', 'polite' );
		button.addEventListener( 'click', async () => {
			try {
				await clipboard.writeText( code.textContent );
			} catch {
				return;
			}
			button.textContent = 'Copied';
			status.textContent = 'Copied';
			later( () => {
				button.textContent = 'Copy';
				status.textContent = '';
			}, 2000 );
		} );
		wrap.append( button, status );
		added++;
	} );
	return added;
}
