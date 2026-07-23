<?php

// Summary
add_filter('em_event_output_placeholder','em_summary',1,3);
function em_summary($replace, $EM_Event, $result){
    if( $result == '#_EMSUMMARY' ){
        $replace = "";
        $id = $EM_Event->post_id;
		
        if(get_field('summary', $id )):
            $replace .= '<h3>Summary:</h3>';
            $replace .= get_field('summary', $id );
        endif;		
		
    }
    return $replace;
}


// Learning Objectives
add_filter('em_event_output_placeholder','em_learnobj',1,3);
function em_learnobj($replace, $EM_Event, $result){
    if( $result == '#_EMOBJ' ){
        $replace = "";
        $id = $EM_Event->post_id;
		
    // Check rows exists.
    if( have_rows('learning_objectives', $id ) ):
        $replace .= '<h3>Learning Objectives:</h3>';
        $replace .= '<ul>';
        // Loop through rows.
        while( have_rows('learning_objectives', $id ) ) : the_row();
    
            // Load sub field value.
            //$sub_value = str_replace('&nbsp;', ' ', get_sub_field('objectives', $id));
            $entities = str_replace('&nbsp;', ' ', htmlentities(get_sub_field('objectives', $id)));
            //html_entity_decode($entities);            
            $replace .=  $entities != '' ? '<li>' .html_entity_decode($entities). '</li>' : '';
        // End loop.
        endwhile;
        $replace .= '</ul>';
    // No value.
    else :
        // Do something...
    endif;
		
    }
    return $replace;
}


// Contact Information
add_filter('em_event_output_placeholder','em_contactinfo',1,3);
function em_contactinfo($replace, $EM_Event, $result){
    if( $result == '#_EMCONTACTINFO' ){
        $replace = "";
        $id = $EM_Event->post_id;

        if(get_field('name', $id ) || get_field('number',  $id  ) || get_field('address', $id )):
            $replace .= '<h3>For more information contact:</h3>';
        endif;
        
         $replace .=  get_field('name', $id ) ? '<p>Name: ' .get_field('name', $id ). '</p>' : '';
         $replace .=  get_field('number', $id ) ? '<p>Phone: ' .get_field('number', $id ). '</p>' : '';
         $replace .=  get_field('address', $id ) ? '<p>Email: ' .get_field('address', $id ). '</p>' : '';		
		
		
    }
    return $replace;
}


// Event URL
add_filter('em_event_output_placeholder','em_url',1,3);
function em_url($replace, $EM_Event, $result){
    if( $result == '#_EMURL' ){
        $replace = "";
        $id = $EM_Event->post_id;
		if(get_field('registration_link', $id)):
            
			$replace = '<a id="register-btn" target="_blank" href="' .get_field('registration_link', $id ). '">Register Here</a>';
		else: 
			$replace = '';

        endif;
    }
    return $replace;
}

// Presenter
add_filter('em_event_output_placeholder','em_presenter',1,3);
function em_presenter($replace, $EM_Event, $result){
    if( $result == '#_EMPRESENTER' ){
        $replace = "";
        $id = $EM_Event->post_id;
		if(get_field('presenter', $id)):
            
			$replace .= '<div class="em-item-meta-line em-item-presenter em-event-presenter"><span class="em-icon-presenter em-icon"></span>';
			$replace .=	'<div>'.get_field('presenter', $id ).'</div>';
			$replace .= '</div>';

        endif;
    }
    return $replace;
}

// Image lightbox
add_filter('em_event_output_placeholder','em_lbimage',1,3);
function em_lbimage($replace, $EM_Event, $result){
    if( $result == '#_LBIMAGE' ){
        $replace = "";
        $id = $EM_Event->post_id;
        $featured_ID = get_post_thumbnail_id($id);
        $title = get_post_meta( $featured_ID, '_wp_attachment_image_alt', true );
        /* grab the url for the full size featured image */
        $featured_img_url = get_the_post_thumbnail_url($id,'full'); 
 
        /* link thumbnail to full size image for use with lightbox*/
        $replace = '<a data-lity data-title="'.$title.'" href="'.esc_url($featured_img_url).'" rel="lightbox"><span class="et_pb_image_wrap "><img src="'.$featured_img_url.'"/><br></span></a>';
    }
    return $replace;
}



// Date
add_filter('em_event_output_placeholder','em_date',1,3);
function em_date($replace, $EM_Event, $result){
    if( $result == '#_EMDATE' ){
        $replace = "";
        $id = $EM_Event->post_id;
		$source = get_post_meta( $id, '_event_start_date', true);
		$date = new DateTime($source);
		$endtime = get_post_meta($id, '_event_end_time', true);
		$starttime = get_post_meta($id, '_event_start_time', true);
        $d = DateTime::createFromFormat( 'd-m-Y H:i:s', $date->format('d-m-Y H:i:s') );		
		date_default_timezone_set(get_post_meta($id, '_event_timezone', true));
		
		$replace .= '<div class="em-item-meta-line em-item-presenter em-event-presenter"><span class="em-icon-clock em-icon"></span>';
		$replace .= date('h:i A', strtotime($starttime)); 
		$replace .= $endtime ? ' - '. date('h:i A', strtotime($endtime)). ' ' : ''; 
		$replace .= date('I', $d->getTimestamp() ) ? date('T', $d->getTimestamp()) : date('T', $d->getTimestamp());
		$replace .= '</div>';
		
    }
    return $replace;
}

// Ticket
add_filter('em_event_output_placeholder','em_ticketprice',1,3);
function em_ticketprice($replace, $EM_Event, $result){
    if( $result == '#_EMTICKETPRICE' ){
        $replace = "";
        $id = $EM_Event->post_id;
		
        if(get_field('ticket_price', $id )):
			$replace .= '<div class="em-item-meta-line em-item-ticket em-event-presenter"><span class="em-icon-ticket em-icon"></span>';
			$replace .= '<div>'.get_field('ticket_price', $id ).'</div></div>';
		else: 
			$replace .= '<div class="em-item-meta-line em-item-ticket em-event-presenter"><span class="em-icon-ticket em-icon"></span><div>Free</div></div>';      
        endif;		
		
    }
    return $replace;
}


// Location
add_filter('em_event_output_placeholder','em_location',1,3);
function em_location($replace, $EM_Event, $result){
    if( $result == '#_EMLOCATION' ){
        $replace = "";
        $id = $EM_Event->post_id;
		
        if(get_field('event_location_type', $id )):

			$replace .= '<div class="em-item-meta-line em-item-ticket em-event-location"><span class="em-icon-location em-icon"></span><div>'.get_field('event_location_type', $id ).'</div></div>';

		else: 

			$replace .= '<div class="em-item-meta-line em-item-ticket em-event-location"><span class="em-icon-location em-icon"></span><div>Virtual</div></div>';   
	
        endif;
		
    }
    return $replace;
}