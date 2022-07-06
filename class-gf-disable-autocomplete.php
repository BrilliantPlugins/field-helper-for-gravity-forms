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
	protected $_full_path = GF_FIELD_HELPER_FILE;

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
	 * Permissions to access the settings page.
	 *
	 * @since 1.4.5
	 *
	 * @var string|array A string or an array of capabilities or roles that have access to the settings page.
	 */
	protected $_capabilities_settings_page = array(
		'gravityforms_edit_forms',
	);

	/**
	 * Permissions to access the form settings.
	 *
	 * @since 1.4.5
	 *
	 * @var string|array A string or an array of capabilities or roles that have access to the form settings.
	 */
	protected $_capabilities_form_settings = array(
		'gravityforms_edit_forms',
	);

	/**
	 * Permissions to access the plugin page.
	 *
	 * @since 1.4.5
	 *
	 * @var string|array A string or an array of capabilities or roles that have access to the plugin page.
	 */
	protected $_capabilities_plugin_page = 'gravityforms_edit_forms';

	/**
	 * Permissions to access the app menu.
	 *
	 * @since 1.4.5
	 *
	 * @var string|array A string or an array of capabilities or roles that have access to the app menu.
	 */
	protected $_capabilities_app_menu = array(
		'gravityforms_edit_forms',
	);

	/**
	 * Permissions to access the app settings page.
	 *
	 * @since 1.4.5
	 *
	 * @var string|array A string or an array of capabilities or roles that have access to the app settings page.
	 */
	protected $_capabilities_app_settings = array(
		'gravityforms_edit_forms',
	);

	/**
	 * Permissions to uninstall plugin.
	 *
	 * @since 1.4.5
	 *
	 * @var string|array A string or an array of capabilities or roles that can uninstall the plugin.
	 */
	protected $_capabilities_uninstall = array(
		'gravityforms_uninstall',
	);

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

		// Backend.
		add_action( 'gform_field_advanced_settings', array( $this, 'field_disable_autocomplete_settings' ), 10, 2 );
		add_action( 'gform_editor_js', array( $this, 'editor_script' ) );
		add_filter( 'gform_tooltips', array( $this, 'add_field_tooltip' ) );

		// Frontend.
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
						'name'        => 'disable_autocomplete',
						'label'       => esc_html__( 'Disable Autocomplete', 'gravity-forms-field-helper' ),
						'description' => esc_html__( 'Instruct browsers not to autocomplete any field in this form. If you want to disable autocomplete only for specific fields, leave this unchecked and edit each field individually.', 'gravity-forms-field-helper' ),
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
	 * Render field setting.
	 *
	 * @since 1.2.0
	 *
	 * @param int $position Form advanced settings position.
	 * @param int $form_id  Form ID.
	 *
	 * @return void
	 */
	public function field_disable_autocomplete_settings( $position, $form_id ) {
		if ( 425 === $position ) {
			?>
			<li class="autocomplete_field_setting field_setting">
				<input type="checkbox" name="disable_autocomplete" id="field_disable_autocomplete" onclick="SetFieldProperty('disable_autocomplete', this.checked);">
				<label class="inline" for="field_disable_autocomplete"><?php esc_html_e( 'Disable browser autocomplete', 'gravity-forms-field-helper' ); ?> <?php gform_tooltip( 'form_field_disable_autocomplete' ); ?></label>
			</li>

			<?php
		}
	}

	/**
	 * Inject supporting script to editor pages.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function editor_script() {
		?>
		<script type='text/javascript'>
			// Add setting to specific field types.
			fieldSettings.address += ', .autocomplete_field_setting';
			fieldSettings.creditcard += ', .autocomplete_field_setting';
			fieldSettings.date += ', .autocomplete_field_setting';
			fieldSettings.email += ', .autocomplete_field_setting';
			fieldSettings.name += ', .autocomplete_field_setting';
			fieldSettings.number += ', .autocomplete_field_setting';
			fieldSettings.password += ', .autocomplete_field_setting';
			fieldSettings.phone += ', .autocomplete_field_setting';
			fieldSettings.price += ', .autocomplete_field_setting';
			fieldSettings.quantity += ', .autocomplete_field_setting';
			fieldSettings.text += ', .autocomplete_field_setting';
			fieldSettings.textarea += ', .autocomplete_field_setting';
			fieldSettings.time += ', .autocomplete_field_setting';
			fieldSettings.website += ', .autocomplete_field_setting';

			// Bind to the load field settings event to initialize the checkbox.
			jQuery(document).on('gform_load_field_settings', function(event, field, form){
				jQuery('#field_disable_autocomplete').attr('checked', field['disable_autocomplete'] == true);
			});
		</script>
		<?php
	}

	/**
	 * Display tooltip.
	 *
	 * @since 1.2.0
	 *
	 * @param array $tooltips Field tooltips.
	 *
	 * @return array Field tooltips.
	 */
	public function add_field_tooltip( $tooltips ) {
		$tooltips['form_field_disable_autocomplete'] = '<h6>Disable Browser Autocomplete</h6>Check this box to instruct browsers not to autocomplete this field (e.g., with a user’s name, address, email address, etc.).';
		return $tooltips;
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
	 * Get field autocomplete setting.
	 *
	 * @since 1.2.0
	 *
	 * @param int $form_id  Form ID.
	 * @param int $field_id Field ID.
	 *
	 * @return bool         True if autocomplete is disabled for this specific field, false if not.
	 */
	public function get_field_autocomplete( $form_id, $field_id ) {
		$field = GFAPI::get_field( $form_id, $field_id );

		return (bool) $field['disable_autocomplete'];
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

		if ( ! $disabled ) {
			$disabled = $this->get_field_autocomplete( $form_id, $field['id'] );
		}

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
		$disabled = apply_filters( 'field_helper_disable_autocomplete_' . $form_id . '_' . $field['id'], $disabled, $form_id, $field );

		if ( $disabled ) {
			$input = preg_replace( '/<(input|textarea)/', '<${1} autocomplete="ca3704aa0b06f" ', $input );
		}

		return $input;
	}
}
