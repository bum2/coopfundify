<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package Fundify
 * @since Fundify 1.0
 */
?>

	<footer id="footer">
	<div class="legal-terms">
		<h3><?php _e('Legal Terms', 'fundify'); ?></h3>
		<ul>
			<?php wp_list_pages('meta_key=legal_page&sort_column=menu_order&title_li=<h2>' . __('Poetry') . '</h2>' ); //include=1149,1146& ?>
		</ul>
	</div>
		
			<div class="last-widget">
				<?php if ( fundify_is_crowdfunding() ) : ?>
				<h3><?php _e( 'Get the Stats', 'fundify' ); ?></h3>
				<ul>
					<li><?php printf( '<strong>%s</strong> %s', wp_count_posts( 'download' )->publish, _n( edd_get_label_singular(), edd_get_label_plural(), wp_count_posts( 'download' )->publish ) ); ?></li>
					<li><?php printf( __( '<strong>%s</strong> Funded', 'fundify' ), edd_currency_filter( edd_format_amount( edd_get_total_earnings() ) ) ); ?></li>
				</ul>
				<?php endif; ?>

				<div class="copy">
					<a href="<?php echo home_url(); ?>">
						<img src="<?php echo esc_url( fundify_theme_mod( 'footer_logo_image' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="footer-logo">
					</a>
					<p><?php printf( __( 'Copyleft:  %s, %s', 'fundify' ), get_bloginfo( 'name' ), date( 'Y' ) ); ?></p>
				</div>
			</div>
		</div>
		<!-- / container -->
	</footer>
	<!-- / footer -->

	<?php wp_footer(); ?>
</body>
</html>
