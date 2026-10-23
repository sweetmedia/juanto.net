/* global matchMedia */
import { armAutoplay } from '../lib/autoplay';

armAutoplay( document, {
	Observer: window.IntersectionObserver,
	reducedMotion: matchMedia( '(prefers-reduced-motion: reduce)' ).matches,
} );
