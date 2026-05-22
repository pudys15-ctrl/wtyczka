<?php
if (!defined('ABSPATH')) exit;

function lokivo_handle_featured_image_upload($ad_id, $file_array_key = 'featured_image') {

    if (empty($_FILES[$file_array_key]['name'])) {
        return;
    }

    $file = $_FILES[$file_array_key];

    $upload = wp_handle_upload($file, ['test_form' => false]);

    if (!isset($upload['file'])) {
        return;
    }

    $attachment_id = wp_insert_attachment([
        'post_title'     => $file['name'],
        'post_mime_type' => $upload['type'],
        'post_status'    => 'inherit'
    ], $upload['file'], $ad_id);

    require_once ABSPATH . 'wp-admin/includes/image.php';

    wp_update_attachment_metadata(
        $attachment_id,
        wp_generate_attachment_metadata($attachment_id, $upload['file'])
    );

    set_post_thumbnail($ad_id, $attachment_id);
}
