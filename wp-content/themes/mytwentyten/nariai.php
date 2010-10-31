<?php
/*
Template Name: Nariai
*/

get_header(); ?>

		<div id="container">
			<div id="content" role="main">

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h1 class="entry-title"><?php the_title(); ?></h1>

					<div class="entry-content">
						<?php the_content(); ?>
						<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'twentyten' ), 'after' => '</div>' ) ); ?>
						<?php edit_post_link( __( 'Edit', 'twentyten' ), '<span class="edit-link">', '</span>' ); ?>
					</div><!-- .entry-content -->
					<?php 
						$aUsersID = $wpdb->get_col( $wpdb->prepare("SELECT $wpdb->users.ID FROM $wpdb->users ORDER BY %s ASC", $szSort ));
					?>
					<table>
						<tr><th><?php _e('Nickname', 'twentyten') ?></th>
							<th><?php _e('Name', 'twentyten') ?></th>
							<th><?php _e('Last name', 'twentyten') ?></th>
						</tr>
						<?php 
							foreach ( $aUsersID as $iUserID ) :
								$user = get_userdata( $iUserID );
								echo '<tr>' . 
								'<td>' . $user->nickname . '</td>' .
								'<td>' . $user->first_name . '</td>' .
								'<td>' . $user->last_name . '</td>' .
								'</tr>';
							endforeach;
						?>
					</table>
				</div><!-- #post-## -->

				<?php comments_template( '', true ); ?>

<?php endwhile; ?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
