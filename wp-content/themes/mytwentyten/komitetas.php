<?php
function get_committee_entry ($username, $status) {
	$user = get_userdatabylogin($username);
	if (!empty($user)) {
		$picture = get_cimyFieldValue($user->ID, 'PICTURE');
		if (empty($picture))
			$picture = get_bloginfo('template_directory') . '/images/empty-profile.jpg';
		$name = $user->first_name;
		$surname = $user->last_name;
	} else {
		$picture = get_bloginfo('template_directory') . '/images/empty-profile.jpg';
		$name = "-";
		$surname = "";
	}
	echo '<td style="width:200px" align="center">' .
		'<img src="' . $picture . '" width="90px" height="120px" title="" alt="" /><br/>' .
		__($status, 'twentyten') . '<br/>' .
		$name . ' ' . $surname .
		'</td>';
}
?>
<?php
/*
Template Name: Komitetas
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
					</div><!-- .entry-content -->
					<table>
						<tr>
							<?php get_committee_entry('povilas', 'Prezidentas') ?>
							<?php get_committee_entry('Gediminas', 'Sekretorius') ?>
							<?php get_committee_entry('PetrasB', 'Viceprezidentas') ?>
						</tr>
						<tr>
							<?php get_committee_entry('neurte', 'Vyriausioji iždininkė') ?>
							<?php get_committee_entry('Lina', 'Jaunesnioji iždininkė') ?>
							<?php get_committee_entry('admin', 'IT') ?>
						</tr>
					</table>
				</div><!-- #post-## -->

				<?php comments_template( '', true ); ?>

<?php endwhile; ?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?> 