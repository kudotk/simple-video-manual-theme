<?php
/**
 * Utils Function
 *
 * Functions is used for use and admin functions.
 *
 * @package simplevideomanual
 */

/**
 * Class Svm_Utils
 */
class Svm_Utils {

	/**
	 * Build data of WP REST - /manuals.
	 *
	 * @param integer $id post id.
	 * @param array   $params Option parameters
	 *      ['status'] string Post status to filter returned values.
	 *
	 * @return array REST response.
	 */
	public function build_rest_manuals_data( $id, $params = array() ) {
		if ( is_null( $id ) ) {
			// get post list.
			$args  = array(
				'posts_per_page'   => - 1,
				'orderby'          => 'date',
				'order'            => 'ASC',
				'post_type'        => Svm_Const::POST_TYPE_MANUAL,
				'post_status'      => isset( $params['status'] ) ? $params['status'] : 'publish',
				'suppress_filters' => true,
			);
			$query = new WP_Query( $args );
			$posts = $query->get_posts();

		} else {
			// get the post of id.
			$posts = array();
			$post  = get_post( $id );
			if ( $post ) {
				// calc update date.
				$update_date = $post->post_date_gmt;

				$args       = array(
					'posts_per_page'   => 1,
					'post_type'        => Svm_Const::POST_TYPE_PAGE,
					'post_status'      => isset( $params['status'] ) ? $params['status'] : 'publish',
					'suppress_filters' => true,
					'meta_query'       => array(
						array(
							'key'   => Svm_Const::META_KEY_NAME_BOOK_ID,
							'value' => $id,
						),
					),
					'orderby'          => array(
						'data' => 'DESC',
					),
				);
				$query      = new WP_Query( $args );
				$page_posts = $query->get_posts();
				if ( $page_posts ) {
					$page_date = $page_posts[0]->post_date_gmt;
					if ( $update_date < $page_date ) {
						$update_date = $page_date;
					}
				}
				$post->svm_last_update_date = $update_date;

				$posts[] = $post;
			}
		}

		return $posts;
	}

	/**
	 * Filter post meta values.
	 *
	 * Id value is always one, change array to single type.
	 *
	 * @param array $meta_values the post meta values.
	 *
	 * @return array filtered post meta values.
	 */
	private function filter_post_meta_value( $meta_values ) {
		$single_type_meta_keys = array(
			Svm_Const::META_KEY_NAME_BOOK_ID,
			Svm_Const::META_KEY_NAME_PARENT_PAGE_ID,
			Svm_Const::META_KEY_NAME_PAGE_LEVEL,
			Svm_Const::META_KEY_NAME_DATA_JSON,
		);
		$numeric_meta_keys     = array(
			Svm_Const::META_KEY_NAME_BOOK_ID,
			Svm_Const::META_KEY_NAME_PARENT_PAGE_ID,
			Svm_Const::META_KEY_NAME_PAGE_LEVEL,
		);
		foreach ( $single_type_meta_keys as $k ) {
			if ( ! isset( $meta_values[ $k ] ) ) {
				continue;
			}
			$v = $meta_values[ $k ];
			if ( ! is_array( $v ) && empty( $v ) ) {
				unset( $meta_values[ $k ] );
			} else {
				if ( in_array( $k, $numeric_meta_keys, true ) ) {
					if ( is_numeric( $v[0] ) ) {
						$meta_values[ $k ] = intval( $v[0] );
					} else {
						// no need invalid values.
						unset( $meta_values[ $k ] );
					}
				} else {
					$meta_values[ $k ] = $v[0];
				}
			}

			// modify json elements.
			if ( Svm_Const::META_KEY_NAME_DATA_JSON === $k ) {
				$json  = json_decode( $v[0] );
				$count = isset( $json->descriptions ) ? count( $json->descriptions ) : 0;
				for ( $i = 0; $i < $count; $i ++ ) {
					$description = $json->descriptions[ $i ];
					if ( empty( $description->hour ) ) {
						$description->hour = '0';
					}
					if ( empty( $description->minute ) ) {
						$description->minute = '0';
					}
					if ( empty( $description->second ) ) {
						$description->second = '0';
					}
					$json->descriptions[ $i ] = $description;
				}
				$meta_values[ $k ] = wp_json_encode( $json );
			}
		}

		return $meta_values;
	}

	/**
	 * Build data of WP REST - /manuals/<id>/pages
	 *
	 * @param integer $id post id.
	 * @param array   $params Option parameters
	 *      ['status'] string Post status to filter returned values.
	 *      ['video'] boolean Include attachment properties.
	 *
	 * @return array REST response.
	 */
	public function build_rest_manual_pages_data( $id, $params = array() ) {
		$args  = array(
			'posts_per_page'   => - 1,
			'post_type'        => Svm_Const::POST_TYPE_PAGE,
			'post_status'      => isset( $params['status'] ) ? $params['status'] : 'publish',
			'suppress_filters' => true,
			'meta_query'       => array(
				array(
					'key'   => Svm_Const::META_KEY_NAME_BOOK_ID,
					'value' => $id,
				),
			),
			'orderby'          => array(
				Svm_Const::META_KEY_NAME_PAGE_LEVEL => 'ASC',
				'data'                              => 'ASC',
			),
		);
		$query = new WP_Query( $args );
		$posts = $query->get_posts();

		// get post meta.
		$meta_keys = array(
			Svm_Const::META_KEY_NAME_DATA_JSON,
			Svm_Const::META_KEY_NAME_BOOK_ID,
			Svm_Const::META_KEY_NAME_PARENT_PAGE_ID,
			Svm_Const::META_KEY_NAME_PAGE_LEVEL,
		);
		$posts     = array_map(
			function ( $post ) use ( $meta_keys, $params ) {
				$meta            = get_post_meta( $post->ID );
				$post->post_meta = $this->filter_post_meta_value( $meta );

				// get video attachment information.
				if ( isset( $params['video'] ) && $params['video'] ) {
					$video_meta = $this->get_attachment_metadata( $post->post_meta );
					if ( $video_meta ) {
						$post->post_meta[ Svm_Const::META_KEY_NAME_VIDEO_META ] = $video_meta;
					}
				}

				return $post;
			},
			$posts
		);

		// build hierarchy.
		$id_idx_map = array();
		$len        = count( $posts );
		for ( $i = 0; $i < $len; ++ $i ) {
			$id_idx_map[ $posts[ $i ]->ID ] = $i;
		}

		$root = array();
		foreach ( array_reverse( $posts ) as $post ) {
			$level = $post->post_meta[ Svm_Const::META_KEY_NAME_PAGE_LEVEL ];
			if ( 1 !== $level ) {
				$parent_post_idx = $id_idx_map[ $post->post_meta[ Svm_Const::META_KEY_NAME_PARENT_PAGE_ID ] ];
				if ( ! isset( $posts[ $parent_post_idx ]->pages ) ) {
					$posts[ $parent_post_idx ]->pages = array();
				}
				array_unshift( $posts[ $parent_post_idx ]->pages, $posts[ $id_idx_map[ $post->ID ] ] );
			}
		}
		$len = count( $posts );
		for ( $i = 0; $i < $len; $i ++ ) {
			$level = $posts[ $i ]->post_meta[ Svm_Const::META_KEY_NAME_PAGE_LEVEL ];
			if ( 1 === $level ) {
				$root[] = $posts[ $i ];
			}
		}

		return $root;
	}

	/**
	 * Build data of WP REST - /pages/<id>
	 *
	 * @param integer $id post id.
	 *
	 * @return array REST response.
	 */
	public function build_rest_pages_data( $id ) {
		$post            = get_post( $id );
		$meta            = get_post_meta( $post->ID );
		$post->post_meta = $this->filter_post_meta_value( $meta );

		// get video attachment information.
		$video_meta = $this->get_attachment_metadata( $post->post_meta );
		if ( $video_meta ) {
			$post->post_meta[ Svm_Const::META_KEY_NAME_VIDEO_META ] = $video_meta;
		}
		return $post;
	}

	/**
	 * Get video attachment metadata.
	 *
	 * @param array $post_meta Post meta of the post.
	 *
	 * @return array attachment meta data.
	 */
	private function get_attachment_metadata( $post_meta ) {
		$meta = false;
		if ( $post_meta && isset( $post_meta[ Svm_Const::META_KEY_NAME_DATA_JSON ] ) ) {
			$json = json_decode( $post_meta[ Svm_Const::META_KEY_NAME_DATA_JSON ] );
			if ( isset( $json->video_id ) && is_numeric( $json->video_id ) ) {
				$video_meta = wp_get_attachment_metadata( $json->video_id );
				if ( strpos( $video_meta['mime_type'], 'video' ) === 0 ) {
					$meta         = array();
					$meta['type'] = 'video/' . $video_meta['fileformat'];
					$meta['url']  = wp_get_attachment_url( $json->video_id );
				}
			}
		}

		return $meta;
	}

	/**
	 * Zip directory.
	 *
	 * @param string $zip_file_path the path is output.
	 * @param string $src_root_dir the directory path has files which is compressed.
	 *
	 * @return bool true if adding files success,
	 */
	public function zip_dir( $zip_file_path, $src_root_dir ) {
		$zip = new ZipArchive();

		if ( false === $zip->open( $zip_file_path, ZipArchive::CREATE ) ) {
			return false;
		}

		try {
			// add file to zip recursively.
			$add_file_recursive = function ( $path, $zip ) use ( &$add_file_recursive, $src_root_dir ) {
				if ( is_file( $path ) ) {
					$path_base_on_zip = str_replace( $src_root_dir . '/', '', $path );
					if ( ! $zip->addFile( $path, $path_base_on_zip ) ) {
						throw new Exception( 'Unable to add file to zip.' );
					};
				} elseif ( is_dir( $path ) ) {
					$files = glob( $path . '/*' );
					foreach ( $files as $file ) {
						$add_file_recursive( $file, $zip );
					}
				}
			};
			$add_file_recursive( $src_root_dir, $zip );

		} catch ( Exception $ex ) {
			return false;
		} finally {
			$zip->close();
		}

		return true;
	}

	/**
	 * Delete backup files in directory.
	 *
	 * @param string   $dir the directory is searched.
	 * @param callback $value_compare_func the function is passed 'usort'.
	 *
	 * @return array old file path array.
	 */
	public function get_file_path_array( $dir, $value_compare_func ) {
		$files = glob( $dir . '/*' );
		if ( ( false === $files ) || empty( $files ) ) {
			return;
		}
		usort( $files, $value_compare_func );

		return $files;
	}

	/**
	 * Copy directory within files.
	 *
	 * @param string $src Directory copy from.
	 * @param string $dst Directory copy to.
	 *
	 * @return bool
	 */
	public function copy_dir_recursive( $src, $dst ) {
		$dir = opendir( $src );
		if ( ! file_exists( $dst ) && ! mkdir( $dst, 0777, true ) ) {
			return false;
		}
		$file = readdir( $dir );
		while ( false !== $file ) {
			if ( ( '.' !== $file ) && ( '..' !== $file ) ) {
				if ( is_dir( $src . '/' . $file ) ) {
					if ( ! $this->copy_dir_recursive( $src . '/' . $file, $dst . '/' . $file ) ) {
						return false;
					}
				} else {
					if ( ! copy( $src . '/' . $file, $dst . '/' . $file ) ) {
						return false;
					}
				}
			}
			$file = readdir( $dir );
		}
		closedir( $dir );

		return true;
	}

	/**
	 * Remove directory recursively.
	 *
	 * @param string $path Directory path are removing.
	 *
	 * @return bool
	 */
	public function rmdir_recursive( $path ) {
		$dir  = opendir( $path );
		$file = readdir( $dir );
		while ( false !== $file ) {
			if ( ( '.' !== $file ) && ( '..' !== $file ) ) {
				if ( is_dir( $path . '/' . $file ) ) {
					if ( ! $this->rmdir_recursive( $path . '/' . $file ) ) {
						return false;
					}
				} else {
					if ( ! unlink( $path . '/' . $file ) ) {
						return false;
					}
				}
			}
			$file = readdir( $dir );
		}
		closedir( $dir );

		return rmdir( $path );
	}
}
