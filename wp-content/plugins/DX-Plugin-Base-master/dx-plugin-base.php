<?php
/**
 * Plugin Name: DX Plugin Base
 * Description: A plugin framework for building new WordPress plugins reusing the accepted APIs and best practices
 * Plugin URI: http://example.org/
 * Author: nofearinc
 * Author URI: http://devwp.eu/
 * Version: 1.6
 * Text Domain: dx-sample-plugin
 * License: GPL2

 Copyright 2011 mpeshev (email : mpeshev AT devrix DOT com)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License, version 2, as
 published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Get some constants ready for paths when your plugin grows 
 * 
 */

define( 'DXP_VERSION', '1.6' );
define( 'DXP_PATH', dirname( __FILE__ ) );
define( 'DXP_PATH_INCLUDES', dirname( __FILE__ ) . '/inc' );
define( 'DXP_FOLDER', basename( DXP_PATH ) );
define( 'DXP_URL', plugins_url() . '/' . DXP_FOLDER );
define( 'DXP_URL_INCLUDES', DXP_URL . '/inc' );


/**
 * 
 * The plugin base class - the root of all WP goods!
 * 
 * @author nofearinc
 *
 */
class DX_Plugin_Base {
	
	/**
	 * 
	 * Assign everything as a call from within the constructor
	 */
	public function __construct() {
		// add script and style calls the WP way 
		// it's a bit confusing as styles are called with a scripts hook
		// @blamenacin - http://make.wordpress.org/core/2011/12/12/use-wp_enqueue_scripts-not-wp_print_styles-to-enqueue-scripts-and-styles-for-the-frontend/
		add_action( 'wp_enqueue_scripts', array( $this, 'dx_add_JS' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'dx_add_CSS' ) );
		
		// add scripts and styles only available in admin
		add_action( 'admin_enqueue_scripts', array( $this, 'dx_add_admin_JS' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'dx_add_admin_CSS' ) );
		
		// register admin pages for the plugin
		add_action( 'admin_menu', array( $this, 'dx_admin_pages_callback' ) );
		
		// register meta boxes for Pages (could be replicated for posts and custom post types)
		add_action( 'add_meta_boxes', array( $this, 'dx_meta_boxes_callback' ) );
		
		// register save_post - this to save the data of student meta_box
		add_action( 'save_post', array( $this, 'student_meta_save' ) );
		
		// Register custom post types and taxonomies
		add_action( 'init', array( $this, 'dx_custom_post_types_callback' ), 5 );
		add_action( 'init', array( $this, 'dx_custom_taxonomies_callback' ), 6 );
		
		// Register activation and deactivation hooks
		register_activation_hook( __FILE__, 'dx_on_activate_callback' );
		register_deactivation_hook( __FILE__, 'dx_on_deactivate_callback' );
		
		// Translation-ready
		add_action( 'plugins_loaded', array( $this, 'dx_add_textdomain' ) );
		
		// Add earlier execution as it needs to occur before admin page display
		add_action( 'admin_init', array( $this, 'dx_register_settings' ), 5 );
		
		// Add a sample shortcode
		add_action( 'init', array( $this, 'dx_student_shortcode' ) );
		
		// Add a sample widget
		add_action( 'widgets_init', array( $this, 'dx_student_widget' ) );
		add_action( 'widgets_init', array( $this, 'jerome_widget' ) );
		
		// Page Template
		add_action( 'template_include', array( $this, 'load_post_type_templates'), 1 );

		// REST API 

		/*
		 * TODO:
		 * 		template_redirect
		 */
		
		// Add actions for storing value and fetching URL
		// use the wp_ajax_nopriv_ hook for non-logged users (handle guest actions)
 		add_action( 'wp_ajax_store_ajax_value', array( $this, 'store_ajax_value' ) );
 		add_action( 'wp_ajax_fetch_ajax_url_http', array( $this, 'fetch_ajax_url_http' ) );
		
	}	
	
	/**
	 * 
	 * Adding JavaScript scripts
	 * 
	 * Loading existing scripts from wp-includes or adding custom ones
	 * 
	 */
	public function dx_add_JS() {
		wp_enqueue_script( 'jquery' );
		// load custom JSes and put them in footer
		wp_register_script( 'samplescript', plugins_url( '/js/samplescript.js' , __FILE__ ), array('jquery'), '1.0', true );
		wp_enqueue_script( 'samplescript' );
	}
	
	
	/**
	 *
	 * Adding JavaScript scripts for the admin pages only
	 *
	 * Loading existing scripts from wp-includes or adding custom ones
	 *
	 */
	public function dx_add_admin_JS( $hook ) {
		wp_enqueue_script( 'jquery' );
		wp_register_script( 'samplescript-admin', plugins_url( '/js/samplescript-admin.js' , __FILE__ ), array('jquery'), '1.0', true );
		wp_enqueue_script( 'samplescript-admin' );
	}
	
	/**
	 * 
	 * Add CSS styles
	 * 
	 */
	public function dx_add_CSS() {
		wp_register_style( 'samplestyle', plugins_url( '/css/samplestyle.css', __FILE__ ), array(), '1.0', 'screen' );
		wp_enqueue_style( 'samplestyle' );
	}
	
	/**
	 *
	 * Add admin CSS styles - available only on admin
	 *
	 */
	public function dx_add_admin_CSS( $hook ) {
		wp_register_style( 'samplestyle-admin', plugins_url( '/css/samplestyle-admin.css', __FILE__ ), array(), '1.0', 'screen' );
		wp_enqueue_style( 'samplestyle-admin' );
		
		if( 'toplevel_page_dx-plugin-base' === $hook ) {
			wp_register_style('dx_help_page',  plugins_url( '/help-page.css', __FILE__ ) );
			wp_enqueue_style('dx_help_page');
		}
	}
	
	/**
	 * 
	 * Callback for registering pages
	 * 
	 * This demo registers a custom page for the plugin and a subpage
	 *  
	 */
	public function dx_admin_pages_callback() {
		add_menu_page(__( "Plugin Base Admin", 'dxbase' ), __( "Plugin Base Admin", 'dxbase' ), 'edit_themes', 'dx-plugin-base', array( $this, 'dx_plugin_base' ) );		
		add_submenu_page( 'dx-plugin-base', __( "Base Subpage", 'dxbase' ), __( "Base Subpage", 'dxbase' ), 'edit_themes', 'dx-base-subpage', array( $this, 'dx_plugin_subpage' ) );
		add_submenu_page( 'dx-plugin-base', __( "Remote Subpage", 'dxbase' ), __( "Remote Subpage", 'dxbase' ), 'edit_themes', 'dx-remote-subpage', array( $this, 'dx_plugin_side_access_page' ) );
	}
	
	/**
	 * 
	 * The content of the base page
	 * 
	 */
	public function dx_plugin_base() {
		include_once( DXP_PATH_INCLUDES . '/base-page-template.php' );
	}
	
	public function dx_plugin_side_access_page() {
		include_once( DXP_PATH_INCLUDES . '/remote-page-template.php' );
	}
	/**
	 * 
	 * The content of the subpage 
	 * 
	 * Use some default UI from WordPress guidelines echoed here (the sample above is with a template)
	 * 
	 * @see http://www.onextrapixel.com/2009/07/01/how-to-design-and-style-your-wordpress-plugin-admin-panel/
	 *
	 */
	public function dx_plugin_subpage() {
		echo '<div class="wrap">';
		_e( "<h2>DX Plugin Subpage</h2> ", 'dxbase' );
		_e( "I'm a subpage and I know it!", 'dxbase' );
		echo '</div>';
	}
	
	/**
	 * 
	 *  Adding right and bottom meta boxes to Pages
	 *   
	 */
	public function dx_meta_boxes_callback() {
		// register side box
		add_meta_box( 
		        'dx_side_meta_box',
		        __( "DX Side Box", 'dxbase' ),
		        array( $this, 'dx_side_meta_box' ),
		        'pluginbase', // leave empty quotes as '' if you want it on all custom post add/edit screens
		        'side',
		        'high'
		    );
		    
		// register bottom box
		add_meta_box(
		    	'dx_bottom_student_meta_box',
		    	__( "Student Information", 'dxbase' ), 
		    	array( $this, 'dx_bottom_student_meta_box' ),
		    	'student' // leave empty quotes as '' if you want it on all custom post add/edit screens or add a post type slug
		    );
	}
	
	/**
	 * 
	 * Init right side meta box here 
	 * @param post $post the post object of the given page 
	 * @param metabox $metabox metabox data
	 */
	public function dx_side_meta_box( $post, $metabox) {
		_e("<p>Side meta content here</p>", 'dxbase');
		
		// Add some test data here - a custom field, that is
		$dx_test_input = '';
		if ( ! empty ( $post ) ) {
			// Read the database record if we've saved that before
			$dx_test_input = get_post_meta( $post->ID, 'dx_test_input', true );
		}
		?>
		<label for="dx-test-input"><?php _e( 'Test Custom Field', 'dxbase' ); ?></label>
		<input type="text" id="dx-test-input" name="dx_test_input" value="<?php echo $dx_test_input; ?>" />
		<?php
	}
	
	/**
	 * Save the custom field from the side metabox
	 * @param $post_id the current post ID
	 * @return post_id the post ID from the input arguments
	 * 
	 */
	public function dx_save_sample_field( $post_id ) {
		// Avoid autosaves
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$slug = 'student';
		// If this isn't a 'book' post, don't update it.
		if ( ! isset( $_POST['post_type'] ) || $slug != $_POST['post_type'] ) {
			return;
		}
		
		// If the custom field is found, update the postmeta record
		// Also, filter the HTML just to be safe
		if ( isset( $_POST['dx_test_input']  ) ) {
			update_post_meta( $post_id, 'dx_test_input',  esc_html( $_POST['dx_test_input'] ) );
		}
	}
	
	/**
	 * 
	 * Init bottom meta box here 
	 * @param post $post the post object of the given page 
	 * @param metabox $metabox metabox data
	 */
	public function dx_bottom_student_meta_box( $post, $metabox) {
		wp_nonce_field( basename( __FILE__ ), 'student_nonce' );
		$student_stored_meta = get_post_meta( $post->ID);

		// retrieve student data
		?>
		<style>
			#dx_bottom_student_meta_box .inside input {
			    display: block;
			}
			#dx_bottom_student_meta_box .inside {
			    background-color: #333;
			    color: #fff;
			}
		</style>
		<label for="student_year" class="student-info">Student Year:</label>
		<input type="number" min="1" max="5" name="student_year" value="<?php if ( ! empty ( $student_stored_meta['student_year'] ) ){echo esc_attr( $student_stored_meta['student_year'][0] );} ?>"><br>

		<label for="student_section" class="student-info">Student Section:</label>
		<input type="text" maxlength="50" name="student_section" value="<?php if ( ! empty ( $student_stored_meta['student_section'] ) ){echo esc_attr( $student_stored_meta['student_section'][0] );} ?>"><br>

		<label for="student_address" class="student-info">Student Address:</label>
		<input class="student-address" type="text" maxlength="100" name="student_address" value="<?php if ( ! empty ( $student_stored_meta['student_address'] ) ){echo esc_attr( $student_stored_meta['student_address'][0] );} ?>"><br>

		<label for="student_id" class="student-info">Student ID:</label>
		<input type="text" maxlength="20" name="student_id" value="<?php if ( ! empty ( $student_stored_meta['student_id'] ) ){echo esc_attr( $student_stored_meta['student_id'][0] );} ?>"><br>

	<?php

	}

	/**
	 *Saving meta data of student
	 */
	public function student_meta_save( $post_id ) {
		  // Checks save status
		    $is_autosave = wp_is_post_autosave( $post_id );
		    $is_revision = wp_is_post_revision( $post_id );
		    $is_valid_nonce = ( isset( $_POST[ 'student_nonce' ] ) && wp_verify_nonce( $_POST[ 'student_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
		    // Exits script depending on save status
		    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
		        return;
		    }
		    if ( isset( $_POST[ 'student_year' ] ) ) {
		      update_post_meta( $post_id, 'student_year', sanitize_text_field( $_POST[ 'student_year' ] ) );
		    }
		    if ( isset( $_POST[ 'student_section' ] ) ) {
		      update_post_meta( $post_id, 'student_section', sanitize_text_field( $_POST[ 'student_section' ] ) );
		    }
		    if ( isset( $_POST[ 'student_address' ] ) ) {
		      update_post_meta( $post_id, 'student_address', sanitize_text_field( $_POST[ 'student_address' ] ) );
		    }
		    if ( isset( $_POST[ 'student_id' ] ) ) {
		      update_post_meta( $post_id, 'student_id', sanitize_text_field( $_POST[ 'student_id' ] ) );
		    }
	}
	
	/**
	 * Register custom post types
     *
	 */
	public function dx_custom_post_types_callback() {
		register_post_type( 'student', array(
			'labels' => array(
				'name' => __("Students", 'dxbase'),
				'singular_name' => __("Student", 'dxbase'),
				'add_new' => _x("Add New", 'pluginbase', 'dxbase' ),
				'add_new_item' => __("Add New Student", 'dxbase' ),
				'edit_item' => __("Edit Student", 'dxbase' ),
				'new_item' => __("New Student", 'dxbase' ),
				'view_item' => __("View Student", 'dxbase' ),
				'search_items' => __("Search Student", 'dxbase' ),
				'not_found' =>  __("No student found", 'dxbase' ),
				'not_found_in_trash' => __("No student found in Trash", 'dxbase' ),
			),
			'description' => __("Students for the demo", 'dxbase'),
			'public' => true,
			'publicly_queryable' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'student' ),
			'show_in_rest'       => true,
	  		/*'rest_base'          => 'student-api',
	  		'rest_controller_class' => 'WP_REST_Posts_Controller',*/
			'exclude_from_search' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 40, // probably have to change, many plugins use this
			'menu_icon' => 'dashicons-universal-access-alt',
			'supports' => array(
				'title',
				'editor',
				'thumbnail',
				'custom-fields',
				'page-attributes',
			),
			'taxonomies' => array( 'post_tag' )
		));	
	}
	
	
	/**
	 * Register custom taxonomies
     *
	 */
	public function dx_custom_taxonomies_callback() {
		register_taxonomy( 'student_taxonomy', 'pluginbase', array(
			'hierarchical' => true,
			'labels' => array(
				'name' => _x( "Student Taxonomies", 'taxonomy general name', 'dxbase' ),
				'singular_name' => _x( "Student Taxonomy", 'taxonomy singular name', 'dxbase' ),
				'search_items' =>  __( "Search Taxonomies", 'dxbase' ),
				'popular_items' => __( "Popular Taxonomies", 'dxbase' ),
				'all_items' => __( "All Taxonomies", 'dxbase' ),
				'parent_item' => null,
				'parent_item_colon' => null,
				'edit_item' => __( "Edit Student Taxonomy", 'dxbase' ), 
				'update_item' => __( "Update Student Taxonomy", 'dxbase' ),
				'add_new_item' => __( "Add New Student Taxonomy", 'dxbase' ),
				'new_item_name' => __( "New Student Taxonomy Name", 'dxbase' ),
				'separate_items_with_commas' => __( "Separate Student taxonomies with commas", 'dxbase' ),
				'add_or_remove_items' => __( "Add or remove Student taxonomy", 'dxbase' ),
				'choose_from_most_used' => __( "Choose from the most used Student taxonomies", 'dxbase' )
			),
			'show_ui' => true,
			'query_var' => true,
			'rewrite'           => array( 'slug' => 'student' ),
	  		/*'show_in_rest'       => true,
	  		'rest_base'          => 'genre',
	  		'rest_controller_class' => 'WP_REST_Terms_Controller',*/
		));
		
		register_taxonomy_for_object_type( 'pluginbase_taxonomy', 'pluginbase' );
	}
	/***************************   R E S T - A P I *************************************/
	//The Following registers an api route with multiple parameters. 
	 public function wpt_all_student() {
	register_rest_route( '/wp/v2', '/get/allstudent', array(
        'methods' => 'GET',
        'callback' => 'all_student_callback',
    ));
	}

	function all_student_callback( $request_data ) {
		$r = array();
		$parameters = $request_data->get_params();
		$args = array('post_type' => 'student', 'post_status' => 'publish');
		$loop = new WP_Query( $args );

		while ( $loop->have_posts() ) : $loop->the_post();
			$r[] = array(
				'post_id' => get_the_ID(),
				'name' => get_the_title(),
				'description' => get_the_content(),
				'student_year' => get_post_meta(get_the_ID(), 'student_year', true),
				'student_section' => get_post_meta(get_the_ID(), 'student_section', true),
				'student_address' => get_post_meta(get_the_ID(), 'student_address', true),
				'student_id' => get_post_meta(get_the_ID(), 'student_id', true)
			);
		endwhile;
		return $r;
	}

	function meta_data($post_id, $field, $value = '') {
		if (empty( $value ) || !$value) {
			delete_post_meta( $post_id, $field );
		} elseif (!get_post_meta($post_id, $field)) {
			add_post_meta($post_id, $field, $value);
		} else {
			update_post_meta($post_id, $field, $value);
		}
	}


	/***************************   /R E S T - A P I *************************************/


	/***************************   CUSTOM PAGE TEMPLATE  *************************************/
	
	public function load_post_type_templates( $original_template ) {

     if ( get_query_var( 'post_type' ) == 'student' ) {

          if ( is_archive() || is_search() ) {

		           if ( file_exists( get_stylesheet_directory(). '/archive.php' ) ) {

		                 return get_stylesheet_directory() . '/archive.php';

		           } else {

		                  return plugin_dir_path( __FILE__ ) . 'inc/archive.php';

		           }

		       } elseif(is_singular('student')) {

	               if (  file_exists( get_stylesheet_directory(). '/single-student.php' ) ) {

	                       return get_stylesheet_directory() . '/single-student.php';

	               } else {

	                       return plugin_dir_path( __FILE__ ) . 'inc/single-student.php';
	               }

		       }else{

	       			return get_page_template();

	       		}
       }
        return $original_template;
	}

	/***************************   /CUSTOM PAGE TEMPLATE *************************************/

	/**
	 * Initialize the Settings class
	 * 
	 * Register a settings section with a field for a secure WordPress admin option creation.
	 * 
	 */
	public function dx_register_settings() {
		require_once( DXP_PATH . '/dx-plugin-settings.class.php' );
		new DX_Plugin_Settings();
	}
	
	/**
	 * Register a sample shortcode to be used
	 * 
	 * First parameter is the shortcode name, would be used like: [dxsampcode]
	 * 
	 */
	public function dx_student_shortcode() {
		add_shortcode( 'studentcode', array( $this, 'dx_student_shortcode_body' ) );
	}
	
	/**
	 * Returns the content of the sample shortcode, like [dxsamplcode]
	 * @param array $attr arguments passed to array, like [dxsamcode attr1="one" attr2="two"]
	 * @param string $content optional, could be used for a content to be wrapped, such as [dxsamcode]somecontnet[/dxsamcode]
	 */
	public function dx_student_shortcode_body( $attr, $content = null ) {

			$studentpost = array( 
				'post_type' => 'student', 
				'post_status' => 'publish'
			);

			$loop = new WP_Query( $studentpost );

			while ( $loop->have_posts() ) : $loop->the_post(); ?>

		            <div class="container">
		                <strong>Name: </strong><?php the_title(); ?><br />
		                <strong>ID: </strong>
		                <?php echo esc_html( get_post_meta( get_the_ID(), 'student_id', true ) ); ?>
		                <br />
		                <strong>Section: </strong>
		                <?php echo esc_html( get_post_meta( get_the_ID(), 'student_section', true ) ); ?>
		                <br />
		                <strong>Year: </strong>
		                <?php echo esc_html( get_post_meta( get_the_ID(), 'student_year', true ) ); ?>
		                <br />
		                <strong>Address: </strong>
		                <?php echo esc_html( get_post_meta( get_the_ID(), 'student_address', true ) ); ?>
		                <br />
						<p>Summary: <?php the_content(); ?></p>
		            </div>

			<?php endwhile; 

		/*
		 * Manage the attributes and the content as per your request and return the result
		       shortcode -> [studentcode]
		 */
		return __( 'Sample Output', 'dxbase');
	}
	
	/**
	 * Hook for including a sample widget with options
	 */
	public function dx_student_widget() {
		include_once DXP_PATH_INCLUDES . '/dx-student-widget.class.php';
	}
	public function jerome_widget() {
		include_once DXP_PATH_INCLUDES . '/jerome-widget.class.php';
	}
	/**
	 * Add textdomain for plugin
	 */


	public function dx_add_textdomain() {
		load_plugin_textdomain( 'dxbase', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}
	
	/**
	 * Callback for saving a simple AJAX option with no page reload
	 */
	public function store_ajax_value() {
		if( isset( $_POST['data'] ) && isset( $_POST['data']['dx_option_from_ajax'] ) ) {
			update_option( 'dx_option_from_ajax' , $_POST['data']['dx_option_from_ajax'] );
		}	
		die();
	}
	
	/**
	 * Callback for getting a URL and fetching it's content in the admin page
	 */
	public function fetch_ajax_url_http() {
		if( isset( $_POST['data'] ) && isset( $_POST['data']['dx_url_for_ajax'] ) ) {
			$ajax_url = $_POST['data']['dx_url_for_ajax'];
			
			$response = wp_remote_get( $ajax_url );
			
			if( is_wp_error( $response ) ) {
				echo json_encode( __( 'Invalid HTTP resource', 'dxbase' ) );
				die();
			}
			
			if( isset( $response['body'] ) ) {
				if( preg_match( '/<title>(.*)<\/title>/', $response['body'], $matches ) ) {
					echo json_encode( $matches[1] );
					die();
				}
			}
		}
		echo json_encode( __( 'No title found or site was not fetched properly', 'dxbase' ) );
		die();
	}
	
}


/**
 * Register activation hook
 *
 */
function dx_on_activate_callback() {
	// do something on activation
}

/**
 * Register deactivation hook
 *
 */
function dx_on_deactivate_callback() {
	// do something when deactivated
}

// Initialize everything
$dx_plugin_base = new DX_Plugin_Base();


/**************************************************************/
// GET ALL STUDENT DATA

function dx_api_get_all_student() {
	register_rest_route( '/wp/v2', '/get/allstudent', array(
        'methods' => 'GET',
        'callback' => 'get_all_student_callback',
    ));
}


function get_all_student_callback( $request_data ) {
	$r = array();
	$parameters = $request_data->get_params();
	$args = array('post_type' => 'student', 'post_status' => 'publish');
	$loop = new WP_Query( $args );

	while ( $loop->have_posts() ) : $loop->the_post();
		$r[] = array(
			'post_id' => get_the_ID(),
			'name' => get_the_title(),
			'description' => get_the_content(),
			'student_year' => get_post_meta(get_the_ID(), 'student_year', true),
			'student_section' => get_post_meta(get_the_ID(), 'student_section', true),
			'student_address' => get_post_meta(get_the_ID(), 'student_address', true),
			'student_id' => get_post_meta(get_the_ID(), 'student_id', true)
		);
	endwhile;
	return $r;
}

add_action( 'rest_api_init', 'dx_api_get_all_student' );

function meta_data($post_id, $field, $value = '') {
	if (empty( $value ) || !$value) {
		delete_post_meta( $post_id, $field );
	} elseif (!get_post_meta($post_id, $field)) {
		add_post_meta($post_id, $field, $value);
	} else {
		update_post_meta($post_id, $field, $value);
	}
}

/***********************************************************************************/

// GET ADD STUDENT 

function dx_api_add_student() {
	register_rest_route( '/wp/v2', '/get/addstudent', array(
        'methods' => 'GET',
        'callback' => 'get_add_student_callback',
    ));
}
add_action( 'rest_api_init', 'dx_api_add_student' );

function get_add_student_callback( $request_data ) {
	$j['status'] = 'fail';
	$parameters = $request_data->get_params();
	$student_stored_meta['student_year'] = isset($parameters['student_year']) ? $parameters['student_year'] : '';
	$student_stored_meta['student_section'] = isset($parameters['student_section']) ? $parameters['student_section'] : '';
	$student_stored_meta['student_address'] = isset($parameters['student_address']) ? $parameters['student_address'] : '';
	$student_stored_meta['student_id'] = isset($parameters['student_id']) ? $parameters['student_id'] : '';

	if (isset($parameters['title']) && isset($parameters['content'])) {
		$post = array(
			'post_title' => $parameters['title'],
			'post_content' => $parameters['content'],
			'post_status' => 'publish',
			'post_type' => 'student'
		);
		$post_id = wp_insert_post($post);
		if ($post_id) {
			foreach ($student_stored_meta as $key => $value) {
				meta_data($post_id, $key, $value);
			}
			$j['status'] = 'success';
		}
	}
	return $j;
}

/***********************************************************************************/

// EDIT STUDENT

function dx_api_edit_student() {
	register_rest_route( '/wp/v2', '/get/editstudent', array(
        'methods' => 'GET',
        'callback' => 'get_edit_student_callback',
    ));
}
add_action( 'rest_api_init', 'dx_api_edit_student' );

function get_edit_student_callback( $request_data ) {
	$j['status'] = 'fail';
	$parameters = $request_data->get_params();
	$student_meta = array();

	if (isset($parameters['post_id'])) {
		if (isset($parameters['student_year']))
			$student_meta['student_year'] = $parameters['student_year'];

		if (isset($parameters['student_section']))
			$student_meta['student_section'] = $parameters['student_section'];

		if (isset($parameters['student_address']))
			$student_meta['student_address'] = $parameters['student_address'];

		if (isset($parameters['student_id']))
			$student_meta['student_id'] = $parameters['student_id'];

		if (isset($parameters['title']) && isset($parameters['content'])) {
			$post = array(
				'ID' => $parameters['post_id'],
				'post_title' => $parameters['title'],
				'post_content' => $parameters['content'],
				'post_status' => 'publish',
				'post_type' => 'student'
			);

			$post_id = wp_insert_post($post);
			if ($post_id) {
				$j['status'] = 'success';
				if (!empty($student_meta)) {
					foreach ($student_meta as $key => $value) {
						meta_data($post_id, $key, $value);
					}
				}
			}
		}
	} else {
		$j['xmessage'] = 'bad request';
	}
	return $j;
}

/***********************************************************************************/

// Delete Student 

function dx_api_delete_student() {
	register_rest_route( '/wp/v2', '/get/deletestudent', array(
        'methods' => 'GET',
        'callback' => 'get_delete_student_callback',
    ));
}
add_action( 'rest_api_init', 'dx_api_delete_student' );

function get_delete_student_callback( $request_data ) {
	$j['status'] = 'fail';
	$parameters = $request_data->get_params();
	$student_meta = array(
		'student_year',
		'student_section',
		'student_address',
		'student_id'
	);

	if (isset($parameters['post_id'])) {
		$deleted = wp_delete_post($parameters['post_id'], true);
		if ($deleted) {
			$j['status'] = 'success';
			for ($i=0; $i < count($student_meta); $i++) {
				meta_data($parameters['post_id'], $student_meta[$i]);
			}
		}
	}
	return $j;
}

/***********************************************************************************/

// GET BY ID

function dx_api_get_student() {
	register_rest_route( '/wp/v2', '/get/student', array(
        'methods' => 'GET',
        'callback' => 'get_student_callback',
    ));
}
add_action( 'rest_api_init', 'dx_api_get_student' );

function get_student_callback( $request_data ) {
	$j['status'] = 'fail';
	$parameters = $request_data->get_params();
	if (isset($parameters['post_id'])) {
		$post = get_post($parameters['post_id']);
		if ($post) {
			$j['status'] = 'success';
			$j['data'] = array(
				'post_id' => $post->ID,
				'name' => $post->post_title,
				'description' => $post->post_content,
				'student_year' => get_post_meta($post->ID, 'student_year', true),
				'student_section' => get_post_meta($post->ID, 'student_section', true),
				'student_address' => get_post_meta($post->ID, 'student_address', true),
				'student_id' => get_post_meta($post->ID, 'student_id', true)
			);
		}
	}
	return $j;
}