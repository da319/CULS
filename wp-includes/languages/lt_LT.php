<?php
/*
Plugin Name: Wordpress LT
Plugin URI: http://chionsas.lt/
Description: Sutvarko lietuviškų datų linksnius, kai data išvedama formatu 'Y F j'
Version: 1.0
Author: Chionsas
Author URI: http://chionsas.lt
*/


/**
 * Ištrina einamus metus iš perduotos datos string'o.
 * @return string
 * @param string $date
 */
function strip_current_year($date) {
	return preg_replace("/".date('Y')." ?/", '', $date);
}


/**
 * Grąžina išverstą (en->lt vardininkas, [01..12]->lt vardininkas)
 * arba kito linksnio (lt vardininkas <-> lt kilmininkas) mėnesio pavadinimą.
 * @return string
 * @param string $month
 */
function translate_month_lt($month) {
	static $m;
	
	if (empty($m)) {
	$m = Array(
		'January' => 'Sausis', 'February' => 'Vasaris', 'March' => 'Kovas', 'April' => 'Balandis', 'May' => 'Gegužė', 'June' => 'Birželis', 'July' => 'Liepa', 'August' => 'Rugpjūtis', 'September' => 'Rugsėjis', 'October' => 'Spalis', 'November' => 'Lapkritis', 'December' => 'Gruodis',
		'01' => 'Sausis', '02' => 'Vasaris', '03' => 'Kovas', '04' => 'Balandis', '05' => 'Gegužė', '06' => 'Birželis', '07' => 'Liepa', '08' => 'Rugpjūtis', '09' => 'Rugsėjis', '10' => 'Spalis', '11' => 'Lapkritis', '12' => 'Gruodis',
		'Sausio' => 'Sausis', 'Vasario' => 'Vasaris', 'Kovo' => 'Kovas', 'Balandžio' => 'Balandis', 'Gegužės' => 'Gegužė', 'Birželio' => 'Birželis', 'Liepos' => 'Liepa', 'Rugpjūčio' => 'Rugpjūtis', 'Rugsėjo' => 'Rugsėjis', 'Spalio' => 'Spalis', 'Lapkričio' => 'Lapkritis', 'Gruodžio' => 'Gruodis',
		'Sausis' => 'Sausio', 'Vasaris' => 'Vasario', 'Kovas' => 'Kovo', 'Balandis' => 'Balandžio', 'Gegužė' => 'Gegužės', 'Birželis' => 'Birželio', 'Liepa' => 'Liepos', 'Rugpjūtis' => 'Rugpjūčio', 'Rugsėjis' => 'Rugsėjo', 'Spalis' => 'Spalio', 'Lapkritis' => 'Lapkričio', 'Gruodis' => 'Gruodžio'
	 );
	}

	if (isset($m[$month])) {
		return $m[$month];
	} else if (isset($m[ucfirst(strtolower($month))])) { // jei gaunam menesi su bloga kapitalizacija (liepa, lIepa, etc.)
		return strtolower($m[ucfirst(strtolower($month))]); // tokiu atveju grazinam visa lowercase'inta
	} else
		return $month;
}


/**
 * String'e 'mėnuo xx' pakeičia į 'mėnesio xx'
 * @return string
 * @param string $date
 */

function filter_month_lt($date) {
	if (preg_match_all("/([^\d ]{5,})(\s+)(3[01]|[12][0-9]|0?[1-9])([,\-\|\.\;\:\"\']|\s|$)/", $date, $matches)) { // jei pagaunam 'Rugpjūtis 14' ir pan.
		$match_i = 0;
		foreach ($matches[0] as $match) {
			if (translate_month_lt($matches[1][$match_i]) != $matches[1][$match_i])
				$date = str_replace($match, translate_month_lt($matches[1][$match_i]).$matches[2][$match_i].$matches[3][$match_i].''.$matches[4][$match_i], $date);
		$match_i++;
		}
	}
	return $date;
}

add_filter('the_date','filter_month_lt');
add_filter('the_time','filter_month_lt');
add_filter('get_the_time','filter_month_lt');
add_filter('get_comment_date','filter_month_lt');
add_filter('get_the_modified_date','filter_month_lt');
add_filter('the_modified_date','filter_month_lt');


/**
 * Admin sąsajoj visus mėnesius naudojam kilmininko linksniu,
 * nes taip dažniau gaunam gražesnį vaizdą.
 * Nėra hook'ų, kad išeitų filtruoti kaip theme'uose (pagal skaičiuką po mėnesio) :(
 */
function translate_admin_month_lt() {
	global $wp_locale;

	for($i=1; $i <= 12; $i++) {
		$i_0 = zeroise($i,2);
		$wp_locale->month[$i_0] = translate_month_lt($wp_locale->month[$i_0]);
		$wp_locale->month_abbrev[$wp_locale->month[$i_0]] = $wp_locale->month_abbrev[translate_month_lt($wp_locale->month[$i_0])];
	}
}

add_action('admin_head', 'translate_admin_month_lt', 3);
?>
