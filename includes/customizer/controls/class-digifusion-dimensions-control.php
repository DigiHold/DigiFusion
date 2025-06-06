<?php
/**
 * DigiFusion Dimensions Control
 *
 * @package DigiFusion
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DigiFusion Dimensions Control class.
 */
class DigiFusion_Dimensions_Control extends DigiFusion_Control_Base {

	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 'digifusion-dimensions';

	/**
	 * Whether this is responsive or not.
	 *
	 * @var bool
	 */
	public $is_responsive = false;

	/**
	 * Available units.
	 *
	 * @var array
	 */
	public $units = array();

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

		if ( isset( $args['units'] ) ) {
			$this->units = $args['units'];
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
		$data['units'] = $this->units;
		return $data;
	}

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		$input_id = '_customize-input-' . $this->id;
		$value = $this->value();
		$data_attr = $this->get_control_data();
		?>
		<div class="digifusion-dimensions-control">
			<div class="digifusion-dimensions-container" 
				data-control-id="<?php echo esc_attr( $this->id ); ?>"
				data-is-responsive="<?php echo esc_attr( $this->is_responsive ? 'true' : 'false' ); ?>"
				data-units="<?php echo esc_attr( json_encode( $this->units ) ); ?>">
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