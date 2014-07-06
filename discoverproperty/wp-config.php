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
define('DB_NAME', 'discoverproperty');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

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
define('AUTH_KEY',         'wOH*blSEiX35r{hFP8(xj+#g+!8Ueel8ovEEZ-AZ?[7Z/yy)7/wA|m~<mgrz]=x}');
define('SECURE_AUTH_KEY',  'mFx3sSH3Tz6f[qm,lvjR9yTZDNThKyXwOc16@I0lIO9Q+#}[tN`&5vUv/`DuGLoz');
define('LOGGED_IN_KEY',    'hzSd^W[4C$wP_~HK`d3pXJ4L|lK]t5i+`Fj?m9J~yQ//.by]HyOOFV*ZWk8]{CQP');
define('NONCE_KEY',        'xMOx|q I#,iJ;eusoNvj~ L7Fo;1dTt}*d?uu:r.<=8q+V)>GC%4?@K3@WWc;~C@');
define('AUTH_SALT',        'jXhDS!:Epm +,=UQLtzVuGa%D(.e~2}mIa(v{g&L7K+6[&p krL|iB7!yUgx=.X.');
define('SECURE_AUTH_SALT', 'GKR%&)P=!0-6|q|l?8-O%x A{B p{anLaR@+:ZbH)h~ FjOSKNRJ;FcgG%beW$kH');
define('LOGGED_IN_SALT',   '[)@37SzAKooqrGx>=6i.0[f?uQYOXt>a7A}?^.,GYa+F?+O3IV<aXrszC B-rCT,');
define('NONCE_SALT',       'GW-:f|@0gQEw<Bt`)-9vRycp$|2y9Fk}YfhQC5>b/<QzlnC<XjR4x+OQh)qn@^o;');

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
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

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
