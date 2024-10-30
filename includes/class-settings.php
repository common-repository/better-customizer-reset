<?php
/**
 * Settings Pages in Dashboard
 *
 * @package     BCR
 * @copyright   Copyright (c) 2021, Dumitru Brinzan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * BCR_Settings Class
 *
 * This class handles plugin settings in the back-end
 *
 * @since 1.0.0
 */
class BCR_Settings {

	/**
	 * Get things going
	 *
	 * @since 1.0.0
	 */

	public $settings = array();

	public function __construct() {

		add_action( 'admin_menu', array( $this, 'admin_menu_options' ) );
		add_action( 'admin_init', array( $this, 'bcr_settings_init' ) );
		add_action( 'admin_init', array( $this, 'bcr_settings_action' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'bcr_admin_scripts_styles' ) );

	}

	/**
	 * Enqueue admin styles and scripts.
	 *
	 * @param string $page
	 * @return void
	 */
	public function bcr_admin_scripts_styles( $page ) {
		
		$bcr_page = strpos($page, 'better-customizer-reset');
		if ( $bcr_page === false ) {
			return;
		}

		if ( !is_customize_preview() ) {
			wp_enqueue_style( 'bcr-admin-styles', BCR_PLUGIN_URL . 'assets/css/bcr-admin.css', array());
		}

	}

	/**
	 * Register options page
	 * 
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_menu_options() {

		add_submenu_page( 
			'tools.php',
			__( 'Getting Started with Better Customizer Reset', 'better-customizer-reset' ), 
			__( 'Better Customizer Reset', 'better-customizer-reset' ), 
			'manage_options', 
			'better-customizer-reset',
			array($this, 'bcr_settings_page_general')
		);

	}

	function bcr_settings_init() {

		global $BCR_instance;
		$bcr_options = get_option( 'better_customizer_reset' );

		register_setting( 'better-customizer-reset-customizer-reset', 'better_customizer_reset' );

		if ( filter_input( INPUT_GET, 'result' ) === 'success' ) {
			add_action( 'admin_notices', array($this, 'bcr_admin_notice__success' ));
		}

		if ( filter_input( INPUT_GET, 'result' ) === 'failed' ) {
			add_action( 'admin_notices', array($this, 'bcr_admin_notice__failure' ));
		}

	}

	/**
	 * General page HTML
	 * 
	 * @since 1.0.0
	 * @return void
	 */
	function bcr_settings_page_general() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wp_settings_fields;
		$bcr_options = get_option( 'better_customizer_reset' );

		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error( 'bcr_messages', 'bcr_message', __( 'Settings Saved', 'better-customizer-reset' ), 'updated' );
		}

		settings_errors( 'bcr_messages' );

		$theme_data = wp_get_theme();
		$current_user = wp_get_current_user();
		?>
		
		<div id="better-customizer-reset" class="wrap">

			<div class="bcr-admin-columns">
				<div class="bcr-admin-column bcr-admin-column--main">
					<div class="bcr-admin-column__wrapper">

						<div id="bcr-customizer-reset">
							<h2 class="page-title"><?php esc_html_e('Theme Customizer Reset', 'better-customizer-reset'); ?></h2>

							<p><?php esc_html_e('On this page you can reset all theme customizer settings that are set using the theme_mods() method.', 'better-customizer-reset'); ?></p>

							<?php 

							$active_theme_slug = 'theme_mods_' . get_option( 'stylesheet' );

							global $wpdb;
							$bcr_theme_mods_results = $wpdb->get_results( "SELECT * FROM {$wpdb->options} WHERE option_name LIKE 'theme_mods_%' ORDER BY option_name ASC" );

							if ( count($bcr_theme_mods_results) > 0 ) { ?>

								<form action="" method="post">

								<?php wp_nonce_field( 'bcr_delete_theme_nonce', 'bcr_delete_theme_nonce' ); ?>

								<table class="wp-list-table widefat fixed striped table-view-list posts">
									<thead>
									<tr>
										<th class="column-title column-primary"><span><?php esc_html_e( 'Theme', 'better-customizer-reset' ) ?></span></th>
									</tr>
									</thead>
									<tbody><?php
									foreach ($bcr_theme_mods_results as $key => $bcr_theme_mod) {
										$theme_mod_values = unserialize($bcr_theme_mod->option_value);
										if ( isset($theme_mod_values['nav_menu_locations']) ) {
											unset($theme_mod_values['nav_menu_locations']);
										}
										if ( isset($theme_mod_values['sidebars_widgets']) ) {
											unset($theme_mod_values['sidebars_widgets']);
										}
										if ( isset($theme_mod_values['custom_css_post_id']) ) {
											unset($theme_mod_values['custom_css_post_id']);
										}
										if ( isset($theme_mod_values['0']) && $theme_mod_values['0'] == '' ) {
											unset($theme_mod_values['0']);
										}
										?>
								<tr<?php if ( $bcr_theme_mod->option_name == $active_theme_slug ) {
									echo ' class="bcr-active-theme"';
								} ?>>
									<td class="title column-primary" data-colname="<?php esc_attr_e( 'Title', 'better-customizer-reset' ) ?>"><label><input type="checkbox" name="bcr_theme_mods[]" value="<?php echo esc_attr($bcr_theme_mod->option_name); ?>"><strong><?php echo esc_html($bcr_theme_mod->option_name); ?></strong></label><?php
											if ( $bcr_theme_mod->option_name == $active_theme_slug ) {
												echo '<span class="bcr-active-theme">';
												esc_html_e(' &mdash; active theme', 'better-customizer-reset');
												echo '</span>';
											}
											if ( isset($theme_mod_values) && count($theme_mod_values) > 0 ) { 
													?><div class="bcr-theme-mods-data">
													<details>
													<summary><?php esc_html_e('View Theme Mods', 'better-customizer-reset'); ?></summary>
													<pre><?php print_r($theme_mod_values); ?></pre>
													</details></div><!-- .bcr-theme-mods-data --><?php
												}
											?></td>
								</tr><?php } ?>
									</tbody>
								</table>
								<br /><label><input type="checkbox" name="bcr_theme_mods_confirmation" value="1"><strong><?php esc_html_e('I understand that performing this action will IRREVERSIBLY delete selected entries from my database. ','better-customizer-reset'); ?></strong></label>
								<br /><br /><input type="submit" class="delete button-primary" name="bcr_delete_theme_mods" value="<?php echo esc_attr_e('Delete Selected Mods', 'better-customizer-reset'); ?>"/>
							</form><?php } // if count > 0
							?>

						</div><!-- #bcr-customizer-reset -->

					</div><!-- .bcr-admin-column__wrapper -->
				</div><!-- .bcr-admin-column .bcr-admin-column--main -->
				<div class="bcr-admin-column bcr-admin-column--sidebar">
					<div class="bcr-admin-column__wrapper">

						<div class="bcr-admin-section bcr-section__branding">
							<a href="https://www.ilovewp.com/better-customizer-reset/" target="_blank" rel="noopener"><img src="<?php echo esc_url( BCR_PLUGIN_URL . '/assets/images/bcr-logo-dark.png' ); ?>" width="320" height="100" class="bcr-logo-welcome" alt="<?php esc_attr_e(__('Better Customizer Reset Logo', 'better-customizer-reset')); ?>" /></a>
						</div><!-- .bcr-admin-section .bcr-section__branding -->

						<div class="bcr-admin-section bcr-section__newsletter">

							<h2 class="bcr-admin-section__title"><?php esc_html_e('Subscribe to the Newsletter', 'better-customizer-reset'); ?></h2>

							<p class="newsletter-description"><?php esc_html_e('We send out the newsletter once every few weeks. It contains information about upcoming plugin and theme updates, special offers and other WordPress-related useful content.','better-customizer-reset'); ?></p>

							<form action="https://ilovewp.us14.list-manage.com/subscribe/post?u=b9a9c29fe8fb1b02d49b2ba2b&amp;id=18a2e743db" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate="">
								<div id="mc_embed_signup_scroll">
									<input type="email" value="<?php echo esc_attr($current_user->user_email); ?>" name="EMAIL" class="email" id="mce-EMAIL" placeholder="<?php esc_attr_e(__('email address','better-customizer-reset')); ?>" required="">
									<!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
									<div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_b9a9c29fe8fb1b02d49b2ba2b_18a2e743db" tabindex="-1" value=""></div>
									<input type="submit" value="<?php esc_attr_e('Subscribe','better-customizer-reset'); ?>" name="subscribe" id="mc-embedded-subscribe" class="button button-primary">
								</div><!-- #mc_embed_signup_scroll -->
								<p class="newsletter-disclaimer"><?php esc_html_e('*We use Mailchimp as our marketing platform. By clicking above to subscribe, you acknowledge that your information will be transferred to Mailchimp for processing.','better-customizer-reset'); ?></p>
							</form>
							
						</div><!-- .bcr-admin-section .bcr-section__newsletter -->

					</div><!-- .bcr-admin-column__wrapper -->
				</div><!-- .bcr-admin-column .bcr-admin-column--sidebar -->
			</div><!-- .bcr-admin-columns -->
		
		</div><!-- #better-customizer-reset .wrap -->

	<?php }

	/**
	 * Checks if a reset action was submitted.
	 *
	 * @since 1.0.0
	 */
	function bcr_settings_action() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['bcr_delete_theme_mods'] ) ) {
			
			if ( check_admin_referer( 'bcr_delete_theme_nonce', 'bcr_delete_theme_nonce' ) ) {
				
				if ( isset( $_POST['bcr_theme_mods_confirmation'] ) && $_POST['bcr_theme_mods_confirmation'] == '1' )
				{

					if ( isset($_POST['bcr_theme_mods']) && count($_POST['bcr_theme_mods']) > 0 ) {

						$deleted_mods = count($_POST['bcr_theme_mods']);
						foreach ($_POST['bcr_theme_mods'] as $key => $theme_mod) {
							delete_option($theme_mod);
						}

						$redirect = add_query_arg(
							array(
								'page' 		=> 'better-customizer-reset',
								'result'	=> 'success',
								'count'		=> $deleted_mods
							),
							admin_url( 'tools.php' )
						);
						wp_safe_redirect( $redirect );
						exit;

					}

					if ( !isset($_POST['bcr_theme_mods']) || count($_POST['bcr_theme_mods']) == 0 ) {

						$redirect = add_query_arg(
							array(
								'page' 		=> 'better-customizer-reset',
								'result'	=> 'failed',
								'count'		=> 0
							),
							admin_url( 'tools.php' )
						);
						wp_safe_redirect( $redirect );
						exit;

					}

				} // if confirmation has been checked

			}
		}

	}

	function bcr_admin_notice__success() {
		?>
		<div id="message" class="notice notice-success"><p><?php echo sprintf(__( 'Successfully deleted <strong>%1$s</strong> theme mods.', 'better-block-patterns' ), filter_input( INPUT_GET, 'count' )); ?></p></div>
		<?php
	}

	function bcr_admin_notice__failure() {
		?>
		<div id="message" class="notice notice-error"><p><?php echo esc_html_e('Theme mods were not deleted. Have you selected any?', 'better-block-patterns'); ?></p></div>
		<?php
	}

}