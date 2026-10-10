/* global matchMedia, IntersectionObserver */
import { stepSchedule } from '../../lib/steps';

const reduce = matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

/**
 * Arms one step track: builds its schedule and wires the replay button and
 * the play-once-on-scroll-into-view behaviour.
 *
 * @param {Element} root The track's wrapper element.
 */
function setup( root ) {
	const steps = [ ...root.querySelectorAll( '.juanto-steps__step' ) ];
	const rej = ( n ) => [
		...root.querySelectorAll( `[data-step="${ n }"] em` ),
	];
	const brace = root.querySelector( '.juanto-steps__brace' );
	const schedule = stepSchedule( {
		steps: steps.length,
		fails: JSON.parse( root.dataset.fails || '{}' ),
		bracket: root.dataset.bracket
			? JSON.parse( root.dataset.bracket )
			: null,
	} );
	let timers = [];

	const reset = () => {
		timers.forEach( clearTimeout );
		timers = [];
		root.classList.add( 'is-playing' ); // CSS hides rejection tags only while playing
		steps.forEach( ( s ) =>
			s.classList.remove( 'is-done', 'is-fail', 'is-shake' )
		);
		root.querySelectorAll( 'em' ).forEach( ( e ) =>
			e.classList.remove( 'is-on' )
		);
		brace?.classList.remove( 'is-on' );
	};
	const apply = ( e ) => {
		const el = steps[ ( e.step || 1 ) - 1 ];
		if ( e.type === 'done' ) {
			el.classList.add( 'is-done' );
		}
		if ( e.type === 'unfail' ) {
			el.classList.remove( 'is-fail' );
		}
		if ( e.type === 'brace' ) {
			brace?.classList.add( 'is-on' );
		}
		if ( e.type === 'fail' ) {
			el.classList.remove( 'is-shake' );
			void el.offsetWidth; // restart the shake
			el.classList.add( 'is-fail', 'is-shake' );
			rej( e.step )[ e.attempt - 1 ]?.classList.add( 'is-on' );
		}
	};
	const play = () => {
		reset();
		if ( reduce ) {
			schedule.forEach( apply );
			return;
		}
		timers = schedule.map( ( e ) => setTimeout( () => apply( e ), e.at ) );
	};

	root.querySelector( '.juanto-steps__replay' ).addEventListener(
		'click',
		play
	);
	if ( 'IntersectionObserver' in window ) {
		const io = new IntersectionObserver(
			( entries ) => {
				if ( entries.some( ( en ) => en.isIntersecting ) ) {
					play();
					io.disconnect();
				}
			},
			{ threshold: 0.5 }
		);
		io.observe( root );
	}
}

document.querySelectorAll( '.juanto-steps' ).forEach( setup );
