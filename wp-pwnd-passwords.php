<?php
/**
 * Plugin name: WP Pwnd Passwords
 * Plugin URI: https://github.com/jukra/wp-pwnd-passwords
 * Description: Check WordPress user passwords against passwords previously appeared in data breaches.
 * Version: 1.1.0
 * Author: Jukka Rautanen
 * Author URI: https://github.com/jukra/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.html
 * Text Domain: wp-pwnd-passwords
 *
 * This plugin validates passwords against known bad passwords
 */

/** Copyright 2018 Jukka Rautanen
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 3, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! class_exists( 'WP_Pwnd_Passwords' ) ) :

define( 'WPPP_VERSION', '1.1.0' );

class WP_Pwnd_Passwords {
    public static $instance;

    public static function init() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new WP_Pwnd_Passwords();
        }
        return self::$instance;
    }

    private function __construct() {
        // Translations
        add_action( 'plugins_loaded', array( $this, 'load_our_textdomain' ) );
        // Validate passwords on reset
        add_action( 'validate_password_reset', array( $this, 'check_pwnd_password' ), 10, 2 );
        // Validate passwords on user edit in the profile page
        add_action( 'user_profile_update_errors', array( $this, 'check_pwnd_password' ), 10, 2 );
    }

    /**
     * Load our plugin textdomain
     */
    public static function load_our_textdomain() {
        $loaded = load_plugin_textdomain( 'wp-pwnd-passwords', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
        if ( ! $loaded ) {
            $loaded = load_muplugin_textdomain( 'wp-pwnd-passwords', dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
        }
    }

    /**
     * Check password on reset
     */
    public function check_pwnd_password( $errors ) {
        // First check that the passwords are set
        if ( isset( $_POST['pass1'] ) && isset( $_POST['pass2'] ) ) {
            $hash = sha1($_POST['pass1']);
            $prefix = substr($hash, 0, 5);
            $suffix = substr($hash, 5);
            $response = wp_remote_get( 'https://api.pwnedpasswords.com/range/' . $prefix );
            // If we get an error when calling the API, let the password validate
            // so that we don't block password reset when downtime
            if ( ! is_wp_error( $response ) && ( isset( $response['response']['code'] ) ) ) {
                // When a password hash with the same first 5 characters is found in the Pwned Passwords repository, the API will respond with an HTTP 200 and include the suffix of every hash beginning with the specified prefix, followed by a count of how many times it appears in the data set.
                // Check response to see if there's a match.
                $regex = "/" . $suffix . ":(\d+)/i";
                if ( preg_match($regex, $response["body"], $matches) ) {
                    $errors->add( 'password_pwnd', __( 'This password has previously appeared in a data breach and should never be used.', 'wp-pwnd-passwords' ) );
                } else {
                    return 0;
                }
            }
        }
    }

}

endif;

// init the plugin
WP_Pwnd_Passwords::init();
