<?php

// json_decode
if ( !function_exists('json_decode') ){
    include_once ICL_PLUGIN_PATH . '/lib/JSON.php';
    function json_decode($data, $bool) {
        if ($bool) {
            $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        } else {
            $json = new Services_JSON();
        }
        return( $json->decode($data) );
    }
}   

if(!function_exists('_cleanup_header_comment')){
    function _cleanup_header_comment($str) {
        return trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $str));
    } 
}

/* remove this when we stop supporting WP versions below 3.0 */
if(!function_exists('wp_get_mu_plugins')){
    function wp_get_mu_plugins() {
        $mu_plugins = array();
        if ( !is_dir( WPMU_PLUGIN_DIR ) )
            return $mu_plugins;
        if ( ! $dh = opendir( WPMU_PLUGIN_DIR ) )
            return $mu_plugins;
        while ( ( $plugin = readdir( $dh ) ) !== false ) {
            if ( substr( $plugin, -4 ) == '.php' )
                $mu_plugins[] = WPMU_PLUGIN_DIR . '/' . $plugin;
        }
        closedir( $dh );
        sort( $mu_plugins );

        return $mu_plugins;
    }
}

if(!defined('E_DEPRECATED')){ define('E_DEPRECATED', 8192); }
?>