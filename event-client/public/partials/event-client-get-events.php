<?php

// Events
class Get_All_Events_Route  {
    
    public function __construct() {
        add_action( 'rest_api_init', array($this, 'sbhc_get_all_events_route') );  
    }

    public function sbhc_get_all_events() {

            $args = array( 'post_type' => 'event', 'posts_per_page' => 8 );
            $loop = new WP_Query( $args );
            $events_data = [];	
		
            while ( $loop->have_posts() ) : $loop->the_post();
            $events_data[] = [
                'event_ID'  => get_the_ID(),
                'event_title'  => get_the_title(),
				'objectives' => get_field('learning_objectives'),
                'event_meta'    => [
                    'event_start_time' => get_post_meta(get_the_ID(), '_event_start_time', true ),
                    'event_end_time' => get_post_meta(get_the_ID(), '_event_end_time', true ),
                    'event_start' => get_post_meta(get_the_ID(), '_event_start', true ),
                    'event_end' => get_post_meta(get_the_ID(), '_event_end', true ),
                    'event_start_date' => get_post_meta(get_the_ID(), '_event_start_date', true ),
                    'event_end_date' => get_post_meta(get_the_ID(), '_event_end_date', true ),
                    'event_start_local' => get_post_meta(get_the_ID(), '_event_start_local', true ),
                    'event_end_local' => get_post_meta(get_the_ID(), '_event_end_local', true ),
                ],
                'featured_image_url' => get_the_post_thumbnail_url(),
            ];
            endwhile;            

            return [ 
                "events_list"  => $events_data,
            ];           


    }
    
    public function sbhc_get_all_events_route() {
        register_rest_route('sbhc/v2', 'events/', array(
            "methods" => WP_REST_Server::READABLE, 
            "callback"	=> array( $this, 'sbhc_get_all_events' ),
			"permission_callback" => array( $this, 'get_events_permission'),
        ));       
    }
	
	public function get_events_permission() {
		//return current_user_can('edit_posts');
		if( is_user_logged_in() ) {
			return true;
		}
		
		return false;
	}
       
}

new Get_All_Events_Route();

?>