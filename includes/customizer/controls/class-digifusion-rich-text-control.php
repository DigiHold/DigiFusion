<?php
/**
 * DigiFusion Rich Text Control
 *
 * @package DigiFusion
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DigiFusion Rich Text Control class.
 */
class DigiFusion_Rich_Text_Control extends DigiFusion_Control_Base {

	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 'digifusion-rich-text';

	/**
	 * Enqueue control related scripts/styles.
	 */
	public function enqueue() {
		wp_enqueue_script( 'quicktags' );
	}

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		$input_id = '_customize-input-' . $this->id;
		$editor_id = 'digifusion-rich-text-' . $this->id;
		$value = $this->value();
		?>
		<div class="digifusion-rich-text-control">
			<?php if ( ! empty( $this->label ) ) : ?>
				<label for="<?php echo esc_attr( $editor_id ); ?>" class="customize-control-title"><?php echo esc_html( $this->label ); ?></label>
			<?php endif; ?>
			
			<?php if ( ! empty( $this->description ) ) : ?>
				<span class="description customize-control-description"><?php echo wp_kses_post( $this->description ); ?></span>
			<?php endif; ?>
			
			<div class="digifusion-rich-text-container" data-control-id="<?php echo esc_attr( $this->id ); ?>">
				<textarea id="<?php echo esc_attr( $editor_id ); ?>" class="digifusion-rich-text-editor" rows="5"><?php echo esc_textarea( $value ); ?></textarea>
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