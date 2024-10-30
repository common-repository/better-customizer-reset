<?php
/**
 * Plugin Name: Better Customizer Reset
 * Plugin URI: https://www.ilovewp.com/better-customizer-reset/
 * Description: The easiest way to inspect and delete customizer data (theme mods) saved by WordPress themes.
 * Author: ILOVEWP.com
 * Author URI: https://www.ilovewp.com
 * Version: 1.0.2
 * Text Domain: better-customizer-reset
 * Domain Path: languages
 *
 * Better Customizer Reset is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with Better Customizer Reset. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package BCR
 * @category Core
 * @author Dumitru Brinzan
 * @version 1.0.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Better_Customizer_Reset' ) ) :

/**
 * Main Better_Customizer_Reset Class.
 *
 * @since 1.0
 */
final class Better_Customizer_Reset {

	/** Singleton *************************************************************/

	/**
	 * @var Better_Customizer_Reset The one true Better_Customizer_Reset
	 * @since 1.0
	 */
	private static $instance;

	/**
	 * Main Better_Customizer_Reset Instance.
	 *
	 * Insures that only one instance of Better_Customizer_Reset exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.0
	 * @static
	 * @staticvar array $instance
	 * @uses Better_Customizer_Reset::setup_constants() Setup the constants needed.
	 * @uses Better_Customizer_Reset::load_textdomain() load the language files.
	 * @uses Better_Customizer_Reset::includes() Include the required files.
	 * @see BCR()
	 * @return object|Better_Customizer_Reset The one true Better_Customizer_Reset
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Better_Customizer_Reset ) ) {
			
			self::$instance = new Better_Customizer_Reset;
			self::$instance->setup_constants();
			self::$instance->includes();
			
			$BCR_Settings			= new BCR_Settings();
			
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
			add_filter( 'plugin_action_links_better-customizer-reset/better-customizer-reset.php', array( self::$instance, 'bcr_custom_action_links' ) );

		}

		return self::$instance;
	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.0
	 * @access protected
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'better-customizer-reset' ), '1.0' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @since 1.0
	 * @access protected
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'better-customizer-reset' ), '1.0' );
	}

	/**
	 * Setup plugin constants.
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function setup_constants() {

		// Plugin version.
		if ( ! defined( 'BCR_VERSION' ) ) {
			define( 'BCR_VERSION', '1.0.2' );
		}

		// Plugin Folder Path.
		if ( ! defined( 'BCR_PLUGIN_DIR' ) ) {
			define( 'BCR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'BCR_PLUGIN_URL' ) ) {
			define( 'BCR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File.
		if ( ! defined( 'BCR_PLUGIN_FILE' ) ) {
			define( 'BCR_PLUGIN_FILE', __FILE__ );
		}

	}

	/**
	 * Include required files.
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function includes() {

		require_once BCR_PLUGIN_DIR . 'includes/class-settings.php';

	}

	/**
	 * Loads the plugin language files.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function load_textdomain() {
		global $wp_version;

		$bcr_lang_dir  = apply_filters( 'bcr_languages_directory', dirname( plugin_basename( BCR_PLUGIN_FILE ) ) . '/languages/');
		load_plugin_textdomain( 'better-customizer-reset', false, $bcr_lang_dir );

	}

	public function bcr_custom_action_links( $links ) { 

		$url = add_query_arg( 'page', 'better-customizer-reset', get_admin_url() . 'tools.php' );
		$setting_link = '<a href="' . esc_url( $url ) . '" style="font-weight: bold;">' . __( 'Open Customizer Reset', 'better-customizer-reset' ) . '</a>';
		array_unshift( $links, $setting_link );

		return $links;
		
	}

}

endif; // End if class_exists check.

/**
 * The main function for that returns Better_Customizer_Reset
 *
 * @since 1.0
 * @return object|Better_Customizer_Reset The one true Better_Customizer_Reset Instance.
 */
if ( ! function_exists( 'better_customizer_reset' ) ) :
	function better_customizer_reset() {
		return Better_Customizer_Reset::instance();
	}
endif;

// Get BCR Running.
$BCR_instance = better_customizer_reset();