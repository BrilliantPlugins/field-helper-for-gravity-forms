<?php
/**
 * Field Helper for Gravity Forms Common
 *
 * @package gravity-forms-field-helper
 */

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

GFForms::include_addon_framework();

/**
 * Field Helper for Gravity Forms Common
 *
 * @package gravity-forms-field-helper
 */
class GF_Field_Helper_Common {

	/**
	 * Our cache of human-friendly labels.
	 *
	 * @since 1.0.0
	 *
	 * @var array $friendly_labels
	 */
	protected static $friendly_labels = array();

	/**
	 * Checkbox fields to coalasce.
	 *
	 * @since 1.0.3.0
	 *
	 * @var array $checkbox_fields
	 */
	protected static $checkbox_fields = array();

	/**
	 * Nested fields to handle.
	 *
	 * @since 1.3.0
	 *
	 * @var array $nested_fields
	 */
	protected static $nested_fields = array();

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
	public static function replace_field_names( $result ) {
		$labels = self::get_form_friendly_labels( $result['form_id'] );

		$fields = array();

		foreach ( $result as $key => $value ) {
			$sanitized_key = self::convert_field_id( $key );

			if ( empty( $labels ) ) {
				$fields['error'] = array(
					'code'    => 500,
					'message' => 'Friendly field names are not set. Please visit your form settings to set them.',
				);
			}

			if ( in_array( $sanitized_key, array_flip( $labels ), false ) ) { // phpcs:ignore WordPress.PHP.StrictInArray -- since GF uses both integer and string field keys.

				if ( in_array( absint( $sanitized_key ), self::$checkbox_fields, true ) ) {
					// Checkbox.
					if ( ! empty( $value ) ) {
						$fields[ $labels[ absint( $sanitized_key ) ] ][] = $value;
					}
				} elseif ( array_key_exists( absint( $sanitized_key ), self::$nested_fields ) ) {
					// Nested Form field.
					if ( ! empty( $value ) ) {
						switch ( self::$nested_fields[ absint( $sanitized_key ) ] ) {

							case 'expanded':
								$entry_ids = explode( ',', $value );
								$value     = array();
								foreach ( $entry_ids as $entry_id ) {
									$fields[ $labels[ absint( $sanitized_key ) ] ][] = self::replace_field_names( GFAPI::get_entry( $entry_id ) );
								}
								break;

							case 'array':
								$value = json_decode( '[' . $value . ']' );
								// Now just fall through and assign the value.

							case 'csv':
							default:
								$fields[ $labels[ absint( $sanitized_key ) ] ] = $value;
								break;
						}
					}
				} else {
					// Others.
					$fields[ $labels[ $sanitized_key ] ] = $value;
				}
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
	 * @return array Human-friendly form labels or false if not set.
	 */
	public static function get_form_friendly_labels( $form_id ) {
		if ( ! isset( self::$friendly_labels[ $form_id ] ) ) {
			$form = GFAPI::get_form( $form_id );

			if ( ! array_key_exists( GF_FIELD_HELPER_SLUG, $form ) ) {
				return array();
			}

			$fields = array_filter( $form[ GF_FIELD_HELPER_SLUG ] );

			foreach ( $form['fields'] as $field ) {
				if ( 'checkbox' === $field['type'] && array_key_exists( $field['id'] . '-checkbox-return', $fields ) && 'combined' === $fields[ $field['id'] . '-checkbox-return' ] ) {

					// Unset the choices.
					foreach ( $field['inputs'] as $input_key => $input_id ) {
						$input_id = self::convert_field_id( $input_id['id'] );
						unset( $fields[ $input_id ] );
					}

					// Set array of checkbox fields.
					self::$checkbox_fields[ $field['id'] ] = $field['id'];
				}

				if ( 'form' === $field['type'] && array_key_exists( $field['id'] . '-form-return', $fields ) ) {
					self::$nested_fields[ $field['id'] ] = $fields[ $field['id'] . '-form-return' ];
				}

				// Unset the format settings.
				unset(
					$fields[ $field['id'] . '-checkbox-return' ],
					$fields[ $field['id'] . '-form-return' ]
				);
			}

			self::$friendly_labels[ $form_id ] = $fields;
		}

		return self::$friendly_labels[ $form_id ];
	}

}
