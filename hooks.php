<?php
/**
 * Hooks
 * 
 * @package upladobe
 */
defined( 'ABSPATH' ) || die( 'Do not load directly.' );

// If Imagick is not installed, return.
if ( ! upladobe_available() ) {
	add_action( 'admin_notices', function () {
		printf(
			'<div class="error"><p><strong>Upladobe: </strong>%s</p></div>',
			__( 'You should install <a href="http://php.net/manual/book.imagick.php">ImageMagick extension</a>.', 'upladobe' )
		);
	} );

	return;
}

/**
 * Fix mimes
 */
add_filter( 'mime_types', function ( $mimes ) {
	foreach ( upladobe_mimes() as $ext => $mime ) {
		$mimes[ $ext ] = $mime;
	}

	return $mimes;
} );


/**
 * Add AI, PSD for uploadable mimes.
 */
add_filter( 'upload_mimes', function ( $mimes ) {
	foreach ( upladobe_mimes() as $ext => $mime ) {
		if ( ! isset( $mimes[ $ext ] ) ) {
			$mimes[ $ext ] = $mime;
		}
	}

	return $mimes;
} );

/**
 * If attachment is added, try to generate thumbnail.
 */
add_filter( 'wp_generate_attachment_metadata', function ( $metadata, $attachment_id ) {
	$should_generate = false !== array_search( get_post_mime_type( $attachment_id ), upladobe_mimes() );
	/**
	 * upladobe_should_generate
	 *
	 * @param bool $should_generate
	 */
	$should_generate = apply_filters( 'upladobe_should_generate', $should_generate, $attachment_id );
	if ( $should_generate ) {
		upladobe_generate( $attachment_id );
	}

	return $metadata;
}, 10, 2 );

/**
 * If attachment is deleted, delete thumbnail
 */
add_action( 'deleted_post', function ( $post_id ) {
	if ( ! $post_id ) {
		return;
	}

	$attachments = get_posts( array(
		'post_type'   => 'attachment',
		'post_status' => 'inherit',
		'post_parent' => (int) $post_id,
		'meta_query'  => array(
			array(
				'key'   => UPLADOBE_KEY,
				'value' => $post_id
			),
		),
	) );

	foreach ( $attachments as $attachment ) {
		wp_delete_post( $attachment->ID, true );
	}
} );

/**
 * If thumbnail has thumbnail, show it
 */
add_filter( 'wp_get_attachment_image_src', function( $image, $attachment_id, $size, $icon ) {
	if ( $thumb_id = get_post_thumbnail_id( $attachment_id ) ) {
		$image = wp_get_attachment_image_src( $thumb_id, $size, $icon );
	}
	return $image;
}, 10, 4 );
