<?php
/**
 * Create_Event_Route Class
 *
 * Handles REST API routes for event creation and media uploads.
 * Provides endpoints for creating events and uploading media attachments
 * with full sanitization and permission checks.
 *
 * @package Event_Client
 * @subpackage REST_API
 * @since 1.0.0
 */
class Create_Event_Route {

	/**
	 * Constructor.
	 *
	 * Initializes the class and registers REST routes on rest_api_init hook.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'create_event_route' ) );
	}

	/**
	 * Create Event Handler
	 *
	 * Handles POST requests to create new events via REST API.
	 * Creates a post of type 'event' and synchronizes with ACF fields
	 * and optional Events Manager plugin database.
	 *
	 * @param WP_REST_Request $request The REST request object containing event data.
	 * @return WP_REST_Response The response with success status and post ID.
	 * @since 1.0.0
	 */
	public function sbhc_create_event( WP_REST_Request $request ) {
		// Extract and sanitize the required event title.
		$event_title = isset( $request['event_title'] ) ? trim( sanitize_text_field( $request['event_title'] ) ) : '';

		// Sanitize event content and extract meta input array.
		$event_content = isset( $request['event_content'] ) ? wp_kses_post( $request['event_content'] ) : '';
		$meta = isset( $request['meta_input'] ) ? $request['meta_input'] : array();

		// Extract and sanitize all meta fields
		$event_start_date = isset( $meta['event_start_date'] ) ? sanitize_text_field( $meta['event_start_date'] ) : '';
		$event_end_date = isset( $meta['event_end_date'] ) ? sanitize_text_field( $meta['event_end_date'] ) : '';
		$event_time_zone = isset( $meta['event_time_zone'] ) ? sanitize_text_field( $meta['event_time_zone'] ) : '';
		$event_start_time = isset( $meta['event_start_time'] ) ? sanitize_text_field( $meta['event_start_time'] ) : '';
		$event_end_time = isset( $meta['event_end_time'] ) ? sanitize_text_field( $meta['event_end_time'] ) : '';
		$event_start = isset( $meta['event_start'] ) ? sanitize_text_field( $meta['event_start'] ) : '';
		$event_end = isset( $meta['event_end'] ) ? sanitize_text_field( $meta['event_end'] ) : '';
		$event_start_local = isset( $meta['event_start_local'] ) ? sanitize_text_field( $meta['event_start_local'] ) : '';
		$event_end_local = isset( $meta['event_end_local'] ) ? sanitize_text_field( $meta['event_end_local'] ) : '';
		$event_active_status = isset( $meta['event_active_status'] ) ? sanitize_text_field( $meta['event_active_status'] ) : '';

		// Extract and sanitize all ACF fields
		$presenter = isset( $request['presenter'] ) ? sanitize_text_field( $request['presenter'] ) : '';
		$ticket_price = isset( $request['ticket_price'] ) ? sanitize_text_field( $request['ticket_price'] ) : '';
		$event_location = isset( $request['event_location'] ) ? sanitize_text_field( $request['event_location'] ) : '';
		$event_type = isset( $request['event_type'] ) ? sanitize_text_field( $request['event_type'] ) : '';
		$summary = isset( $request['summary'] ) ? sanitize_text_field( $request['summary'] ) : '';
		$short_summary = isset( $request['short_summary'] ) ? sanitize_text_field( $request['short_summary'] ) : '';
		$contact_name = isset( $request['contact_name'] ) ? sanitize_text_field( $request['contact_name'] ) : '';
		$contact_number = isset( $request['contact_number'] ) ? sanitize_text_field( $request['contact_number'] ) : '';
		$contact_address = isset( $request['contact_address'] ) ? sanitize_text_field( $request['contact_address'] ) : '';
		$registration_link = isset( $request['registration_link'] ) ? sanitize_text_field( $request['registration_link'] ) : '';

		// Extract objectives field
		$objectives = isset( $request['objectives'] ) ? $request['objectives'] : array();

		// Critical validation: event title is required for WordPress to create a post
		if ( empty( $event_title ) ) {
			return new WP_REST_Response( 
				array( 
					'success' => false,
					'message' => 'Cannot create event - event title is required',
					'error' => 'event_title',
					'error_code' => 'missing_event_title'
				), 
				400 
			);
		}

		// Build post arguments with sanitized meta fields.
		$args = array(
			'post_type'   => 'event',
			'post_title'  => $event_title,
			'post_content' => $event_content,
			'post_status' => 'publish',
			'post_author' => get_current_user_id() ? get_current_user_id() : 1,
			'meta_input'  => array(
				'_event_timezone'       => isset( $meta['event_time_zone'] ) ? sanitize_text_field( $meta['event_time_zone'] ) : '',
				'_event_start_time'     => isset( $meta['event_start_time'] ) ? sanitize_text_field( $meta['event_start_time'] ) : '',
				'_event_end_time'       => isset( $meta['event_end_time'] ) ? sanitize_text_field( $meta['event_end_time'] ) : '',
				'_event_start'          => isset( $meta['event_start'] ) ? sanitize_text_field( $meta['event_start'] ) : '',
				'_event_end'            => isset( $meta['event_end'] ) ? sanitize_text_field( $meta['event_end'] ) : '',
				'_event_start_date'     => isset( $meta['event_start_date'] ) ? sanitize_text_field( $meta['event_start_date'] ) : '',
				'_event_end_date'       => isset( $meta['event_end_date'] ) ? sanitize_text_field( $meta['event_end_date'] ) : '',
				'_event_active_status'  => isset( $meta['event_active_status'] ) ? sanitize_text_field( $meta['event_active_status'] ) : '',
				'_event_start_local'    => isset( $meta['event_start_local'] ) ? sanitize_text_field( $meta['event_start_local'] ) : '',
				'_event_end_local'      => isset( $meta['event_end_local'] ) ? sanitize_text_field( $meta['event_end_local'] ) : '',
			),
		);

		// Insert the post into the database.
		$result = wp_insert_post( $args );

		if ( $result && ! is_wp_error( $result ) ) {
			$post_id = $result;
			$errors = array();
			
			// Set featured image if provided in request.
			if ( isset( $request['featured_media'] ) ) {
				if ( ! set_post_thumbnail( $post_id, $request['featured_media'] ) ) {
					$errors[] = 'Warning: Failed to set featured image (attachment ID may be invalid)';
				}
			}

			// Update ACF fields if Advanced Custom Fields plugin is active.
			if ( function_exists( 'update_field' ) ) {
				// Wrap ACF updates with error handling
				$acf_updates = array(
					'presenter' => $presenter,
					'ticket_price' => $ticket_price,
					'location' => $event_location,
					'event_location_type' => $event_type,
					'summary' => $summary,
					'short_summary' => $short_summary,
					'name' => $contact_name,
					'number' => $contact_number,
					'address' => $contact_address,
					'registration_link' => $registration_link,
				);

				foreach ( $acf_updates as $field_name => $field_value ) {
					$result = update_field( $field_name, $field_value, $post_id );
					if ( false === $result ) {
						$errors[] = "Warning: Failed to update ACF field '$field_name'";
					}
				}

				// Add learning objectives as repeater rows if provided.
				if ( is_array( $objectives ) && ! empty( $objectives ) ) {
					foreach ( $objectives as $row ) {
						if ( ! empty( $row ) ) {
							$add_result = add_row( 'learning_objectives', array( 'objectives' => sanitize_text_field( $row ) ), $post_id );
							if ( false === $add_result ) {
								$errors[] = 'Warning: Failed to add one or more learning objectives';
								break;
							}
						}
					}
				}
			}

			// Sync event data to Events Manager plugin table if it exists.
			global $wpdb;
			$em_table = $wpdb->prefix . 'em_events';
			if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $em_table ) ) === $em_table ) {
				$em_data = array(
					'event_name'          => $event_title,
					'event_status'        => 1,
					'event_slug'          => sanitize_title( $event_title ),
					'post_id'             => $post_id,
					'event_start_date'    => $event_start_date,
					'event_end_date'      => $event_end_date,
					'event_start'         => $event_start,
					'event_end'           => $event_end,
					'event_timezone'      => $event_time_zone,
					'event_active_status' => $event_active_status,
					'event_owner'         => isset( $meta['event_owner'] ) ? sanitize_text_field( $meta['event_owner'] ) : '',
				);
				$insert_result = $wpdb->insert( $em_table, $em_data );
				if ( false === $insert_result ) {
					$errors[] = 'Warning: Failed to sync event data to Events Manager plugin';
				}
			}

			// Retrieve the saved post and ACF data from database
			$post = get_post( $post_id );
			$saved_data = array(
				'post_id' => $post_id,
				'title' => $post->post_title,
				'content' => $post->post_content,
				'meta' => get_post_meta( $post_id ),
			);

			// Include ACF field data if available
			if ( function_exists( 'get_field' ) ) {
				$saved_data['acf_fields'] = array(
					'presenter' => get_field( 'presenter', $post_id ),
					'ticket_price' => get_field( 'ticket_price', $post_id ),
					'location' => get_field( 'location', $post_id ),
					'event_location_type' => get_field( 'event_location_type', $post_id ),
					'summary' => get_field( 'summary', $post_id ),
					'short_summary' => get_field( 'short_summary', $post_id ),
					'name' => get_field( 'name', $post_id ),
					'number' => get_field( 'number', $post_id ),
					'address' => get_field( 'address', $post_id ),
					'registration_link' => get_field( 'registration_link', $post_id ),
					'learning_objectives' => get_field( 'learning_objectives', $post_id ),
				);
			}

			// Build response with saved data
			$response_array = array( 
				'success' => true,
				'post_id' => $post_id,
				'message' => 'Event created successfully',
				'data' => $saved_data
			);

			// Include any warnings that occurred during processing
			if ( ! empty( $errors ) ) {
				$response_array['warnings'] = $errors;
				$response_array['message'] = 'Event created successfully with ' . count( $errors ) . ' warning(s)';
			}

			return new WP_REST_Response( $response_array, 201 );
		}

		// Return error response if post creation failed.
		return new WP_REST_Response( 
			array( 
				'success' => false, 
				'message' => 'Failed to create event',
				'error' => is_wp_error( $result ) ? $result->get_error_code() : 'unknown_error',
				'error_details' => is_wp_error( $result ) ? $result->get_error_message() : 'Database insertion failed',
				'error_code' => is_wp_error( $result ) ? $result->get_error_code() : 'db_error'
			), 
			500 
		);
	}

	/**
	 * Media Upload Handler
	 *
	 * Handles POST requests to upload media files via REST API.
	 * Validates file types and permissions before processing.
	 *
	 * @param WP_REST_Request $request The REST request object with file data.
	 * @return WP_REST_Response Response with attachment ID or error message.
	 * @since 1.0.0
	 */
	public function media_upload( WP_REST_Request $request ) {
		// Verify user has permission to upload files.
		if ( ! current_user_can( 'upload_files' ) ) {
			return new WP_REST_Response( 
				array( 
					'success' => false, 
					'message' => 'Forbidden' 
				), 
				403 
			);
		}

		return $this->uploadFile();
	}

	/**
	 * File Upload Processing
	 *
	 * Processes the actual file upload, validates extension against whitelist,
	 * and handles WordPress media attachment creation.
	 *
	 * @return WP_REST_Response Response with attachment ID or error details.
	 * @since 1.0.0
	 */
	public function uploadFile() {
		// Load WordPress media handling functions.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		
		// Define allowed file extensions (whitelist approach for security).
		$allowed_extensions = array( 'jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'tiff', 'tif', 'ico', 'zip', 'pdf', 'docx' );
		
		// Extract file extension from uploaded file.
		$file_extension = strtolower( pathinfo( $_FILES['async-upload']['name'], PATHINFO_EXTENSION ) );
		
		// Validate file extension against whitelist.
		if ( ! in_array( $file_extension, $allowed_extensions, true ) ) {
			return wp_send_json( array(
				'success' => false,
				'data' => array(
					'message' => __( 'The uploaded file is not a valid file. Please try again.' ),
					'filename' => esc_html( $_FILES['async-upload']['name'] ),
				),
			) );
		}

		// Process file upload through WordPress media handler.
		$attachment_id = media_handle_upload( 'async-upload', 0 );

		// Handle upload errors.
		if ( is_wp_error( $attachment_id ) ) {
			return wp_send_json( array(
				'success' => false,
				'data' => array(
					'message' => $attachment_id->get_error_message(),
					'filename' => esc_html( $_FILES['async-upload']['name'] ),
				),
			) );
		}

		// Prepare attachment data for response.
		$attachment = wp_prepare_attachment_for_js( $attachment_id );
		if ( ! $attachment ) {
			return wp_send_json( array(
				'success' => false,
				'data' => array(
					'message' => __( 'Image cannot be uploaded.' ),
					'filename' => esc_html( $_FILES['async-upload']['name'] ),
				),
			) );
		}

		// Return success response with attachment ID.
		return wp_send_json( array(
			'success' => true,
			'data' => $attachment['id'],
		) );
	}
	
	/**
	 * Register REST Routes
	 *
	 * Registers the two main REST API endpoints:
	 * - POST /wp-json/sbhc/v2/postevent - Create events
	 * - POST /wp-json/sbhc/v2/media_upload - Upload media
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function create_event_route() {
		// Register event creation endpoint.
		register_rest_route( 'sbhc/v2', 'postevent', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'sbhc_create_event' ),
			'permission_callback' => array( $this, 'post_events_permission' ),
		) );

		// Register media upload endpoint.
		register_rest_route( 'sbhc/v2', 'media_upload', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'media_upload' ),
			'permission_callback' => array( $this, 'post_media_permission' ),
		) );
	}

	/**
	 * Check Event Creation Permission
	 *
	 * Verifies user has the 'edit_posts' capability required for event creation.
	 *
	 * @return bool True if user can edit posts, false otherwise.
	 * @since 1.0.0
	 */
	public function post_events_permission() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Check Media Upload Permission
	 *
	 * Verifies user has the 'upload_files' capability required for media uploads.
	 *
	 * @return bool True if user can upload files, false otherwise.
	 * @since 1.0.0
	 */
	public function post_media_permission() {
		return current_user_can( 'upload_files' );
	}
}

// Instantiate the class to register hooks and routes.
new Create_Event_Route();
