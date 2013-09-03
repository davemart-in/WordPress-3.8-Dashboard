<?php
/* Plugin Name: Dashboard
 * Description: Refresh WordPress dashboard screen
 * Author: Core Volunteers
 * Author URI: http://make.wordpress.org/ui/tag/dash/
 * Version: 0.1
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if ( !class_exists( 'Plugin_Dashboard' ) ) {
	class Plugin_Dashboard {
		
		const version = '0.1';
		static $instance;
		private $screen;

		private $modules = array(
			'combinednews',
			'quickdraft',
			'rightnow',
		);
		
		function __construct() {		
			self::$instance = $this;
			
			add_action( 'admin_menu', array( $this , 'dash_add_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this , 'enqueue_scripts' ) );
			
			foreach ( $this->modules as $module_slug ) include( plugin_dir_path( __FILE__ ) . $module_slug . '.php';
		}
		
		function enqueue_scripts() {
			if ( get_current_screen()->base !== $this->screen )
				return;

			foreach ( $this->modules as $module_slug ) {
				// JS
				wp_enqueue_script( $module_slug . '-js', plugins_url( __FILE__ ) . '/js/' . $module_slug . '.js', array( 'jquery' ), self::version, true );
				// CSS
				wp_enqueue_style( $module_slug . '-css', plugins_url( __FILE__ ) . '/css/' . $module_slug . '.css', array(), self::version );
			}
		}
		
		function dash_add_menu() {
			$this->screen = add_dashboard_page( 'Dash', 'Dash', 'read', 'dash-dash', array( __CLASS__, 'dash_page' ) );
		}
		
		function dash_page() {
		?>
		<div class="wrap">

			<?php screen_icon(); ?>

			<h2><?php esc_html_e( 'Dash' ); ?></h2>

		</div><!-- .wrap -->
		<?php
		}
	}
	new Plugin_Dashboard;
}
