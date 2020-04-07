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
	 * Add plugin settings.
	 *
	 * @since 1.0.3.0
	 *
	 * @return array License fields.
	 */
	public function plugin_settings_fields() {
		return array(
			array(
				'title'       => 'Licensing Settings',
				'description' => wp_kses_post( 'Enter your license below to get automatic plugin updates. If you don’t have a license, visit <a href="https://gravityintegrations.com">gravityintegrations.com</a>.', 'gravityforms-field-helper' ),
				'fields'      => array(
					array(
						'title'               => esc_html__( 'GravityForms Field Helper Settings', 'gravityforms-field-helper' ),
						'label'               => esc_html__( 'License Key', 'gravityforms-field-helper' ),
						'name'                => 'license_key',
						'type'                => 'text',
						'input_type'          => 'password',
						'validation_callback' => array( $this, 'license_validation' ),
						'feedback_callback'   => array( $this, 'license_feedback' ),
						'error_message'       => esc_html__( 'Invalid license', 'gravityforms-field-helper' ),
						'class'               => 'large',
						'default_value'       => '',
					),
				),
			),
		);
	}

	/**
	 * Determine if the license key is valid so the appropriate icon can be displayed next to the field.
	 *
	 * @since 1.0.3.0
	 *
	 * @param string $value The current value of the license_key field.
	 * @param array  $field The field properties.
	 *
	 * @return bool|null
	 */
	public function license_feedback( $value, $field ) {
		if ( empty( $value ) ) {
			return null;
		}

		// Send the remote request to check that the license is valid.
		$license_data = $this->perform_edd_license_request( 'check_license', $value );

		$valid = null;
		if ( empty( $license_data ) || 'invalid' === $license_data->license ) {
			$valid = false;
		} elseif ( 'valid' === $license_data->license ) {
			$valid = true;
		}

		return $valid;
	}

	/**
	 * Handle license key activation or deactivation.
	 *
	 * @since 1.0.3.0
	 *
	 * @param array  $field         The field properties.
	 * @param string $field_setting The submitted value of the license_key field.
	 *
	 * @return void
	 */
	public function license_validation( $field, $field_setting ) {
		$old_license = $this->get_plugin_setting( 'license_key' );

		if ( $old_license && $field_setting !== $old_license ) {
			// Send the remote request to deactivate the old license.
			$this->perform_edd_license_request( 'deactivate_license', $old_license );
		}

		if ( ! empty( $field_setting ) ) {
			// Send the remote request to activate the new license.
			$this->perform_edd_license_request( 'activate_license', $field_setting );
		}
	}

	/**
	 * Send a request to the EDD store url.
	 *
	 * @param string $edd_action The action to perform (check_license, activate_license, or deactivate_license).
	 * @param string $license    The license key.
	 *
	 * @return object
	 */
	public function perform_edd_license_request( $edd_action, $license ) {

		// Prepare the request arguments.
		$args = array(
			'timeout'   => 10,
			'sslverify' => false,
			'body'      => array(
				'edd_action' => $edd_action,
				'license'    => trim( $license ),
				'item_name'  => rawurlencode( GF_FIELD_HELPER_EDD_ITEM_NAME ),
				'url'        => home_url(),
			),
		);

		// Send the remote request.
		$response = wp_remote_post( GF_FIELD_HELPER_EDD_STORE_URL, $args );

		return json_decode( wp_remote_retrieve_body( $response ) );
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
		wp_enqueue_script( 'gravityforms-field-helper-admin' );

		$friendly_fields = array(
			array(
				'title'       => esc_html__( 'Field Helper Settings', 'gravityforms-field-helper' ),
				// Translators: %s: REST API endpoint URL.
				'description' => sprintf( __( 'Enter human-friendly field names for each field below, or leave blank to ignore. To use these human-friendly names for this form, use this API URL: <code>%s</code><br/>The Field Helper is an extension of the Gravity Forms REST API, and query parameters should pass through; for more information, see <a href="https://docs.gravityforms.com/rest-api-v2/" target="_blank">their documentation</a>.', 'gravityforms-field-helper' ), rest_url( 'gf/v2/forms/' . $form['id'] . '/entries/json/' ) ),
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

		// Handle html, page, and section fields: add a header and bail out.
		if ( in_array( $field['type'], array( 'html', 'page', 'section' ), true ) ) {

			if ( ! empty( $field['label'] ) ) {
				$title = $field['label'];
			} else {
				// Translators: %s is the field type key.
				$title = sprintf( esc_html__( '%s Field', 'gravityforms-field-helper' ), ucfirst( $field['type'] ) );
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
		if ( array_key_exists( 'description', $field ) ) {
			$description = $field['description'];
		}

		if ( array_key_exists( 'inputs', $field ) && is_array( $field['inputs'] ) ) {

			// This is a multiple-input field.
			if ( 'checkbox' === $field['type'] ) {
				$friendly_fields['fields'][ $id . '-checkbox-return' ] = array(
					'name'       => $id . '-checkbox-return',
					'label'      => esc_html__( 'Response Format', 'gravityforms-field-helper' ),
					'class'      => 'checkbox-return-format',
					'data-input' => $id,
					'type'       => 'radio',
					'choices'    => array(
						array(
							'label' => esc_html__( 'One array item for each choice', 'gravityforms-field-helper' ),
							'value' => 'single',
						),
						array(
							'label' => esc_html__( 'An array with all selected choices', 'gravityforms-field-helper' ),
							'value' => 'combined',
						),
					),
					'tooltip'    => esc_html__( 'How should selected values from this field be returned in the JSON response?', 'gravityforms-field-helper' ),
				);

				$value = '';
				if ( array_key_exists( $id, $helper_settings ) ) {
					$value = $helper_settings[ $id ];
				}

				$friendly_fields['fields'][ $id ] = array(
					'name'              => $id,
					'label'             => esc_html__( 'Combined Field', 'gravityforms-field-helper' ),
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
	 * Display note on html, section, and page fields.
	 *
	 * @param array $field Gravity Forms field.
	 *
	 * @since 1.0.3.0
	 *
	 * @return void
	 */
	public function settings_gf_helper_no_return_value( $field ) {
		esc_html_e( 'No return value is available for this type of field.', 'gravityforms-field-helper' );
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
