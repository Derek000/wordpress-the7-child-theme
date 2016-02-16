<?php
/**
 * Single Event Meta (Venue) Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe-events/modules/meta/venue.php
 *
 * @package TribeEventsCalendar
 */

if ( ! has_post_thumbnail() ) {
	return;
}
?>

<div class="tribe-events-meta-group tribe-events-meta-group-image">
<?php
  // Event featured image, but exclude link
  echo tribe_event_featured_image( $event_id, 'full', false );
?>
</div>
