<?php
if (!defined('ABSPATH')) exit;

function lokivo_handle_gallery_upload($post_id) {

    $gallery = get_post_meta($post_id, '_gallery', true);
    $gallery = is_array($gallery) ? $gallery : [];

    $order_raw = isset($_POST['ad_gallery']) ? explode(',', $_POST['ad_gallery']) : [];
    $deleted = isset($_POST['gallery_deleted']) ? explode(',', $_POST['gallery_deleted']) : [];

    foreach ($deleted as $del) {
        if (is_numeric($del)) {
            wp_delete_attachment((int)$del, true);
            $gallery = array_diff($gallery, [(int)$del]);
        }
    }

    $final_gallery = [];

    foreach ($order_raw as $id) {

        if (strpos($id, 'new-') === 0) {

            $index = (int) str_replace('new-', '', $id);

            if (!empty($_FILES['images']['name'][$index])) {

                $file = [
                    'name'     => $_FILES['images']['name'][$index],
                    'type'     => $_FILES['images']['type'][$index],
                    'tmp_name' => $_FILES['images']['tmp_name'][$index],
                    'error'    => $_FILES['images']['error'][$index],
                    'size'     => $_FILES['images']['size'][$index],
                ];

                $upload = wp_handle_upload($file, ['test_form' => false]);

                if (isset($upload['file'])) {

                    $attachment_id = wp_insert_attachment([
                        'post_title'     => $file['name'],
                        'post_mime_type' => $upload['type'],
                        'post_status'    => 'inherit'
                    ], $upload['file'], $post_id);

                    require_once ABSPATH . 'wp-admin/includes/image.php';
                    wp_update_attachment_metadata(
                        $attachment_id,
                        wp_generate_attachment_metadata($attachment_id, $upload['file'])
                    );

                    $final_gallery[] = $attachment_id;
                }
            }

        } else {
            if (is_numeric($id)) {
                $final_gallery[] = (int)$id;
            }
        }
    }

    $final_gallery = array_values(array_unique($final_gallery));

    update_post_meta($post_id, '_gallery', $final_gallery);

    if (!empty($final_gallery)) {
        set_post_thumbnail($post_id, $final_gallery[0]);
    }
}
