<?php
/**
 * Debug
 *
 * @package simplevideomanual
 */

/**
 * Get call_trace string
 *
 * @return false|string
 */
function _get_call_trace() {
	$trace = debug_backtrace();
	if ( ! $trace || ( count( $trace ) < 2 ) ) {
		return '';
	}
	// 0: this
	// 1: _log or _dd
	// 2: caller
	$str = wp_json_encode( $trace[2] );

	return $str;
}

/**
 * Debug dump
 *
 * @param array|string $obj dump object.
 * @param bool         $exit does stop php script.
 */
function _dd( $obj, $exit = true ) {
	var_dump( _get_call_trace() );
	var_dump( $obj );
	if ( $exit ) {
		exit;
	}
}

/**
 * Debug log
 *
 * @param array|string $obj the object will be written.
 */
function _log( $obj ) {
	$obj = var_export( $obj, true );
	error_log( $obj );
}
