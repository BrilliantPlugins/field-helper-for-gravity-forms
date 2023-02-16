<?php
/**
 * Field Helper For Gravity Forms: Likert
 *
 * @package formhelperforgravityforms
 */

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * Field Helper: Likert
 */
class FH_Likert {
    
	/**
	 * Class instance.
	 *
	 * @var FH_Likert $instance
	 */
	private static $instance;

    /**
	 * Return only one instance of this class.
	 *
	 * @return FH_Likert class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new FH_Likert();
		}

		return self::$instance;
	}

	/**
	 * Load actions and hooks.
	 */
	private function __construct() {
		add_filter( 'gf_field_helper_friendly_entry', array( $this, 'contract_evaluation_survey_fields' ) );
	}

	/**
	 * Handle survey fields.
	 *
	 * @param array $results Form entry with friendly field names.
	 *
	 * @return array
	 */
	public function contract_evaluation_survey_fields( array $results ) : array {

		$form  = GFAPI::get_form( $results['form_id'] );
		$entry = GFAPI::get_entry( $results['id'] );

        // @codingStandardsIgnoreStart 
        $labels = GF_Field_Helper_Common::get_form_friendly_labels( $results['form_id'] );
        $field_labels = array_map( function( $field ) {
            return[ 'value' => $field ];
        }, $labels );

        // phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
        foreach ( $form['fields'] as $field ) {

            $field_choices = $field->choices;
            $survey_choices = array_map( function( $choices ) {
                return[ 'value' => $choices ];
            }, $field_choices );

            if ( $field->glikertcol ) {

            }
		}
		// @codingStandardsIgnoreEnd
	}
}
