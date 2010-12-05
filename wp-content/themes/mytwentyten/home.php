<?php 
	if (ICL_LANGUAGE_CODE == 'en') {
		query_posts("category_name='news'");
	} else {
		query_posts("category_name='naujienos'");
	}
	require_once('category-naujienos.php'); 
?>