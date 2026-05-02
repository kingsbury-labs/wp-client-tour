<?php
defined( 'ABSPATH' ) || exit;

class WCT_Tour_Renderer {

	/**
	 * Called on admin_init. Loads eligible tours and hooks enqueue if any exist.
	 */
	public static function init(): void {
		$loader = new WCT_Tour_Loader();
		$tours  = $loader->get_eligible_tours();

		if ( empty( $tours ) ) {
			return;
		}

		// Store tours for use inside the enqueue closure.
		add_action(
			'admin_enqueue_scripts',
			static function () use ( $tours ) {
				self::enqueue( $tours );
			}
		);
	}

	/**
	 * Enqueue assets and pass tour data to JS.
	 *
	 * @param array $tours Eligible tour configs.
	 */
	private static function enqueue( array $tours ): void {
		wp_enqueue_style(
			'wct-tour-client',
			WCT_PLUGIN_URL . 'assets/tour-client.css',
			array(),
			WCT_VERSION
		);

		wp_enqueue_script(
			'wct-tour-client',
			WCT_PLUGIN_URL . 'assets/tour-client.js',
			array(),
			WCT_VERSION,
			true
		);

		wp_localize_script(
			'wct-tour-client',
			'wpClientTour',
			array(
				'tours'       => array_map( array( self::class, 'strip_tour_for_js' ), $tours ),
				'nonce'       => wp_create_nonce( 'wp_rest' ),
				'restUrl'     => rest_url( 'wp-client-tour/v1/complete' ),
				'adminUrl'    => admin_url(),
				'currentPage' => self::get_current_admin_page(),
			)
		);
	}

	/**
	 * Build the current admin page string (file + query string, WCT params stripped).
	 * Used by JS to match per-step target_page values for the dual-list model.
	 *
	 * @return string e.g. "edit.php?post_type=bh_event"
	 */
	private static function get_current_admin_page(): string {
		global $pagenow;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$params = $_GET;
		unset( $params['wct_resume'], $params['wct_step'] );
		if ( empty( $params ) ) {
			return $pagenow;
		}
		$safe = array_map( 'sanitize_text_field', array_map( 'wp_unslash', $params ) );
		return add_query_arg( $safe, $pagenow );
	}

	/**
	 * Reduce a tour config to the fields the JS renderer actually uses.
	 * Authoring metadata (created, version, confidence, label) stays server-side.
	 *
	 * @param array $tour Full tour config.
	 * @return array
	 */
	private static function strip_tour_for_js( array $tour ): array {
		$steps = array_map(
			static function ( $step ) {
				$out = array(
					'id'       => $step['id'],
					'selector' => $step['selector'],
					'position' => $step['position'],
					'title'    => $step['title'],
					'body'     => $step['body'],
				);
				if ( isset( $step['target_page'] ) ) {
					$out['target_page'] = $step['target_page'];
				}
				if ( isset( $step['navigate_on_next'] ) ) {
					$out['navigate_on_next'] = $step['navigate_on_next'];
				}
				if ( isset( $step['navigate_label'] ) ) {
					$out['navigate_label'] = $step['navigate_label'];
				}
				return $out;
			},
			$tour['steps']
		);

		$out = array(
			'id'         => $tour['id'],
			'trigger'    => $tour['trigger'],
			'targetPage' => $tour['target_page'],
			'steps'      => $steps,
		);

		if ( isset( $tour['resumeStep'] ) ) {
			$out['resumeStep'] = $tour['resumeStep'];
		}

		return $out;
	}

	/**
	 * Register REST API routes. Called on rest_api_init.
	 */
	public static function register_rest_routes(): void {
		register_rest_route(
			'wp-client-tour/v1',
			'/complete',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( self::class, 'handle_complete' ),
				'permission_callback' => static function () {
					return is_user_logged_in();
				},
				'args'                => array(
					'tour_id' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => static function ( $value ) {
							return is_string( $value ) && preg_match( '/^[a-z0-9-]+$/', $value );
						},
					),
				),
			)
		);
	}

	/**
	 * REST handler: mark a tour as completed for the current user.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public static function handle_complete( WP_REST_Request $request ): WP_REST_Response {
		$user_id = get_current_user_id();
		$tour_id = $request->get_param( 'tour_id' );

		// Ensure the tour actually exists on disk; format-valid IDs alone aren't enough
		// to commit usermeta, otherwise any logged-in user could pollute their own meta.
		if ( ! WCT_Tour_Loader::tour_exists( $tour_id ) ) {
			return new WP_REST_Response(
				array( 'success' => false, 'error' => 'unknown_tour' ),
				404
			);
		}

		$completed = get_user_meta( $user_id, WCT_META_KEY, true );
		if ( ! is_array( $completed ) ) {
			$completed = array();
		}

		if ( ! in_array( $tour_id, $completed, true ) ) {
			$completed[] = $tour_id;
			update_user_meta( $user_id, WCT_META_KEY, $completed );
		}

		return new WP_REST_Response( array( 'success' => true ), 200 );
	}
}
