/* global matchMedia, innerHeight, requestAnimationFrame, addEventListener, history */
import { readingProgress, activeSection } from '../../lib/reading';

const nav = document.querySelector( '.juanto-toc' );
const article = document.querySelector( '.juanto-case' );
const bar = document.querySelector( '.juanto-progress' );
const reduce = matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

if ( nav ) {
	const links = [ ...nav.querySelectorAll( 'a' ) ];
	const sections = links
		.map( ( a ) => document.getElementById( a.hash.slice( 1 ) ) )
		.filter( Boolean );
	let ticking = false;
	const update = () => {
		ticking = false;
		const line = innerHeight * 0.35;
		const at = activeSection(
			sections.map( ( s ) => s.getBoundingClientRect().top ),
			line
		);
		links.forEach( ( a, i ) =>
			a.classList.toggle( 'is-active', i === at )
		);
		if ( bar && article ) {
			const r = article.getBoundingClientRect();
			bar.style.transform = `scaleX(${ readingProgress( {
				top: r.top,
				height: r.height,
				viewport: innerHeight,
			} ) })`;
		}
	};
	addEventListener(
		'scroll',
		() => {
			if ( ! ticking ) {
				ticking = true;
				requestAnimationFrame( update );
			}
		},
		{ passive: true }
	);
	nav.addEventListener( 'click', ( e ) => {
		const a = e.target.closest( 'a' );
		const target = a && document.getElementById( a.hash.slice( 1 ) );
		if ( ! target ) {
			return;
		}
		e.preventDefault();
		// e.detail === 0 means the keyboard activated the link: jump instantly.
		target.scrollIntoView( {
			behavior: reduce || e.detail === 0 ? 'auto' : 'smooth',
			block: 'start',
		} );
		history.replaceState( null, '', a.hash );
		target.querySelector( 'h2' )?.setAttribute( 'tabindex', '-1' );
		target.querySelector( 'h2' )?.focus( { preventScroll: true } );
	} );
	update();
}
