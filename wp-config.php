<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'RDwordpress' );

/** Database username */
define( 'DB_USER', 'RDwordpress' );

/** Database password */
define( 'DB_PASSWORD', 'y@fYYji8YKdtyX6e' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '4fV5@93Xc]+acWq$eJ|6zM.)WqG{g^%u)U<QCcF$-.F!R1 S/ZUBr8rQQ!M,|P{~');
define('SECURE_AUTH_KEY',  'e]//tiw{d7f*$IhNV4=Ot+hz$!.{@52>wXC DIK[+#.p5@[)^ld6e-_>+3/|xy|.');
define('LOGGED_IN_KEY',    '8,S~Tyfetg6^h8u5mzCaC|_:Q^OA1-*h(=-%d:RX*=p$jg|2{h:k9#G6[3pF1O8W');
define('NONCE_KEY',        'n-@0ysN(t_T);flgM3w{z8C0jm0.~qOLdI&Pu.XnyUdX9+*R.xP~!A+t,Q%Sx&{P');
define('AUTH_SALT',        'D6e.j|-.O<aSeX*tB(Y^y(I+.+rFhae>R+23{ q8s?.DOagOizyX)Sn|V|blCw@h');
define('SECURE_AUTH_SALT', '7E#,Ba*41fhNEo`3M43ZRV%l56RHc8xtFgY34I-)7{~D|Ma*$P|=g90%d2Yicajo');
define('LOGGED_IN_SALT',   '4As?I|$W^S1&Kh!XZiHK8P;H3bZufLaKsy8|j7rb9X:J8QdWI+/&HcuPBja+&i7_');
define('NONCE_SALT',       'U[lbq_v!Or(2sM4XR7]H_.o;3|gqalUVA;iPcxxkF4oRkPA0T<^7w>79XWH3#?Kn');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'RDwp_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
