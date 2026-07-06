<?php
// This is for templating only. add this code to the Event Settings. Enable Advance and Super Advance mode to make the listing template field show up. and paste this template Default Event Listing Format field:
?>
<div class="sbhc-event-list em-event em-item {is_cancelled}em-event-cancelled{/is_cancelled}" style="--default-border:#_CATEGORYCOLOR">
	<div class="em-item-image">
				#_LBIMAGE
	</div>
	<div class="em-item-info">
		<h3 class="em-item-title">#_EVENTNAME</h3>
		{is_cancelled}
		<div class="em-event-cancelled em-notice em-notice-error em-notice-thin em-notice-icon">
			<span class="em-icon em-icon-cross-circle"></span>
			This event has been cancelled.		</div>
		{/is_cancelled}
		<div class="em-event-meta em-item-meta">
			<div class="em-item-meta-line em-event-date em-event-meta-datetime">
				<span class="em-icon-calendar em-icon"></span>
				#_EVENTDATES  
			</div>
			#_EMDATE
			{bookings_open}
			<div style="display: none" class="em-item-meta-line em-event-prices">
				<span class="em-icon-ticket em-icon"></span>
				#_EVENTPRICERANGE
			</div>
			{/bookings_open}
			#_EMLOCATION
			{has_category}
			<div class="em-item-meta-line em-item-taxonomy em-event-categories">
				<span class="em-icon-category em-icon"></span>
				<div>#_EVENTCATEGORIES</div>
			</div>
			{/has_category}	
			#_EMTICKETPRICE	
			{has_location_venue}
			<div style="display: none" class="em-item-meta-line em-event-location">
				<span class="em-icon-location em-icon"></span>
				#_LOCATIONLINK
			</div>
			{/has_location_venue}
			{has_event_location}
			<div class="em-item-meta-line em-event-location">
				<span class="em-icon-at em-icon"></span>
				#_EVENTLOCATION
			</div>
			{/has_event_location}
			
			#_EMPRESENTER
			
			{has_tag}
			<div style="display: none" class="em-item-meta-line em-item-taxonomy em-event-tags">
				<span class="em-icon-tag em-icon"></span>
				<div>#_EVENTTAGS</div>
			</div>
			{/has_tag}
		</div>
		<div class="em-item-desc summary">
			#_EMSUMMARY
		</div>
		<div class="objectives">
			#_EMOBJ
		</div>
		<div class="more-information">
			#_EMCONTACTINFO
		</div>
		<div class="em-item-actions input">
			#_EMURL
			<a style="display: none" class="em-item-read-more button" href="#_EVENTURL">More Info</a>
			{bookings_open}
			<a style="display: none" class="em-event-book-now button" href="#_EVENTURL#em-event-booking-form">
				<span class="em-icon em-icon-ticket"></span>
				Book Now!			</a>
			{/bookings_open}
		</div>
	</div>
</div>