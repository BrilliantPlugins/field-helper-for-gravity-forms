<?php
/**
 * Input patterns
 *
 * @package gravity-forms-field-helper
 */

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

GFForms::include_addon_framework();

/**
 * Input patterns
 *
 * @package gravity-forms-field-helper
 */
class GF_Input_Pattern extends GFAddOn {

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
	protected $_slug = GF_FIELD_HELPER_SLUG . '-input-pattern';

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
	protected $_title = 'Field Input Pattern';

	/**
	 * Short plugin title.
	 *
	 * @since 1.2.0
	 *
	 * @var string $_short_title
	 */
	protected $_short_title = 'Field Input Pattern';

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
	 * @return GF_Input_Pattern class.
	 */
	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new GF_Input_Pattern();
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
		add_action( 'gform_field_standard_settings', array( $this, 'field_input_pattern_settings' ), 10, 2 );
		add_action( 'gform_editor_js', array( $this, 'editor_script' ) );
		add_filter( 'gform_tooltips', array( $this, 'add_field_tooltip' ) );

		// Frontend.
		add_filter( 'gform_field_content', array( $this, 'add_input_pattern' ), 15, 5 );

		// Validation.
		add_filter( 'gform_field_validation', array( $this, 'validate_input_pattern' ), 10, 4 );
	}

	/**
	 * Render plugin page content.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function plugin_page() {
		echo esc_html__( 'To use this plugin, set input patterns on individual fields.', 'gravity-forms-field-helper' );
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
	public function field_input_pattern_settings( $position, $form_id ) {
		if ( 1450 === $position ) {
			?>
			<li class="field_input_pattern_setting field_setting">
				<input type="checkbox" id="field_input_pattern" onclick="ToggleInputPattern();" onkeypress="ToggleInputPattern();" >
				<label class="inline" for="field_input_pattern"><?php esc_html_e( 'Input Pattern', 'gravity-forms-field-helper' ); ?> <?php gform_tooltip( 'form_field_input_pattern' ); ?></label>

				<div id="gform_input_pattern">

					<br />

					<input type="text" id="field_input_pattern_value" placeholder="[a-zA-Z0-9]"/>

					<p class="input_pattern_text_description" style="margin:5px 0 0;">
						<?php esc_html_e( 'Enter an input pattern', 'gravity-forms-field-helper' ) ?>.
						<a href="javascript:void(0);" onclick="tb_show('<?php echo esc_js( __( 'Input Pattern Instructions', 'gravity-forms-field-helper' ) ); ?>', '#TB_inline?width=350&amp;inlineId=input_pattern_instructions', '');" onkeypress="tb_show('<?php echo esc_js( __( 'Input Pattern Instructions', 'gravity-forms-field-helper' ) ); ?>', '#TB_inline?width=350&amp;inlineId=input_pattern_instructions', '');"><?php esc_html_e( 'Help', 'gravityforms' ) ?></a>
					</p>

					<div id="input_pattern_instructions" style="display:none;">
						<div class="input_pattern_instructions custom_mask_instructions">

							<h4><?php esc_html_e( 'Usage', 'gravityforms' ) ?></h4>
							<p>Use a regex to specify the field format.</p>
							<p>Note: this input pattern <strong>should not</strong> be used for email addresses, dates, or websites; use the appropriate field type for those types of data.</p>

							<h4><?php esc_html_e( 'Examples', 'gravityforms' ) ?></h4>
							<ul class="examples-list">
								<li>
									<h5><?php esc_html_e( 'Only letters (either case), numbers, and the underscore; no more than 15 characters.', 'gravity-forms-field-helper' ) ?></h5>
									<span class="label"><?php esc_html_e( 'Input Pattern', 'gravity-forms-field-helper' ) ?></span> <code>[A-Za-z0-9_]{1,15}</code><br />
									<span class="label"><?php esc_html_e( 'Valid Input', 'gravity-forms-field-helper' ) ?></span> <code>ABCabc_123</code>
								</li>
								<li>
									<h5><?php esc_html_e( 'Only lowercase letters and numbers; at least 5 characters, but no max limit.', 'gravity-forms-field-helper' ) ?></h5>
									<span class="label"><?php esc_html_e( 'Input Pattern', 'gravity-forms-field-helper' ) ?></span> <code>[a-zd.]{5,}</code><br />
									<span class="label"><?php esc_html_e( 'Valid Input', 'gravity-forms-field-helper' ) ?></span> <code>abc123</code>
								</li>
							</ul>

						</div>
					</div>

				</div>

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
			fieldSettings.text += ', .field_input_pattern_setting';
			fieldSettings.textarea += ', .field_input_pattern_setting';

			// Bind to the load field settings event to initialize the checkbox.
			jQuery(document).on('gform_load_field_settings', function(event, field, form){
				jQuery('#field_input_pattern').attr('checked', field['input_pattern'] == true);
			});

			// Toggle input pattern field.
			function ToggleInputPattern(isInit) {
				var speed = isInit ? "" : "slow";

				if(jQuery('#field_input_pattern').is(':checked')){
					jQuery('#gform_input_pattern').show(speed);
					jQuery('.input_pattern_text_description').show(speed);
					SetFieldProperty('input_pattern', true);
				}
				else{
					jQuery('#gform_input_pattern').hide(speed);
					jQuery('.input_pattern_text_description').hide(speed);
					SetFieldProperty('input_pattern', false);
					SetFieldProperty('input_pattern_value', '');
				}
			}

			// Save the input pattern.
			jQuery('#field_input_pattern_value').on('input propertychange', function(){
				SetFieldProperty('input_pattern_value', this.value);
			});

			// Set fields on init.
			jQuery(document).on('gform_load_field_settings', function(event, field) {
				ToggleInputPattern(true);
				if (field.input_pattern) {
					jQuery('#field_input_pattern_value').val(field.input_pattern_value);
				}
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
		$tooltips['form_field_input_pattern'] = '<h6>Input Pattern</h6><p>Input Patterns are used to validate the format of submitted data. (An input mask formats the visual display to help users more easily enter a specific format.)</p><p>Enter a valid regex <a href="https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#attr-pattern">input pattern</a>, and be sure to note the requirements somewhere visible to the end user.</p>';
		return $tooltips;
	}

	/**
	 * Get field input pattern value.
	 *
	 * @since 1.2.0
	 *
	 * @param int $form_id  Form ID.
	 * @param int $field_id Field ID.
	 *
	 * @return string|false Input pattern or false.
	 */
	public function get_field_input_pattern( $form_id, $field_id ) {
		$field = GFAPI::get_field( $form_id, $field_id );

		if ( $field['input_pattern'] ) {
			return $field['input_pattern_value'];
		}

		return false;
	}

	/**
	 * Set input pattern on each field.
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
	public function add_input_pattern( $input, $field, $value, $entry_id, $form_id ) {
		if ( is_admin() ) {
			return $input;
		}

		$input_pattern = $this->get_field_input_pattern( $form_id, $field['id'] );

		if ( $input_pattern ) {
			$input = preg_replace( '/<input/', '<input pattern="' . $input_pattern . '" ', $input );
		}

		return $input;
	}

	/**
	 * Perform server-side validation.
	 *
	 * @since 1.2.0
	 *
	 * @param array        $result Validation result.
	 * @param string|array $value  Field value to be validated.
	 * @param array        $form   Current form object.
	 * @param array        $field  Current field object.
	 *
	 * @return array               Validation result.
	 */
	public function validate_input_pattern( $result, $value, $form, $field ) {

		// Bail out for empty values.
		if ( empty( $value ) ) {
			return $result;
		}

		$input_pattern = $this->get_field_input_pattern( $form['id'], $field['id'] );

		// Bail out for empty input patterns.
		if ( false === $input_pattern ) {
			return $result;
		}

		$matches = preg_replace( '/^' . $input_pattern . '/', '', $value );
		if ( ! empty( $matches ) ) {
			$result['is_valid'] = false;
			$result['message']  = 'Please follow the specified pattern.';
		}

		return $result;
	}
}
