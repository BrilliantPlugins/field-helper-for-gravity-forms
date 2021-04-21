<?php
/**
 * Field Helper for Gravity Forms Endpoint
 *
 * @package gravity-forms-field-helper
 */

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

GFForms::include_addon_framework();

/**
 * Field Helper for Gravity Forms Endpoint
 *
 * @package gravity-forms-field-helper
 */
class GF_Field_Helper_Endpoint extends GF_REST_Entries_Controller {

	/**
	 * Plugin slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string $_slug
	 */
	protected $_slug = 'gravity-forms-field-helper';

	/**
	 * API base.
	 *
	 * @since 1.0.0
	 *
	 * @var string $rest_base
	 */
	public $rest_base = 'json';

	/**
	 * Register our REST endpoint.
	 *
	 * @since 1.0.0
	 * @since 1.0.2 Added support for forms.
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		$namespace = $this->namespace;
		$base      = $this->rest_base;

		/**
		 * Entries.
		 */
		register_rest_route(
			$namespace,
			'/entries/' . $base,
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
			'/entries/(?P<entry_id>[\d]+)/' . $base,
			array(
				array(
					'methods'             => WP_REST_SERVER::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);

		/**
		 * Forms.
		 */
		register_rest_route(
			$namespace,
			'/forms/(?P<form_id>[\d]+)/entries/' . $base,
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
			'/forms/(?P<form_id>[\d]+)/entries/(?P<entry_id>[\d]+)/' . $base,
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
	 * @param \WP_REST_Response $response Original API response.
	 * @param bool              $single   Whether this is a single entry or multiple entries.
	 *
	 * @return mixed                      Result to send to the client.
	 */
	public function customize_rest_request( $response, $single = false ) {

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$results = $response->get_data();

		if ( $single ) {
			$results = GF_Field_Helper_Common::replace_field_names( $results );
		} else {
			foreach ( $results['entries'] as $key => $result ) {
				$results['entries'][ $key ] = GF_Field_Helper_Common::replace_field_names( $result );
			}
		}

		return new WP_REST_Response( $results, $response->get_status() );
	}
}
