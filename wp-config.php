<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'rxleaf');

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
define('AUTH_KEY',         'ie,Eb[Fj$`jLaz7B4.@1pgXFYIQ@H[IKW.]g]!G6*WG_).xM-ap*j^|JD;`f1Wgg');
define('SECURE_AUTH_KEY',  '.DPT&iQR$%pmT4U8h/xt``!y;[6/QbD7W2po0mho-J/t:2hkOXt?(+<e@u[ujENF');
define('LOGGED_IN_KEY',    '*V-P$*J2._p+y[>Zdw$%)`k6sKeoQ1o3eZEg1cAU?hG)B) +lzkuSn8UehNPR-qy');
define('NONCE_KEY',        '=b.pRzK-X--#f,Y7M6TlyXtSXcRbpli>[{abMvuWN?}S67eQszYEc}se-M$Em2*n');
define('AUTH_SALT',        'FUH-eyp;qo?sp@1!C+}Y-8Bqe[`$673Mxz.Z$]Q+7(G1tX;=TnEhiE5!^H [eoLx');
define('SECURE_AUTH_SALT', 'YkX}_<AR#%2Qf0,eM=lzqBaKB~J0:[W#G#vj~YquT|[-iIBddyZ3e3,5Ai)*y]JI');
define('LOGGED_IN_SALT',   '41# ~Z]8LIwy^428+XsV=dH`|~zp,nI`T4R(l[ q+=RgDyppT4HA|gJ]%`m=)HZ@');
define('NONCE_SALT',       'CU6S,r-Q7VDoT1O?v9]D]sQ,qi|M@Tego/!0lsvWXL:*n_,p?:n$GWXq?;Uq%&?o');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
