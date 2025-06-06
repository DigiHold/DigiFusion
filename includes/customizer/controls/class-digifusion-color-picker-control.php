<?php
/**
 * DigiFusion Color Picker Control
 *
 * @package DigiFusion
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DigiFusion Color Picker Control class.
 */
class DigiFusion_Color_Picker_Control extends DigiFusion_Control_Base {

	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 'digifusion-color-picker';

	/**
	 * Whether this is alpha enabled.
	 *
	 * @var bool
	 */
	public $alpha = false;

	/**
	 * Constructor.
	 *
	 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
	 * @param string               $id      Control ID.
	 * @param array                $args    Optional. Arguments to override class property defaults.
	 */
	public function __construct( $manager, $id, $args = array() ) {
		parent::__construct( $manager, $id, $args );

		if ( isset( $args['alpha'] ) ) {
			$this->alpha = $args['alpha'];
		}
	}

	/**
	 * Get control data.
	 *
	 * @return array
	 */
	protected function get_control_data() {
		$data = parent::get_control_data();
		$data['alpha'] = $this->alpha;
		return $data;
	}

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		$input_id = '_customize-input-' . $this->id;
		$value = $this->value();
		?>
		<div class="digifusion-color-picker-control">
			<div class="digifusion-color-picker-container" 
				data-control-id="<?php echo esc_attr( $this->id ); ?>"
				data-alpha="<?php echo esc_attr( $this->alpha ? 'true' : 'false' ); ?>">
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