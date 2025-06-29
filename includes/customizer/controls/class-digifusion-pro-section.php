<?php
/**
 * DigiFusion Pro Section
 *
 * @package DigiFusion
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DigiFusion Pro Section class.
 */
class DigiFusion_Pro_Section extends WP_Customize_Section {

	/**
	 * Section type.
	 *
	 * @var string
	 */
	public $type = 'digifusion-pro';

	/**
	 * Pro URL.
	 *
	 * @var string
	 */
	public $button_url = '';

	/**
	 * Pro text.
	 *
	 * @var string
	 */
	public $button_text = '';

	/**
	 * Constructor.
	 *
	 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
	 * @param string               $id      Section ID.
	 * @param array                $args    Optional. Arguments to override class property defaults.
	 */
	public function __construct( $manager, $id, $args = array() ) {
		parent::__construct( $manager, $id, $args );

		if ( isset( $args['button_url'] ) ) {
			$this->button_url = $args['button_url'];
		}

		if ( isset( $args['button_text'] ) ) {
			$this->button_text = $args['button_text'];
		}
	}

	/**
	 * Render the section.
	 */
	public function render() {
		?>
		<li id="accordion-section-<?php echo esc_attr( $this->id ); ?>" class="accordion-section control-section control-section-<?php echo esc_attr( $this->id ); ?> cannot-expand">
			<h3 class="accordion-section-title"><?php echo esc_html( $this->title ); ?></h3>
			<p class="accordion-section-description"><?php echo esc_html( $this->description ); ?></p>
			<div class="accordion-section-buttons">
				<a href="<?php echo esc_url( $this->button_url ); ?>" target="_blank"><?php echo esc_html( $this->button_text ); ?></a>
			</div>
		</li>
		<?php
	}
}