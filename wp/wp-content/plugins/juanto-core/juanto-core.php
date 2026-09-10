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
