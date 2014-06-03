<?php

/**
 *  to override a function at appthemer-crowdfunding/includes/shortcode-profile.php
 *  just to add an id to the 'your-campaigns' h3
 * 
 */
 
if ( ! function_exists( 'atcf_shortcode_profile_campaigns' ) ) {
	function atcf_shortcode_profile_campaigns( $user ) {
		$campaigns = new WP_Query( array(
			'post_type'   => 'download',
			'author' => $user->ID,
			'post_status' => array( 'publish', 'pending', 'draft' ),
			'nopaging'    => true
		) );

		if ( ! $campaigns->have_posts() )
			return;
	?>
		<h3 class="atcf-profile-section your-campaigns" id="campaigns"><?php _e( 'Your Campaigns', 'atcf' ); // bumbum: added id="campaigns" to h3 for the menu link // ?></h3>

		<ul class="atcf-profile-campaigns">
		<?php while ( $campaigns->have_posts() ) : $campaigns->the_post(); $campaign = atcf_get_campaign( get_post()->ID ); ?>
			<li class="atcf-profile-campaign-overview">
				<?php do_action( 'atcf_profile_campaign_before', $campaign ); ?>

				<h4 class="entry-title">
					<?php the_title(); ?>
				</h4>

				<?php do_action( 'atcf_profile_campaign_after_title', $campaign ); ?>

				<?php if ( 'pending' == get_post()->post_status ) : ?>
					<?php do_action( 'atcf_profile_campaign_pending_before', $campaign ); ?>
					<span class="campaign-awaiting-review"><?php _e( 'This campaign is awaiting review.', 'atcf' ); ?></span>
					<?php do_action( 'atcf_profile_campaign_pending_after', $campaign ); ?>
				<?php elseif ( 'draft' == get_post()->post_status ) : ?>
					<?php do_action( 'atcf_profile_campaign_draft_before', $campaign ); ?>
					<span class="campaign-awaiting-review"><?php printf( __( 'This campaign is a draft. <a href="%s">Finish editing</a> it and submit it for review.', 'atcf' ), add_query_arg( array( 'edit' => true ), get_permalink( get_post()->ID ) ) ); ?></span>
					<?php do_action( 'atcf_profile_campaign_draft_after', $campaign ); ?>			
				<?php else : ?>	
					<?php do_action( 'atcf_profile_campaign_published_before', $campaign ); ?>

					<ul class="actions">
						<li><a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'atcf' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php _e( 'View', 'atcf' ); ?></a></li>
						<li><a href="<?php the_permalink(); ?>edit/" title="<?php echo esc_attr( sprintf( __( 'Edit %s', 'atcf' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php _e( 'Edit', 'atcf' ); ?></a></li>
						<?php do_action( 'atcf_profile_campaign_actions_all', $campaign ); ?>
					</ul>

					<ul class="actions">
						<?php if ( 'donation' == $campaign->type() || ( 'flexible' == $campaign->type() || $campaign->is_funded() ) ) : ?>
						<li><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'atcf-request-data', 'campaign' => $campaign->ID ) ), 'atcf-request-data' ) ); ?>" title="<?php echo esc_attr( sprintf( __( 'Export data for %s', 'atcf' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php _e( 'Export Data', 'atcf' ); ?></a></li>
						<?php endif; ?>
						<?php do_action( 'atcf_profile_campaign_actions_special', $campaign ); ?>
					</ul>

					<?php do_action( 'atcf_profile_campaign_published_after', $campaign ); ?>
				<?php endif; ?>
				<?php do_action( 'atcf_profile_campaign_after', $campaign ); ?>
			</li>	
		<?php endwhile; wp_reset_query(); ?>
		</ul>
	<?php
	}
	add_action( 'atcf_shortcode_profile', 'atcf_shortcode_profile_campaigns', 20, 1 );
}

?>
