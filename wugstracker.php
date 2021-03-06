<?php
/**
* wugstracker
*
*
* @package   wugstracker
* @author    Ivrusson
* @license   GPL-3.0
* @link      https://github.com/maquinantes
*
* @wordpress-plugin
* Plugin Name:       WugsTracker
* Plugin URI:        https://maquinantes.com/ivrusson/wugstracker
* Description:       Wordpress plugin to debug javascript code.
* Version:           1.0.0
* Author:            Ivrusson
* Author URI:        https://maquinantes.com
* Text Domain:       wugstracker
* License:           GPL-3.0
* License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
* Domain Path:       /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define('WPJS_DEBUG_VERSION', '1.0.0');

define( 'WPJS_DEBUG__FILE__', __FILE__ );

define( 'WPJS_DEBUG_PLUGIN_BASE', plugin_basename( WPJS_DEBUG__FILE__ ) );
define( 'WPJS_DEBUG_PATH', plugin_dir_path( WPJS_DEBUG__FILE__ ) );
define( 'WPJS_DEBUG_URL', plugins_url( '/', WPJS_DEBUG__FILE__ ) );
define('WPJS_DEBUG_ASSETS', plugins_url('/assets', __FILE__));

add_action( 'plugins_loaded', 'wugstracker_load_plugin_textdomain' );

if ( ! version_compare( PHP_VERSION, '5.6', '>=' ) ) {
	add_action( 'admin_notices', 'wugstracker_fail_php_version' );
} elseif ( ! version_compare( get_bloginfo( 'version' ), '5.2', '>=' ) ) {
	add_action( 'admin_notices', 'wugstracker_fail_wp_version' );
} else {
	require WPJS_DEBUG_PATH . 'vendor/autoload.php';
	require WPJS_DEBUG_PATH . 'includes/plugin.php';
}

/**
 * Load wugstracker textdomain.
 *
 * Load gettext translate for wugstracker text domain.
 *
 * @since 1.0.0
 *
 * @return void
 */
function wugstracker_load_plugin_textdomain() {
	load_plugin_textdomain( 'wugstracker' );
}

/**
 * wugstracker admin notice for minimum PHP version.
 *
 * Warning when the site doesn't have the minimum required PHP version.
 *
 * @since 1.0.0
 *
 * @return void
 */
function wugstracker_fail_php_version() {
	/* translators: %s: PHP version */
	$message = sprintf( esc_html__( 'WugsTracker requires PHP version %s+, plugin is currently NOT RUNNING.', 'wugstracker' ), '5.6' );
	$html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
	echo wp_kses_post( $html_message );
}

/**
 * wugstracker admin notice for minimum WordPress version.
 *
 * Warning when the site doesn't have the minimum required WordPress version.
 *
 * @since 1.0.0
 *
 * @return void
 */
function wugstracker_fail_wp_version() {
	/* translators: %s: WordPress version */
	$message = sprintf( esc_html__( 'WugsTracker requires WordPress version %s+. Because you are using an earlier version, the plugin is currently NOT RUNNING.', 'wugstracker' ), '5.2' );
	$html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
	echo wp_kses_post( $html_message );
}
