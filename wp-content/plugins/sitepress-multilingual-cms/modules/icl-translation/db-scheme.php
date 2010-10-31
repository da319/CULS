<?php
  global $wpdb;
  
  if ( method_exists($wpdb, 'has_cap') && $wpdb->has_cap( 'collation' ) ) {
        if ( ! empty($wpdb->charset) )
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if ( ! empty($wpdb->collate) )
                $charset_collate .= " COLLATE $wpdb->collate";
  }else{
    $charset_collate = '';
  }    
  
  $icl_translation_sql = "
         CREATE TABLE IF NOT EXISTS {$wpdb->prefix}icl_core_status (
        `id` BIGINT NOT NULL auto_increment,
        `rid` BIGINT NOT NULL,
        `module` VARCHAR( 16 ) NOT NULL ,
        `origin` VARCHAR( 64 ) NOT NULL ,
        `target` VARCHAR( 64 ) NOT NULL ,
        `status` SMALLINT NOT NULL,
        PRIMARY KEY ( `id` ) ,
        INDEX ( `rid` )
        ) ENGINE=MyISAM {$charset_collate}
  ";
  mysql_query($icl_translation_sql);
  $icl_translation_sql = "
        CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}icl_content_status` (
        `rid` BIGINT NOT NULL ,
        `nid` BIGINT NOT NULL ,
        `timestamp` DATETIME NOT NULL ,
        `md5` VARCHAR( 32 ) NOT NULL ,
        PRIMARY KEY ( `rid` ) ,
        INDEX ( `nid` )
        ) ENGINE=MyISAM {$charset_collate} 
  ";  
   mysql_query($icl_translation_sql);
   
  $icl_translation_sql = "
        CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}icl_node` (
        `nid` BIGINT NOT NULL ,
        `md5` VARCHAR( 32 ) NOT NULL ,
        `links_fixed` TINYINT NOT NULL DEFAULT 0,
        PRIMARY KEY ( `nid` )
        ) ENGINE=MyISAM {$charset_collate}  
  ";  
   mysql_query($icl_translation_sql);
   
  $icl_translation_sql = "
        CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}icl_reminders` (
        `id` BIGINT NOT NULL ,
        `message` TEXT NOT NULL ,
        `url`  TEXT NOT NULL ,
        `can_delete` TINYINT NOT NULL ,
        `show` TINYINT NOT NULL ,
        PRIMARY KEY ( `id` )
        ) ENGINE=MyISAM {$charset_collate}  
  ";  
   mysql_query($icl_translation_sql);
   
?>