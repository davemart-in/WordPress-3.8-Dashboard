<?php
/* Plugin Name: Dashboard
 * Description: Refresh WordPress dashboard screen
 * Author: Core Volunteers
 * Author URI: http://make.wordpress.org/ui/tag/dash/
 * Version: 0.2
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// NOTE: This class won't exist once this plugin is merged into core.
if ( !class_exists( 'Plugin_Dashboard' ) ) {
	class Plugin_Dashboard {

		const version = '0.2';
		static $instance;
		private $screen;

		private $active_modules = array(
			'wpnews',
			'quickdraft',
			'rightnow',
			'activity',
			'dash-rwd'
		);

		function __construct() {
			self::$instance = $this;

			// Override dashboard temporarily
			add_action( 'load-index.php', array( $this , 'override_dashboard' ) );

			// Load JS & CSS
			add_action( 'admin_enqueue_scripts', array( $this , 'enqueue_scripts' ) );

			// Load new module files
			foreach ( $this->active_modules as $module_slug ) {
				$module = plugin_dir_path( __FILE__ ) . $module_slug . '.php';
				if ( file_exists( $module ) )
					include $module;
			}
			
			$this->screen = 'dashboard';
		}

		function enqueue_scripts() {
			if ( get_current_screen()->base !== $this->screen && get_current_screen()->base !== 'dashboard' )
				return;

			foreach ( $this->active_modules as $module_slug ) {
				// JS
				wp_enqueue_script( $module_slug . '-js', plugins_url( '/js/' . $module_slug . '.js', __FILE__ ), array( 'jquery' ), self::version, true );
				// CSS
				wp_enqueue_style( $module_slug . '-css', plugins_url( '/css/' . $module_slug . '.css', __FILE__ ), array(), self::version );
			}
		}

		function override_dashboard() {
			if ( get_current_screen()->in_admin( 'site' ) ) {
				require dirname( __FILE__ ) . '/dashboard-override.php';
				exit;
			}
		}
	}
	new Plugin_Dashboard;
}