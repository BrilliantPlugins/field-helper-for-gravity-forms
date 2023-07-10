<?php
/**
 * Field Helper for Gravity Forms JSON
 *
 * @since 1.5.0
 *
 * @package brilliant-plugins/field-helper-for-gravity-forms
 */

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

/**
 * Field Helper for Gravity Forms JSON
 *
 * @since 1.5.0
 *
 * @package brilliant-plugins/field-helper-for-gravity-forms
 */
class GF_Field_Helper_Json {

	/**
	 * Class instance.
	 *
	 * @var self
	 */
	private static $instance = null;

	/**
	 * Return only one instance of this class.
	 *
	 * @return GF_Field_Helper_Json class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new GF_Field_Helper_Json();
		}

		return self::$instance;
	}

	/**
	 * Load action hooks.
	 */
	private function __construct() {
		add_filter( 'gform_form_post_get_meta', array( $this, 'get_form_meta' ), 10, 2 );
		add_filter( 'gform_post_update_form_meta', array( $this, 'update_form_meta' ), 10, 3 );
	}

	/**
	 * Get storage directory.
	 *
	 * @return string Directory.
	 */
	private function get_directory() {
		/**
		 * Filters location of the Gravity Forms Field Helper JSON storage.
		 * Defaults to gf-json in the current theme directory.
		 *
		 * @param string $path Directory where JSON is stored.
		 */
		return apply_filters( 'gf_field_helper_json_directory', get_stylesheet_directory() . '/gf-json' );
	}

	/**
	 * Get path to file.
	 *
	 * @param int|string $form_id Form ID.
	 *
	 * @return string             Path to JSON file.
	 */
	private function get_filename( $form_id ) {
		return $this->get_directory() . '/gform_' . $form_id . '-field-helper.json';
	}

	/**
	 * Retrieve field helper settings from JSON storage.
	 *
	 * @param array $form GF Form object.
	 *
	 * @return array      GF Form object.
	 */
	public function get_form_meta( $form ) {

		try {
			if ( ! file_exists( $this->get_filename( $form['id'] ) ) ) {
				return $form;
			}

			// phpcs:disable WordPress.WP.AlternativeFunctions
			$json_file = fopen( $this->get_filename( $form['id'] ), 'r' );

			if ( ! $json_file ) {
				return $form;
			}

			$json = fread( $json_file, filesize( $this->get_filename( $form['id'] ) ) );
			fclose( $json_file );
			// phpcs:enable WordPress.WP.AlternativeFunctions
		} catch ( Exception $e ) {
			error_log( 'Couldn’t read from GF JSON directory: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return $form;
		}

		$field_helper_settings = json_decode( $json, true );

		if ( JSON_ERROR_NONE === json_last_error() && ! is_null( $field_helper_settings ) ) {
			$form[ GF_FIELD_HELPER_SLUG ] = $field_helper_settings;
		}

		return $form;
	}

	/**
	 * Save field helper settings to JSON file.
	 *
	 * @param string $form_meta Form meta.
	 * @param int    $form_id   Form ID.
	 * @param string $meta_name Meta name.
	 *
	 * @return string|array     Form meta.
	 */
	public function update_form_meta( $form_meta, $form_id, $meta_name ) {

		// Test whether filesystem is writeable.
		if ( ! is_writable( $this->get_directory() ) ) {
			$create_directory = mkdir( $this->get_directory(), 0755 );
			if ( ! $create_directory ) {
				return $form_meta;
			}
		}

		$form_meta = json_decode( $form_meta, true );

		$field_helper_settings = $form_meta[ GF_FIELD_HELPER_SLUG ];

		if ( empty( $field_helper_settings ) ) {
			return $form_meta;
		}

		ksort( $field_helper_settings, SORT_NATURAL );

		// Write the file.
		try {
			// phpcs:disable WordPress.WP.AlternativeFunctions
			$json_file = fopen( $this->get_filename( $form_id ), 'w' );
			fwrite( $json_file, wp_json_encode( $field_helper_settings, JSON_PRETTY_PRINT ) );
			fclose( $json_file );
			// phpcs:enable WordPress.WP.AlternativeFunctions
		} catch ( Exception $e ) {
			error_log( 'Couldn’t write to GF JSON directory: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}

		return $form_meta;
	}
}
