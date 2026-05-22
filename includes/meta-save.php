<?php
if (!defined('ABSPATH')) exit;

/**
 * Zapis meta dla ogłoszenia (dodawanie + edycja)
 * Używa tej samej listy pól co filtry
 */
function lokivo_save_ad_meta($ad_id, $data) {

    if (!$ad_id) return;

    // znajdź kategorię ogłoszenia
    $terms = wp_get_post_terms($ad_id, 'ogloszenia_kategoria');
    if (empty($terms) || is_wp_error($terms)) return;

    $term_id = $terms[0]->term_id;

    $fields = lokivo_get_category_filters_full($term_id);
    if (!$fields) return;

    foreach ($fields as $field) {

        $id = $field['id'];

        if (!isset($data[$id])) {
            delete_post_meta($ad_id, $id);
            continue;
        }

        $value = $data[$id];

        if (is_array($value)) {
            $value = array_map('sanitize_text_field', $value);
        } else {
            $value = sanitize_text_field($value);
        }

        update_post_meta($ad_id, $id, $value);
    }
}
add_action('add_meta_boxes', function() {
    add_meta_box(
        'lokivo_dynamic_fields',
        'Pola ogłoszenia',
        'lokivo_admin_render_fields',
        'ogloszenie',
        'normal',
        'default'
    );
});
function lokivo_admin_render_fields($post) {

    // Pobierz kategorię ogłoszenia
    $terms = wp_get_post_terms($post->ID, 'ogloszenia_kategoria');
    if (empty($terms)) {
        echo "<p><em>Najpierw wybierz kategorię ogłoszenia i zapisz.</em></p>";
        return;
    }

    $term_id = $terms[0]->term_id;

    echo "<div class='lokivo-admin-fields'>";

    // Render pól (używamy tej samej funkcji co na froncie)
    lokivo_render_category_fields($term_id, 'form');

    echo "</div>";
}
add_action('save_post_ogloszenie', function($post_id) {

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    lokivo_save_ad_meta($post_id, $_POST);
});
