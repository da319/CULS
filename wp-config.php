<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'culs');

/** MySQL database username */
define('DB_USER', 'culs');
//define('DB_USER', 'da319');

/** MySQL database password */
//define('DB_PASSWORD', 'Aediobei');
define('DB_PASSWORD', 'mypass');

/** MySQL hostname */
define('DB_HOST', '79.98.25.182');
//define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'w3%60@:I91<O@Ff1|+il~.Ou-+OtY dG$Jm-kdsTrqUMY.v;U@m}<+V;l0F7u{ 9');
define('SECURE_AUTH_KEY',  'qCmr%>|[9Axo!n nD^FP|,j?t-l-soO}Qut.VUG-j^8+-~-nI#Q!H?b+v0$MKk^6');
define('LOGGED_IN_KEY',    'g*6m()1,-)?LI2)[7}G3k0dO+!l4)j|;e+X68a|tY?;o-l|cgkRSjYhKm`o_JoEh');
define('NONCE_KEY',        ':nix}0@KXk[@&/%9>JuT`EF |aTSA]C%_M+ZLJ:&=}O~0*fB@hOgS)^_?-MZ1GDK');
define('AUTH_SALT',        'RIro?R+7NkXF1YyKaw|~M 9Vez^^7yA`9_]T-1D0rGL?hkit/R#?/j~-6I{DjmeF');
define('SECURE_AUTH_SALT', '(TQ:Q,la&V<fLftly>$T8@]UJIY=DBE^j-PnLI3Qpx<t+7KQ%?]%%9|6p[-m+n@Y');
define('LOGGED_IN_SALT',   'LM@Jm[O<1${->B`kIF4r[&ZT`T``JAIp`TA^1m~czK#TN}6-eU^hd{go;S473t==');
define('NONCE_SALT',       'HyOV>.9-eXuqt$7|0O:Rs,~ ]^cvZi}^-S!d;h 8bZAUnn-[I}[DOs^L[d-WP%Pr');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress.  A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de.mo to wp-content/languages and set WPLANG to 'de' to enable German
 * language support.
 */
define ('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

// more memory
//define('WP_MEMORY_LIMIT', '64M');