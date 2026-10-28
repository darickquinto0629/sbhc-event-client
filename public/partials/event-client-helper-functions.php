<?php

// This will allow Cross Origin (disabled for now as it generates header already sent error.)
// if ( ! function_exists( 'enable_cors' ) ) {
//     function enable_cors(){
//         header("Access-Control-Allow-Origin: *");
//     }
//     add_action('init','enable_cors');    
// }

/**
 * Events Manager API + ACF Integration
 * Hook into em_api_event_save to capture ACF fields from EM API payload
 */
add_action( 'em_api_event_save', function( $EM_Event, $data, $event_id ) {
    if ( ! function_exists( 'update_field' ) ) {
        return;
    }

    $post_id = $EM_Event->post_id ?? 0;
    if ( ! $post_id ) {
        return;
    }

    $data = is_array( $data ) ? $data : array();

    // ACF field mappings
    $acf_field_mappings = array(
        'ticket_price'        => 'ticket_price',
        'event_location_type' => 'event_location_type',
        'summary'             => 'summary',
        'short_summary'       => 'short_summary',
        'contact_name'        => 'name',
        'contact_number'      => 'number',
        'contact_address'     => 'address',
        'registration_link'   => 'registration_link',
    );

    // Update ACF fields
    foreach ( $acf_field_mappings as $request_field => $acf_field_name ) {
        if ( ! isset( $data[ $request_field ] ) || empty( $data[ $request_field ] ) ) {
            continue;
        }

        $value = $data[ $request_field ];

        if ( in_array( $acf_field_name, array( 'summary', 'short_summary' ), true ) ) {
            $value = wp_kses_post( $value );
        } else {
            $value = sanitize_text_field( $value );
        }

        update_field( $acf_field_name, $value, $post_id );
    }

    // Checked separately from the loop above so that false can un-feature an event.
    if ( isset( $data['featured_event'] ) ) {
        update_field( 'featured_event', rest_sanitize_boolean( $data['featured_event'] ) ? 1 : 0, $post_id );
    }

    // Handle location group field
    if ( ! empty( $data['location'] ) && is_array( $data['location'] ) ) {
        $location = array();
        $location_fields = array( 'location_name', 'country', 'address_1', 'address_2', 'city', 'state', 'zip_code' );
        
        foreach ( $location_fields as $field ) {
            if ( isset( $data['location'][ $field ] ) ) {
                $location[ $field ] = sanitize_text_field( $data['location'][ $field ] );
            }
        }
        
        update_field( 'location', $location, $post_id );
    }

    // Handle presenter repeater field (array of names; wrap a plain string for backward compatibility with older payloads)
    if ( ! empty( $data['presenter'] ) ) {
        $presenters = is_array( $data['presenter'] ) ? $data['presenter'] : array( $data['presenter'] );

        $presenter_rows = array();
        foreach ( $presenters as $presenter_name ) {
            if ( ! empty( $presenter_name ) ) {
                $presenter_rows[] = array( 'presenter_name_and_title' => sanitize_text_field( $presenter_name ) );
            }
        }

        // update_field() replaces all existing rows, so this never appends duplicates.
        update_field( 'presenter', $presenter_rows, $post_id );
    }

    // Handle learning objectives array
    if ( ! empty( $data['objectives'] ) && is_array( $data['objectives'] ) ) {
        $objective_rows = array();
        foreach ( $data['objectives'] as $objective ) {
            if ( ! empty( $objective ) ) {
                $objective_rows[] = array( 'objectives' => sanitize_text_field( $objective ) );
            }
        }

        // update_field() replaces all existing rows, so this never appends duplicates.
        update_field( 'learning_objectives', $objective_rows, $post_id );
    }

}, 10, 3 );

/**
 * Sort featured events first in Events Manager listings, then by the existing order.
 */
add_filter( 'em_events_build_sql_orderby', function( $orderby ) {
    global $wpdb;

    // In multisite global mode events span blogs, so a single postmeta table can't be used.
    if ( defined( 'EM_MS_GLOBAL' ) && EM_MS_GLOBAL ) {
        return $orderby;
    }

    $featured_first = "EXISTS ( SELECT 1 FROM {$wpdb->postmeta} WHERE {$wpdb->postmeta}.post_id = " . EM_EVENTS_TABLE . ".post_id AND {$wpdb->postmeta}.meta_key = 'featured_event' AND {$wpdb->postmeta}.meta_value = '1' ) DESC";

    $orderby = is_array( $orderby ) ? $orderby : array();
    array_unshift( $orderby, $featured_first );

    return $orderby;
} );