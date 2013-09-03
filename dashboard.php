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
		
		function __construct() {		
			self::$instance = $this;
			
			add_action( 'admin_menu', array( $this , 'dash_add_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this , 'enqueue_scripts' ) );
			
			include( plugin_dir_path( __FILE__ ) . 'combinednews.php' );
			include( plugin_dir_path( __FILE__ ) . 'quickdraft.php' );
			include( plugin_dir_path( __FILE__ ) . 'rightnow.php' );
		}
		
		function enqueue_scripts() {
			if ( get_current_screen()->base !== $this->screen )
				return;

			// JS
			wp_enqueue_script( 'combinednews-js', dirname(__FILE__) . '/js/combinednews.js', array( 'jquery' ), self::version, true );
			wp_enqueue_script( 'quickdraft-js', dirname(__FILE__) . '/js/quickdraft.js', array( 'jquery' ), self::version, true );
			wp_enqueue_script( 'rightnow-js', dirname(__FILE__) . '/js/rightnow.js', array( 'jquery' ), self::version, true );
			// CSS
			wp_enqueue_style( 'combinednews-css', dirname(__FILE__) . '/css/combinednews.css', array(), self::version );
			wp_enqueue_style( 'quickdraft-css', dirname(__FILE__) . '/css/quickdraft.css', array(), self::version );
			wp_enqueue_style( 'rightnow-css', dirname(__FILE__) . '/css/rightnow.css', array(), self::version );
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