<?php
/**
 * Plugin Name: juanto.net Core
 * Description: Content model, dynamic blocks and data for juanto.net.
 * Version: 0.1.0
 * Requires at least: 6.8
 * Requires PHP: 8.3
 * Author: Juan Pérez
 * License: GPL-2.0-or-later
 * Text Domain: juanto
 *
 * @package juanto-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JUANTO_CORE_VERSION', '0.1.0' );
define( 'JUANTO_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'JUANTO_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once JUANTO_CORE_DIR . 'includes/post-types.php';
require_once JUANTO_CORE_DIR . 'includes/meta.php';
require_once JUANTO_CORE_DIR . 'includes/tutorial.php';
require_once JUANTO_CORE_DIR . 'includes/code.php';
require_once JUANTO_CORE_DIR . 'includes/tutorial-migrate.php';
require_once JUANTO_CORE_DIR . 'includes/format.php';
require_once JUANTO_CORE_DIR . 'includes/settings.php';
require_once JUANTO_CORE_DIR . 'includes/github.php';
require_once JUANTO_CORE_DIR . 'includes/queries.php';
require_once JUANTO_CORE_DIR . 'includes/templates.php';
require_once JUANTO_CORE_DIR . 'includes/blocks.php';
require_once JUANTO_CORE_DIR . 'includes/editor.php';
require_once JUANTO_CORE_DIR . 'includes/lang.php';
require_once JUANTO_CORE_DIR . 'includes/seo.php';
require_once JUANTO_CORE_DIR . 'includes/media.php';

register_activation_hook(
	__FILE__,
	static function () {
		juanto_register_content_types();
		juanto_seed_terms();
		flush_rewrite_rules();
	}
);

register_deactivation_hook(
	__FILE__,
	static function () {
		wp_clear_scheduled_hook( 'juanto_refresh_commit_date' );
	}
);
