<?php
/**
 * Plugin name: WP Pwnd Passwords
 * Plugin URI: https://github.com/jukra/wp-pwnd-passwords
 * Description: Check WordPress user passwords against passwords previously appeared in data breaches.
 * Version: 1.0.0
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

define( 'WPPP_VERSION', '1.0.0' );

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
            $response = wp_remote_get( 'https://api.pwnedpasswords.com/pwnedpassword/' . $_POST['pass1'] );
                // If we get an error when calling the API, let the password validate
                // so that we don't block password reset when downtime
                if ( ! is_wp_error( $response ) && ( isset( $response['response']['code'] ) ) ) {
                    // The API pwndpasswords.com API returns 404 when password is not pwnd yet
                    if ( $response['response']['code'] !== 404 ) {
                        // If the password is found, add the validation error
                        $errors->add( 'password_pwnd', __( 'This password has previously appeared in a data breach and should never be used.', 'wp-pwnd-passwords' ) );
                    }
                }
        }
    }

}

endif;

// init the plugin
WP_Pwnd_Passwords::init();
