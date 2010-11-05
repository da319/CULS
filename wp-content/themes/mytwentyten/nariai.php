<?php

function member_entry( $title, $value, $visibility ) {
	if (!empty ($value) 
	    && (($visibility == 'Public') 
		     || (($visibility == 'Members only') && is_user_logged_in())))
		return $title . ' : ' . $value . '<br/>';
	else
		return '';
}

?>
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
						<?php 
							foreach ( $aUsersID as $iUserID ) :
								// get default parameters
								$user = get_userdata( $iUserID );
								// get CULS parameters
								$contact_vis  = get_cimyFieldValue($iUserID, 'CONTACT_VISIBILITY');
								$picture      = get_cimyFieldValue($iUserID, 'PICTURE');
								$picture_vis  = get_cimyFieldValue($iUserID, 'PICTURE_VISIBILITY');
								$degree       = get_cimyFieldValue($iUserID, 'DEGREE');
								$degree_vis   = get_cimyFieldValue($iUserID, 'DEGREE_VISIBILITY');
								$college      = get_cimyFieldValue($iUserID, 'COLLEGE');
								$college_vis  = get_cimyFieldValue($iUserID, 'COLLEGE_VISIBILITY');
								$course       = get_cimyFieldValue($iUserID, 'COURSE');
								$course_vis   = get_cimyFieldValue($iUserID, 'COURSE_VISIBILITY');
								$adm_year     = get_cimyFieldValue($iUserID, 'ADMISSION_YEAR');
								$adm_year_vis = get_cimyFieldValue($iUserID, 'ADM_YEAR_VISIBILITY');
								$fin_year     = get_cimyFieldValue($iUserID, 'FINISHING_YEAR');
								$fin_year_vis = get_cimyFieldValue($iUserID, 'FIN_YEAR_VISIBILITY');
								$crsid        = get_cimyFieldValue($iUserID, 'CRSID');
								$crsid_vis    = get_cimyFieldValue($iUserID, 'CRSID_VISIBILITY');								
								
								if (($contact_vis == 'Public') || (($contact_vis == 'Members only') && is_user_logged_in())) {
									echo '<tr>' . 
									'<td width="150" align="center">' . 
									(!empty ($picture) && (($picture_vis == 'Public') || (($picture_vis == 'Members only') && is_user_logged_in())) ?
									'<img src="' . $picture . '" /><br/>' : '') .
									$user->first_name . ' ' . $user->last_name . 
									'</td>' .
									'<td style="vertical-align:top"><div>' .
									member_entry (__('Degree'),         $degree,   $degree_vis) .
									member_entry (__('College'),        $college,  $college_vis) .
									member_entry (__('Course'),         $course,   $course_vis) .
									member_entry (__('Admission year'), $adm_year, $adm_year_vis) .
									member_entry (__('Finishing year'), $fin_year, $fin_year_vis) .
									member_entry (__('CRSID'),          $crsid,    $crsid_vis) .
									member_entry (__('Nickname'),       $user->nickname, $true) .
									'</div></td>' .
									'</tr>';
								}
								
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
