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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** MySQL database username */
define( 'DB_USER', 'wordpress' );

/** MySQL database password */
define( 'DB_PASSWORD', 'wordpress' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'd@AgQZn_wIN{<b-dgL>C?)_hJ*vsfdD:$z`NBY|<c9qK>Vl6>ahcle]N|y4by$gu');
define('SECURE_AUTH_KEY',  '8*~,+)L|?!` |_UkK-KiyQ((xMURT;F6yR-g|({G9$tgXAa- O)i50vxQVbp%`&0');
define('LOGGED_IN_KEY',    'Ut-TL|p00keG:eU4qPQ}{@Z-nl$}1#gB81ew%N&W.-Pn8bfP5xZ8V{,9|RK;37vX');
define('NONCE_KEY',        '&XL3yC)-&13)oc@EX6Ggm)48;Z?_36%e@SE9Edh_hwFy9U:|E7jtz*.+^5k_<$nz');
define('AUTH_SALT',        'eToG14rKoQ(@Y1]C-fyU6d#!}~Qv&D},WMJiJ2oZ)]jznypD_Y[KYE8p?YpqfvE5');
define('SECURE_AUTH_SALT', 'hVc7>?i@+e@@mT%|<<>u( ,4M-DH66nxxa|.EHw<e|{]/[d4&HE+p<Te r/*=n}&');
define('LOGGED_IN_SALT',   '{-Zfq3S!z1Q`R@ZG8,[7[~[GV8wS)k0Tqf}q-tkl^N7/1*DU8[MqiWbKq]8|=W_`');
define('NONCE_SALT',       'fV/O.RvM*gR-y%Jl,Hh]@m2HdYRu+k3u|++g>I=?kAb:Ip-|@cYE?~}T>?=e@PMK');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false);
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
