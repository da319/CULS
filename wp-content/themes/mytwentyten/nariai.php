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
					<!--	<?php edit_post_link( __( 'Edit', 'twentyten' ), '<span class="edit-link">', '</span>' ); ?> -->
					</div><!-- .entry-content -->
					
					<table>
						<?php 
							$aUsersID = $wpdb->get_col( $wpdb->prepare("SELECT $wpdb->users.ID FROM $wpdb->users ORDER BY %s ASC", $szSort ));
							
							$nariai = array();
							foreach ( $aUsersID as $iUserID ) :
								// get default parameters
								$user = get_userdata( $iUserID );
								$narys = array();
								// get CULS parameters
								$narys['id']           = $iUserID;
								$narys['first_name']   = $user->first_name;
								$narys['last_name']    = $user->last_name;
								$narys['contact_vis']  = get_cimyFieldValue($iUserID, 'CONTACT_VISIBILITY');
								$narys['picture']      = get_cimyFieldValue($iUserID, 'PICTURE');
								$narys['picture_vis']  = get_cimyFieldValue($iUserID, 'PICTURE_VISIBILITY');
								$narys['degree']       = get_cimyFieldValue($iUserID, 'DEGREE');
								$narys['degree_vis']   = get_cimyFieldValue($iUserID, 'DEGREE_VISIBILITY');
								$narys['college']      = get_cimyFieldValue($iUserID, 'COLLEGE');
								$narys['college_vis']  = get_cimyFieldValue($iUserID, 'COLLEGE_VISIBILITY');
								$narys['course']       = get_cimyFieldValue($iUserID, 'COURSE');
								$narys['course_vis']   = get_cimyFieldValue($iUserID, 'COURSE_VISIBILITY');
								$narys['adm_year']     = get_cimyFieldValue($iUserID, 'ADMISSION_YEAR');
								$narys['adm_year_vis'] = get_cimyFieldValue($iUserID, 'ADM_YEAR_VISIBILITY');
								$narys['fin_year']     = get_cimyFieldValue($iUserID, 'FINISHING_YEAR');
								$narys['fin_year_vis'] = get_cimyFieldValue($iUserID, 'FIN_YEAR_VISIBILITY');
								$narys['crsid']        = get_cimyFieldValue($iUserID, 'CRSID');
								$narys['crsid_vis']    = get_cimyFieldValue($iUserID, 'CRSID_VISIBILITY');
								array_push($nariai, $narys);
							endforeach;
							
							function cmp_nariai($a, $b) {
								return strcmp($a['last_name'], $b['last_name']);
							}
							
							usort($nariai, "cmp_nariai");
							
							foreach ( $nariai as $narys ) :								
								if (($narys['contact_vis'] == 'Public') || (($narys['contact_vis'] == 'Members only') && is_user_logged_in())) {
									echo '<tr>' . 
									'<td width="150" align="center">' . 
									(!empty ($narys['picture']) && (($narys['picture_vis'] == 'Public') || (($narys['picture_vis'] == 'Members only') && is_user_logged_in())) ?
									'<img src="' . $narys['picture'] . '" /><br/>' : '') .
									$narys['first_name'] . ' ' . $narys['last_name'] . 
									'</td>' .
									'<td style="vertical-align:top"><div>' .
									member_entry (__('Degree'),         $narys['degree'],   $narys['degree_vis']) .
									member_entry (__('College'),        $narys['college'],  $narys['college_vis']) .
									member_entry (__('Course'),         $narys['course'],   $narys['course_vis']) .
									member_entry (__('Admission year'), $narys['adm_year'], $narys['adm_year_vis']) .
									member_entry (__('Finishing year'), $narys['fin_year'], $narys['fin_year_vis']) .
									member_entry (__('CRSID'),          $narys['crsid'],    $narys['crsid_vis']) .
									member_entry (__('Nickname'),       $narys['nickname'], $true) .
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
