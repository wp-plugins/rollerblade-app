<?php
/**
* Plugin Name: Rollerblade
* Plugin URI: https://rollerbladeapp.com/support/
* Description: Feedback tool
* Version: 0.0.4
* Author: Webatix
* Author URI: http://webatix.com
* Text Domain: rollerblade
* Domain Path: /lang/
* License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {

	exit; // Exit if accessed directly

}

class Rollerblade {
	
	/**
	 * Triggered on plugin activation
	 * 
	 * @return void
	 */
	public function activate() {
		
		add_option( 'rb_plugin_activated', true );
		
	}
	
	
	/**
	 * Initializes the plugin
	 * 
	 * @return void
	 */
	public static function initialize_plugin() {
		
		//display user instructions only on plugin activation
		if ( true == get_option( 'rb_plugin_activated', false ) ) {
				
			delete_option( 'rb_plugin_activated' );
				
			add_action( 'admin_footer', array( 'Rollerblade', 'rb_display_user_instructions' ) );
				
		}
		
		//add 
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( 'Rollerblade', 'action_links' ) );
		
		add_action( 'wp_enqueue_scripts', array( 'Rollerblade', 'enqueue_scripts_and_styles' ) );
		
		add_action( 'wp_footer', array( 'Rollerblade', 'print_the_rollerblade_button' ) );
		
		add_action( 'admin_menu', array( 'Rollerblade', 'add_rollerblade_options_menu_item' ) );
		
		//AJAX calls hander
		add_action( 'wp_ajax_send_rb_request', array( 'Rollerblade', 'send_rb_request' ) );
		add_action( 'wp_ajax_nopriv_send_rb_request', array( 'Rollerblade', 'send_rb_request' ) );
		
	}
	
	
	/**
	 * Displays user instructions on plugin activation
	 *
	 * @return void
	 */
	public function rb_display_user_instructions() {
	
		wp_enqueue_script( 'jquery-ui-dialog' );
	
		wp_enqueue_style( 'jqueryui', plugins_url( 'css/smoothness-jquery-ui.css', __FILE__ ) );
	
		?><div id="rb-user-instructions" style="display: none"><?php
			
				$subdomain = get_option( '_rb_subdomain' );
			
				if ( empty( $subdomain ) ) {
			
					_e( 'Thank you for installing Rollerblade! Please set up a free account on <a href="http://rollerbladeapp.com/signup/" target="_blank">Rollerbladeapp.com</a> so we have a place to store your screenshots and comments. If you\'ve already created an account, enter your account sub-domain in the <a href="' . admin_url( 'options-general.php?page=rollerblade' ) . '">settings</a> to link it to your account.', 'rollerblade' );
					
				} else {
	
					_e( 'Please login to <a href="http://' . $subdomain . '.rollerbladeapp.com/wp-admin/options-general.php?page=rollerblade-options" target="_blank">http://' . $subdomain . '.rollerbladeapp.com/wp-admin/options-general.php?page=rollerblade-options</a> and add ' . get_bloginfo( 'url' ) . ' to your settings so we can store your tickets. Once you\'ve done this, just go to the front end and click the Rollerblade button to use the tool!', 'rollerblade' );
	
				}
				
		?></div><script type="text/javascript">jQuery( function($) { $( '#rb-user-instructions' ).dialog( { title: '<?php _e( 'Plugin activated!', 'rollerblade' ); ?>', width: 830 } ); } );</script><?php
			
	}
	
	
	/**
	 * Adds "Settings" link to plugin row on plugins page
	 *
	 * @param array $links
	 *
	 * @return array $links
	 */
	public static function action_links( $links ) {
	
		$plugin_links = array(
				'<a href="' .admin_url( 'options-general.php?page=rollerblade' ) . '">' . __( 'Settings', 'rollerblade' ) . '</a>',
				'<a href="http://rollerbladeapp.com/support">' . __( 'Support', 'rollerblade' ) . '</a>',
		);
	
		return array_merge( $plugin_links, $links );
	
	}
	
	
	/**
	 * Enqueues scripts and styles
	 * 
	 * @return void
	 */
	public static function enqueue_scripts_and_styles() {
		
		//make sure that subdomain is set and current user is allowed to use Rollerblade
		$usage_allowed = self::is_rb_usage_allowed();
		
		if ( ! $usage_allowed ) {
			
			return;

		}
		
		wp_register_script( 'html2canvas', plugins_url( 'scripts/html2canvas.js' , __FILE__ ), array( 'jquery' ) );
		
		wp_enqueue_script( 'html2canvas' );
		
		wp_register_script( 'feedback', plugins_url( 'scripts/feedback.js' , __FILE__ ), array( 'jquery', 'html2canvas' ) );
		
		wp_enqueue_script( 'feedback' );
		
		wp_register_script( 'rollerblade', plugins_url( 'scripts/rollerblade.js' , __FILE__ ), array( 'jquery', 'feedback' ) );
		
		wp_enqueue_script( 'rollerblade' );
		
		wp_register_style( 'rollerblade', plugins_url( 'css/rollerblade.css' , __FILE__ ) );
		
		wp_enqueue_style( 'rollerblade' );
		
		wp_register_style( 'feedback', plugins_url( 'css/feedback.css' , __FILE__ ) );
		
		wp_enqueue_style( 'feedback' );

		wp_enqueue_script( 'jquery-ui-draggable', array( 'jquery' ) );
		
	}
	
	
	/**
	 * Prints out the Rollerblade button
	 * 
	 * @return void
	 */
	public static function print_the_rollerblade_button() {
		
		//make sure that subdomain is set and current user is allowed to use Rollerblade
		if ( ! self::is_rb_usage_allowed() ) {

			return;
			
		}

		$subdomain = get_option( '_rb_subdomain' );
		
		//TODO: don't forget to change the target URL
		//echo '<div id="rollerblade-button"><div id="rb-button-drag-area"></div><a href="http://' . $subdomain . '.rollerblade.dev/tickets/" id="rb-tickets-link" target="_blank"></a></div>';
		//echo '<div id="rollerblade-button"><div id="rb-button-drag-area"></div><a href="http://' . $subdomain . '.dev.rollerbladeapp.com/tickets/" id="rb-tickets-link" target="_blank"></a></div>';
		echo '<div id="rollerblade-button"><div id="rb-button-drag-area"></div><a href="http://' . $subdomain . '.rollerbladeapp.com/tickets/" id="rb-tickets-link" target="_blank"></a></div>';
		
		//mouse tip
		echo '<div id="mouse-tip">' . __( 'Click and drag to highlight the area', 'rollerblade' ) . '</div><div id="feedback-highlighter-next-clone"></div>';
		
		$ajax_nonce = wp_create_nonce( 'rb-request-nonce' );
		
		echo '<script type="text/javascript">var rollerblade_ajax_url = "' . admin_url( 'admin-ajax.php' ) . '"; var rollerblade_nonce = "' . $ajax_nonce . '";</script>';
		
	}
	
	
	/**
	 * Returns true if subdomain is set and current user is allowed to use Rollerblade. Otherwise false.
	 *  
	 * @return boolean $is_allowed
	 */
	public static function is_rb_usage_allowed() {
		
		//make sure subdomain is set
		$subdomain = get_option( '_rb_subdomain' );
		
		if ( empty( $subdomain ) ) {
				
			return false;
				
		}
		
		//restrict RB presence by selected user roles
		$active_user_roles = get_option( '_rb_active_user_roles', array( 'administrator' ) );
		
		//if visitors are allowed to use RB, everyone can
		if ( in_array( 'visitor', $active_user_roles ) ) {
			
			return true;
			
		}
		
		//if visitor is not allowed, let's check whether user has right permissions
		foreach( $active_user_roles as $role ) {
			
			if ( current_user_can( $role ) ) {
				
				return true;
				
			}
			
		}
		
		return false;
		
	}
	
	
	/**
	 * Handles AJAX call to send a remote request to RB site.
	 * 
	 * @return void
	 */
	public static function send_rb_request() {
		
		check_ajax_referer( 'rb-request-nonce', 'security' );
		
		if ( ! self::is_rb_usage_allowed() ) {
			
			die();		//this call was not from our tool, die silently
			
		}
		
		$subdomain = get_option( '_rb_subdomain' );
		
		//TODO: change URL to the right one!
		//$remote_url = 'http://rollerblade.dev/api/' . $subdomain . '/ticket/add';
		//$remote_url = 'https://dev.rollerbladeapp.com/api/' . $subdomain . '/ticket/add';
		$remote_url = 'https://rollerbladeapp.com/api/' . $subdomain . '/ticket/add';
		
		$res = wp_remote_post( 
				$remote_url,
				array(
						'headers' => array( 'Content-type' => 'application/json' ),
						'body' => json_encode( $_POST['request_data'] ),
				)
		);
		
		if ( ! is_wp_error( $res ) ) {
		
			echo $res['body'];
			
		} else {
			
			echo json_encode( array( 'status' => 'error', 'error_message' => __( 'Could not send remote request.', 'rollerblade' ), 'error_details' => $res ) );
			
		}	
		
		die();
		
	}
	
	
	/**
	 * Adds Rollerblade Options Menu Item
	 * 
	 * @return void
	 */
	public static function add_rollerblade_options_menu_item() {
		
		add_options_page( __( 'Rollerblade', 'rollerblade' ), __( 'Rollerblade', 'rollerblade' ), 'manage_options', 'rollerblade', array( 'Rollerblade', 'options_page_view' ) );
		
	}
	
	
	/**
	 * Rollerblade Options page view
	 * 
	 * @return void
	 */
	public static function options_page_view() {
		
		if ( ! current_user_can( 'manage_options' ) )
			return false;
		
		wp_enqueue_style( 'rollerblade', plugins_url( 'css/rollerblade.css' , __FILE__ ) );
		
		//process data if it was submitted
		if ( isset( $_POST['rb-subdomain'] ) ) {
				
			$subdomain = trim( $_POST['rb-subdomain'] );
			
			update_option( '_rb_subdomain', $subdomain );
				
		} else {
			
			$subdomain = get_option( '_rb_subdomain', '' );
			
		}
		
		$active_user_roles = get_option( '_rb_active_user_roles', array( 'administrator' ) );
		
		if ( isset( $_POST['rb-options-form'] ) ) {
			
			$active_user_roles = array();
			
			if ( isset( $_POST['rb-active-user-roles'] ) ) {
			
				$active_user_roles = $_POST['rb-active-user-roles'];
				
			}	
			
			update_option( '_rb_active_user_roles', $active_user_roles );
			
		}
		
		?>
		
			<div id="rb-options-header">
			
				 <div id="rb-logo-wrapper">
				
					<div id="rb-logo">

							<img id="rb-options-icon" src="<?php echo plugins_url( 'img/rb-options-icon.svg', __FILE__ ); ?>" />
							
							<img id="rb-options-logo" src="<?php echo plugins_url( 'img/rb-options-logo.svg', __FILE__ ); ?>" />
						
					</div>
					
				</div>
			
				<div id="rb-options-header-text-wrapper">
				
					<p id="rb-options-slogan"><?php _e( 'We\'re changing the way Wordpress teams work', 'rollerblade' ); ?></p>
					
					<p id="rb-options-header-links">
					
						<a href="<?php if ( ! empty( $subdomain ) ) { echo 'http://' . $subdomain . '.rollerbladeapp.com/wp-admin/admin.php?page=rollerblade-options'; } else { echo '#'; } ?>" target="_blank"><?php _e( 'Your Account', 'rollerblade' ); ?></a> <span id="after-first-item">&nbsp; &#x7c; &nbsp;</span><a href="<?php if ( ! empty( $subdomain ) ) { echo 'http://' . $subdomain . '.rollerbladeapp.com/'; } else { echo '#'; } ?>" target="_blank"><?php _e( 'Your Project Tickets', 'rollerblade' ); ?></a> <span id="after-second-item">&nbsp; &#x7c; &nbsp;</span><a href="http://rollerbladeapp.com/support/" target="_blank"><?php _e( 'Support and Docs', 'rollerblade' ); ?></a>
						
					</p>
					
				</div>
				
			</div>
		
			<form method="POST" action="<?php echo add_query_arg( array( 'page' => 'rollerblade', 'message' => 1 ), admin_url( 'admin.php', 'http' ) ); ?>">
			
				<input type="hidden" name="rb-options-form" value="1" />
			
				<table class="form-table">
			
					<tbody>
					
						<tr>
						
							<th scope="row">
							
								<label for="rb-subdomain"><?php _e( 'Rollerblade Subdomain', 'rollerblade' ); ?></label>
							
							</th>
							
							<td>
							
								<input type="text" id="rb-subdomain" name="rb-subdomain" value="<?php echo esc_attr( $subdomain ); ?>" />
							
							</td>
						
						</tr>
					
						<tr>
						
							<th scope="row">
							
								<label for="rb-active-user-roles"><?php _e( 'Who should be able to use Rollerblade on this Site?', 'rollerblade' ); ?></label>
							
							</th>
							
							<td>
							
								<?php
									
									$roles = new WP_Roles(); 
									
									$all_user_roles = $roles->get_names();
									
									$all_user_roles['visitor'] = __( 'Visitor', 'rollerblade' );
									
								?>
										
									<ul>

									<?php 
										
										foreach( $all_user_roles as $role => $name ) {
									
									?>
										
											<li><input type="checkbox" id="rb-user-role-<?php echo esc_attr( $role ); ?>" class="rb-active-user-roles" name="rb-active-user-roles[]"<?php if ( in_array( $role, $active_user_roles ) ) echo 'checked="checked"'; ?> value="<?php echo esc_attr( $role ); ?>" /> <label for="rb-user-role-<?php echo esc_attr( $role ); ?>"><?php echo $name; ?></label></li>
											
									<?php 
								
										}
										
									?>
										
									</ul>
							
							</td>
						
						</tr>
					
					</tbody>
					
				</table>	
			
				<p class="submit">
				
					<input id="save-rb-data" class="button-primary" type="submit" value="<?php _e( 'Save Options', 'rollerblade' ); ?>" />
				
				</p>
			
			</form>
			
			<script type="text/javascript">
			
				jQuery(function($) {

					function mobileViewMenu() {

						$( '#rb-options-header-links' ).css({ display: 'none', height: '0px', width: window.innerWidth, left: '-' + ( window.innerWidth - 49 ) + 'px' });
							
						$( '#rb-options-header-text-wrapper' ).hover(function() {
								
							$( '#rb-options-header-links' ).css({ display: 'block' }).stop().animate({
								height: '256px'
							}, 'slow');
								
						}, function() {
								
							$( '#rb-options-header-links' ).stop().animate({
								height: '0px'
							}, 'slow', function() {
								$( '#rb-options-header-links' ).css({ display: 'none' });
							});
								
						});
						
					}
					
					//accordion menu for mobile version
					$( window ).resize(function() {

						if ( window.innerWidth <= 480 ) {

							mobileViewMenu();

						} else {

							//discard all the dynamic changes, made for mobile view
							$( '#rb-options-header-text-wrapper, #rb-options-header-links' ).off( 'hover' ).removeAttr( 'style' );
							
						}
							
					}).resize();
					
				});
			
			</script>
			
			<?php 
		
	}
	
}

//initialize plugin
add_action( 'init', array( 'Rollerblade', 'initialize_plugin' ) );

register_activation_hook( __FILE__, array( 'Rollerblade', 'activate' ) );

