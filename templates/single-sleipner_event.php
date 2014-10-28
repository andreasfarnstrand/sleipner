<?php
/**
 * The Template for displaying a single Sleipner Events
 *
 * @package Sleipner
 */

get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">

			<?php while ( have_posts() ) : the_post(); ?>

				<?php the_post_thumbnail(); ?>

				<?php 

				$event = Sleipner\Posttypes\Sleipner_Event::fromId( get_the_ID() );
				echo $event->single_template_output();

				?>

				<nav class="nav-single">
					<h3 class="assistive-text"><?php _e( 'Post navigation', SLEIPNER_TEXTDOMAIN ); ?></h3>
					<span class="nav-previous"><?php previous_post_link( '%link', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', SLEIPNER_TEXTDOMAIN ) . '</span> %title' ); ?></span>
					<span class="nav-next"><?php next_post_link( '%link', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', SLEIPNER_TEXTDOMAIN ) . '</span>' ); ?></span>
				</nav><!-- .nav-single -->

				<?php comments_template( '', true ); ?>

			<?php endwhile; // end of the loop. ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
