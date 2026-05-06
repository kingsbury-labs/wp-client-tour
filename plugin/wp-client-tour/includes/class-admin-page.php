<?php
defined( 'ABSPATH' ) || exit;

class WCT_Admin_Page {

	const NONCE_FIELD = 'wct_nonce';

	/**
	 * Register the Settings > WP Client Tour submenu page.
	 */
	public static function register(): void {
		add_options_page(
			__( 'WP Client Tour', 'wp-client-tour' ),
			__( 'WP Client Tour', 'wp-client-tour' ),
			'manage_options',
			'wp-client-tour',
			array( self::class, 'render_page' )
		);
	}

	/**
	 * Handle form submissions, then render the page.
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wp-client-tour' ) );
		}

		self::handle_actions();

		$tours          = WCT_Tour_Loader::get_all_valid_tours();
		$test_mode      = (bool) get_option( WCT_OPTION_TEST_MODE, false );
		$widget_enabled = (bool) get_option( WCT_Dashboard_Widget::OPTION_KEY, false );

		?>
		<div class="wrap">
			<h1>
			<?php esc_html_e( 'WP Client Tour', 'wp-client-tour' ); ?>
			<span style="font-size:13px;font-weight:400;color:#646970;margin-left:10px;">v<?php echo esc_html( WCT_VERSION ); ?></span>
		</h1>

			<?php self::render_notices(); ?>

			<h2><?php esc_html_e( 'Tours', 'wp-client-tour' ); ?></h2>

			<?php if ( empty( $tours ) ) : ?>
				<p><?php esc_html_e( 'No tours found. Add JSON tour files to the tours/ directory inside the plugin folder.', 'wp-client-tour' ); ?></p>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Tour ID', 'wp-client-tour' ); ?></th>
							<th><?php esc_html_e( 'Label', 'wp-client-tour' ); ?></th>
							<th><?php esc_html_e( 'Target Page', 'wp-client-tour' ); ?></th>
							<th><?php esc_html_e( 'Roles', 'wp-client-tour' ); ?></th>
							<th><?php esc_html_e( 'Steps', 'wp-client-tour' ); ?></th>
							<th><?php esc_html_e( 'Trigger', 'wp-client-tour' ); ?></th>
							<th><?php esc_html_e( 'Created', 'wp-client-tour' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $tours as $tour ) : ?>
							<tr>
								<td><code><?php echo esc_html( $tour['id'] ); ?></code></td>
								<td><?php echo esc_html( $tour['label'] ?? $tour['id'] ); ?></td>
								<td><code><?php echo esc_html( $tour['target_page'] ); ?></code></td>
								<td><?php echo esc_html( implode( ', ', $tour['target_roles'] ) ); ?></td>
								<td><?php echo (int) count( $tour['steps'] ); ?></td>
								<td><?php echo esc_html( $tour['trigger'] ); ?></td>
								<td><?php echo esc_html( $tour['created'] ?? '—' ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<h2><?php esc_html_e( 'Settings', 'wp-client-tour' ); ?></h2>
			<form method="post">
				<?php wp_nonce_field( 'wct_save_settings', self::NONCE_FIELD ); ?>
				<input type="hidden" name="wct_action" value="save_settings">
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Test Mode', 'wp-client-tour' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="wct_test_mode" value="1"<?php checked( $test_mode ); ?>>
								<?php esc_html_e( 'Make all auto_once tours replay on every page load (useful during development)', 'wp-client-tour' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Dashboard Widget', 'wp-client-tour' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="wct_dashboard_widget" value="1"<?php checked( $widget_enabled ); ?>>
								<?php esc_html_e( 'Show a tour launcher on the WordPress Dashboard for users who have eligible tours', 'wp-client-tour' ); ?>
							</label>
						</td>
					</tr>
				</table>
				<p class="submit">
					<button type="submit" class="button button-primary">
						<?php esc_html_e( 'Save Settings', 'wp-client-tour' ); ?>
					</button>
				</p>
			</form>

			<h2><?php esc_html_e( 'Updates', 'wp-client-tour' ); ?></h2>
			<?php
			$release = WCT_Update_Checker::get_latest_release();
			$latest  = ( $release && ! empty( $release['tag_name'] ) ) ? ltrim( $release['tag_name'], 'v' ) : null;
			if ( isset( $_GET['wct_refreshed'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				?>
				<div class="notice notice-success inline"><p><?php esc_html_e( 'Update cache refreshed.', 'wp-client-tour' ); ?></p></div>
			<?php endif; ?>
			<p>
				<?php
				if ( $latest ) {
					if ( version_compare( $latest, WCT_VERSION, '>' ) ) {
						printf(
							/* translators: %s: version number */
							esc_html__( 'Version %s is available on GitHub.', 'wp-client-tour' ),
							esc_html( $latest )
						);
					} else {
						esc_html_e( 'You are running the latest version.', 'wp-client-tour' );
					}
				} else {
					esc_html_e( 'Update status unavailable (GitHub API unreachable).', 'wp-client-tour' );
				}
				?>
			</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'wct_refresh_update' ); ?>
				<input type="hidden" name="action" value="wct_refresh_update">
				<button type="submit" class="button button-secondary">
					<?php esc_html_e( 'Check for Updates', 'wp-client-tour' ); ?>
				</button>
			</form>

			<h2><?php esc_html_e( 'Reset Completion Data', 'wp-client-tour' ); ?></h2>

			<form method="post" style="margin-bottom: 24px;">
				<?php wp_nonce_field( 'wct_reset_all', self::NONCE_FIELD ); ?>
				<input type="hidden" name="wct_action" value="reset_all">
				<p><?php esc_html_e( 'Clear completion data for every user. All tours marked as seen will appear again.', 'wp-client-tour' ); ?></p>
				<button type="submit" class="button button-secondary"
					onclick="return confirm('<?php echo esc_js( __( 'Reset completion data for ALL users?', 'wp-client-tour' ) ); ?>')">
					<?php esc_html_e( 'Reset All Users', 'wp-client-tour' ); ?>
				</button>
			</form>

			<form method="post">
				<?php wp_nonce_field( 'wct_reset_user', self::NONCE_FIELD ); ?>
				<input type="hidden" name="wct_action" value="reset_user">
				<p><?php esc_html_e( 'Clear completion data for a specific user.', 'wp-client-tour' ); ?></p>
				<label for="wct_username"><?php esc_html_e( 'Username:', 'wp-client-tour' ); ?></label>
				<input type="text" id="wct_username" name="wct_username" class="regular-text" autocomplete="off">
				<button type="submit" class="button button-secondary">
					<?php esc_html_e( 'Reset User', 'wp-client-tour' ); ?>
				</button>
			</form>
		</div>
		<?php
	}

	/**
	 * Process POST actions before output.
	 */
	private static function handle_actions(): void {
		if ( empty( $_POST['wct_action'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'wp-client-tour' ) );
		}

		$action       = sanitize_key( wp_unslash( $_POST['wct_action'] ) );
		$nonce_action = self::nonce_action_for( $action );

		if ( null === $nonce_action ) {
			return;
		}

		if ( ! isset( $_POST[ self::NONCE_FIELD ] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ), $nonce_action )
		) {
			wp_die( esc_html__( 'Security check failed.', 'wp-client-tour' ) );
		}

		switch ( $action ) {
			case 'save_settings':
				$test_mode_enabled = isset( $_POST['wct_test_mode'] )
					&& '1' === sanitize_text_field( wp_unslash( $_POST['wct_test_mode'] ) );
				update_option( WCT_OPTION_TEST_MODE, $test_mode_enabled ? '1' : '' );

				$widget_enabled = isset( $_POST['wct_dashboard_widget'] )
					&& '1' === sanitize_text_field( wp_unslash( $_POST['wct_dashboard_widget'] ) );
				update_option( WCT_Dashboard_Widget::OPTION_KEY, $widget_enabled ? '1' : '' );

				self::add_notice( __( 'Settings saved.', 'wp-client-tour' ) );
				break;

			case 'reset_all':
				self::reset_all_users();
				self::add_notice( __( 'Completion data cleared for all users.', 'wp-client-tour' ) );
				break;

			case 'reset_user':
				$username = isset( $_POST['wct_username'] )
					? sanitize_user( wp_unslash( $_POST['wct_username'] ) )
					: '';
				if ( '' === $username ) {
					self::add_notice( __( 'Please enter a username.', 'wp-client-tour' ), 'error' );
					break;
				}
				$user = get_user_by( 'login', $username );
				if ( $user ) {
					delete_user_meta( $user->ID, WCT_META_KEY );
				}
				// Generic message regardless of existence — avoids username enumeration.
				self::add_notice( __( 'Reset complete.', 'wp-client-tour' ) );
				break;
		}
	}

	/**
	 * Map an action name to its nonce action string.
	 *
	 * @param string $action
	 * @return string|null
	 */
	private static function nonce_action_for( string $action ): ?string {
		$map = array(
			'save_settings' => 'wct_save_settings',
			'reset_all'     => 'wct_reset_all',
			'reset_user'    => 'wct_reset_user',
		);
		return $map[ $action ] ?? null;
	}

	/**
	 * Delete completion meta from every user who has it.
	 */
	private static function reset_all_users(): void {
		global $wpdb;

		// Collect affected user IDs before the delete so we can flush their caches afterward.
		$user_ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$wpdb->prepare(
				"SELECT DISTINCT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s",
				WCT_META_KEY
			)
		);

		$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->usermeta,
			array( 'meta_key' => WCT_META_KEY ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			array( '%s' )
		);

		foreach ( (array) $user_ids as $user_id ) {
			clean_user_cache( (int) $user_id );
		}
	}

	/**
	 * Queue an admin notice for display on the current page render.
	 *
	 * @param string $message
	 * @param string $type    'success' or 'error'
	 */
	private static function add_notice( string $message, string $type = 'success' ): void {
		$notices   = get_transient( 'wct_admin_notices_' . get_current_user_id() );
		$notices   = is_array( $notices ) ? $notices : array();
		$notices[] = array( 'message' => $message, 'type' => $type );
		set_transient( 'wct_admin_notices_' . get_current_user_id(), $notices, 60 );
	}

	/**
	 * Render and clear queued notices.
	 */
	private static function render_notices(): void {
		$key     = 'wct_admin_notices_' . get_current_user_id();
		$notices = get_transient( $key );
		if ( ! is_array( $notices ) ) {
			return;
		}
		delete_transient( $key );
		foreach ( $notices as $notice ) {
			$class = 'error' === $notice['type'] ? 'notice-error' : 'notice-success';
			printf(
				'<div class="notice %s is-dismissible"><p>%s</p></div>',
				esc_attr( $class ),
				esc_html( $notice['message'] )
			);
		}
	}
}
