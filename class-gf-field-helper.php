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
	protected $_slug = GF_FIELD_HELPER_SLUG;

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
		echo esc_html__( 'To use this plugin, go to the Field Helper section on each of your forms’ settings.', 'gravityforms-field-helper' );
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
				'description' => sprintf( __( 'Enter human-friendly field names for each field below, or leave blank to ignore. To use these human-friendly names for this form, use this API URL: <code>%s</code><br/>The Field Helper is an extension of the Gravity Forms REST API, and query parameters should pass through; for more information, see <a href="https://docs.gravityforms.com/rest-api-v2/" target="_blank">their documentation</a>.', 'gravityforms-field-helper' ), rest_url( 'v2/forms/' . $form['id'] . '/entries/json/' ) ),
				'fields'      => array(),
			),
		);

		foreach ( $form['fields'] as $key => $field ) {
			$friendly_fields[] = $this->build_form_settings_array( $field, $form[ GF_FIELD_HELPER_SLUG ] );
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
	private function build_form_settings_array( $field = array(), $helper_settings ) {

		// Defaulting $helper_settings to an array in the function line does not always work.
		if ( ! is_array( $helper_settings ) ) {
			$helper_settings = array();
		}

		// Handle page fields: add a header and bail out.
		if ( is_a( $field, 'GF_Field_Page' ) ) {
			return array(
				'title'  => esc_html__( 'Page Break', 'gravityforms-field-helper' ),
				'fields' => array(),
			);
		}

		// Create section header.
		$friendly_fields = array(
			'title'  => $field['label'],
			'fields' => array(),
		);

		$id = $this->get_field_id( $field );

		$description = '';
		if ( array_key_exists( 'description', $field ) ) {
			$description = $field['description'];
		}

		if ( array_key_exists( 'inputs', $field ) && is_array( $field['inputs'] ) ) {

			// This is a multiple-input field.
			if ( 'checkbox' === $field['type'] ) {
				$friendly_fields['fields'][ $id . '-checkbox-return' ] = array(
					'name'    => $id . '-checkbox-return',
					'label'   => esc_html__( 'Response Type', 'gravityforms-field-helper' ),
					'type'    => 'radio',
					'choices' => array(
						array(
							'label' => esc_html__( 'One array item for each choice', 'gravityforms-field-helper' ),
							'value' => 'single',
						),
						array(
							'label' => esc_html__( 'An array with all selected choices', 'gravityforms-field-helper' ),
							'value' => 'combined',
						),
					),
					'tooltip' => esc_html__( 'How should selected values from this field be returned in the JSON response?', 'gravityforms-field-helper' ),
				);
			}

			foreach ( $field['inputs'] as $key => $field ) {
				$id = $this->get_field_id( $field );

				$value = '';
				if ( array_key_exists( $id, $helper_settings ) ) {
					$value = $helper_settings[ $id ];
				}

				$friendly_fields['fields'][ $id ] = array(
					'name'              => $id,
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
			if ( array_key_exists( $id, $helper_settings ) ) {
				$value = $helper_settings[ $id ];
			}

			$friendly_fields['fields'][ $id ] = array(
				'name'              => $id,
				'label'             => $field['label'],
				'type'              => 'text',
				'class'             => 'small',
				'value'             => $value,
				'feedback_callback' => array( $this, 'is_valid_name' ),
			);

			if ( ! empty( $description ) ) {
				$friendly_fields['fields'][ $id ]['tooltip'] = esc_html__( 'Field Description: ', 'gravityforms-field-helper' ) . $description;
			}
		}

		return $friendly_fields;
	}

	/**
	 * Retrieve field ID.
	 *
	 * @since 1.0.3.0
	 *
	 * @param array $field Field object.
	 *
	 * @return int|string  Field ID.
	 */
	public function get_field_id( $field ) {
		return GF_Field_Helper_Common::convert_field_id( $field['id'] );
	}

}
