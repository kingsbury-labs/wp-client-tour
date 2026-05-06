<?php
defined( 'ABSPATH' ) || exit;

class WCT_Manual_Trigger {

	/**
	 * Register the shortcode and admin bar hook. Called from the main plugin file.
	 */
	public static function init(): void {
		add_action( 'admin_init', array( self::class, 'register_shortcode' ) );
		add_action( 'admin_bar_menu', array( self::class, 'register_admin_bar' ), 100 );
	}

	/**
	 * Build a URL that launches a specific tour by ID.
	 * Returns false if the tour ID is not found or invalid.
	 *
	 * @param string $tour_id Tour ID.
	 * @return string|false Admin URL with wct_force param, or false on failure.
	 */
	public static function get_launch_url( string $tour_id ) {
		foreach ( WCT_Tour_Loader::get_all_valid_tours() as $tour ) {
			if ( $tour['id'] !== $tour_id ) {
				continue;
			}
			$target = $tour['target_page'];
			$sep    = strpos( $target, '?' ) !== false ? '&' : '?';
			return admin_url( $target . $sep . 'wct_force=' . rawurlencode( $tour_id ) );
		}

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( '[WP Client Tour] wct_tour_launch_url(): tour not found: ' . $tour_id );
		}

		return false;
	}

	/**
	 * Shortcode: [wct_launch tour="id" label="Start the tour"]
	 * Renders a WP-native button linking to the tour launch URL.
	 * Outputs nothing if the tour doesn't exist or the current user lacks the required role.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function render_shortcode( array $atts ): string {
		$atts = shortcode_atts(
			array(
				'tour'  => '',
				'label' => __( 'Start tour', 'client-tour' ),
			),
			$atts,
			'wct_launch'
		);

		$tour_id = sanitize_text_field( $atts['tour'] );
		if ( '' === $tour_id ) {
			return '';
		}

		$tour = null;
		foreach ( WCT_Tour_Loader::get_all_valid_tours() as $candidate ) {
			if ( $candidate['id'] === $tour_id ) {
				$tour = $candidate;
				break;
			}
		}

		if ( null === $tour ) {
			return '';
		}

		if ( ! WCT_Tour_Loader::matches_user_role( $tour ) ) {
			return '';
		}

		$url   = self::get_launch_url( $tour_id );
		$label = sanitize_text_field( $atts['label'] );

		if ( false === $url ) {
			return '';
		}

		return sprintf(
			'<a href="%s" class="button">%s</a>',
			esc_url( $url ),
			esc_html( $label )
		);
	}

	/**
	 * Register the [wct_launch] shortcode. Runs on admin_init so it only
	 * exists in wp-admin context — no is_admin() guard needed in the callback.
	 */
	public static function register_shortcode(): void {
		add_shortcode( 'wct_launch', array( self::class, 'render_shortcode' ) );
	}

	/**
	 * Add a "Tours" node to the admin bar listing all manual-trigger tours
	 * the current user is eligible for. Parent node is suppressed when empty.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 */
	public static function register_admin_bar( WP_Admin_Bar $wp_admin_bar ): void {
		$user          = wp_get_current_user();
		$all_tours     = WCT_Tour_Loader::get_all_valid_tours();
		$completed     = get_user_meta( get_current_user_id(), WCT_META_KEY, true );
		$completed     = is_array( $completed ) ? $completed : array();
		$manual_tours  = array();

		foreach ( $all_tours as $tour ) {
			if ( 'manual' !== $tour['trigger'] ) {
				continue;
			}
			foreach ( $tour['target_roles'] as $role ) {
				if ( in_array( $role, (array) $user->roles, true ) ) {
					$manual_tours[] = $tour;
					break;
				}
			}
		}

		if ( empty( $manual_tours ) ) {
			return;
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'wct-tours',
				'title' => __( 'Tours', 'client-tour' ),
				'href'  => false,
			)
		);

		foreach ( $manual_tours as $tour ) {
			$label       = $tour['label'] ?? $tour['id'];
			$is_done     = in_array( $tour['id'], $completed, true );
			$title       = $is_done
				/* translators: %s: tour label */
				? sprintf( __( '%s (Replay)', 'client-tour' ), esc_html( $label ) )
				: esc_html( $label );
			$url         = self::get_launch_url( $tour['id'] );
			$aria_label  = sprintf(
				/* translators: %s: tour label */
				__( 'Launch %s tour', 'client-tour' ),
				esc_attr( $label )
			);

			if ( false === $url ) {
				continue;
			}

			$wp_admin_bar->add_node(
				array(
					'id'     => 'wct-tour-' . $tour['id'],
					'parent' => 'wct-tours',
					'title'  => $title,
					'href'   => esc_url( $url ),
					'meta'   => array( 'aria-label' => $aria_label ),
				)
			);
		}
	}
}
