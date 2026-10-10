/**
 * Turn a track description into timed animation events.
 *
 * @param {Object} root0         Track parameters.
 * @param {number} root0.steps   Number of steps in the track.
 * @param {Object} root0.fails   Failed attempts per step, keyed by step number.
 * @param {?Array} root0.bracket `[ from, to ]` steps that run inside a bracket, or null.
 * @return {Array} Timed events: `{ at, type, step?, attempt? }`.
 */
export function stepSchedule( { steps, fails = {}, bracket = null } ) {
	const events = [];
	let at = 0;
	for ( let step = 1; step <= steps; step++ ) {
		const inBracket =
			bracket && step >= bracket[ 0 ] && step <= bracket[ 1 ];
		if ( bracket && step === bracket[ 0 ] ) {
			at += 200;
			events.push( { at, type: 'brace' } );
		}
		const attempts = fails[ step ] || 0;
		for ( let attempt = 1; attempt <= attempts; attempt++ ) {
			at += 260;
			events.push( { at, type: 'fail', step, attempt } );
			at += 240;
			events.push( { at, type: 'unfail', step } );
		}
		let delay = 110;
		if ( attempts ) {
			delay = 260;
		} else if ( inBracket ) {
			delay = 70;
		}
		at += delay;
		events.push( { at, type: 'done', step } );
	}
	return events;
}
