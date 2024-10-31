<?php
/**
 * Field Helper for Gravity Forms Common
 *
 * @package brilliant-plugins/field-helper-for-gravity-forms
 */

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

GFForms::include_addon_framework();

/**
 * Field Helper for Gravity Forms Common
 *
 * @package brilliant-plugins/field-helper-for-gravity-forms
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
	 * Checkbox fields to coalasce, keyed by formId_fieldId strings.
	 *
	 * @since 1.0.3.0
	 *
	 * @var array<string, string> $checkbox_fields
	 */
	protected static $checkbox_fields = array();

	/**
	 * Nested fields to handle, keyed by formId_fieldId strings.
	 *
	 * @since 1.3.0
	 *
	 * @var array<string, string> $nested_fields
	 */
	protected static $nested_fields = array();

	/**
	 * Survey fields to handle, keyed by formId_fieldId strings.
	 *
	 * @since 1.3.0
	 *
	 * @var array<string, string> $survey_fields
	 */
	protected static $survey_fields = array();

	/**
	 * Signature fields to handle, keyed by formId_fieldId strings.
	 *
	 * @since 1.10.0
	 *
	 * @var array<string, string> $signature_fields
	 */
	protected static $signature_fields = array();

	/**
	 * Convert field ID with period to underscore.
	 *
	 * @since 1.0.0
	 *
	 * @param string     $id      Field ID.
	 * @param int|string $form_id Form ID.
	 *
	 * @return string    Sanitized field ID for/from database.
	 */
	public static function convert_field_id( $id, $form_id = null ) {
		return 'field-' . str_replace( '.', '_', $id ) . ( $form_id ? '_' . $form_id : '' );
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

		if ( empty( $labels ) ) {
			$result['fields']['error'] = array(
				'code'    => 500,
				'message' => 'Friendly field names are not set. Please visit your form settings to set them.',
			);

			/**
			 * Filter the friendly field names for the entry.
			 *
			 * @since 1.6.0
			 * @since 1.9.3 Added to this guard condition.
			 *
			 * @param array $result Form entry with friendly field names.
			 *
			 * @return array
			 */
			$result = apply_filters( 'gf_field_helper_friendly_entry', $result );

			return $result;
		}

		$fields = array();

		$original_entry = $result;

		foreach ( $result as $key => $value ) {
			$field_id          = self::convert_field_id( $key );
			$compound_field_id = self::convert_field_id( absint( $key ) ); // Convert checkboxes and other compound fields to a single ID.
			$field_and_form_id = self::convert_field_id( $key, $result['form_id'] );

			if ( array_key_exists( $field_and_form_id, self::$checkbox_fields ) ) {
				// Checkbox.
				if ( ! empty( $value ) ) {
					$fields[ $labels[ $compound_field_id ] ][] = $value;
				}
			} elseif ( array_key_exists( $field_and_form_id, self::$nested_fields ) ) {
				// Nested Form field.
				if ( ! empty( $value ) ) {
					switch ( self::$nested_fields[ $field_and_form_id ] ) {

						case 'expanded':
							if ( is_array( $value ) ) {
								$entries = $value;
							} else {
								$entry_ids = explode( ',', $value );
								$entries   = GFAPI::get_entries(
									0,
									array(
										'field_filters' => array(
											array(
												'key'      => 'id',
												'operator' => 'IN',
												'value'    => $entry_ids,
											),
										),
									)
								);
							}
							foreach ( $entries as $nested_entry ) {
								$fields[ $labels[ $compound_field_id ] ][] = self::replace_field_names( $nested_entry );
							}
							break;

						case 'array':
							if ( is_array( $value ) ) {
								$value = wp_list_pluck( $value, 'id' );
							}
							$value                          = json_decode( '[' . $value . ']' );
							$fields[ $labels[ $compound_field_id ] ] = $value;
							break;

						case 'csv':
						default:
							if ( is_array( $value ) ) {
								$value = wp_list_pluck( $value, 'id' );
							}
							$fields[ $labels[ $compound_field_id ] ] = $value;
							break;
					}
				}
			} elseif ( array_key_exists( $field_and_form_id, self::$signature_fields ) ) {
				if ( self::$signature_fields[ $field_and_form_id ] === 'url' ) {
					$field                          = GFAPI::get_field( $result['form_id'], $compound_field_id );
					$fields[ $labels[ $field_id ] ] = $field->get_value_url( $value );
				} else {
					$fields[ $labels[ $field_id ] ] = $value;
				}
			} elseif ( array_key_exists( $field_id, self::$survey_fields ) ) {
				$field = GFAPI::get_field( $result ['form_id'], $compound_field_id );
				if ( method_exists( $field, 'get_column_text' ) ) {
					/** @var \GF_Field_Likert $field */ // phpcs:ignore
					$fields[ $labels[ $field_id ] ] = $field->get_column_text( $value, $original_entry, $key );
				} elseif ( in_array( $field['inputType'], array( 'checkbox', 'select', 'radio' ), true ) ) {
					$fields[ $labels[ $field_id ] ] = $field->get_selected_choice( $value )['text'];
				} else {
					$fields[ $labels[ $field_id ] ] = $field->get_value_export( $original_entry, $field_id );
				}
			} elseif ( in_array( $field_id, array_flip( $labels ), false ) ) { // phpcs:ignore WordPress.PHP.StrictInArray -- since GF uses both integer and string field keys.
				// Others.
				$fields[ $labels[ $field_id ] ] = $value;
			}

			// Unset only field keys (strings will convert to 0, floats to integers).
			if ( 0 !== absint( $key ) ) {
				unset( $result[ $key ] );
			}
		}

		$result['fields'] = $fields;

		/**
		 * Filter the friendly field names for the entry.
		 *
		 * @since 1.6.0
		 *
		 * @param array $result Form entry with friendly field names.
		 *
		 * @return array
		 */
		$result = apply_filters( 'gf_field_helper_friendly_entry', $result );

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

			if ( ! is_array( $form[ GF_FIELD_HELPER_SLUG ] ) ) {
				return array();
			}

			$fields = array_filter( $form[ GF_FIELD_HELPER_SLUG ] );

			foreach ( $form['fields'] as $field ) {
				$field_id          = self::convert_field_id( $field['id'] );
				$field_and_form_id = self::convert_field_id( $field['id'], $form_id );

				if ( 'checkbox' === $field['type'] && array_key_exists( $field_id . '-checkbox-return', $fields ) && 'combined' === $fields[ $field_id . '-checkbox-return' ] ) {

					// Unset the choices.
					foreach ( $field['inputs'] as $input_key => $input_id ) {
						$input_and_form_id = self::convert_field_id( $input_id['id'], $form_id );
						unset( $fields[ $input_and_form_id ] );
						self::$checkbox_fields[ $input_and_form_id ] = $field_id;
					}

					// Set array of checkbox fields.
					self::$checkbox_fields[ $field_and_form_id ] = $field_id;
				}

				if ( 'signature' === $field['type'] && array_key_exists( $field_id . '-signature-return', $fields ) ) {
					self::$signature_fields[ $field_and_form_id ] = $fields[ $field_id . '-signature-return' ];
				}

				if ( 'form' === $field['type'] && array_key_exists( $field_id . '-form-return', $fields ) ) {
					self::$nested_fields[ $field_and_form_id ] = $fields[ $field_id . '-form-return' ];
				}

				if ( 'survey' === $field['type'] ) {
					if ( $field['inputs'] ) {
						// Unset the choices.
						foreach ( $field['inputs'] as $input_key => $input_id ) {
							$input_id                         = self::convert_field_id( $input_id['id'], $form_id );
							self::$survey_fields[ $input_id ] = $input_id;
						}
					} else {
						self::$survey_fields[ $field_and_form_id ] = $fields[ $field_and_form_id ];
					}
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
