<?php
/**
 * Theme functions
 *
 * Functions for users.
 * For Admin's one is defined in admin-functions.php.
 * Set up post types, register rest api endpoint, and enqueue stylesheets and js for users.
 *
 * @package simplevideomanual
 */

/**
 * Class Svm_Function
 */
class Svm_Function {

	/**
	 * Initialize.
	 */
	public function init() {
		add_action( 'init', array( $this, 'add_custom_post_type' ) );
		add_action( 'rest_api_init', array( $this, 'add_rest_functions' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'action_wp_enqueue_scripts' ) );
		add_action( 'after_setup_theme', array( $this, 'action_after_setup_theme' ) );

		// hide admin header.
		add_filter(
			'show_admin_bar',
			function() {
				return false;
			}
		);
	}

	/**
	 * Set up theme languages Domain Path.
	 */
	public function action_after_setup_theme() {
		load_theme_textdomain( 'simplevideomanual', get_template_directory() . '/languages' );
	}

	/**
	 * Set up post type.
	 */
	public function add_custom_post_type() {
		register_post_type(
			Svm_Const::POST_TYPE_MANUAL,
			array(
				'label'         => __( 'Manuals', 'simplevideomanual' ),
				'labels'        => array(
					'name'          => __( 'Manuals', 'simplevideomanual' ),
					'singular_name' => __( 'Manual', 'simplevideomanual' ),
					'menu_name'     => __( 'Manual', 'simplevideomanual' ),
					'all_items'     => __( 'Manual list', 'simplevideomanual' ),
				),
				'description'   => __( 'post presents a manual book.', 'simplevideomanual' ),
				'public'        => true,
				'has_archive'   => true,
				'supports'      => array( 'title', 'editor', 'revisions' ),
				// bottom of post menu.
				'menu_position' => 5,
			)
		);

		register_post_type(
			Svm_Const::POST_TYPE_PAGE,
			array(
				'label'         => __( 'Manual Pages', 'simplevideomanual' ),
				'labels'        => array(
					'name'          => __( 'Manual Pages', 'simplevideomanual' ),
					'singular_name' => __( 'Manual Page', 'simplevideomanual' ),
					'menu_name'     => __( 'Manual Page', 'simplevideomanual' ),
					'all_items'     => __( 'Manual Page list', 'simplevideomanual' ),
				),
				'description'   => __( 'post presents a manual page.', 'simplevideomanual' ),
				'public'        => true,
				'has_archive'   => true,
				'supports'      => array( 'title', 'editor', 'revisions' ),
				// bottom of post menu.
				'menu_position' => 5,
			)
		);
	}

	/**
	 * WP REST callback - /manuals.
	 *
	 * @param array $data parameters.
	 *
	 * @return array REST response.
	 */
	public function rest_callback_manuals( $data ) {
		$id        = $data->get_param( 'id' );
		$post_vars = array();

		if ( isset( $_SERVER['REQUEST_METHOD'] ) && ( 'POST' === $_SERVER['REQUEST_METHOD'] ) ) {
			/**
			 * Check nonce.
			 *
			 * @see https://developer.wordpress.org/rest-api/using-the-rest-api/authentication
			 */
			if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'wp_rest' ) ) {
				return rest_ensure_response( new WP_Error( 500, esc_html( 'Unable to get manuals.' ) ) );
			}
			$post_vars = wp_unslash( $_POST );
		}

		$utils = new Svm_Utils();
		$posts = $utils->build_rest_manuals_data( $id, $post_vars );

		return rest_ensure_response( $posts );
	}

	/**
	 * WP REST callback - /manuals/<id>/pages
	 *
	 * @param array $data parameters.
	 *
	 * @return array REST response.
	 */
	public function rest_callback_manual_pages( $data ) {
		$id        = $data->get_param( 'id' );
		$post_vars = array( 'video' => true );

		if ( isset( $_SERVER['REQUEST_METHOD'] ) && ( 'POST' === $_SERVER['REQUEST_METHOD'] ) ) {
			/**
			 * Check nonce.
			 *
			 * @see https://developer.wordpress.org/rest-api/using-the-rest-api/authentication
			 */
			if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'wp_rest' ) ) {
				return rest_ensure_response( new WP_Error( 500, esc_html( 'Unable to get pages.' ) ) );
			}
			$post_vars = wp_unslash( $_POST );
		}

		$utils = new Svm_Utils();
		$root  = $utils->build_rest_manual_pages_data( $id, $post_vars );

		return rest_ensure_response( $root );
	}

	/**
	 * WP REST callback - /pages/<id>
	 *
	 * @param array $data parameters.
	 *
	 * @return array REST response.
	 */
	public function rest_callback_pages( $data ) {
		$id = $data->get_param( 'id' );

		$utils = new Svm_Utils();
		$post  = $utils->build_rest_pages_data( $id );

		return rest_ensure_response( $post );
	}

	/**
	 * Add WP REST functions.
	 */
	public function add_rest_functions() {

		// return post meta values, when it's get the post.
		register_rest_field(
			Svm_Const::POST_TYPE_PAGE,
			Svm_Const::META_KEY_NAME_DATA_JSON,
			array(
				'get_callback'    => function ( $object, $field_name, $request ) {
					return get_post_meta( $object['id'], $field_name, true );
				},
				'update_callback' => null,
				'schema'          => null,
			)
		);

		$manuals_list_args = array(
			'methods'  => WP_REST_Server::ALLMETHODS,
			'callback' => array( $this, 'rest_callback_manuals' ),
		);
		register_rest_route( Svm_Const::REST_NAMESPACE, '/manuals.json', $manuals_list_args );

		$manuals_args = array(
			'methods'  => WP_REST_Server::ALLMETHODS,
			'callback' => array( $this, 'rest_callback_manuals' ),
			'args'     => array(
				'id' => array(
					'validate_callback' => function ( $pram, $request, $key ) {
						return is_numeric( $pram );
					},
				),
			),
		);
		register_rest_route( Svm_Const::REST_NAMESPACE, '/manuals/(?P<id>[\d]+).json', $manuals_args );

		// return pages in manual book.
		$manual_pages_args = array(
			'methods'  => WP_REST_Server::ALLMETHODS,
			'callback' => array( $this, 'rest_callback_manual_pages' ),
			'args'     => array(
				'id' => array(
					'validate_callback' => function ( $pram, $request, $key ) {
						return is_numeric( $pram );
					},
				),
			),
		);
		register_rest_route( Svm_Const::REST_NAMESPACE, '/manuals/(?P<id>[\d]+)/pages.json', $manual_pages_args );

		// return page.
		$pages_args = array(
			'methods'  => WP_REST_Server::ALLMETHODS,
			'callback' => array( $this, 'rest_callback_pages' ),
			'args'     => array(
				'id' => array(
					'validate_callback' => function ( $pram, $request, $key ) {
						return is_numeric( $pram );
					},
				),
			),
		);
		register_rest_route( Svm_Const::REST_NAMESPACE, '/pages/(?P<id>[\d]+).json', $pages_args );
	}

	/**
	 * Enqueue stylesheets and scripts.
	 */
	public function action_wp_enqueue_scripts() {
		wp_enqueue_style( 'svm-style1', get_template_directory_uri() . '/assets/user/css/chunk-vendors.css', array(), true );
		wp_enqueue_style( 'svm-style2', get_template_directory_uri() . '/assets/user/css/app.css', array( 'svm-style1' ), true );
		wp_enqueue_script( 'svm-script-app1', get_template_directory_uri() . '/assets/user/js/chunk-vendors.js', array(), true, true );
		wp_enqueue_script( 'svm-script-app2', get_template_directory_uri() . '/assets/user/js/app.js', array( 'svm-script-app1' ), true, true );

		if ( is_single() ) {
			// pass the post properties for Vue.
			$post_id = get_the_ID();
			$utils   = new Svm_Utils();
			$posts   = $utils->build_rest_manuals_data( $post_id );
			$pages   = $utils->build_rest_manual_pages_data( $post_id, array( 'video' => true ) );
			wp_localize_script(
				'svm-script-app2',
				Svm_Const::SCRIPT_ARG_NAME,
				array(
					'locale'                 => get_locale(),
					'svm_book_post_id'       => $post_id,
					'manual_post_data'       => $posts,
					'manual_pages_post_data' => $pages,
				)
			);
		}
	}
}

$function = new Svm_Function();
$function->init();
