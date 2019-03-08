<?php
/**
 * Plugin Name: Gravity Forms Field Helper
 * Plugin URI: https://gravityintegrations.com/
 * Description: Enables Gravity Forms users to set consistent, human-friendly field names for use in the Gravity Forms REST API.
 * Version: 1.0.1
 * Author: LuminFire
 * Author URI: https://luminfire.com
 * License: GPL-2.0+
 * Text Domain: gravityforms-field-helper
 * Domain Path: /languages
 *
 * ------------------------------------------------------------------------
 * Copyright 2019 LuminFire
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package gravityforms-field-helper
 */

define( 'GF_FIELD_HELPER_VERSION', '1.0.1' );
define( 'GF_FIELD_HELPER_REST_BASE', 'entries/json' );
define( 'GF_FIELD_HELPER_SLUG', 'gravityforms-field-helper' );

add_action( 'gform_loaded', array( 'GF_Field_Helper_Bootstrap', 'load_field_helper' ), 5 );
add_action( 'rest_api_init', array( 'GF_Field_Helper_Bootstrap', 'register_api_endpoint' ) );

/**
 * Load up the plugin.
 *
 * @since 1.0.0
 */
class GF_Field_Helper_Bootstrap {

	/**
	 * Load the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function load_field_helper() {

		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}

		// Common.
		require_once 'class-gf-field-helper-common.php';

		// Form settings.
		require_once 'class-gf-field-helper.php';
		GFAddOn::register( 'GF_Field_Helper' );
	}

	/**
	 * Register custom REST API endpoint.
	 *
	 * @since 1.0.0
	 * @since 1.0.1 Moved into GF_Field_Helper_Bootstrap class.
	 *
	 * @return void
	 */
	public static function register_api_endpoint() {
		if ( class_exists( 'GF_REST_Entries_Controller' ) ) {
			require_once 'class-gf-field-helper-endpoint.php';
			$endpoint = new GF_Field_Helper_Endpoint();
			$endpoint->register_rest_routes();
		}
	}
}

/**
 * Get the GF Field Helper instance.
 *
 * @since 1.0.0
 *
 * @return GF_Field_Helper Instance of GF Field Helper.
 */
function gf_field_helper() {
	return GF_Field_Helper::get_instance();
}
