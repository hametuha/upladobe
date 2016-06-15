<?php
/**
 * Functions
 *
 * @package upladboe
 */

function upladobe_available(){
	return extension_loaded( 'imagick' );
}

/**
 * Upload mimes
 *
 * @return array
 */
function upladobe_mimes(){
	/**
	 * upladobe_mimes
	 *
	 * Mime types to generage thumbnails
	 *
	 * @param array $mimes key is extension, values is mime type.
	 * @return array
	 */
	return (array) apply_filters( 'upladobe_mimes', array(
		'psd' => 'image/x-photoshop',
		'ai'  => 'image/x-illustrator',
		'pdf' => 'application/pdf',
	) );
}

/**
 * Delete thumbnail if exists
 *
 * @param int $attachment_id
 */
function upladobe_regenerate( $attachment_id) {
	$thumbnail_id = get_post_thumbnail_id($attachment_id);
	if ( $thumbnail_id && get_post_meta( $thumbnail_id, UPLADOBE_KEY, true ) ) {
		wp_delete_attachment( $thumbnail_id, true );
	}
	upladobe_generate( $attachment_id );
}

/**
 * Generate thumbnail
 *
 * @param int $attachment_id
 */
function upladobe_generate( $attachment_id ) {
	$file_name = get_attached_file($attachment_id);
	$basename = str_replace('.', '-', basename( $file_name ) ) . '-image.jpg';
	$uploaded = wp_upload_bits( $basename, null, upladode_get_blob( $file_name ) );
	if ( $uploaded['error'] === false ) {
		upladobe_insert( $attachment_id, $uploaded );
	}
}

/**
 * Get upload image bits
 *
 * @param string $filename
 *
 * @return string
 */
function upladode_get_blob($filename) {
	/**
	 * upladobe_blog
	 *
	 * If you wanna customize thumbnail, Use this hook.
	 *
	 * @param string $blob
	 * @param string $filename
	 * @return string
	 */
	$blob = apply_filters('upladobe_blog', null, $filename);
	if ($blob) {
		return $blob;
	}
	$imagick = new Imagick( $filename );
	$imagick->setIteratorIndex(0);
	$imagick->setImageFormat('jpg');
	return $imagick->getImageBlob();
}

/**
 * Set meta data for attachment
 *
 * @param int $attachment_id
 * @param array $uploaded
 */
function upladobe_insert($attachment_id, $uploaded) {
	$attachment = get_post( $attachment_id );
	if ( ! $attachment ) {
		return;
	}
	// Create thumbnail
	$attachment_arr = array(
		'post_mime_type' => 'image/jpeg',
		'post_type' => 'attachment',
		'post_content' => '',
		'post_title' => $attachment->post_title . '-thumbnail',
		'post_parent' => $attachment->ID
	);
	$thumbnail_id = wp_insert_attachment($attachment_arr, $uploaded['file']);
	$thumbnail_metadata = wp_generate_attachment_metadata($thumbnail_id, $uploaded['file']);
	wp_update_attachment_metadata($thumbnail_id, $thumbnail_metadata);
	set_post_thumbnail($attachment_id, $thumbnail_id);

	// Mark generated thumbnails for future reference
	update_post_meta( $thumbnail_id, UPLADOBE_KEY, $attachment_id );
}
