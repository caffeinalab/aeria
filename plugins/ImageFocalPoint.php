<?php

add_action( 'admin_enqueue_scripts', function(){
	wp_register_script( 'focal-point', AERIA_RESOURCE_URL.'/js/image-focal-point.js', ['jquery'], '1.0', true );
	wp_register_style( 'focal-point', AERIA_RESOURCE_URL.'/css/image-focal-point.css', '1.0' );

	wp_enqueue_script( 'focal-point' );
	wp_enqueue_style( 'focal-point' );
} );

class FocalPoint {

	public static function get_option( $optionId ) {

		$options = [
			'default_focal_point_x'        => 0.5,
			'default_focal_point_y'        => 0.5,
			'previewSizes'                 => self::get_default_preview_sizes()
		];

		return $options[ $optionId ];

	}

	public static function get_default_preview_sizes() {
		return
			"200x200\n" .
			"200x150\n" .
			"200x100\n" .
			"200x50\n" .
			"150x200\n" .
			"100x200" ;
	}

	public static function get_focal_point( $postId, $orig_w = null, $orig_h = null ) {
		$focal_point = get_post_meta($postId,'focal_position',true);

		if (!$focal_point) {
			$focal_point = [(float)self::get_option('default_focal_point_x'), (float)self::get_option('default_focal_point_y')];
		}

		return $focal_point;
	}

	public static function is_compatible_post( $post ) {
		if (is_array($post)) {
			if (array_key_exists('ID',$post)) {
				$post = get_post($post['ID']);
			} else {
				return false;
			}
		}

		if ( $post->post_type != 'attachment' ) {
			return false;
		}

		$split = explode('/',$post->post_mime_type);
		if ( $split[0] != 'image' ) {
			return false;
		}

		return true;
	}

}

add_action( 'attachment_fields_to_edit', function($form_fields,$post) {

	if (!FocalPoint::is_compatible_post($post)) {
		return $form_fields;
	}

	if (!is_array($form_fields)){
		$form_fields = [];
	}

	$focal_point = FocalPoint::get_focal_point( $post->ID );

	$image      = wp_get_attachment_image_src( $post->ID, 'large' );
	$image_id   = 'focal_point_image_' . $post->ID;
	$preview_id = 'focal_point_preview_' . $post->ID;
	ob_start();
	?>
	<div class="focalPoint_mediaUpload">
		<p>
			<span aria-hidden="true" class="tst-icon tst-icon-target"></span>
			<?php echo __( 'Clicca sull\'immagine e seleziona il <strong>punto focale</strong>', 'aeria' ); ?>
		</p>
		<div id="<?php echo $image_id; ?>" class="_picker"><img src="<?php echo $image[0]; ?>"></div>
		<p class="label-example">
			<span aria-hidden="true" class="tst-icon tst-icon-eye"></span>
			<?php echo __( '<strong>Esempi:</strong>', 'aeria' ); ?>
		</p>
		<div id="<?php echo $preview_id ?>" class="_preview"></div>
	</div>
	<?php
	$html = ob_get_contents();
	ob_end_clean();

	$new_sizes = [];
	$sizes = FocalPoint::get_option( 'previewSizes' );
	$sizes = explode( "\n", $sizes );
	foreach ( $sizes as $size ) {
		$values = explode( "x", $size );
		$new_sizes[ $values[0] . ' &times; ' . $values[1] . ' px' ] = [
			'width'  => $values[0],
			'height' => $values[1]
		];
	}
	unset( $size );
	$sizes = $new_sizes;

	$script = '
		jQuery(document).ready(function() {
			tst.createPickerDelayed({
				attachmentId: "' . $post->ID . '",
				image: "#' . $image_id . '",
				input: "input[name=\'attachments[' . $post->ID . '][focal_position]\']",
				preview: "#' . $preview_id . '",
				sizes: ' . json_encode( $sizes ) . ',
				position: {
					x: ' . number_format( $focal_point[0], 10 ) . ',
					y: ' . number_format( $focal_point[1], 10 ) . '
				}
			});
		});
	';
	$html .= '<script type="text/javascript">' . $script . '</script>';

	$form_fields[ 'focal_position' ] = [
		'label' => '',
		'input' => 'text'
	];

	$form_fields[ 'theiaSmartThumbnails_positionPicker' ] = array(
		'label' => '',
		'input' => 'html',
		'html'  => $html
	);

	return $form_fields;
}, 20, 2 );

add_action( 'attachment_fields_to_save', function($post,$attachment) {
	if (!FocalPoint::is_compatible_post( $post ) ) {
		return $post;
	}
	if ( isset( $attachment[ 'focal_position' ] ) && $attachment[ 'focal_position' ] ) {
		$previous_position = FocalPoint::get_focal_point( $post['ID'] );
		$position = json_decode( $attachment[ 'focal_position' ] );
		update_post_meta( $post['ID'], 'focal_position', $position );
	}
	return $post;
}, 10, 2 );
