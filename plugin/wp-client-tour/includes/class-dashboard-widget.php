<?php
defined( 'ABSPATH' ) || exit;

class WCT_Dashboard_Widget {

	const OPTION_KEY = 'wct_dashboard_widget';

	/**
	 * Register hooks. Called from the main plugin file when the option is on.
	 */
	public static function init(): void {
		add_action( 'wp_dashboard_setup', array( self::class, 'register_widget' ) );
	}

	/**
	 * Register the dashboard widget.
	 */
	public static function register_widget(): void {
		wp_add_dashboard_widget(
			'wct_tour_launcher',
			__( 'Your Help Tours', 'client-tour' ),
			array( self::class, 'render_widget' )
		);
	}

	/**
	 * Render the dashboard widget content.
	 */
	public static function render_widget(): void {
		$user       = wp_get_current_user();
		$all_tours  = WCT_Tour_Loader::get_all_valid_tours();
		$completed  = get_user_meta( get_current_user_id(), WCT_META_KEY, true );
		$completed  = is_array( $completed ) ? $completed : array();

		// Filter to tours this user's role can see. All trigger types shown.
		$eligible = array_filter(
			$all_tours,
			static function ( $tour ) use ( $user ) {
				foreach ( $tour['target_roles'] as $role ) {
					if ( in_array( $role, (array) $user->roles, true ) ) {
						return true;
					}
				}
				return false;
			}
		);

		if ( empty( $eligible ) ) {
			echo '<p>' . esc_html__( 'No tours available for your account.', 'client-tour' ) . '</p>';
			return;
		}

		echo '<style>
		.wct-widget-list { list-style: none; margin: 0; padding: 0; }
		.wct-widget-item {
			display: flex; align-items: center; justify-content: space-between;
			padding: 8px 0; border-bottom: 1px solid #f0f0f1;
			gap: 12px;
		}
		.wct-widget-item:last-child { border-bottom: none; }
		.wct-widget-item-left { display: flex; align-items: center; gap: 8px; min-width: 0; }
		.wct-widget-check {
			width: 18px; height: 18px; flex-shrink: 0;
			border-radius: 50%; border: 2px solid #c3c4c7;
			display: flex; align-items: center; justify-content: center;
		}
		.wct-widget-check.done { background: #00a32a; border-color: #00a32a; color: #fff; font-size: 11px; }
		.wct-widget-label { font-size: 13px; color: #1d2327; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
		.wct-widget-start {
			flex-shrink: 0; font-size: 12px; padding: 3px 10px;
			text-decoration: none;
		}
		</style>';

		echo '<ul class="wct-widget-list">';
		foreach ( $eligible as $tour ) {
			$is_done   = in_array( $tour['id'], $completed, true );
			$label     = esc_html( $tour['label'] ?? $tour['id'] );
			$check_cls = $is_done ? 'wct-widget-check done' : 'wct-widget-check';
			$checkmark = $is_done ? '✓' : '';

			// Build the launch URL: navigate to target_page with wct_force param.
			$launch_url = esc_url( admin_url( $tour['target_page'] . ( strpos( $tour['target_page'], '?' ) !== false ? '&' : '?' ) . 'wct_force=' . rawurlencode( $tour['id'] ) ) );

			echo '<li class="wct-widget-item">';
			echo '<div class="wct-widget-item-left">';
			printf( '<span class="%s">%s</span>', esc_attr( $check_cls ), esc_html( $checkmark ) );
			printf( '<span class="wct-widget-label" title="%s">%s</span>', esc_attr( $tour['label'] ?? $tour['id'] ), $label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $label is esc_html()'d on assignment above
			echo '</div>';
			printf(
				'<a href="%s" class="button button-small wct-widget-start">%s</a>',
				esc_url( $launch_url ),
				esc_html( $is_done ? __( 'Replay', 'client-tour' ) : __( 'Start', 'client-tour' ) )
			);
			echo '</li>';
		}
		echo '</ul>';
	}
}
