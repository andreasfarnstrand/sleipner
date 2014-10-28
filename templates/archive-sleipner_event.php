<?php

/**
 * The Template for displaying all Sleipner Events in archive
 *
 * @package Sleipner
 */

	get_header(); 

?>

	<section id="primary" class="content-area">
		<div id="content" class="site-content" role="main">

			<?php if ( have_posts() ) : ?>

			<header class="page-header">
				<h1 class="page-title">
					<?php _e( 'Events', SLEIPNER_TEXTDOMAIN ); ?>
				</h1>
			</header><!-- .page-header -->

			<?php
					// Start the Loop.
					while ( have_posts() ) : the_post();

						$event = Sleipner\Posttypes\Sleipner_Event::fromId( get_the_ID() );
						echo $event->archive_template_output();

					endwhile;
					
					?>
					<div class="navigation"><p><?php posts_nav_link('&#8734;','&laquo;&laquo;' .  __( 'Prev', SLEIPNER_TEXTDOMAIN ), __( 'Next', SLEIPNER_TEXTDOMAIN ) .  ' &raquo;&raquo;'); ?></p></div>
					<?php
				else :
					// If no content, include the "No posts found" template.
					get_template_part( 'content', 'none' );

				endif;
			?>
		</div><!-- #content -->
	</section><!-- #primary -->

<?php
get_sidebar( 'content' );
get_sidebar();
get_footer();