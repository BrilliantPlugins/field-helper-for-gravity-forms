<?php
/**
 * Gravity Forms Field Helper Common
 *
 * @package gravityforms-field-helper
 */

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

GFForms::include_addon_framework();

/**
 * Gravity Forms Field Helper Common
 *
 * @package gravityforms-field-helper
 */
class GF_Field_Helper_Common {

	/**
	 * Our cache of human-friendly labels.
	 *
	 * @since 1.0.0
	 *
	 * @var array $friendly_labels
	 */
	protected $friendly_labels = array();

	/**
	 * Convert field ID with period to underscore.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id Field ID.
	 *
	 * @return string    Sanitized field ID for/from database.
	 */
	public static function convert_field_id( $id ) {
		return str_replace( '.', '_', $id );
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
			$sanitized_key = self::convert_field_id( $key );

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

			$this->friendly_labels[ $form_id ] = array_filter( $form[ $this->_slug ] );
		}

		return $this->friendly_labels[ $form_id ];
	}

}
