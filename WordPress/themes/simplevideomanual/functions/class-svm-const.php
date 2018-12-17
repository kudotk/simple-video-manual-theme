<?php
/**
 * Constant values.
 *
 * @package simplevideomanual
 */

/**
 * Class Svm_Const
 */
class Svm_Const {
	const THEME_NAME       = 'simplevideomanual';
	const THEME_VER        = '1.0.0';
	const POST_TYPE_MANUAL = 'svm_book';
	const POST_TYPE_PAGE   = 'svm_page';

	// meta keys in this theme.
	const META_KEY_NAME_BOOK_ID        = 'svm_book_id';
	const META_KEY_NAME_PAGE_LEVEL     = 'svm_page_level';
	const META_KEY_NAME_DATA_JSON      = 'svm_description_data_json';
	const META_KEY_NAME_PARENT_PAGE_ID = 'svm_parent_page_id';
	const META_KEY_NAME_VIDEO_META     = 'svm_video_meta';

	// object name used in 'wp_localize_script'.
	const SCRIPT_ARG_NAME = 'svm_args';

	const REST_NAMESPACE = 'svm/v1';
}
