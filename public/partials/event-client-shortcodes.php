<?php
// Shortcode for the SBHC homepage event section with tab
add_shortcode('event-loop', function() {
    ob_start();
	$today = date('Y-m-d');
	$args = array( 
		'post_type' => 'event', 
		'post_status'=>'publish', 
		'posts_per_page' => 2,   
		'orderby'  => '_event_start_date',
		'order' => 'ASC', 
		'meta_query' => array(
			array(
			'key'        => '_event_start_date',
			'meta-value' => date('Y-m-d'),
			'value'      => $today,
			'compare'    => '>=',
// 			'type'       => 'CHAR'
		 )
    ));
	$loop = new WP_Query( $args );
	global $post;

    echo '<div id="he-list">';

	while ( $loop->have_posts() ) : $loop->the_post();
	    echo '<div class="he-item">';
	    $source = get_post_meta($post->ID, '_event_start_date', true);
        $date = new DateTime($source);
        $endtime = get_post_meta($post->ID, '_event_end_time', true);
        $starttime = get_post_meta($post->ID, '_event_start_time', true);
        $d = DateTime::createFromFormat( 'd-m-Y H:i:s', $date->format('d-m-Y H:i:s') );
	    date_default_timezone_set(get_post_meta($post->ID, '_event_timezone', true));
	
        ?>

<h2 style="display: none;"><a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a></h2>
<h2><?php echo get_the_title(); ?></h2>
        
        
        <h3 class="edate"><?php echo $date->format('l, F j, Y'); ?></h3>
        <h3 class="etime"><?php echo date('h:i A', strtotime($starttime)); ?> <?php echo $endtime ? ' - '. date('h:i A', strtotime($endtime)) : ''; ?> <?php echo date('I', $d->getTimestamp() ) ? date('T', $d->getTimestamp()) : date('T', $d->getTimestamp()); ?></h3>
        
        
        <?php
 
        // create a $dt object with the UTC timezone
        
        $dt_utc = new DateTimeImmutable($starttime, new DateTimeZone( date('T', $d->getTimestamp()) ));

        if (date('I', $d->getTimestamp() )) {

            // Create a new instance with the new timezone
            $ct = $dt_utc->setTimezone(new DateTimeZone( 'CDT' ));
            $mst = $dt_utc->setTimezone(new DateTimeZone('MDT'));
    		$et = $dt_utc->setTimezone(new DateTimeZone('EDT'));
            $pt = $dt_utc->setTimezone(new DateTimeZone('PDT'));
        } else {
            //$dt_utc = new DateTimeImmutable($starttime, new DateTimeZone( date('T', $d->getTimestamp()) ));
            // Create a new instance with the new timezone
            $ct = $dt_utc->setTimezone(new DateTimeZone( 'CST' ));
            $mst = $dt_utc->setTimezone(new DateTimeZone('MST'));
    		$et = $dt_utc->setTimezone(new DateTimeZone('EST'));
            $pt = $dt_utc->setTimezone(new DateTimeZone('PST'));
        }        
        
            
        // format the datetime
        ?>
        <p  class="other-timezone">
			
			<?php 
					echo $pt->format('h:i A T'). ' | '. $mst->format('h:i A T') .' | '. $ct->format('h:i A T') .' | '. $et->format('h:i A T');
			?>
					
		</p>
        
        
        <?php echo get_field('event_location_type') ? '<p id="location-type">'. get_field('event_location_type'). '</p>' : ''; ?>        
        
        <?php echo get_field('location') ? '<p class="location">Location: '. get_field('location'). '</p>' : '<p class="location">Location: Online</p>'; ?>
        
		<?php if(get_field('summary')): ?>
				<div class="summary">
						<?php 
							if(get_field('short_summary')): 
								echo get_field('short_summary');
							else: 
								echo get_field('summary');
							endif;
						?>
				</div>
		<?php endif; ?>
        
        
        
        <?php echo get_field('registration_link') ? '<a href="'.get_field('registration_link').'" target="_blank"class="reg-link">More info & Registration</a>' : ''; ?>
        <?php
        echo '</div>';
	endwhile;	
	
	echo '</div>';
	$html = ob_get_contents();
$output = ob_get_clean();
return $output;
});