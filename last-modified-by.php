<?php
/**
 * Plugin Name:     Last Modified By
 * Plugin URI:      https://github.com/erikdmitchell/last-modified-by
 * Description:     Adds a modified by column to the admin columns
 * Author:          Erik Mitchell
 * Author URI:      http://erikmitchell.net
 * Text Domain:     last-modified-by
 * Domain Path:     /languages
 * Version:         0.1.1
 *
 * @package         last_modified_by
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( ! defined( 'LAST_MODIFIED_BY_PLUGIN_FILE' ) ) {
    define( 'LAST_MODIFIED_BY_PLUGIN_FILE', __FILE__ );
}

// Include the main Last_Modified_By class.
if ( ! class_exists( 'Last_Modified_By' ) ) {
    include_once dirname( __FILE__ ) . '/class-last-modified-by.php';
}
