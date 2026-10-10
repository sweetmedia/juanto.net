/* global matchMedia, requestAnimationFrame, innerWidth */
import {
	nextOrder,
	prevOrder,
	bringToTop,
	layerTransform,
} from '../../lib/stack';
import { shouldThrow, throwDirection } from '../../lib/flick';

const reduce = matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
const pad = ( n ) => String( n + 1 ).padStart( 2, '0' );

document.querySelectorAll( '.juanto-lab' ).forEach( ( root ) => {
	const pile = root.querySelector( '.juanto-lab__pile' );
	const stage = root.querySelector( '.juanto-lab__stage' );
	const all = root.querySelector( '.juanto-lab__all' );
	const views = root.querySelector( '.juanto-lab__views' );
	const cards = [ ...pile.querySelectorAll( '.juanto-print' ) ];
	const swap = root.querySelector( '.juanto-lab__swap' );
	const out = {
		count: root.querySelector( '.juanto-lab__count' ),
		name: root.querySelector( '.juanto-lab__name' ),
		meta: root.querySelector( '.juanto-lab__meta' ),
	};
	let order = cards.map( ( _, i ) => i );
	let busy = false;
	let shown = -1;

	const base = ( k, i ) => {
		const t = layerTransform( k, Number( cards[ i ].dataset.rot ) );
		return `translate(-50%, -50%) translate(${ t.x }px, ${ t.y }px) rotate(${ t.rotate }deg) scale(${ t.scale })`;
	};
	const info = () => {
		const i = order[ 0 ];
		if ( i === shown ) {
			return;
		}
		const write = () => {
			out.count.innerHTML = `<b>${ pad( i ) }</b> / ${ cards.length }`;
			out.name.textContent = cards[ i ].dataset.name;
			out.meta.textContent = cards[ i ].dataset.meta;
		};
		const first = shown === -1;
		shown = i;
		if ( first || reduce ) {
			return write();
		}
		swap.classList.add( 'is-out' );
		setTimeout( () => {
			write();
			swap.classList.remove( 'is-out' );
		}, 160 );
	};
	const layout = () => {
		order.forEach( ( i, k ) => {
			const c = cards[ i ];
			c.style.zIndex = String( 100 - k );
			c.style.opacity = k < 5 ? '1' : '0';
			c.style.transform = base( k, i );
			c.classList.toggle( 'is-top', k === 0 );
			c.setAttribute( 'aria-hidden', String( k !== 0 ) );
		} );
		info();
	};
	const throwTop = ( dir, instant = false ) => {
		if ( busy ) {
			return;
		}
		busy = true;
		const c = cards[ order[ 0 ] ];
		const d = reduce || instant ? 0 : 280;
		c.style.transition = `transform ${ d }ms var(--juanto-ease-out), opacity ${ d }ms ease`;
		c.style.transform = `translate(-50%, -50%) translate(${
			dir * pile.clientWidth * 0.9
		}px, -60px) rotate(${ dir * 20 }deg)`;
		c.style.opacity = '0';
		order = nextOrder( order );
		const settle = () => {
			c.style.transition = 'none';
			layout();
			void c.offsetHeight;
			c.style.transition = '';
			busy = false;
		};
		if ( d ) {
			order.slice( 0, 5 ).forEach( ( i, k ) => {
				// lift the next prints now
				cards[ i ].style.zIndex = String( 100 - k );
				cards[ i ].style.opacity = '1';
				cards[ i ].style.transform = base( k, i );
			} );
			info();
			setTimeout( settle, d );
		} else {
			settle();
		}
	};
	const back = ( instant = false ) => {
		if ( busy ) {
			return;
		}
		const c = cards[ order[ order.length - 1 ] ];
		c.style.transition = 'none';
		c.style.zIndex = '200';
		c.style.opacity = '0';
		c.style.transform = `translate(-50%, -50%) translate(${
			-pile.clientWidth * 0.85
		}px, -60px) rotate(-18deg)`;
		void c.offsetHeight;
		c.style.transition = reduce || instant ? 'none' : '';
		order = prevOrder( order );
		layout();
		if ( reduce || instant ) {
			requestAnimationFrame( () => ( c.style.transition = '' ) );
		}
	};

	root.querySelector( '[data-go="next"]' ).addEventListener( 'click', () =>
		throwTop( 1 )
	);
	root.querySelector( '[data-go="back"]' ).addEventListener( 'click', () =>
		back()
	);
	pile.addEventListener( 'keydown', ( e ) => {
		// keyboard actions never animate
		if ( e.key === 'ArrowRight' ) {
			e.preventDefault();
			throwTop( 1, true );
		}
		if ( e.key === 'ArrowLeft' ) {
			e.preventDefault();
			back( true );
		}
	} );

	let drag = null;
	pile.addEventListener( 'pointerdown', ( e ) => {
		const c = e.target.closest( '.juanto-print.is-top' );
		if ( ! c || busy || drag || e.button !== 0 ) {
			return;
		}
		drag = {
			c,
			x: e.clientX,
			y: e.clientY,
			lx: e.clientX,
			lt: performance.now(),
			v: 0,
			dx: 0,
			rot: Number( c.dataset.rot ),
		};
		c.classList.add( 'is-grab' );
		try {
			pile.setPointerCapture( e.pointerId );
		} catch {}
	} );
	pile.addEventListener( 'pointermove', ( e ) => {
		if ( ! drag ) {
			return;
		}
		const now = performance.now();
		drag.v = ( e.clientX - drag.lx ) / Math.max( 1, now - drag.lt );
		drag.lx = e.clientX;
		drag.lt = now;
		drag.dx = e.clientX - drag.x;
		drag.c.style.transform = `translate(-50%, -50%) translate(${
			drag.dx
		}px, ${ ( e.clientY - drag.y ) * 0.6 }px) rotate(${
			drag.rot * 0.35 + drag.dx * 0.05
		}deg)`;
	} );
	const end = () => {
		if ( ! drag ) {
			return;
		}
		const { c, dx } = drag;
		const velocity = performance.now() - drag.lt < 90 ? drag.v : 0; // held still before release: no flick
		c.classList.remove( 'is-grab' );
		drag = null;
		if ( shouldThrow( { dx, velocity, width: pile.clientWidth } ) ) {
			throwTop( throwDirection( dx ) );
		} else if ( reduce ) {
			c.style.transition = 'none';
			layout();
		} else {
			c.style.transition =
				'transform 440ms cubic-bezier(0.34, 1.3, 0.64, 1)';
			layout();
			setTimeout( () => ( c.style.transition = '' ), 440 );
		}
	};
	pile.addEventListener( 'pointerup', end );
	pile.addEventListener( 'pointercancel', end );

	const setView = ( v ) => {
		views
			.querySelectorAll( 'button' )
			.forEach( ( b ) =>
				b.setAttribute( 'aria-pressed', String( b.dataset.view === v ) )
			);
		stage.hidden = v !== 'stack';
		all.hidden = v !== 'all';
		if ( v === 'stack' ) {
			layout();
		}
	};
	views.addEventListener( 'click', ( e ) => {
		const b = e.target.closest( 'button' );
		if ( b ) {
			setView( b.dataset.view );
		}
	} );
	all.addEventListener( 'click', ( e ) => {
		const t = e.target.closest( '.juanto-tile' );
		if ( ! t ) {
			return;
		}
		order = bringToTop( order, Number( t.dataset.i ) );
		cards.forEach( ( c ) => ( c.style.transition = 'none' ) );
		setView( 'stack' );
		void pile.offsetHeight;
		cards.forEach( ( c ) => ( c.style.transition = '' ) );
		pile.focus( { preventScroll: true } );
	} );

	// Ready: show the controls, then drop the top prints onto the table.
	root.classList.add( 'is-ready' );
	views.hidden = false;
	setView( innerWidth >= 480 ? 'stack' : 'all' );
	if ( ! reduce && ! stage.hidden ) {
		order.forEach( ( i, k ) => {
			cards[ i ].style.transition = 'none';
			cards[ i ].style.opacity = '0';
			cards[ i ].style.transform = base( k, i ) + ' translateY(-40px)';
		} );
		void pile.offsetHeight;
		order.forEach( ( i, k ) => {
			cards[ i ].style.transition = '';
			cards[ i ].style.transitionDelay = `${
				( 4 - Math.min( k, 4 ) ) * 55
			}ms`;
		} );
		layout();
		setTimeout(
			() => cards.forEach( ( c ) => ( c.style.transitionDelay = '' ) ),
			700
		);
	}
} );
