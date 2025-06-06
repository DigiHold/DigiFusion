<?php
/**
 * DigiFusion Control Base
 *
 * Base class for all custom DigiFusion controls.
 *
 * @package DigiFusion
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DigiFusion base control class.
 */
class DigiFusion_Control_Base extends WP_Customize_Control {

	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 'digifusion-base';

	/**
	 * Custom data parameters.
	 *
	 * @var array
	 */
	public $params = array();

	/**
	 * Render the control's content.
	 * Allows the content to be overridden without having to rewrite the wrapper.
	 */
	public function render_content() {
		// Get control data
		$data = $this->get_control_data();
		
		// Output empty container for React to render into
		?>
		<div class="digifusion-control-container" 
			id="<?php echo esc_attr( $this->id ); ?>-control" 
			data-control-params="<?php echo esc_attr( json_encode( $data ) ); ?>">
			
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>
			
			<?php if ( ! empty( $this->description ) ) : ?>
				<span class="customize-control-description"><?php echo wp_kses_post( $this->description ); ?></span>
			<?php endif; ?>
			
			<div class="digifusion-control-content"></div>
		</div>
		<?php
	}

	/**
	 * Get control data to pass to JS.
	 * 
	 * @return array Control data
	 */
	protected function get_control_data() {
		return array(
			'id'          => $this->id,
			'type'        => $this->type,
			'label'       => $this->label,
			'description' => $this->description,
			'params'      => $this->params,
		);
	}
}