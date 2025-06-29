<?php
/**
 * DigiFusion Typography Control
 *
 * @package DigiFusion
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Typography control class
 */
class DigiFusion_Typography_Control extends WP_Customize_Control {

	/**
	 * Control type
	 *
	 * @var string
	 */
	public $type = 'digifusion-typography';

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		// Get the default value from the setting
		$default_value = $this->setting->default;
		?>
		<div class="digifusion-typography-container" 
			 data-control-id="<?php echo esc_attr( $this->id ); ?>"
			 data-defaults="<?php echo esc_attr( $default_value ); ?>">
		</div>
		
		<!-- Hidden input element that WordPress Customizer expects -->
		<input type="hidden" 
			   id="<?php echo esc_attr( '_customize-input-' . $this->id ); ?>" 
			   name="<?php echo esc_attr( $this->id ); ?>" 
			   value="<?php echo esc_attr( $this->value() ); ?>"
			   <?php $this->link(); ?> />
		<?php
	}

	/**
	 * An Underscore (JS) template for this control's content (but not its container).
	 */
	protected function content_template() {
		?>
		<div class="digifusion-typography-container" 
			 data-control-id="{{ data.id }}"
			 data-defaults="{{ data.defaultValue }}">
		</div>
		
		<input type="hidden" 
			   id="_customize-input-{{ data.id }}" 
			   name="{{ data.id }}" 
			   value="{{ data.value }}"
			   {{{ data.link }}} />
		<?php
	}
}