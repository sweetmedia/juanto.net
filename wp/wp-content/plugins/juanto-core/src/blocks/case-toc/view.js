/* global matchMedia, innerHeight, requestAnimationFrame, addEventListener, history */
import { readingProgress, activeSection } from '../../lib/reading';

const nav = document.querySelector( '.juanto-toc' );
const article = document.querySelector( '.juanto-case' );
const bar = document.querySelector( '.juanto-progress' );
const reduce = matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

/**
 * Run `fn` once per animation frame while the page scrolls.
 *
 * @param {Function} fn Called at most once per frame.
 */
const onScrollFrame = ( fn ) => {
	let queued = false;
	addEventListener(
		'scroll',
		() => {
			if ( ! queued ) {
				queued = true;
				requestAnimationFrame( () => {
					queued = false;
					fn();
				} );
			}
		},
		{ passive: true }
	);
};

/**
 * Jump to a section from a click: smooth for pointers, instant for the
 * keyboard (e.detail === 0) or reduced motion; update the hash, focus the h2.
 *
 * @param {Event}       e      The click event.
 * @param {HTMLElement} link   The activated link.
 * @param {HTMLElement} target The section to reveal.
 */
const jumpTo = ( e, link, target ) => {
	target.scrollIntoView( {
		behavior: reduce || e.detail === 0 ? 'auto' : 'smooth',
		block: 'start',
	} );
	history.replaceState( null, '', link.hash );
	const heading = target.querySelector( 'h2' );
	heading?.setAttribute( 'tabindex', '-1' );
	heading?.focus( { preventScroll: true } );
};

if ( nav ) {
	const links = [ ...nav.querySelectorAll( 'a' ) ];
	const sections = links
		.map( ( a ) => document.getElementById( a.hash.slice( 1 ) ) )
		.filter( Boolean );
	const update = () => {
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
	onScrollFrame( update );
	nav.addEventListener( 'click', ( e ) => {
		const a = e.target.closest( 'a' );
		const target = a && document.getElementById( a.hash.slice( 1 ) );
		if ( ! target ) {
			return;
		}
		e.preventDefault();
		jumpTo( e, a, target );
	} );
	update();
}

// Tutorial step pill: follow the reading line, fill the current circle, jump on click.
const stepNav = document.querySelector( '.juanto-step-nav' );
if ( stepNav ) {
	const details = stepNav.querySelector( 'details' );
	// Pair each link with its section so a missing target never shifts indexes.
	const pairs = [ ...stepNav.querySelectorAll( 'ol a' ) ]
		.map( ( a ) => [ a, document.getElementById( a.hash.slice( 1 ) ) ] )
		.filter( ( [ , s ] ) => s );
	const stepLinks = pairs.map( ( [ a ] ) => a );
	const steps = pairs.map( ( [ , s ] ) => s );
	const count = stepNav.querySelector( '[data-step-current]' );
	const title = stepNav.querySelector( '[data-step-title]' );
	const marks = [
		...stepNav.querySelectorAll( '.juanto-step-nav__track i' ),
	];
	let at = -1;
	const sync = () => {
		const next = activeSection(
			steps.map( ( s ) => s.getBoundingClientRect().top ),
			innerHeight * 0.35
		);
		if ( next === at || ! steps.length ) {
			return;
		}
		at = next;
		count.textContent = String( at + 1 );
		title.textContent = stepLinks[ at ].textContent;
		marks.forEach( ( m, i ) => m.classList.toggle( 'is-on', i <= at ) );
		steps.forEach( ( s, i ) =>
			s.classList.toggle( 'is-current', i === at )
		);
		stepLinks.forEach( ( a, i ) =>
			i === at
				? a.setAttribute( 'aria-current', 'step' )
				: a.removeAttribute( 'aria-current' )
		);
	};
	onScrollFrame( sync );
	stepNav.addEventListener( 'click', ( e ) => {
		const a = e.target.closest( 'ol a' );
		const pair = pairs.find( ( [ link ] ) => link === a );
		if ( ! pair ) {
			return;
		}
		e.preventDefault();
		details.open = false;
		jumpTo( e, a, pair[ 1 ] );
	} );
	sync();
}
