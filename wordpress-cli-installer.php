<?php
// Surprise Deprecated and PHP Strict Messages
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

/**
 * Nexcess.net Wordpress CLI Installer
 * Copyright (C) 2011  Nexcess.net L.L.C.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * @author Alex Headley <aheadley@nexcess.net>
 */

/**
 * Simple message logging helper
 *
 * @param string $message
 */
function _wpi_log( $message ) {
    printf( '%s %s' . PHP_EOL, @date( 'c' ), trim( $message, PHP_EOL ) );
}

/**
 * Log debug messages (hidden without the -v option)
 *
 * @param string $message
 */
function _wpi_debug( $message ) {
    if( _WPI_VERBOSE ) {
        _wpi_log( 'DEBUG: ' . $message );
    }
}

/**
 * Die with an error message + optional exit code
 *
 * @param string $message
 * @param int $code
 */
function _wpi_die( $message, $code = 1 ) {
    print 'ERROR: ' . rtrim( $message, PHP_EOL ) . PHP_EOL;
    exit( $code );
}

/**
 * Print usage information and die (2)
 */
function _wpi_usage() {
    print 'Usage: wordpress-cli-installer.sh [-hPv] -b base-url -e email-address [-p admin-password]
    [-T blog-title] [-u admin-user] [-l lang] [--dbuser=database-user] [--dbpass=database-pass]
    [--dbname=database-name] [--dbhost=database-host] path/to/wp/files/

General options:
    -b <base-url>
        Base URL for the blog since wordpress can\'t detect it from a CLI
        install, should be a fully qualified URL (ex: http://example.com/)
        REQUIRED
    -e <email-address>
        Admin user\'s email address
        REQUIRED
    -h
        Display this help text
    -p <admin-password>
        Admin users\'s password
        default: randomly generated
    -P
        Toggle whether the blog is public or not (visible to search engines, etc)
        default: public (on)
    -s
        Toggle whether the blog requires an SSL connection for the admin section
        default: off
    -T <blog-title>
        Set the blog\'s title, this should probably be short (and quoted)
        default: Change Me
    -u <admin-user>
        Admin user\'s username
        default: admin
    -l <lang>
        Language of this wordpress blog
        default: <empty> (en-US)
    -v
        Verbose flag, enable more output

wp-config options:
    These options are only used if wp-config.php isn\'t found, in which case
        they are required.
    --dbuser=<database-user>
        Database user\'s username
    --dbpass=<database-pass>
        Database user\'s password
    --dbname=<database-name>
        Database name
    --dbhost=<database-host>
        Database hostname. Passing host:port or /path/to/socket.sock might also
        work
        default: localhost'. PHP_EOL;
    exit( 2 );
}

/**
 * Generate a random alphanumeric string
 *
 * @param int $length
 * @return string
 */
function _wpi_random_string( $length = 12 ) {
    $validChars = array_merge(
        range( 'a', 'z' ),
        range( 'A', 'Z' ),
        // here twice so that numbers are more common
        range( '0', '9' ),
        range( '0', '9' ) );
    $validCharCount = count( $validChars );
    $pass = '';
    while( strlen( $pass ) < $length ) {
        $pass .= $validChars[rand() % $validCharCount];
    }
    return $pass;
}

/**
 * Create a wp-config.php file, will overwrite an existing one
 *
 * @param string $dbName
 * @param string $dbUser
 * @param string $dbPass
 * @param string $dbHost
 * @param string $lang
 */
function _wpi_create_wp_config( $dbName, $dbUser, $dbPass, $dbHost, $secureAdmin, $lang ) {
    _wpi_debug( 'Creating wp-config.php' );
    if( is_null( $dbName ) || is_null( $dbUser ) || is_null( $dbPass ) ) {
        _wpi_die( 'Database name, user and password are required to create the wp-config.php file', 9 );
    }
    if( $fp = fopen( 'wp-config.php', 'w' ) ) {
        fwrite( $fp, '<?php' . PHP_EOL );
        //configurable database stuff
        fprintf( $fp, 'define( \'%s\', \'%s\' );' . PHP_EOL, 'DB_NAME', $dbName );
        fprintf( $fp, 'define( \'%s\', \'%s\' );' . PHP_EOL, 'DB_USER', $dbUser );
        fprintf( $fp, 'define( \'%s\', \'%s\' );' . PHP_EOL, 'DB_PASSWORD', $dbPass );
        fprintf( $fp, 'define( \'%s\', \'%s\' );' . PHP_EOL, 'DB_HOST', $dbHost );
        //non-configurable database stuff
        fprintf( $fp, 'define( \'%s\', \'%s\' );' . PHP_EOL, 'DB_CHARSET', 'utf8' );
        fprintf( $fp, 'define( \'%s\', \'%s\' );' . PHP_EOL, 'DB_COLLATE', '' );
        fprintf( $fp, '$table_prefix = \'%s\';' . PHP_EOL, 'wp_' );
        //secret key stuff
        $authKeys = array(
            'AUTH_KEY',
            'SECURE_AUTH_KEY',
            'LOGGED_IN_KEY',
            'NONCE_KEY',
            'AUTH_SALT',
            'SECURE_AUTH_SALT',
            'LOGGED_IN_SALT',
            'NONCE_SALT',
        );
        foreach( $authKeys as $authKey ) {
            fprintf( $fp, 'define( \'%s\', \'%s\' );' . PHP_EOL, $authKey,
                _wpi_random_string( 32 ) );
        }
        //some more wp configs
        fprintf( $fp, 'define( \'%s\', \'%s\' );' . PHP_EOL, 'WP_LANG', $lang );
        fprintf( $fp, 'define( \'%s\', %s );' . PHP_EOL, 'WP_DEBUG', 'false' );
        if( $secureAdmin ) {
            fprintf( $fp, 'define( \'%s\', %s );' . PHP_EOL, 'FORCE_SSL_ADMIN', 'true' );
        }
        //finish up
        fwrite( $fp, 'if ( !defined(\'ABSPATH\') )
    define(\'ABSPATH\', dirname(__FILE__) . \'/\');

require_once(ABSPATH . \'wp-settings.php\');' . PHP_EOL );
        fclose( $fp );
    } else {
        _wpi_die( 'Could not open wp-config.php for writing', 3 );
    }
}

/**
 * Parse the options or print the usage text if parsing failed
 *
 * @param array $result
 * @return array
 */
function _wpi_clean_opts( $result ) {
    if( !is_array( $result ) ) {
        _wpi_die( 'Failed parsing options: ' . $result->getMessage(), 6 );
    } else {
        list( $opts, $args ) = $result;
        $parsed = array(
            'baseurl'   => null,
            'email'     => null,
            'pass'      => _wpi_random_string(),
            'public'    => true,
            'title'     => 'Change Me',
            'secure'    => false,
            'lang'      => '',
            'user'      => 'admin',
            'dbuser'    => null,
            'dbpass'    => null,
            'dbname'    => null,
            'dbhost'    => 'localhost',
        );
        foreach( $opts as $opt ) {
            switch( $opt[0] ) {
                case 'b':
                    //if we define this here, wp_guess_url will use it
                    //  which avoids a few issues
                    if( !defined( 'WP_SITEURL' ) ) {
                        define( 'WP_SITEURL', rtrim( $opt[1], '/' ) );
                        $parsed['baseurl'] = WP_SITEURL;
                    } else {
                        _wpi_debug( 'WP_SITEURL already defined, skipping another baseurl' );
                    }
                    break;
                case 'e':
                    $parsed['email'] = $opt[1];
                    break;
                case 'h':
                    _wpi_usage();
                case 'p':
                    $parsed['pass'] = $opt[1];
                    break;
                case 'l':
                    $parsed['lang'] = $opt[1];
                    break;
                case 'P':
                    $parsed['public'] = false;
                    break;
                case 'T':
                    $parsed['title'] = $opt[1];
                    break;
                case 's':
                    $parsed['secure'] = true;
                    break;
                case 'u':
                    $parsed['user'] = $opt[1];
                    break;
                case 'v':
                    if( !defined( '_WPI_VERBOSE' ) ) {
                        define( '_WPI_VERBOSE', true );
                    }
                    break;
                default:
                    if( strstr( $opt[0], '--' ) !== false ) {
                        $parsed[ltrim( $opt[0], '-' )] = $opt[1];
                    } else {
                        _wpi_die( 'Unrecognized option: ' . $opt[1], 5 );
                    }
                    break;
            }
        }
        if( !defined( '_WPI_VERBOSE' ) ) {
            define( '_WPI_VERBOSE', false );
        }
        if( !defined( 'WP_SITEURL' ) ) {
            _wpi_die( '-b option is required', 8 );
        }
        if( is_null( $parsed['email'] ) ) {
            _wpi_die( '-e option is required', 8 );
        }
        if( count( $args ) === 1 ) {
            $parsed['path'] = realpath( $args[0] );
            if( is_dir( $parsed['path'] ) ) {
                //parse the rest
                _wpi_debug( 'Read options: ' . print_r( $parsed, true ) );
                return $parsed;
            } else {
                _wpi_die( 'Path is not a directory: ' . $parsed['path'], 4 );
            }
        } else {
            _wpi_die( 'Incorrect arg count: ' . count( $args ), 4 );
        }
    }
}

// remove crap we don't care about
array_shift( $argv );
$argc--;

$shortOptions = 'b:e:hp:PT:su:vl:';
$longOptions = array(
    'dbuser=',
    'dbpass=',
    'dbname=',
    'dbhost=',
);

require_once 'Console/Getopt.php';
$reader = new Console_Getopt;
$parsed = _wpi_clean_opts( $reader->getopt( $argv, $shortOptions, $longOptions ) );
_wpi_debug( 'Moving to path: ' . $parsed['path'] );
chdir( $parsed['path'] );
if( !is_readable( 'wp-config.php' ) ) {
    _wpi_create_wp_config( $parsed['dbname'], $parsed['dbuser'],
        $parsed['dbpass'], $parsed['dbhost'], $parsed['secure'], $parsed['lang'] );
}
_wpi_debug( 'Running installer' );
$deprecated = null;
define( 'WP_INSTALLING', true );
require_once 'wp-load.php';
require_once 'wp-admin/includes/upgrade.php';
require_once 'wp-includes/wp-db.php';
wp_install( $parsed['title'], $parsed['user'], $parsed['email'],
    $parsed['public'], $deprecated, $parsed['pass'] );
printf( 'Blog URL:  %4$s
Admin URL: %1$s
Username:  %2$s
Password:  %3$s' . PHP_EOL,
    WP_SITEURL . '/wp-admin/', $parsed['user'],
    $parsed['pass'], WP_SITEURL . '/' );
//there are die() calls in the wp_install routine so we can't be sure that
// we installed correctly unless we use some weird return value (bad installs
// will exit 0 if it was in wp_install and 1-$something_low if it was in this
// script, then we exit 128 if successful
exit( 128 );
