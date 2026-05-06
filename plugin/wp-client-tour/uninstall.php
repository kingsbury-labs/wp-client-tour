<?php
/**
 * Uninstall handler — fires when the user deletes the plugin via the
 * Plugins screen. Clears all completion data and plugin options.
 */
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'wct_test_mode' );

global $wpdb;
$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->usermeta,
	array( 'meta_key' => 'wct_completed_tours' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	array( '%s' )
);
