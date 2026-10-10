<?php
/**
 * Turns legacy "Step N: Title" posts into juanto/tutorial-step blocks.
 * Pure (content in, content out) and idempotent. Driven by tools/migrate-tutorials.php
 * and kept for future imports.
 *
 * @package juanto-core
 */

/**
 * Whether a parsed block or any descendant is a heading reading exactly $text.
 *
 * @param array  $block Parsed block.
 * @param string $text  Heading text.
 */
function juanto_block_has_heading( array $block, string $text ): bool {
	if ( 'core/heading' === $block['blockName'] && 0 === strcasecmp( juanto_block_plain_text( $block ), $text ) ) {
		return true;
	}
	foreach ( $block['innerBlocks'] as $inner ) {
		if ( juanto_block_has_heading( $inner, $text ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Applies alt text and code file names to one parsed block.
 *
 * @param array $block   Parsed block.
 * @param array $options Transform options.
 */
function juanto_migrate_tutorial_leaf( array $block, array $options ): array {
	if ( 'core/code' === $block['blockName'] ) {
		$code = html_entity_decode( juanto_block_plain_text( $block ), ENT_QUOTES );
		if ( isset( $options['code_filenames'][ $code ] ) ) {
			$block['attrs']['filename'] = $options['code_filenames'][ $code ];
		}
	}
	if ( 'core/image' === $block['blockName'] ) {
		$id = (int) ( $block['attrs']['id'] ?? 0 );
		if ( isset( $options['alts'][ $id ] ) ) {
			$p = new WP_HTML_Tag_Processor( $block['innerHTML'] );
			if ( $p->next_tag( 'img' ) ) {
				$p->set_attribute( 'alt', $options['alts'][ $id ] );
				$block['innerHTML']    = $p->get_updated_html();
				$block['innerContent'] = [ $block['innerHTML'] ];
			}
		}
	}
	return $block;
}

/**
 * A juanto/tutorial-step parsed block wrapping $inner.
 *
 * @param string $title Step title.
 * @param array  $inner Parsed inner blocks.
 */
function juanto_migrate_tutorial_step( string $title, array $inner ): array {
	$content = [ "\n" ];
	foreach ( $inner as $i => $unused ) {
		$content[] = null;
		$content[] = count( $inner ) - 1 === $i ? "\n" : "\n\n";
	}
	return [
		'blockName'    => 'juanto/tutorial-step',
		'attrs'        => [ 'title' => $title ],
		'innerBlocks'  => $inner,
		'innerHTML'    => implode( '', array_filter( $content, 'is_string' ) ),
		'innerContent' => $content,
	];
}

/**
 * Legacy tutorial content to tutorial-step blocks.
 *
 * @param string $content Post content.
 * @param array  $options drop_embed (string), code_filenames (array), alts (array),
 *                        outro_starts (array of text prefixes, case-insensitive): a top-level block
 *                        whose text starts with one closes the open step and stays top-level, with
 *                        everything after it, until a later "Step N:" heading opens a new step.
 */
function juanto_migrate_tutorial_content( string $content, array $options = [] ): string {
	$options += [
		'drop_embed'     => '',
		'code_filenames' => [],
		'alts'           => [],
		'outro_starts'   => [],
	];
	$out      = [];
	$outro    = false;
	$step     = null;
	$close    = static function () use ( &$step, &$out ) {
		if ( null !== $step ) {
			$out[] = juanto_migrate_tutorial_step( $step['title'], $step['inner'] );
			$step  = null;
		}
	};
	foreach ( parse_blocks( $content ) as $block ) {
		$name = $block['blockName'];
		if ( null === $name && '' === trim( $block['innerHTML'] ) ) {
			continue;
		}
		if ( 'core/spacer' === $name || juanto_block_has_heading( $block, 'Tutorial Steps' ) ) {
			continue;
		}
		if ( 'core/embed' === $name && '' !== $options['drop_embed'] && ( $block['attrs']['url'] ?? '' ) === $options['drop_embed'] ) {
			continue;
		}
		if ( ! $outro && $options['outro_starts'] ) {
			$text = juanto_block_plain_text( $block );
			foreach ( $options['outro_starts'] as $prefix ) {
				if ( '' !== $prefix && 0 === stripos( $text, $prefix ) ) {
					$close();
					$outro = true;
					break;
				}
			}
		}
		$is_h2 = 'core/heading' === $name && 2 === (int) ( $block['attrs']['level'] ?? 2 );
		if ( $is_h2 ) {
			$close();
			if ( preg_match( '/^Step\s+\d+\s*[:.\-]\s*(.+)$/i', juanto_block_plain_text( $block ), $m ) ) {
				$outro = false;
				$step  = [
					'title' => trim( $m[1] ),
					'inner' => [],
				];
				continue;
			}
		}
		if ( 'juanto/tutorial-step' === $name ) {
			$block['innerBlocks'] = array_map( static fn( $b ) => juanto_migrate_tutorial_leaf( $b, $options ), $block['innerBlocks'] );
		}
		$block = juanto_migrate_tutorial_leaf( $block, $options );
		if ( null !== $step ) {
			$step['inner'][] = $block;
		} else {
			$out[] = $block;
		}
	}
	$close();
	return implode( "\n\n", array_map( 'serialize_block', $out ) );
}
