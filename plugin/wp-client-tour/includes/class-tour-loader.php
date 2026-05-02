<?php
defined( 'ABSPATH' ) || exit;

class WCT_Tour_Loader {

	/**
	 * Return all tours eligible for the current user on the current admin page.
	 *
	 * @return array Array of validated tour config arrays.
	 */
	public function get_eligible_tours(): array {
		// Force branch: wct_force bypasses completion state (used by dashboard widget).
		$forced = $this->maybe_get_forced_tour();
		if ( null !== $forced ) {
			return array( $forced );
		}

		// Resume branch: honour wct_resume + wct_step if present and valid.
		$resumed = $this->maybe_get_resumed_tour();
		if ( null !== $resumed ) {
			return array( $resumed );
		}

		$tours     = self::get_all_valid_tours();
		$eligible  = array();
		$test_mode = (bool) get_option( WCT_OPTION_TEST_MODE, false );

		foreach ( $tours as $tour ) {
			if ( 'manual' === $tour['trigger'] ) {
				continue;
			}
			if ( ! $this->matches_current_page( $tour ) ) {
				continue;
			}
			if ( ! self::matches_user_role( $tour ) ) {
				continue;
			}
			if ( 'auto_once' === $tour['trigger'] && ! $test_mode && $this->user_has_completed( $tour['id'] ) ) {
				continue;
			}

			$eligible[] = $tour;
		}

		return $eligible;
	}

	/**
	 * If wct_force is present, return that tour ignoring completion state.
	 * Role check is still enforced. Used by the dashboard widget launch links.
	 *
	 * @return array|null
	 */
	private function maybe_get_forced_tour(): ?array {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['wct_force'] ) ) {
			return null;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tour_id = sanitize_text_field( wp_unslash( $_GET['wct_force'] ) );
		if ( ! preg_match( '/^[a-z0-9-]+$/', $tour_id ) ) {
			return null;
		}

		foreach ( self::get_all_valid_tours() as $tour ) {
			if ( $tour['id'] !== $tour_id ) {
				continue;
			}
			if ( ! self::matches_user_role( $tour ) ) {
				return null;
			}
			return $tour;
		}

		return null;
	}

	/**
	 * If valid wct_resume + wct_step params are present, return the resumed tour
	 * with resumeStep injected. Returns null if params are absent or invalid.
	 *
	 * @return array|null
	 */
	private function maybe_get_resumed_tour(): ?array {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['wct_resume'] ) || ! isset( $_GET['wct_step'] ) ) {
			return null;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tour_id = sanitize_text_field( wp_unslash( $_GET['wct_resume'] ) );
		if ( ! preg_match( '/^[a-z0-9-]+$/', $tour_id ) ) {
			return null;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$step_index = (int) $_GET['wct_step'];
		if ( $step_index < 0 ) {
			return null;
		}

		$tour = null;
		foreach ( self::get_all_valid_tours() as $candidate ) {
			if ( $candidate['id'] === $tour_id ) {
				$tour = $candidate;
				break;
			}
		}

		if ( null === $tour ) {
			return null;
		}

		// Still enforce role — users cannot resume tours they shouldn't see.
		if ( ! self::matches_user_role( $tour ) ) {
			return null;
		}

		if ( $step_index >= count( $tour['steps'] ) ) {
			return null;
		}

		$tour['resumeStep'] = $step_index;
		return $tour;
	}

	/**
	 * Scan the tours/ directory and return every tour that passes schema
	 * validation. Used by the admin page so its table cannot drift from
	 * the loader's notion of "valid". No page/role/seen filtering applied.
	 *
	 * @return array
	 */
	public static function get_all_valid_tours(): array {
		static $cache = null;
		if ( null !== $cache ) {
			return $cache;
		}

		$files = glob( WCT_TOURS_DIR . '*.json' );
		if ( empty( $files ) ) {
			$cache = array();
			return $cache;
		}

		$tours = array();
		foreach ( $files as $file ) {
			$raw = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( false === $raw ) {
				continue;
			}
			$tour = json_decode( $raw, true );
			if ( ! is_array( $tour ) ) {
				continue;
			}
			if ( ! self::validate_tour( $tour ) ) {
				continue;
			}
			$tours[] = $tour;
		}

		$cache = $tours;
		return $cache;
	}

	/**
	 * Check whether a tour with the given ID exists on disk and validates.
	 *
	 * @param string $tour_id Tour ID.
	 * @return bool
	 */
	public static function tour_exists( string $tour_id ): bool {
		foreach ( self::get_all_valid_tours() as $tour ) {
			if ( $tour['id'] === $tour_id ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check whether the tour targets the current admin page.
	 *
	 * Matches on $pagenow plus any query string parameters encoded in target_page.
	 * Example target_page values: "edit.php", "admin.php?page=wc-orders"
	 *
	 * @param array $tour Tour config.
	 * @return bool
	 */
	private function matches_current_page( array $tour ): bool {
		global $pagenow;

		$target = $tour['target_page'];

		// Split target_page into file and query parts.
		$parts      = explode( '?', $target, 2 );
		$target_php = $parts[0];
		$target_qs  = isset( $parts[1] ) ? $parts[1] : '';

		if ( $pagenow !== $target_php ) {
			return false;
		}

		// If the target has no query string, matching on $pagenow alone is enough.
		if ( '' === $target_qs ) {
			return true;
		}

		// Every key=value pair in target_page must be present in the current request.
		parse_str( $target_qs, $required_params );
		foreach ( $required_params as $key => $value ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! isset( $_GET[ $key ] ) || sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) !== $value ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check whether the current user holds at least one of the tour's target roles.
	 *
	 * @param array $tour Tour config.
	 * @return bool
	 */
	public static function matches_user_role( array $tour ): bool {
		$user = wp_get_current_user();
		if ( ! $user || ! $user->exists() ) {
			return false;
		}

		foreach ( $tour['target_roles'] as $role ) {
			if ( in_array( $role, (array) $user->roles, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check whether the current user has already completed this tour.
	 *
	 * @param string $tour_id Tour ID.
	 * @return bool
	 */
	private function user_has_completed( string $tour_id ): bool {
		$user_id   = get_current_user_id();
		$completed = get_user_meta( $user_id, WCT_META_KEY, true );

		if ( ! is_array( $completed ) ) {
			return false;
		}

		return in_array( $tour_id, $completed, true );
	}

	/**
	 * Validate that a tour config contains all required fields with correct types.
	 *
	 * @param array $tour Tour config.
	 * @return bool
	 */
	public static function validate_tour( array $tour ): bool {
		$required = array( 'id', 'target_page', 'target_roles', 'trigger', 'steps' );
		foreach ( $required as $field ) {
			if ( ! isset( $tour[ $field ] ) ) {
				return false;
			}
		}

		if ( ! is_string( $tour['id'] ) || ! preg_match( '/^[a-z0-9-]+$/', $tour['id'] ) ) {
			return false;
		}

		if ( ! is_array( $tour['target_roles'] ) || empty( $tour['target_roles'] ) ) {
			return false;
		}

		if ( ! in_array( $tour['trigger'], array( 'auto_once', 'auto_always', 'manual' ), true ) ) {
			return false;
		}

		if ( ! is_array( $tour['steps'] ) || empty( $tour['steps'] ) ) {
			return false;
		}

		$step_required   = array( 'id', 'selector', 'position', 'title', 'body' );
		$valid_positions = array( 'top', 'right', 'bottom', 'left' );

		foreach ( $tour['steps'] as $step ) {
			if ( ! is_array( $step ) ) {
				return false;
			}
			foreach ( $step_required as $field ) {
				if ( ! isset( $step[ $field ] ) || ! is_string( $step[ $field ] ) ) {
					return false;
				}
			}
			if ( ! in_array( $step['position'], $valid_positions, true ) ) {
				return false;
			}
			// Optional: navigate_on_next must be a safe relative admin path.
			if ( isset( $step['navigate_on_next'] ) ) {
				if ( ! is_string( $step['navigate_on_next'] ) || ! self::is_safe_relative_path( $step['navigate_on_next'] ) ) {
					return false;
				}
			}
			// Optional: per-step target_page must be a string.
			if ( isset( $step['target_page'] ) && ! is_string( $step['target_page'] ) ) {
				return false;
			}
			// Optional: navigate_label must be a string.
			if ( isset( $step['navigate_label'] ) && ! is_string( $step['navigate_label'] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Ensure a path is a safe relative admin path.
	 * Rejects absolute URLs, protocol-relative, javascript:, data:, and path traversal.
	 *
	 * @param string $path Path to check.
	 * @return bool
	 */
	private static function is_safe_relative_path( string $path ): bool {
		if ( preg_match( '#^(https?:|//|javascript:|data:)#i', $path ) ) {
			return false;
		}
		if ( strpos( $path, '..' ) !== false ) {
			return false;
		}
		return true;
	}
}
