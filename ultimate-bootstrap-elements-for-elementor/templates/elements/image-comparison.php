<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Group_Control_Image_Size;

/**
 * @var $element UBE_Element_Image_Comparison
 */

$settings = $element->get_settings_for_display();

$wrapper_class[] = 'ube-image-comparison';
if ( $settings['image_comparison_label_pos'] !== '' && ! is_null( $settings['image_comparison_label_pos'] ) ) {
	$wrapper_class[] = 'ube-label-pos-' . $settings['image_comparison_label_pos'];
}
if ( $settings['image_comparison_label_pos_ver'] !== '' && ! is_null( $settings['image_comparison_label_pos_ver'] ) ) {
	$wrapper_class[] = 'ube-label-pos-' . $settings['image_comparison_label_pos_ver'];
}
if ( $settings['show_title_on_hover'] == 'yes' && $settings['hover_effect'] == 'yes' ) {
	$wrapper_class[] = 'ube-label-show-on-hover';
}
if ( $settings['hover_effect'] == 'yes' ) {
	$wrapper_class[] = 'ube-image-comparison-hover-animation';
}


$element->add_render_attribute( 'ube_image_comparison', 'class', $wrapper_class );

$options                       = array();
// Plain text labels (escaped again in image-comparison.js before twentytwenty builds its HTML).
$options['before_label']       = html_entity_decode( wp_strip_all_tags( (string) $settings['before_title'] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
$options['after_label']        = html_entity_decode( wp_strip_all_tags( (string) $settings['after_title'] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
$options['default_offset_pct'] = $settings['start_amount'];
$options['orientation']        = $settings['image_comparison_direction'];

if ( $settings['hover_effect'] == 'yes' ) {
	$options['no_overlay'] = false;
} else {
	$options['no_overlay'] = true;
}
if ( $settings['image_comparison_handler_move'] == 'hover' ) {
	$options['move_slider_on_hover'] = true;
} elseif ( $settings['image_comparison_handler_move'] == 'handle' ) {
	$options['move_with_handle_only'] = true;
} elseif ( $settings['image_comparison_handler_move'] == 'click' ) {
	$options['click_to_move'] = true;
}
$element->add_render_attribute( 'ube_image_comparison', 'data-options', wp_json_encode( $options, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) );

?>


<div <?php echo $element->get_render_attribute_string( 'ube_image_comparison' ); ?>>
	<?php
	if (isset($settings['before_image'])) {
		echo Group_Control_Image_Size::get_attachment_image_html( $settings, 'before_image_size', 'before_image' );
	}
	?>
	<?php
	if (isset($settings['after_image'])) {
		echo Group_Control_Image_Size::get_attachment_image_html( $settings, 'after_image_size', 'after_image' );
	}
	?>
</div>