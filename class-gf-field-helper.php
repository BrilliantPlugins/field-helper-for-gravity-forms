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
	 * Our cache of human-friendly labels.
	 *
	 * @since 1.0.0
	 *
	 * @var array $friendly_labels
	 */
	protected $friendly_labels = array();

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
	 * Load hooks and actions.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {
		parent::init();

		// Filter REST response to use friendly field names.
		add_filter( 'rest_dispatch_request', array( $this, 'intercept_rest_request' ), 10, 4 );
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
				'description' => esc_html__( 'Enter human-friendly field names for each field below, or leave blank to ignore.', 'gravityforms-field-helper' ),
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
				if ( array_key_exists( $this->convert_field_id( $field['id'] ), $helper_settings ) ) {
					$value = $helper_settings[ $this->convert_field_id( $field['id'] ) ];
				}

				$friendly_fields['fields'][] = array(
					'name'              => $this->convert_field_id( $field['id'] ),
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
			if ( array_key_exists( $this->convert_field_id( $field['id'] ), $helper_settings ) ) {
				$value = $helper_settings[ $this->convert_field_id( $field['id'] ) ];
			}

			$friendly_fields['fields'][] = array(
				'name'              => $this->convert_field_id( $field['id'] ),
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

	/**
	 * Convert field ID with period to underscore.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id Field ID.
	 *
	 * @return string    Sanitized field ID for/from database.
	 */
	private function convert_field_id( $id ) {
		return str_replace( '.', '_', $id );
	}

	/**
	 * Add friendly field names to REST API response.
	 *
	 * @since 1.0.0
	 *
	 * @param bool            $dispatch_result Dispatch result, will be used if not empty.
	 * @param WP_REST_Request $request         Request used to generate the response.
	 * @param string          $route           Route matched for the request.
	 * @param array           $handler         Route handler used for the request.
	 *
	 * @return mixed                           Result to send to the client.
	 */
	public function intercept_rest_request( $dispatch_result, $request, $route, $handler ) {

		// If not an entries request, bail out.
		if ( ! is_a( $handler['callback'][0], 'GF_REST_Entries_Controller' ) ) {
			return $dispatch_result;
		}

		// Get the default response.
		$response      = call_user_func( $handler['callback'], $request );
		$response_data = $response->get_data();

		// Add human-friendly field names to the response.
		foreach ( $response_data['entries'] as $key => $entry ) {
			$labels = $this->get_form_friendly_labels( $entry['form_id'] );

			foreach ( $entry as $e_key => $e_value ) {
				$sanitized_key = $this->convert_field_id( $e_key );

				if ( in_array( $sanitized_key, array_flip( $labels ), false ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict -- since GF uses both integer and string field keys.
					$response_data['entries'][ $key ][ $labels[ $sanitized_key ] ] = $e_value;
				}
			}
		}

		return new WP_REST_Response( $response_data, 200 );
	}

	/**
	 * Get friendly labels for the given form.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id Form ID.
	 *
	 * @return array       Human-friendly form labels.
	 */
	private function get_form_friendly_labels( $form_id ) {
		if ( ! isset( $this->friendly_labels[ $form_id ] ) ) {
			$form = GFAPI::get_form( $form_id );

			$this->friendly_labels[ $form_id ] = array_filter( $form[ $this->_slug ] );
		}

		return $this->friendly_labels[ $form_id ];
	}

}
