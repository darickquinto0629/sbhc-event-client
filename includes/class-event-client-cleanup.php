<?php

/**
 * Permanently deletes Events Manager events that ended more than 3 months ago,
 * including their featured images.
 *
 * @package    Event_Client
 * @subpackage Event_Client/includes
 */
class Event_Client_Cleanup {

	const CRON_HOOK  = 'event_client_delete_old_events';
	const TIMEZONE   = 'America/New_York';
	const BATCH_SIZE = 50;

	public function schedule() {
		if ( wp_next_scheduled( self::CRON_HOOK ) ) {
			return;
		}

		$timezone = new DateTimeZone( self::TIMEZONE );
		$next_run = new DateTime( 'last day of this month', $timezone );
		$next_run->setTime( 0, 0 );

		if ( $next_run <= new DateTime( 'now', $timezone ) ) {
			$next_run = new DateTime( 'last day of next month', $timezone );
			$next_run->setTime( 0, 0 );
		}

		wp_schedule_single_event( $next_run->getTimestamp(), self::CRON_HOOK );
	}

	public static function unschedule() {
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}

	public function delete_old_events() {
		global $wpdb;

		if ( ! function_exists( 'em_get_event' ) || ! defined( 'EM_EVENTS_TABLE' ) ) {
			return;
		}

		// event_end is stored in UTC by Events Manager.
		$cutoff = new DateTime( '-3 months', new DateTimeZone( 'UTC' ) );

		// Recurring/repeating parents are skipped; their past occurrences are deleted individually.
		$event_ids = $wpdb->get_col( $wpdb->prepare(
			'SELECT event_id FROM ' . EM_EVENTS_TABLE . " WHERE event_end < %s AND event_type NOT IN ( 'recurring', 'repeating' ) ORDER BY event_end ASC LIMIT %d",
			$cutoff->format( 'Y-m-d H:i:s' ),
			self::BATCH_SIZE
		) );

		if ( empty( $event_ids ) ) {
			return;
		}

		// Events Manager refuses deletes without a capable user, and cron runs with none.
		$admin_ids = get_users( array(
			'role'    => 'administrator',
			'number'  => 1,
			'orderby' => 'ID',
			'order'   => 'ASC',
			'fields'  => 'ID',
		) );

		if ( empty( $admin_ids ) ) {
			return;
		}

		$previous_user_id = get_current_user_id();
		wp_set_current_user( (int) $admin_ids[0] );

		$deleted = 0;

		try {
			foreach ( $event_ids as $event_id ) {
				$EM_Event = em_get_event( (int) $event_id );
				if ( empty( $EM_Event->event_id ) ) {
					continue;
				}

				$post_id      = (int) $EM_Event->post_id;
				$thumbnail_id = $post_id ? (int) get_post_thumbnail_id( $post_id ) : 0;

				if ( ! $EM_Event->delete( true ) ) {
					continue;
				}

				$deleted++;

				if ( $thumbnail_id && ! $this->is_thumbnail_in_use( $thumbnail_id ) ) {
					wp_delete_attachment( $thumbnail_id, true );
				}
			}
		} finally {
			wp_set_current_user( $previous_user_id );
		}

		// Full batch with progress means there may be more left; continue shortly instead of waiting a day.
		if ( $deleted > 0 && count( $event_ids ) === self::BATCH_SIZE ) {
			wp_clear_scheduled_hook( self::CRON_HOOK );
			wp_schedule_single_event( time() + 5 * MINUTE_IN_SECONDS, self::CRON_HOOK );
		}
	}

	private function is_thumbnail_in_use( $attachment_id ) {
		global $wpdb;

		return (bool) $wpdb->get_var( $wpdb->prepare(
			"SELECT 1 FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id' AND meta_value = %d LIMIT 1",
			$attachment_id
		) );
	}

}
