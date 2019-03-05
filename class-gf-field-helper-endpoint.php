<?php
/**
 * Gravity Forms Field Helper Endpoint
 *
 * @package gravityforms-field-helper
 */

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

GFForms::include_addon_framework();

/**
 * Gravity Forms Field Helper Endpoint
 *
 * @package gravityforms-field-helper
 */
class GF_Field_Helper_Endpoint extends GF_REST_Entries_Controller {

	/**
	 * Plugin slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string $_slug
	 */
	protected $_slug = 'gravityforms-field-helper';

	/**
	 * API base.
	 *
	 * @since 1.0.0
	 *
	 * @var string $rest_base
	 */
	public $rest_base = GF_FIELD_HELPER_REST_BASE;

	/**
	 * Our cache of human-friendly labels.
	 *
	 * @since 1.0.0
	 *
	 * @var array $friendly_labels
	 */
	protected $friendly_labels = array();

	/**
	 * Register our REST endpoint.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		$namespace = $this->namespace;
		$base      = $this->rest_base;

		register_rest_route(
			$namespace,
			'/' . $base,
			array(
				array(
					'methods'             => WP_REST_SERVER::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);

		register_rest_route(
			$namespace,
			$base . '/(?P<entry_id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_SERVER::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);
	}

	/**
	 * Get a collection of entries.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$original = parent::get_items( $request );
		return $this->customize_rest_request( $original );
	}

	/**
	 * Get a single entry.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$original = parent::get_item( $request );
		return $this->customize_rest_request( $original, true );
	}

	/**
	 * Add friendly field names to REST API response.
	 *
	 * @since 1.0.0
	 *
	 * @param array $response Original API response.
	 * @param bool  $single   Whether this is a single entry or multiple entries.
	 *
	 * @return mixed          Result to send to the client.
	 */
	public function customize_rest_request( $response, $single = false ) {

		$results = $response->get_data();

		if ( $single ) {
			$results = $this->replace_field_names( $results );
		} else {
			foreach ( $results['entries'] as $key => $result ) {
				$results['entries'][ $key ] = $this->replace_field_names( $result );
			}
		}

		return new WP_REST_Response( $results, $response->get_status() );
	}

	/**
	 * Replace field IDs with human-friendly names.
	 *
	 * @since 1.0.0
	 *
	 * @param array $result Original entry object.
	 *
	 * @return array        Modified entry object.
	 */
	public function replace_field_names( $result ) {
		$labels = $this->get_form_friendly_labels( $result['form_id'] );

		$fields = array();

		foreach ( $result as $key => $value ) {
			$sanitized_key = GF_Field_Helper_Common::convert_field_id( $key );

			if ( in_array( $sanitized_key, array_flip( $labels ), false ) ) { // phpcs:ignore WordPress.PHP.StrictInArray -- since GF uses both integer and string field keys.
				$fields[ $labels[ $sanitized_key ] ] = $value;
			}

			// Unset only field keys (strings will convert to 0, floats to integers).
			if ( 0 !== absint( $key ) ) {
				unset( $result[ $key ] );
			}
		}

		$result['fields'] = $fields;

		return $result;
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
	public function get_form_friendly_labels( $form_id ) {
		if ( ! isset( $this->friendly_labels[ $form_id ] ) ) {
			$form = GFAPI::get_form( $form_id );

			$this->friendly_labels[ $form_id ] = array_filter( $form[ GF_FIELD_HELPER_SLUG ] );
		}

		return $this->friendly_labels[ $form_id ];
	}

}
