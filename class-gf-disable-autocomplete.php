<?php
/**
 * Disable autocomplete
 *
 * @package gravity-forms-field-helper
 */

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

GFForms::include_addon_framework();

/**
 * Disable autocomplete
 *
 * @package gravity-forms-field-helper
 */
class GF_Disable_Autocomplete extends GFAddOn {

	/**
	 * Plugin version.
	 *
	 * @since 1.2.0
	 *
	 * @var string $_version
	 */
	protected $_version = GF_FIELD_HELPER_VERSION;

	/**
	 * Minimum Gravity Forms version.
	 *
	 * @since 1.2.0
	 *
	 * @var string $_min_gravityforms_version
	 */
	protected $_min_gravityforms_version = '2.4';

	/**
	 * Plugin slug.
	 *
	 * @since 1.2.0
	 *
	 * @var string $_slug
	 */
	protected $_slug = GF_FIELD_HELPER_SLUG . '-disable-autocomplete';

	/**
	 * Plugin path.
	 *
	 * @since 1.2.0
	 *
	 * @var string $_path
	 */
	protected $_path = 'field-helper-for-gravity-forms/field-helper-for-gravity-forms.php';

	/**
	 * Full plugin path.
	 *
	 * @since 1.2.0
	 *
	 * @var string $_full_path
	 */
	protected $_full_path = __FILE__;

	/**
	 * Plugin title.
	 *
	 * @since 1.2.0
	 *
	 * @var string $_title
	 */
	protected $_title = 'Disable Field Autocomplete';

	/**
	 * Short plugin title.
	 *
	 * @since 1.2.0
	 *
	 * @var string $_short_title
	 */
	protected $_short_title = 'Disable Field Autocomplete';

	/**
	 * Form object.
	 *
	 * @since 1.2.0
	 *
	 * @var array $form
	 */
	protected $form;

	/**
	 * Class instance.
	 *
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * Return only one instance of this class.
	 *
	 * @return GF_Disable_Autocomplete class.
	 */
	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new GF_Disable_Autocomplete();
		}

		return self::$_instance;
	}

	/**
	 * Load actions and hooks.
	 *
	 * @since 1.2.0
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'gform_field_content', array( $this, 'maybe_disable_autocomplete' ), 15, 5 );
	}

	/**
	 * Render plugin page content.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function plugin_page() {
		echo esc_html__( 'To use this plugin, go to the Disable Field Autocomplete section on each of your forms’ settings or set it on individual fields.', 'gravity-forms-field-helper' );
	}

	/**
	 * Build form settings array.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $form Form object.
	 *
	 * @return array      Form settings.
	 */
	public function form_settings_fields( $form ) {

		$autocomplete_fields = array(
			array(
				'title'  => esc_html__( 'Autocomplete Settings', 'gravity-forms-field-helper' ),
				'fields' => array(
					array(
						'id'          => 'prevent_autocomplete',
						'label'       => esc_html__( 'Disable Autocomplete', 'gravity-forms-field-helper' ),
						'description' => esc_html__( 'Prevents browsers from autocompleting all fields in this form.', 'gravity-forms-field-helper' ),
						'type'        => 'checkbox',
						'choices'     => array(
							array(
								'label' => esc_html__( 'Disable autocomplete for all fields', 'gravity-forms-field-helper' ),
								'name'  => 'disable-autocomplete',
							),
						),
					),
				),
			),
		);

		return $autocomplete_fields;
	}

	/**
	 * Get form autocomplete setting.
	 *
	 * @since 1.2.0
	 *
	 * @param int $form_id Form ID.
	 *
	 * @return bool        True if autocomplete is disabled for the entire form, false if not.
	 */
	public function get_form_autocomplete( $form_id ) {
		if ( is_null( $this->form ) ) {
			$this->form = GFAPI::get_form( $form_id );
		}

		return (bool) rgar( $this->get_form_settings( $this->form ), 'disable-autocomplete' );
	}

	/**
	 * Maybe disable autocomplete for each field.
	 *
	 * @since 1.2.0
	 *
	 * @param string $input    Field markup.
	 * @param array  $field    Field object.
	 * @param string $value    Default/initial value.
	 * @param int    $entry_id Entry ID.
	 * @param int    $form_id  Form ID.
	 *
	 * @return string          Field markup.
	 */
	public function maybe_disable_autocomplete( $input, $field, $value, $entry_id, $form_id ) {
		if ( is_admin() ) {
			return $input;
		}

		$disabled = $this->get_form_autocomplete( $form_id );

		/**
		 * Filters the disable-autocomplete value.
		 *
		 * @param bool  $disabled Whether autocomplete is disabled for this field.
		 * @param int   $form_id  Gravity Form ID.
		 * @param array $field    Gravity Form Field object.
		 *
		 * @return bool           Whether autocomplete is disabled for this field.
		 */
		$disabled = apply_filters( 'field_helper_disable_autocomplete', $disabled, $form_id, $field );
		$disabled = apply_filters( 'field_helper_disable_autocomplete_' . $form_id, $disabled, $form_id, $field );
		$disabled = apply_filters( 'field_helper_disable_autocomplete_' . $form_id . '_' . $field['ID'], $disabled, $form_id, $field );

		if ( $disabled ) {
			$input = preg_replace( '/<(input|textarea)/', '<${1} autocomplete="ca3704aa0b06f" ', $input );
		}

		return $input;
	}
}
