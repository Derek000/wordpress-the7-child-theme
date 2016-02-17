<?php
/**
 * List View Single Event
 * This file contains one event in the list view
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/list/single-event.php
 *
 * @package TribeEventsCalendar
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// Setup an array of venue details for use later in the template
$venue_details = tribe_get_venue_details();

// Venue
$has_venue_address = ( ! empty( $venue_details['address'] ) ) ? ' location' : '';

// Organizer
$organizer = tribe_get_organizer();
$start_ts_meta = tribe_get_start_date(null, true, 'c', "UTC");
$end_ts_meta = tribe_get_end_date(null, true, 'c', "UTC");

?>
<div itemscope itemtype="http://schema.org/Event">
  <meta itemprop="organizer" content="<?php echo $organizer ?>">
<!-- Event Cost -->

<?php if ( tribe_get_cost() ) : ?>
	<div class="tribe-events-event-cost">
		<span><?php echo tribe_get_cost( null, true ); ?></span>
	</div>
<?php endif; ?>

<!-- Event Title -->
<?php do_action( 'tribe_events_before_the_event_title' ) ?>
<h2 class="tribe-events-list-event-title" itemprop="name">
	<a itemprop="url" class="tribe-event-url" href="<?php echo esc_url( tribe_get_event_link() ); ?>" title="<?php the_title_attribute() ?>" rel="bookmark">
		<?php the_title() ?>
	</a>
</h2>
<?php do_action( 'tribe_events_after_the_event_title' ) ?>

<!-- Event Meta -->
<?php do_action( 'tribe_events_before_the_meta' ) ?>
<div class="tribe-events-event-meta">
	<div class="author <?php echo esc_attr( $has_venue_address ); ?>">

		<!-- Schedule & Recurrence Details -->
		<div class="tribe-event-schedule-details">
      <meta itemprop="startDate" content="<?php echo $start_ts_meta ?>">
      <meta itemprop="endDate" content="<?php echo $end_ts_meta ?>">
			<?php echo tribe_events_event_schedule_details() ?>
		</div>
		<?php if ( $venue_details ) : ?>
			<!-- Venue Display Info -->
			<div class="tribe-events-venue-details" itemprop="location"  itemscope="" itemtype="http://schema.org/Place">
        <span itemprop="name">
          <?php echo $venue_details['name']; ?>,
        </span>
        <span itemprop="address">
          <?php echo $venue_details['address']; ?>
        </span>
			</div> <!-- .tribe-events-venue-details -->
		<?php endif; ?>

	</div>
</div><!-- .tribe-events-event-meta -->
<?php do_action( 'tribe_events_after_the_meta' ) ?>

<!-- Event Image -->
<?php echo tribe_event_featured_image( null, 'medium' ) ?>

<!-- Event Content -->
<?php do_action( 'tribe_events_before_the_content' ) ?>
<div class="tribe-events-list-event-description tribe-events-content" itemprop="description">
	<?php echo tribe_events_get_the_excerpt();?>
	<a href="<?php echo esc_url( tribe_get_event_link() ); ?>" class="tribe-events-read-more" rel="bookmark"><?php esc_html_e( 'Find out more', 'the-events-calendar' ) ?> &raquo;</a>
</div><!-- .tribe-events-list-event-description -->
<?php
do_action( 'tribe_events_after_the_content' );
?>
</div> <!-- End of Event item scope -->
