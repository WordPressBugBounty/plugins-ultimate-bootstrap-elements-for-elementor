<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var $element UBE_Element_Gallery_Metro
 */

$settings          = $element->get_settings_for_display();
$wrapper_classes   = array( 'ube-gallery', 'ube-gallery-metro', 'ube-list-grid' );
$wrapper_classes[] = 'ube-gallery-caption-' . $settings['show_caption'];
if ( ! empty( $settings['hover_image_animation'] ) ) {
	$wrapper_classes[] = 'ube-gallery-hover-' . $settings['hover_image_animation'];
}
if ( ! empty( $settings['hover_animation'] ) ) {
	$wrapper_classes[] = 'ube-gallery-hover-' . $settings['hover_animation'];
}
$element->add_render_attribute( 'wrapper_attr', 'class', $wrapper_classes );
$galleries = array();
foreach ( $settings['gallery'] as $item ) {
	$image_meta = ube_get_img_meta( $item['id'] );
	$caption    = '';
	if ( $settings['show_caption'] != 'none' && $image_meta['caption'] != '' ) {
		$caption = $image_meta['caption'];
	}
	$galleries[] = array( 'url' => $item['url'], 'caption' => $caption );
}
$animation = $settings['hover_caption_animation'];
$id        = $element->get_id();
$ratio     = $custom_ratio = 100;
if ( $settings['height'] > 0 && $settings['width'] > 0 ) {
	$custom_ratio = ( $settings['height'] / $settings['width'] ) * 100;
}
$ratios = array(
	'3by2'   => 66.7,
	'4by3'   => 75,
	'9by16'  => 177.8,
	'16by9'  => 56.25,
	'21by9'  => 42.86,
	'custom' => $custom_ratio
);
if ( array_key_exists( $settings['gallery_ratio'], $ratios ) ) {
	$ratio = $ratios[ $settings['gallery_ratio'] ];
}

$item_bg_classes = apply_filters( 'ube_gallery_metro_item_bg_classes', array(
	'card-img',
	'ube-gallery-ration-custom'
) );
$breakpoints     = \Elementor\Plugin::$instance->breakpoints->get_active_breakpoints();
$breakpoints     = is_array( $breakpoints ) ? array_keys( $breakpoints ) : array( 'mobile', 'tablet' );

?>
<div <?php echo $element->get_render_attribute_string( 'wrapper_attr' ) ?>>
	<?php
	$grid_items           = $settings['gallery_grid_items'];
	$all_image            = count( $galleries );
	$number_image_show    = intval( $settings['gallery_number_images'] ) > 0 ? $settings['gallery_number_images'] : $all_image;
	foreach ( $galleries as $i => $gallery ) :
		$grid_class = array( 'ube-grid-item' );
		$item_bg_styles   = array();
		$item_bg_styles[] = sprintf( 'background-image: url(%s)', esc_url( $gallery['url'] ) );

		if ( $number_image_show < $all_image && $i + 1 == $number_image_show ) {
			$grid_class[] = 'ube-gallery-view-more';
		}

		$item_col = 1;
		$item_row = 1;
		if ( $grid_items ) {
			$grid_count = count( $grid_items );
			$grid_index = $settings['gallery_loop_layout'] !== 'yes' ? $i : $i % $grid_count;

			if ( $grid_index < $grid_count ) {
				if ( isset( $grid_items[ $grid_index ]['number_column'] ) && $grid_items[ $grid_index ]['number_column'] !== '' ) {
					$item_col     = $grid_items[ $grid_index ]['number_column'];
					$grid_class[] = 'gc-' . $item_col;
				}
				if ( isset( $grid_items[ $grid_index ]['number_row'] ) && $grid_items[ $grid_index ]['number_row'] !== '' ) {
					$item_row     = $grid_items[ $grid_index ]['number_row'];
					$grid_class[] = 'gr-' . $item_row;
				}

				foreach ( $breakpoints as $points ) {
					$key_number_column   = "number_column_{$points}";
					$key_number_row      = "number_row_{$points}";
					$item_col_responsive = 1;
					$item_row_responsive = 1;

					if ( isset( $grid_items[ $grid_index ][ $key_number_column ] ) && $grid_items[ $grid_index ][ $key_number_column ] !== '' ) {
						$item_col_responsive = $grid_items[ $grid_index ][ $key_number_column ];
						$grid_class[]        = "gc-{$points}-" . $item_col_responsive;
					}
					if ( isset( $grid_items[ $grid_index ][ $key_number_row ] ) && $grid_items[ $grid_index ][ $key_number_row ] !== '' ) {
						$item_row_responsive = $grid_items[ $grid_index ][ $key_number_row ];
						$grid_class[]        = "gr-{$points}-" . $item_row_responsive;
					}

					$item_ratio_responsive = $ratio * intval( $item_row_responsive ) / intval( $item_col_responsive );
					$item_bg_styles[]      = sprintf( "--ube-gallery-ratio-{$points}: %s", $item_ratio_responsive . '%' );
				}
			}

		}

		$item_ratio          = $ratio * $item_row / $item_col;
		$item_bg_styles[]    = sprintf( '--ube-gallery-ratio: %s', $item_ratio . '%' );
		$gallery_setting_key = $element->get_repeater_setting_key( 'gallery_grid', 'gallery_grid_items', $i );
		$element->add_render_attribute( $gallery_setting_key, 'class', $grid_class );
		$gallery_bg_setting_key = $element->get_repeater_setting_key( 'gallery_grid', 'gallery_grid_items_bg', $i );
		do_action( 'ube_gallery_metro/frontend/before_render_item', $element, $gallery_bg_setting_key, $gallery );
		$element->add_render_attribute( $gallery_bg_setting_key, 'class', implode( ' ', $item_bg_classes ) );
		$element->add_render_attribute( $gallery_bg_setting_key, 'style', implode( ';', $item_bg_styles ) );

		if ( $i < $number_image_show ):
			?>

            <div <?php echo $element->get_render_attribute_string( $gallery_setting_key ) ?>>
                <a class="card" href="<?php echo esc_url( $gallery['url'] ) ?>"
                   data-elementor-lightbox-slideshow="<?php echo esc_attr( $id ) ?>">
                    <div <?php $element->print_render_attribute_string( $gallery_bg_setting_key ) ?>></div>
                    <div class="card-img-overlay">
						<?php
						if ( $number_image_show < $all_image && $i + 1 == $number_image_show ):
							?>
                            <div class="ube-view-more-wrap">
                                <p class="ube-number-gallery text-center">
                                    +<?php echo esc_html( ( $all_image - $number_image_show ) ) ?></p>
                                <p class="ube-view-more-image text-center"><?php esc_html_e( 'View More', 'ube' ) ?></p>
                            </div>
						<?php
						endif;
						?>
						<?php
						if ( $gallery['caption'] != '' ):
							?>
                            <div class="ube-gallery-caption">
                                <p class="card-text"
                                   data-animation="<?php echo esc_attr( $animation ) ?>"><?php echo esc_html( $gallery['caption'] ) ?></p>
                            </div>
						<?php
						endif;
						?>

                    </div>
                </a>
            </div>
		<?php
		else:
			?>
            <a class="d-none" href="<?php echo esc_url( $gallery['url'] ) ?>"
               data-elementor-lightbox-slideshow="<?php echo esc_attr( $id ) ?>">
                <img src="<?php echo esc_url( $gallery['url'] ) ?>" alt="">
            </a>
		<?php endif;
	endforeach;
	?>
</div>
