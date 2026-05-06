<?php
defined( 'ABSPATH' ) || exit;

class WCT_Update_Checker {

	const TRANSIENT_KEY  = 'wct_github_release';
	const TRANSIENT_TTL  = 43200; // 12 hours.
	const GITHUB_API_URL = 'https://api.github.com/repos/kingsbury-labs/wp-client-tour/releases/latest';

	/**
	 * Hook in on plugins_loaded. We attach to admin_notices only when on the
	 * plugins screen to keep the check lazy.
	 */
	public static function init(): void {
		add_action( 'admin_notices', array( self::class, 'maybe_show_notice' ) );
		// Allow the settings page to trigger a manual refresh.
		add_action( 'admin_post_wct_refresh_update', array( self::class, 'handle_manual_refresh' ) );
	}

	/**
	 * Show a notice only on the plugins list screen.
	 */
	public static function maybe_show_notice(): void {
		$screen = get_current_screen();
		if ( ! $screen || 'plugins' !== $screen->id ) {
			return;
		}
		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		$release = self::get_latest_release();
		if ( null === $release ) {
			return;
		}

		$latest  = ltrim( $release['tag_name'], 'v' );
		$current = WCT_VERSION;

		if ( version_compare( $latest, $current, '<=' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-info"><p><strong>%s</strong> — %s <a href="%s" target="_blank" rel="noopener">%s (v%s) &rarr;</a></p></div>',
			esc_html__( 'WP Client Tour update available', 'wp-client-tour' ),
			esc_html__( 'A new version is available on GitHub:', 'wp-client-tour' ),
			esc_url( $release['html_url'] ),
			esc_html__( 'Download', 'wp-client-tour' ),
			esc_html( $latest )
		);
	}

	/**
	 * Fetch (or return cached) the latest GitHub release data.
	 *
	 * @return array|null Release array with at least tag_name and html_url, or null on failure.
	 */
	public static function get_latest_release(): ?array {
		$cached = get_transient( self::TRANSIENT_KEY );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$response = wp_remote_get(
			self::GITHUB_API_URL,
			array(
				'timeout' => 8,
				'headers' => array(
					'Accept'     => 'application/vnd.github+json',
					'User-Agent' => 'wp-client-tour/' . WCT_VERSION,
				),
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			// Cache a failure marker for 1 hour so we don't hammer GitHub on errors.
			set_transient( self::TRANSIENT_KEY, array( 'error' => true ), 3600 );
			return null;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $body ) || empty( $body['tag_name'] ) || empty( $body['html_url'] ) ) {
			set_transient( self::TRANSIENT_KEY, array( 'error' => true ), 3600 );
			return null;
		}

		$release = array(
			'tag_name' => sanitize_text_field( $body['tag_name'] ),
			'html_url' => esc_url_raw( $body['html_url'] ),
		);

		set_transient( self::TRANSIENT_KEY, $release, self::TRANSIENT_TTL );
		return $release;
	}

	/**
	 * Clear the cached release so the next page load re-fetches.
	 */
	public static function clear_cache(): void {
		delete_transient( self::TRANSIENT_KEY );
	}

	/**
	 * Handle the manual "Check for updates" POST action from the settings page.
	 */
	public static function handle_manual_refresh(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'wp-client-tour' ) );
		}
		check_admin_referer( 'wct_refresh_update' );
		self::clear_cache();
		wp_safe_redirect( admin_url( 'options-general.php?page=wp-client-tour&wct_refreshed=1' ) );
		exit;
	}
}
