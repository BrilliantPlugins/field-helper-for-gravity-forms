<?php
/**
 * Gravity Forms Field Helper
 *
 * @package gravityforms-field-helper
 */

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

GFForms::include_addon_framework();

/**
 * Gravity Forms Field Helper
 *
 * @package gravityforms-field-helper
 */
class GF_Field_Helper extends GFAddOn {

	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 *
	 * @var string $_version
	 */
	protected $_version = GF_FIELD_HELPER_VERSION;

	/**
	 * Minimum Gravity Forms version.
	 *
	 * @since 1.0.0
	 *
	 * @var string $_min_gravityforms_version
	 */
	protected $_min_gravityforms_version = '2.4';

	/**
	 * Plugin slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string $_slug
	 */
	protected $_slug = 'gravityforms-field-helper';

	/**
	 * Plugin path.
	 *
	 * @since 1.0.0
	 *
	 * @var string $_path
	 */
	protected $_path = 'gravityforms-field-helper/gravityforms-field-helper.php';

	/**
	 * Full plugin path.
	 *
	 * @since 1.0.0
	 *
	 * @var string $_full_path
	 */
	protected $_full_path = __FILE__;

	/**
	 * Plugin title.
	 *
	 * @since 1.0.0
	 *
	 * @var string $_title
	 */
	protected $_title = 'Gravity Forms Field Helper';

	/**
	 * Short plugin title.
	 *
	 * @since 1.0.0
	 *
	 * @var string $_short_title
	 */
	protected $_short_title = 'Field Helper';

	/**
	 * Class instance.
	 *
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * Return only one instance of this class.
	 *
	 * @return GF_Field_Helper class.
	 */
	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new GF_Field_Helper();
		}

		return self::$_instance;
	}

	/**
	 * Render plugin page content.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function plugin_page() {
		echo 'To use this plugin, go to the Field Helper section on each of your forms’ settings.';
	}

	/**
	 * Basic sanity check for field names.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value Field value.
	 *
	 * @return bool         Whether field value checks out.
	 */
	public function is_valid_name( $value ) {
		return ( strpos( $value, ' ' ) === false );
	}

	/**
	 * Build form settings array.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $form Form object.
	 *
	 * @return array      Form settings.
	 */
	public function form_settings_fields( $form ) {
		$friendly_fields = array(
			array(
				'title'       => esc_html__( 'Field Helper Settings', 'gravityforms-field-helper' ),
				// Translators: %s: REST API endpoint URL.
				'description' => sprintf( esc_html__( 'Enter human-friendly field names for each field below, or leave blank to ignore. To use these human-friendly names, use this API URL: %s', 'gravityforms-field-helper' ), admin_url( 'wp-json/v2/' . GF_FIELD_HELPER_REST_BASE . '/' ) ),
				'fields'      => array(),
			),
		);

		foreach ( $form['fields'] as $key => $field ) {
			$friendly_fields[] = $this->build_form_settings_array( $field, $form[ $this->_slug ] );
		}

		return $friendly_fields;
	}

	/**
	 * Recursively build form settings fields array.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field           Field object.
	 * @param array $helper_settings Saved options for this form.
	 *
	 * @return array                 Field settings array.
	 */
	private function build_form_settings_array( $field, $helper_settings = array() ) {

		// Handle page fields: add a header and bail out.
		if ( is_a( $field, 'GF_Field_Page' ) ) {
			return array(
				'title'  => 'Page Break',
				'fields' => array(),
			);
		}

		// Create section header.
		$friendly_fields = array(
			'title'  => $field['label'],
			'fields' => array(),
		);

		$description = '';
		if ( array_key_exists( 'description', $field ) ) {
			$description = $field['description'];
		}

		if ( array_key_exists( 'inputs', $field ) && is_array( $field['inputs'] ) ) {
			// This is a multiple-input field.
			foreach ( $field['inputs'] as $key => $field ) {
				$value = '';
				if ( array_key_exists( GF_Field_Helper_Common::convert_field_id( $field['id'] ), $helper_settings ) ) {
					$value = $helper_settings[ GF_Field_Helper_Common::convert_field_id( $field['id'] ) ];
				}

				$friendly_fields['fields'][] = array(
					'name'              => GF_Field_Helper_Common::convert_field_id( $field['id'] ),
					'label'             => $field['label'],
					'type'              => 'text',
					'class'             => 'small',
					'value'             => $value,
					'feedback_callback' => array( $this, 'is_valid_name' ),
				);
			}
		} else {
			// This is a single-input field.
			$value = '';
			if ( array_key_exists( GF_Field_Helper_Common::convert_field_id( $field['id'] ), $helper_settings ) ) {
				$value = $helper_settings[ GF_Field_Helper_Common::convert_field_id( $field['id'] ) ];
			}

			$friendly_fields['fields'][] = array(
				'name'              => GF_Field_Helper_Common::convert_field_id( $field['id'] ),
				'tooltip'           => esc_html__( 'Field Description: ', 'gravityforms-field-helper' ) . $description,
				'label'             => $field['label'],
				'type'              => 'text',
				'class'             => 'small',
				'value'             => $helper_settings[ $field['id'] ],
				'feedback_callback' => array( $this, 'is_valid_name' ),
			);
		}

		return $friendly_fields;
	}

}
