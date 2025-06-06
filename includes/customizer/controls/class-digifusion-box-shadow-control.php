<?php
/**
 * DigiFusion Box Shadow Control
 *
 * @package DigiFusion
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DigiFusion Box Shadow Control class.
 */
class DigiFusion_Box_Shadow_Control extends DigiFusion_Control_Base {

	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 'digifusion-box-shadow';

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		$input_id = '_customize-input-' . $this->id;
		$value = $this->value();
		?>
		<div class="digifusion-box-shadow-control">
			<?php if ( ! empty( $this->label ) ) : ?>
				<label for="<?php echo esc_attr( $input_id ); ?>" class="customize-control-title"><?php echo esc_html( $this->label ); ?></label>
			<?php endif; ?>
			
			<?php if ( ! empty( $this->description ) ) : ?>
				<span class="description customize-control-description"><?php echo wp_kses_post( $this->description ); ?></span>
			<?php endif; ?>
			
			<div class="digifusion-box-shadow-container" data-control-id="<?php echo esc_attr( $this->id ); ?>"></div>
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