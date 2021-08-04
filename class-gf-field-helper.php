<?php
/**
 * Field Helper for Gravity Forms
 *
 * @package gravity-forms-field-helper
 */

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

GFForms::include_addon_framework();

/**
 * Field Helper for Gravity Forms
 *
 * @package gravity-forms-field-helper
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
	protected $_path = 'field-helper-for-gravity-forms/field-helper-for-gravity-forms.php';

	/**
	 * Full plugin path.
	 *
	 * @since 1.0.0
	 *
	 * @var string $_full_path
	 */
	protected $_full_path = GF_FIELD_HELPER_FILE;

	/**
	 * Plugin title.
	 *
	 * @since 1.0.0
	 *
	 * @var string $_title
	 */
	protected $_title = 'Field Helper for Gravity Forms';

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
		echo esc_html__( 'To use this plugin, go to the Field Helper section on each of your forms’ settings.', 'gravity-forms-field-helper' );
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
		wp_enqueue_script( 'gravity-forms-field-helper-admin' );

		$friendly_fields = array(
			array(
				'title'       => esc_html__( 'Field Helper Settings', 'gravity-forms-field-helper' ),
				// Translators: %s: REST API endpoint URL.
				'description' => sprintf( __( '<p>Enter human-friendly field names for each field below, or leave blank to ignore. To use these human-friendly names for this form, use this API URL: <code>%s</code></p><p>The Field Helper is an extension of the Gravity Forms REST API, and query parameters should pass through; for more information, see <a href="https://docs.gravityforms.com/rest-api-v2/" target="_blank">their documentation</a>.</p><p>For more information, see <a href="https://field-helper-for-gravity-forms.brilliantplugins.info/#/" target="_blank">the Field Helper for Gravity Forms documentation</a>.</p>', 'gravity-forms-field-helper' ), rest_url( 'gf/v2/forms/' . $form['id'] . '/entries/json/' ) ),
				'fields'      => array(),
			),
		);

		foreach ( $form['fields'] as $key => $field ) {
			$friendly_fields[] = $this->build_form_settings_array( $form[ $this->_slug ], $field );
		}

		return $friendly_fields;
	}

	/**
	 * Recursively build form settings fields array.
	 *
	 * @since 1.0.0
	 * @since 1.3.2 Switch order of parameters.
	 *
	 * @param array $helper_settings Saved options for this form.
	 * @param array $field           Field object.
	 *
	 * @return array                 Field settings array.
	 */
	private function build_form_settings_array( $helper_settings, $field = array() ) {

		// Defaulting $helper_settings to an array in the function line does not always work.
		if ( ! is_array( $helper_settings ) ) {
			$helper_settings = array();
		}

		// Handle html, page, and section fields: add a header and bail out.
		if ( in_array( $field['type'], array( 'html', 'page', 'section' ), true ) ) {

			if ( ! empty( $field['label'] ) ) {
				$title = $field['label'];
			} else {
				// Translators: %s is the field type key.
				$title = sprintf( esc_html__( '%s Field', 'gravity-forms-field-helper' ), ucfirst( $field['type'] ) );
			}

			return array(
				'title'  => $title,
				'fields' => array(
					array(
						'name'  => $this->get_field_id( $field ),
						'label' => '',
						'type'  => 'gf_helper_no_return_value',
					),
				),
			);
		}

		// Create section header.
		$friendly_fields = array(
			'title'  => esc_html( $field['label'] ),
			'fields' => array(),
		);

		$id = $this->get_field_id( $field );

		$description = '';
		if ( isset( $field['description'] ) ) {
			$description = $field['description'];
		}

		if ( isset( $field['inputs'] ) && is_array( $field['inputs'] ) ) {

			// This is a multiple-input field.
			if ( 'checkbox' === $field['type'] ) {
				$friendly_fields['fields'][ $id . '-checkbox-return' ] = array(
					'name'       => $id . '-checkbox-return',
					'label'      => esc_html__( 'Response Format', 'gravity-forms-field-helper' ),
					'class'      => 'checkbox-return-format',
					'data-input' => $id,
					'type'       => 'radio',
					'choices'    => array(
						array(
							'label' => esc_html__( 'One array item for each choice', 'gravity-forms-field-helper' ),
							'value' => 'single',
						),
						array(
							'label' => esc_html__( 'An array with all selected choices', 'gravity-forms-field-helper' ),
							'value' => 'combined',
						),
					),
					'tooltip'    => esc_html__( 'How should selected values from this field be returned in the JSON response?', 'gravity-forms-field-helper' ),
				);

				$value = '';
				if ( array_key_exists( $id, $helper_settings ) ) {
					$value = $helper_settings[ $id ];
				}

				$friendly_fields['fields'][ $id ] = array(
					'name'              => $id,
					'label'             => esc_html__( 'Combined Field', 'gravity-forms-field-helper' ),
					'type'              => 'text',
					'class'             => 'small checkbox combined',
					'value'             => $value,
					'feedback_callback' => array( $this, 'is_valid_name' ),
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
					'class'             => 'small checkbox single',
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

			// This is a Nested Forms field.
			if ( 'form' === $field['type'] ) {
				$friendly_fields['fields'][ $id . '-form-return' ] = array(
					'name'       => $id . '-form-return',
					'label'      => esc_html__( 'Response Format', 'gravity-forms-field-helper' ),
					'class'      => 'form-return-format',
					'data-input' => $id,
					'type'       => 'radio',
					'choices'    => array(
						array(
							'label' => esc_html__( 'Comma-separated entried IDs', 'gravity-forms-field-helper' ),
							'value' => 'csv',
						),
						array(
							'label' => esc_html__( 'An array of entry IDs', 'gravity-forms-field-helper' ),
							'value' => 'array',
						),
						array(
							'label' => esc_html__( 'An array of expanded entry objects', 'gravity-forms-field-helper' ),
							'value' => 'expanded',
						),
					),
					'tooltip'    => sprintf(
						// Translators: %s are code tags and examples.
						esc_html__( 'How should nested form entries be returned in the JSON response? %1$s %2$s %1$s %3$s %1$s %4$s', 'gravity-forms-field-helper' ),
						'<br/>',
						__( 'Comma-separated', 'gravity-forms-field-helper' ) . ': <code>"1,2,3"</code>',
						__( 'Array of entry IDs', 'gravity-forms-field-helper' ) . ': <code>[1,2,3]</code>',
						__( 'Array of expanded entry objects', 'gravity-forms-field-helper' ) . ': <code>[{"id": 1, etc.}, {"id": 2, etc.}, {"id": 3, etc.}]</code>'
					),
				);
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
				$friendly_fields['fields'][ $id ]['tooltip'] = esc_html__( 'Field Description: ', 'gravity-forms-field-helper' ) . $description;
			}
		}

		return $friendly_fields;
	}

	/**
	 * Display note on html, section, and page fields.
	 *
	 * @param array $field Gravity Forms field.
	 *
	 * @since 1.0.3.0
	 *
	 * @return void
	 */
	public function settings_gf_helper_no_return_value( $field ) {
		esc_html_e( 'No return value is available for this type of field.', 'gravity-forms-field-helper' );
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
