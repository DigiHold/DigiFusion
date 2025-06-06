<?php
/**
 * DigiFusion Button Group Control
 *
 * @package DigiFusion
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DigiFusion Button Group Control class.
 */
class DigiFusion_Button_Group_Control extends DigiFusion_Control_Base {

	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 'digifusion-button-group';

	/**
	 * Whether this is responsive or not.
	 *
	 * @var bool
	 */
	public $is_responsive = false;

	/**
	 * Options for the button group.
	 *
	 * @var array
	 */
	public $choices = array();

	/**
	 * Default value for the control.
	 *
	 * @var string
	 */
	public $default_value = '';

	/**
	 * Default values for each device.
	 *
	 * @var array
	 */
	public $default_values = array();

	/**
	 * Constructor.
	 *
	 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
	 * @param string               $id      Control ID.
	 * @param array                $args    Optional. Arguments to override class property defaults.
	 */
	public function __construct( $manager, $id, $args = array() ) {
		parent::__construct( $manager, $id, $args );

		if ( isset( $args['is_responsive'] ) ) {
			$this->is_responsive = $args['is_responsive'];
		}

		if ( isset( $args['choices'] ) ) {
			$this->choices = $args['choices'];
		}

		if ( isset( $args['default_value'] ) ) {
			$this->default_value = $args['default_value'];
		} elseif ( !empty( $this->choices ) ) {
			$this->default_value = $this->choices[0]['value'];
		}

		if ( isset( $args['default_values'] ) ) {
			$this->default_values = $args['default_values'];
		}
	}

	/**
	 * Get control data.
	 *
	 * @return array
	 */
	protected function get_control_data() {
		$data = parent::get_control_data();
		$data['is_responsive'] = $this->is_responsive;
		$data['choices'] = $this->choices;
		$data['default_value'] = $this->default_value;
		$data['default_values'] = $this->default_values;
		return $data;
	}

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		$input_id = '_customize-input-' . $this->id;
		$value = $this->value();
		?>
		<div class="digifusion-button-group-control">
			<div class="digifusion-button-group-container" 
				data-control-id="<?php echo esc_attr( $this->id ); ?>"
				data-is-responsive="<?php echo esc_attr( $this->is_responsive ? 'true' : 'false' ); ?>"
				data-choices="<?php echo esc_attr( json_encode( $this->choices ) ); ?>"
				data-default-value="<?php echo esc_attr( $this->default_value ); ?>"
				data-default-values="<?php echo esc_attr( json_encode( $this->default_values ) ); ?>">
			</div>
			<input
				id="<?php echo esc_attr( $input_id ); ?>"
				type="hidden"
				<?php $this->link(); ?>
				value="<?php echo esc_attr( $value ); ?>"
			/>
		</div>
		<?php
	}
}