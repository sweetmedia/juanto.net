/* global matchMedia, requestAnimationFrame, Image */
import { springStep, isSettled } from '../../lib/spring';

const fine = matchMedia( '(hover: hover) and (pointer: fine)' ).matches;
const reduce = matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

document.querySelectorAll( '.juanto-list.is-rows' ).forEach( ( list ) => {
	const preview = list.querySelector( '.juanto-preview' );
	if ( ! preview ) {
		return;
	}
	const rows = list.querySelector( '.juanto-rows' );
	const box = preview.querySelector( '.juanto-preview__box' );
	const imgs = new Map();
	const imgFor = ( url ) => {
		if ( ! imgs.has( url ) ) {
			const im = new Image();
			im.src = url;
			im.alt = '';
			im.decoding = 'async';
			box.appendChild( im );
			imgs.set( url, im );
		}
		return imgs.get( url );
	};
	let x = 0,
		y = 0,
		tx = 0,
		ty = 0,
		raf = 0,
		on = false;
	const place = () =>
		( preview.style.transform = `translate3d(${ x }px, ${ y }px, 0)` );
	const tick = () => {
		x = springStep( x, tx );
		y = springStep( y, ty );
		place();
		raf =
			on && ! ( isSettled( x, tx ) && isSettled( y, ty ) )
				? requestAnimationFrame( tick )
				: 0;
	};
	const hide = () => {
		preview.classList.remove( 'is-on' );
		on = false;
	};
	const show = ( row ) => {
		const url = row.dataset.preview;
		if ( ! url ) {
			hide();
			return;
		}
		const current = imgFor( url );
		imgs.forEach( ( im ) =>
			im.classList.toggle( 'is-on', im === current )
		);
		preview.classList.add( 'is-on' );
		on = true;
	};
	const clampTo = ( cx, cy ) => {
		const r = list.getBoundingClientRect();
		tx = Math.min(
			Math.max( cx - r.left + 40, 0 ),
			r.width - preview.offsetWidth
		);
		ty = Math.min(
			Math.max( cy - r.top - preview.offsetHeight / 2, 0 ),
			r.height - preview.offsetHeight
		);
	};

	if ( fine ) {
		rows.addEventListener( 'pointermove', ( e ) => {
			const row = e.target.closest( '.juanto-row' );
			if ( ! row ) {
				return;
			}
			clampTo( e.clientX, e.clientY );
			if ( ! preview.classList.contains( 'is-on' ) || reduce ) {
				x = tx;
				y = ty;
				place();
			}
			show( row );
			if ( ! reduce && ! raf ) {
				raf = requestAnimationFrame( tick );
			}
		} );
		rows.addEventListener( 'pointerleave', hide );
	}
	// Keyboard: the preview sits beside the focused row, no following.
	rows.addEventListener( 'focusin', ( e ) => {
		const row = e.target.closest( '.juanto-row' );
		if ( ! row ) {
			return;
		}
		const r = list.getBoundingClientRect();
		const rr = row.getBoundingClientRect();
		x = tx = r.width - preview.offsetWidth;
		y = ty = Math.min(
			Math.max( rr.top - r.top - 20, 0 ),
			r.height - preview.offsetHeight
		);
		place();
		show( row );
	} );
	rows.addEventListener( 'focusout', hide );
} );
