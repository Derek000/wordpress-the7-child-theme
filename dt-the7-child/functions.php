<?php
/*******************************************************************************
*
* Load parent theme stuff
*
******************************************************************************/

add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
  wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );

}

/*******************************************************************************
*
* BuddyPress stuffs
*
******************************************************************************/

add_action( 'bp_before_directory_members_page', 'kmpl_bp_action_on_member_restricted_page', 10, 0 );
add_action( 'bp_before_directory_groups_page', 'kmpl_bp_action_on_member_restricted_page', 10, 0 );
add_action( 'bp_before_directory_activity_page', 'kmpl_bp_action_on_member_restricted_page', 10, 0 );
add_action( 'bp_before_directory_forums_page', 'kmpl_bp_action_on_member_restricted_page', 10, 0 );
add_action( 'bp_before_directory_blogs_page', 'kmpl_bp_action_on_member_restricted_page', 10, 0 );
add_action( 'bp_members_screen_display_profile', 'kmpl_bp_action_on_member_restricted_page', 10, 0 );

/**
* Reject the curren page view. It currently only supports re-direction.
* But should perhaps support to just hide content and show a message or something?
*/
function reject_this_page_view() {
  global $post;
  $redirect_page_id = get_option( 'wc_memberships_redirect_page_id' );
  if ($post->ID == $redirect_page_id) {
    return;
  }

  $redirect_url = $redirect_page_id ? get_permalink( $redirect_page_id ) : home_url();
  bp_core_redirect( $redirect_url );
  exit;
}

/**
* Check if user has access to view certain page. Otherwise redirect.
*/
function kmpl_bp_before_directory_page(){
  if ( kmpl_wc_memberships_has_access_to_restricted_page() )  {
    return;
  }
  reject_this_page_view();
}

/**
* Check if user has access to view certain page.
*/
function kmpl_bp_action_on_member_restricted_page(){
  if (bp_is_my_profile() || kmpl_wc_memberships_has_access_to_restricted_page()) {
    return;
  }

  reject_this_page_view();
}


/**
* Check if current page is restricted (works with BuddyPress who doesn't have any page/post id)
*
* @param string|int|array $plans Optional. The membership plan or plans to check against.
*                                Accepts a plan slug, ID, or an array of slugs or IDs. Default: all plans.
* @param string $delay
* @param bool $exclude_trial
*/
function kmpl_wc_memberships_has_access_to_restricted_page( $plans = null, $delay = null, $exclude_trial = false ) {

  $has_access   = false;
  $member_since = null;
  $access_time  = null;

  // grant access to super users
  if ( is_user_logged_in() && current_user_can( 'wc_memberships_access_all_restricted_content' ) ) {
    $has_access = true;
  }

  // Convert to an array in all cases
  $plans = (array) $plans;

  // default to use all plans if no plan is specified
  if ( empty( $plans ) ) {
    $plans = wc_memberships_get_membership_plans();
  }

  foreach ( $plans as $plan_id_or_slug ) {
    $membership_plan = wc_memberships_get_membership_plan( $plan_id_or_slug );

    if ( $membership_plan && wc_memberships_is_user_active_member( get_current_user_id(), $membership_plan->get_id() ) ) {

      $has_access = true;

      if ( ! $delay && ! $exclude_trial ) {
        break;
      }

      // Determine the earliest membership for the user
      $user_membership = wc_memberships()->user_memberships->get_user_membership( get_current_user_id(), $membership_plan->get_id() );

      // Create a pseudo-rule to help applying filters
      $rule = new WC_Memberships_Membership_Plan_Rule( array(
        'access_schedule_exclude_trial' => $exclude_trial ? 'yes' : 'no'
      ) );

      /** This filter is documented in includes/class-wc-memberships-capabilities.php **/
      $from_time = apply_filters( 'wc_memberships_access_from_time', $user_membership->get_start_date( 'timestamp' ), $rule, $user_membership );

      // If there is no time to calculate the access time from, simply
      // use the current time as access start time
      if ( ! $from_time ) {
        $from_time = current_time( 'timestamp', true );
      }

      if ( is_null( $member_since ) || $from_time < $member_since ) {
        $member_since = $from_time;
      }
    }
  }

  // Add delay
  if ( $has_access && ( $delay || $exclude_trial ) && $member_since ) {

    $access_time = $member_since;

    // Determine access time
    if ( strpos( $delay, 'month' ) !== false ) {

      $parts  = explode( ' ', $delay );
      $amount = isset( $parts[1] ) ? (int) $parts[0] : '';

      $access_time = wc_memberships()->add_months( $member_since, $amount );

    } else if ( $delay ) {

      $access_time = strtotime( $delay, $member_since );

    }

    // Output or show delayed access message
    if ( $access_time <= current_time( 'timestamp', true ) ) {

      $has_access = true;

    } else {
      $has_access = false;
      $message = __( 'This content is part of your membership, but not yet! You will gain access on {date}', 'woocommerce-memberships' );

      // Apply the deprecated filter
      if ( has_filter( 'get_content_delayed_message' ) ) {
        /** This filter is documented in includes/frontend/class-wc-memberships-frontend.php **/
        $message = apply_filters( 'get_content_delayed_message', $message, null, $access_time );
        // Notify developers that this filter is deprecated
        _deprecated_function( 'The get_content_delayed_message filter', '1.3.1', 'wc_memberships_get_content_delayed_message' );
      }

      /** This filter is documented in includes/frontend/class-wc-memberships-frontend.php **/
      $message = apply_filters( 'wc_memberships_get_content_delayed_message', $message, null, $access_time );
      $message = str_replace( '{date}', date_i18n( wc_date_format(), $access_time ), $message );
      $output  = '<div class="wc-memberships-content-delayed-message">' . $message . '</div>';

      echo $output;

    }

  }

  return $has_access;
}

?>
