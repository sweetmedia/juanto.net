/**
 * 1-based position of a block among the tutorial steps.
 *
 * @param {string[]} ids      Client IDs of every tutorial step, in document order.
 * @param {string}   clientId This block's client ID.
 * @return {number} The step number (1 when not found).
 */
export const stepNumber = ( ids, clientId ) => {
	const at = ids.indexOf( clientId );
	return at === -1 ? 1 : at + 1;
};

/**
 * Whether the edited post carries the Tutorials category.
 *
 * @param {number[]|undefined} categories  Category IDs of the edited post.
 * @param {number}             tutorialsId Term ID of "Tutorials" (0 when missing).
 * @return {boolean} True for tutorials.
 */
export const isTutorialPost = ( categories, tutorialsId ) =>
	tutorialsId > 0 &&
	Array.isArray( categories ) &&
	categories.includes( tutorialsId );
