<?php
/**
 * Theme Functions on admin view
 *
 * This Function Class is used in admin view, so initialize method is called only that.
 *
 * Functions for admin.
 *
 * @package simplevideomanual
 */

/**
 * Class Svm_Admin_Function
 */
class Svm_Admin_Function {

	const NONCE_ACTION             = 'svm_metabox';
	const NONCE_NAME               = 'svm_metabox_nonce';
	const NONCE_ACTION_PAGE_EXPORT = 'svm_export';
	const NONCE_NAME_PAGE_EXPORT   = 'svm_export_nonce';

	const INPUT_FIELD_NAME_DATA_JSON      = 'svm_description_json';
	const INPUT_FIELD_NAME_BOOK_ID        = 'svm_book_post_id';
	const INPUT_FIELD_NAME_PARENT_PAGE_ID = 'svm_parent_page_post_id';

	const INPUT_FIELD_NAME_EXPORT_BOOK_POST_ID = 'svm_book_post_id';
	const INPUT_FIELD_NAME_EXPORT_INC_MEDIA    = 'svm_include_media';

	const BACKUP_EXPORT_FILES_COUNT = 10;

	/**
	 * Set up hook and filter.
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'admin_menu_action' ) );
		add_action(
			'wp_ajax_svm_export_static_page_files',
			array(
				$this,
				'action_wp_ajax_svm_export_static_page_files',
			)
		);
		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
		add_action( 'load-post.php', array( $this, 'init_for_post_view' ) );
		add_action( 'load-post-new.php', array( $this, 'init_for_post_view' ) );
	}

	/**
	 * Set up hook and filter for post views.
	 */
	public function init_for_post_view() {
		add_action( 'add_meta_boxes_' . Svm_Const::POST_TYPE_MANUAL, array( $this, 'action_add_meta_box_manual' ) );
		add_action( 'add_meta_boxes_' . Svm_Const::POST_TYPE_PAGE, array( $this, 'action_add_meta_box_page' ) );
		add_action( 'save_post', array( $this, 'action_save_post' ) );
		add_action( 'admin_footer-post.php', array( $this, 'action_admin_footer_post_php' ) );
	}

	/**
	 * Add sub-menu to svm_book post menu.
	 *
	 * This menu is only used for 'function', so CSS sets display:none;
	 */
	public function admin_menu_action() {
		add_submenu_page(
			'edit.php?post_type=svm_book',
			__( 'Export Manual Page Files', 'simplevideomanual' ),
			__( 'Export Manual Page Files', 'simplevideomanual' ),
			'export',
			'svm-export-static-page-files',
			array( $this, 'menu_export_static_page_files_function' )
		);
		// disable customize menu
		global $submenu;
		if ( Svm_Const::THEME_NAME === get_template() ) {
			_log( get_template() );
			if ( isset( $submenu['themes.php'] ) ) {
				foreach ( $submenu['themes.php'] as $index => $menu_item ) {
					foreach ( $menu_item as $value ) {
						if ( strpos( $value, 'customize' ) !== false ) {
							unset( $submenu['themes.php'][ $index ] );
						}
					}
				}
			}
		}
	}

	/**
	 * Start to export static page files.
	 *
	 * Render a progress bar and scripts.
	 * Scripts call export process via ajax.
	 *
	 * @see: /wp-admin/admin.php?page=svm-export-static-page-files
	 */
	public function menu_export_static_page_files_function() {
		// check permissions.
		if ( ! current_user_can( 'export' ) ) {
			wp_die( esc_html__( 'No permission. Check the authority.', 'simplevideomanual' ), esc_html__( 'Failure | A Simple Video Manual', 'simplevideomanual' ), 403 );

			return;
		}

		// check nonce.
		if ( ! isset( $_POST[ self::NONCE_NAME_PAGE_EXPORT ] ) || ! wp_verify_nonce( sanitize_key( $_POST[ self::NONCE_NAME_PAGE_EXPORT ] ), self::NONCE_ACTION_PAGE_EXPORT ) ) {
			wp_die( esc_html__( 'Unable to export. Retry.', 'simplevideomanual' ), esc_html__( 'Failure | A Simple Video Manual', 'simplevideomanual' ), 403 );

			return;
		}

		// check parameters.
		$post_vars = wp_unslash( $_POST );

		$book_post_id  = isset( $post_vars[ self::INPUT_FIELD_NAME_EXPORT_BOOK_POST_ID ] ) ? $post_vars[ self::INPUT_FIELD_NAME_EXPORT_BOOK_POST_ID ] : false;
		$include_media = isset( $post_vars[ self::INPUT_FIELD_NAME_EXPORT_INC_MEDIA ] ) ? $post_vars[ self::INPUT_FIELD_NAME_EXPORT_INC_MEDIA ] : false;
		if ( false === $book_post_id ) {
			wp_die( esc_html__( 'Unable to export. Retry.', 'simplevideomanual' ), esc_html__( 'Failure | A Simple Video Manual', 'simplevideomanual' ), 403 );

			return;
		}

		// pass js variables.
		wp_localize_script(
			'svm-script-admin2',
			Svm_Const::SCRIPT_ARG_NAME,
			array(
				'locale'         => get_locale(),
				'ajax_call_data' => array(
					'action'                     => 'svm_export_static_page_files',
					self::NONCE_NAME_PAGE_EXPORT => wp_create_nonce( self::NONCE_ACTION_PAGE_EXPORT ),
					self::INPUT_FIELD_NAME_EXPORT_BOOK_POST_ID => $book_post_id,
					self::INPUT_FIELD_NAME_EXPORT_INC_MEDIA => $include_media,
					'process_step'               => '1',
				),
				'admin_ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);

		echo '<div class="svm-Exp"></div>';
	}

	/**
	 * Export static page files.
	 *
	 * @see: /wp-admin/admin.php?page=svm-export-static-page-files
	 */
	public function action_wp_ajax_svm_export_static_page_files() {

		$send_error = function ( $message ) {
			$data = array(
				'message' => $message,
			);
			wp_send_json_error( $data );
		};

		// check permissions.
		if ( ! current_user_can( 'export' ) ) {
			$send_error( __( 'Unable to create files. Retry.', 'simplevideomanual' ) );
		}

		// check nonce.
		if ( ! isset( $_POST[ self::NONCE_NAME_PAGE_EXPORT ] ) || ! wp_verify_nonce( sanitize_key( $_POST[ self::NONCE_NAME_PAGE_EXPORT ] ), self::NONCE_ACTION_PAGE_EXPORT ) ) {
			$send_error( __( 'Unable to create files. Retry.', 'simplevideomanual' ) );
		}

		$post_vars = wp_unslash( $_POST );

		$book_post_id  = isset( $post_vars[ self::INPUT_FIELD_NAME_EXPORT_BOOK_POST_ID ] ) ? $post_vars[ self::INPUT_FIELD_NAME_EXPORT_BOOK_POST_ID ] : false;
		$include_media = isset( $post_vars[ self::INPUT_FIELD_NAME_EXPORT_INC_MEDIA ] ) ? $post_vars[ self::INPUT_FIELD_NAME_EXPORT_INC_MEDIA ] : false;
		if ( false === $book_post_id ) {
			$send_error( __( 'Unable to create files. Retry.', 'simplevideomanual' ) );
		}

		// start to export.
		try {
			$process_step = isset( $post_vars['process_step'] ) ? $post_vars['process_step'] : false;
			$utils        = new Svm_Utils();
			$work_dir     = get_template_directory() . '/admin';
			$tmp_dir      = $work_dir . '/tmp';

			if ( '1' === $process_step ) {
				// mkdir compress working directory.
				if ( file_exists( $tmp_dir ) && ! $utils->rmdir_recursive( $tmp_dir ) ) {
					$send_error( __( 'Unable to remove old files. Retry.', 'simplevideomanual' ) );
				}
				if ( ! mkdir( $tmp_dir, 0777, true ) ) {
					$send_error( __( 'Unable to create files. Retry.', 'simplevideomanual' ) );
				}

				// copy static files to working directory.
				if ( ! $utils->copy_dir_recursive( $work_dir . '/static-page-template', $tmp_dir ) ) {
					$send_error( __( 'Unable to create files. Retry.', 'simplevideomanual' ) );
				}

				wp_send_json_success( array( 'process_step' => '1' ) );
			}

			// start to create files.
			if ( '2' === $process_step ) {
				if ( ! mkdir( $tmp_dir . '/data', 0777, true ) ) {
					$send_error( __( 'Unable to create files. Retry.', 'simplevideomanual' ) );
				}

				// create WordPress config js file.
				$wp_config_script_name = 'js/wp-localize-script.js';
				$posts                 = $utils->build_rest_manuals_data( $book_post_id );
				$pages                 = $utils->build_rest_manual_pages_data( $book_post_id, array( 'video' => true ) );

				// copy video file to work directory if it's checked "include media"
				if ( $include_media ) {
					$copy_replace = function ( $page_post ) use ( $send_error, $tmp_dir, &$copy_replace ) {
						// recursive if page has children.
						if ( ! empty( $page_post->pages ) ) {
							foreach ( $page_post->pages as &$child_page ) {
								$child_page = $copy_replace( $child_page );
							}
						}

						$base_url = $page_post->post_meta['svm_video_meta']['url'];
						if ( empty( $base_url ) ) {
							return;
						}

						// copy video to export directory.
						$base_filename = basename( $base_url );
						$base_path     = rtrim( get_home_path(), '/' ) . str_replace( site_url(), '', $base_url );
						$filename      = $page_post->ID . '_' . $base_filename;
						if ( false === copy( $base_path, $tmp_dir . '/data/' . $filename ) ) {
							$send_error( __( 'Unable to create files. Retry.', 'simplevideomanual' ) );
						}

						// replace path in json.
						$page_post->post_meta['svm_video_meta']['url'] = 'data/' . $filename;

						return $page_post;
					};
					foreach ( $pages as &$page_post ) {
						$page_post = $copy_replace( $page_post );
					}
				}
				$config_args = array(
					'locale'                 => get_locale(),
					'svm_book_post_id'       => $book_post_id,
					'manual_post_data'       => $posts,
					'manual_pages_post_data' => $pages,
				);
				file_put_contents(
					$tmp_dir . '/' . $wp_config_script_name,
					'var ' . Svm_Const::SCRIPT_ARG_NAME . ' = ' . wp_json_encode( $config_args ) . ';'
				);
				// inject config js tag to index.html.
				$html = file_get_contents( $tmp_dir . '/index.html' );
				$html = str_replace( '<div id=app></div>', '<div id=app></div><script src="' . $wp_config_script_name . '"></script>', $html );
				file_put_contents(
					$tmp_dir . '/index.html',
					$html
				);

				wp_send_json_success( array( 'process_step' => '2' ) );
			}

			// compress.
			if ( '3' === $process_step ) {
				$archive_filename = 'svm_' . date( 'YmdHis' ) . '.zip';
				$archive_path     = $work_dir . '/export/' . $archive_filename;

				if ( false === file_exists( dirname( $archive_path ) ) ) {
					if ( false === mkdir( dirname( $archive_path ), 0777, true ) ) {
						$send_error( __( 'Unable to compress. Retry.', 'simplevideomanual' ) );
					}
				}
				if ( false === $utils->zip_dir( $archive_path, $tmp_dir ) ) {
					$send_error( __( 'Unable to compress. Retry.', 'simplevideomanual' ) );
				}

				// get file path array order by new.
				$exported_files = $utils->get_file_path_array(
					$work_dir . '/export',
					function ( $lhs, $rhs ) {
						$l_date = empty( $lhs ) ? '' : str_replace( 'svm_', '', $lhs );
						$r_date = empty( $rhs ) ? '' : str_replace( 'svm_', '', $rhs );

						return strcmp( $r_date, $l_date );
					}
				);
				// remove the path that is created in this process.
				$exported_files = array_filter(
					$exported_files,
					function ( $path ) use ( $archive_filename ) {
						return ( false === strpos( $path, $archive_filename ) );
					}
				);

				// remove old files.
				// '-1' means that considering the file in this time process.
				$old_files = array_slice( $exported_files, ( self::BACKUP_EXPORT_FILES_COUNT - 1 ) );
				foreach ( $old_files as $old_file ) {
					unlink( $old_file );
				}

				$archive_url = get_template_directory_uri() . '/admin/export/' . $archive_filename;
				$archive_url = str_replace( site_url(), '', $archive_url );
				wp_send_json_success(
					array(
						'process_step' => '3',
						'archive_url'  => $archive_url,
					)
				);
			}
		} catch ( Exception $ex ) {
			$send_error( __( 'Unable to export. Retry.', 'simplevideomanual' ) );
		}
	}

	/**
	 * Enqueue script.
	 *
	 * Add jquery, video.js, vue
	 */
	public function action_admin_enqueue_scripts() {
		// ex. /var/www/html/wp-admin/post.php.
		$server               = wp_unslash( $_SERVER );
		$is_ajax_request      = ( false !== strpos( $server['SCRIPT_FILENAME'], 'amdin-ajax.php' ) );
		$is_post_view_request = ( false !== strpos( $server['SCRIPT_FILENAME'], 'post.php' ) )
								|| ( false !== strpos( $server['SCRIPT_FILENAME'], 'post-new.php' ) );

		wp_register_style( 'svm-style-admin2', get_template_directory_uri() . '/assets/admin/css/app.css', array(), Svm_Const::THEME_VER );
		wp_register_script( 'svm-script-admin1', get_template_directory_uri() . '/assets/admin/js/chunk-vendors.js', array( 'jquery' ), true, true );
		wp_register_script( 'svm-script-admin2', get_template_directory_uri() . '/assets/admin/js/app.js', array( 'svm-script-admin1' ), true, true );

		if ( $is_post_view_request ) {
			// for selecting video file.
			wp_enqueue_media();

			wp_enqueue_style( 'svm-style-admin1' );
			wp_enqueue_style( 'svm-style-admin2' );
			wp_enqueue_script(
				'svm-script-admin1',
				false,
				array(
					'media-upload',
					'underscore',
				),
				Svm_Const::THEME_VER,
				true
			);
			wp_enqueue_script( 'svm-script-admin2', false, array(), Svm_Const::THEME_VER, true );
			// pass variables to vue.
			wp_localize_script(
				'svm-script-admin2',
				Svm_Const::SCRIPT_ARG_NAME,
				array(
					'locale'         => get_locale(),
					'rest_endpoint'  => site_url( 'wp-json/' . Svm_Const::REST_NAMESPACE ),
					'page_post_id'   => get_the_ID(),
					'meta_key'       => array(
						'description_json'    => Svm_Const::META_KEY_NAME_DATA_JSON,
						'book_post_id'        => Svm_Const::META_KEY_NAME_BOOK_ID,
						'parent_page_post_id' => Svm_Const::META_KEY_NAME_PARENT_PAGE_ID,
						'video_meta'          => Svm_Const::META_KEY_NAME_VIDEO_META,
					),
					'ajax_call_data' => array(
						'_wpnonce' => wp_create_nonce( 'wp_rest' ),
					),
				)
			);

		} elseif ( ! $is_ajax_request ) {
			wp_enqueue_style( 'jquery-ui-progressbar' );
			wp_enqueue_script( 'jquery-ui-progressbar', false, array( 'jquery' ), Svm_Const::THEME_VER, true );

			wp_enqueue_style( 'svm-style-admin1' );
			wp_enqueue_style( 'svm-style-admin2' );
			wp_enqueue_script( 'svm-script-admin1', false, array( 'jquery-ui-progressbar' ), Svm_Const::THEME_VER, true );
			wp_enqueue_script( 'svm-script-admin2' );
		}
	}

	/**
	 * Action of add_meta_boxes_<post_type>
	 *
	 * @param WP_Post $post post object.
	 */
	public function action_add_meta_box_manual( $post ) {
		// add metabox to side-area in svm_book post view.
		// this can export static files.
		add_meta_box(
			Svm_Const::THEME_NAME,
			__( 'Export Manual Page Files', 'simplevideomanual' ),
			array( $this, 'render_book_meta_box_content' ),
			$post->post_type,
			'side',
			'low'
		);

		// remove slug-metabox.
		remove_meta_box( 'slugdiv', $post->post_type, 'normal' );
	}

	/**
	 * Action of add_meta_boxes_<post_type>
	 *
	 * @param WP_Post $post post object.
	 */
	public function action_add_meta_box_page( $post ) {
		// add metabox to main-area in svm_page post view.
		// this can input description information.
		add_meta_box(
			Svm_Const::THEME_NAME,
			__( 'Video Description', 'simplevideomanual' ),
			array( $this, 'render_page_meta_box_content' ),
			$post->post_type,
			'normal',
			'high'
		);

		// remove slug-metabox.
		remove_meta_box( 'slugdiv', $post->post_type, 'normal' );
	}

	/**
	 * The template of meta box in svm_book view.
	 */
	public function render_book_meta_box_content() {
		echo '<div>';
		echo '<label for="' . esc_attr( self::INPUT_FIELD_NAME_EXPORT_INC_MEDIA ) . '"><input form="svm_export_form" type="checkbox" value="1" id="' . esc_attr( self::INPUT_FIELD_NAME_EXPORT_INC_MEDIA ) . '" name="' . esc_attr( self::INPUT_FIELD_NAME_EXPORT_INC_MEDIA ) . '">' . esc_html__( 'Include video files', 'simplevideomanual' ) . '</label>';
		echo '</div>';
		echo '<input form="svm_export_form" type="hidden" name="' . esc_attr( self::INPUT_FIELD_NAME_EXPORT_BOOK_POST_ID ) . '" value="' . get_the_ID() . '">';
		echo '<input form="svm_export_form" type="hidden" name="process_step" value="1">';
		echo '<input style="margin: 10px 0 0 0;" form="svm_export_form" type="submit" name="submit" id="aep_submit" class="button button-primary" value="' . esc_attr__( 'Export', 'simplevideomanual' ) . '">';
	}

	/**
	 * The template of form for export file meta box
	 *
	 * This form is submit by button in aep-Export div.
	 */
	public function action_admin_footer_post_php() {
		echo '<form name="svm_export_form" action="' . esc_url( admin_url( 'admin.php?page=svm-export-static-page-files' ) ) . '" method="post" id="svm_export_form">';
		wp_nonce_field( self::NONCE_ACTION_PAGE_EXPORT, self::NONCE_NAME_PAGE_EXPORT );
		echo '</form>';
	}

	/**
	 * Action of save_post
	 *
	 * Save input values as post meta values.
	 *
	 * @param string $post_id post id.
	 */
	public function action_save_post( $post_id ) {
		// ignore autosaving.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// check nonce.
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_key( $_POST[ self::NONCE_NAME ] ), self::NONCE_ACTION ) ) {
			return;
		}

		$post_vars = wp_unslash( $_POST );

		// check permissions.
		$post_type = isset( $post_vars['post_type'] ) ? sanitize_text_field( $post_vars['post_type'] ) : false;
		if ( Svm_Const::POST_TYPE_PAGE === $post_type ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}
		}

		// check ok. start to save the data.
		$this->update_post_metas( $post_id, $post_vars );
	}

	/**
	 * Save post meta values.
	 *
	 * @param string $post_id post id.
	 * @param array  $post_vars $_POST applied wp_slash.
	 */
	private function update_post_metas( $post_id, $post_vars ) {
		$input_json_str = isset( $post_vars[ self::INPUT_FIELD_NAME_DATA_JSON ] ) ? $post_vars[ self::INPUT_FIELD_NAME_DATA_JSON ] : '';
		if ( empty( $input_json_str ) ) {
			return;
		}
		$input_json_str = sanitize_textarea_field( $input_json_str );
		$input_json     = json_decode( $input_json_str );
		if ( ! $input_json ) {
			return;
		}

		// start to build json data.
		$json = array();

		if ( isset( $input_json->video_id ) && is_int( $input_json->video_id ) ) {
			$json['video_id'] = $input_json->video_id;
		}

		if ( isset( $input_json->book_post_id ) && is_int( $input_json->book_post_id ) ) {
			$json['book_post_id'] = $input_json->book_post_id;
		}

		if ( isset( $input_json->parent_page_post_id ) && is_int( $input_json->parent_page_post_id ) ) {
			$json['parent_page_post_id'] = $input_json->parent_page_post_id;
		}

		$filter_description = function ( $description ) {
			$filtered = array();
			if ( ! $description ) {
				$filtered = array(
					'id'     => 0,
					'hour'   => '0',
					'minute' => '0',
					'second' => '0',
					'text'   => '',
				);
			} else {
				$filtered['id'] = '';
				if ( is_int( $description->id ) ) {
					$filtered['id'] = $description->id;
				}
				$filtered['hour'] = '';
				if ( is_string( $description->hour ) ) {
					$filtered['hour'] = preg_replace( '![^0-9]!', '', $description->hour );
				}
				$filtered['minute'] = '';
				if ( is_string( $description->minute ) ) {
					$filtered['minute'] = preg_replace( '![^0-9]!', '', $description->minute );
				}
				$filtered['second'] = '';
				if ( is_string( $description->second ) ) {
					$filtered['second'] = preg_replace( '![^0-9]!', '', $description->second );
				}
				$filtered['text'] = '';
				if ( ! empty( $description->text ) && is_string( $description->text ) ) {
					$filtered['text'] = $description->text;
				}
			}

			return $filtered;
		};
		if ( isset( $input_json->input_description ) ) {
			$json['input_description'] = $filter_description( $input_json->input_description );
		}
		if ( isset( $input_json->descriptions ) && is_array( $input_json->descriptions ) && ( 0 < count( $input_json->descriptions ) ) ) {
			$json['descriptions'] = array_map( $filter_description, $input_json->descriptions );
		}

		// start to save.
		$json_str = wp_json_encode( $json );
		// follow WordPress post process(@see wp-includes/functions.php).
		$json_str = addslashes( $json_str );
		$json_str = sanitize_textarea_field( $json_str );

		update_post_meta( $post_id, Svm_Const::META_KEY_NAME_DATA_JSON, $json_str );
		update_post_meta( $post_id, Svm_Const::META_KEY_NAME_BOOK_ID, $json['book_post_id'] );
		update_post_meta( $post_id, Svm_Const::META_KEY_NAME_PARENT_PAGE_ID, $json['parent_page_post_id'] );

		$level = '0';
		if ( ! empty( $json['book_post_id'] ) ) {
			$level = '1';
		}
		if ( ! empty( $json['parent_page_post_id'] ) ) {
			$level = '2';
		}
		update_post_meta( $post_id, Svm_Const::META_KEY_NAME_PAGE_LEVEL, $level );
	}

	/**
	 * The template of meta box in svm_page view.
	 *
	 * @param WP_Post $post the post.
	 */
	public function render_page_meta_box_content( $post ) {
		// output for save.
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		// this template is used with Vue.
		echo '<div class="svm-Mbox"></div>';
	}
}

// used only in admin view.
if ( is_admin() ) {
	$function = new Svm_Admin_Function();
	$function->init();
}
