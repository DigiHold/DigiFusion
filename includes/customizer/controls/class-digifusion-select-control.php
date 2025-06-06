<?php
/**
 * DigiFusion Select Control
 *
 * @package DigiFusion
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DigiFusion Select Control class.
 */
class DigiFusion_Select_Control extends DigiFusion_Control_Base {

	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 'digifusion-select';

	/**
	 * Options for the select field.
	 *
	 * @var array
	 */
	public $choices = array();

	/**
	 * Constructor.
	 *
	 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
	 * @param string               $id      Control ID.
	 * @param array                $args    Optional. Arguments to override class property defaults.
	 */
	public function __construct( $manager, $id, $args = array() ) {
		parent::__construct( $manager, $id, $args );

		if ( isset( $args['choices'] ) ) {
			$this->choices = $args['choices'];
		}
	}

	/**
	 * Get control data.
	 *
	 * @return array
	 */
	protected function get_control_data() {
		$data = parent::get_control_data();
		$data['choices'] = $this->choices;
		return $data;
	}

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		$input_id = '_customize-input-' . $this->id;
		$value = $this->value();
		?>
		<div class="digifusion-select-control">
			<?php if ( ! empty( $this->label ) ) : ?>
				<label for="<?php echo esc_attr( $input_id ); ?>" class="customize-control-title"><?php echo esc_html( $this->label ); ?></label>
			<?php endif; ?>
			
			<?php if ( ! empty( $this->description ) ) : ?>
				<span class="description customize-control-description"><?php echo wp_kses_post( $this->description ); ?></span>
			<?php endif; ?>
			
			<div class="digifusion-select-container" 
				data-control-id="<?php echo esc_attr( $this->id ); ?>"
				data-choices="<?php echo esc_attr( json_encode( $this->choices ) ); ?>">
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