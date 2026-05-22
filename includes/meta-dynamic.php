<?php

if (!defined('ABSPATH')) exit;

/**
 * Definicja pól dla kategorii
 */
function lokivo_get_category_fields() {

    return [

        // Kategoria: Praca
        'praca' => [
            [
                'id' => 'wynagrodzenie_min',
                'label' => 'Wynagrodzenie od',
                'type' => 'number',
                'filter' => true
            ],
            [
                'id' => 'wynagrodzenie_max',
                'label' => 'Wynagrodzenie do',
                'type' => 'number',
                'filter' => true
            ],
            [
                'id' => 'forma_zatrudnienia',
                'label' => 'Forma zatrudnienia',
                'type' => 'checkboxes',
                'options' => [
                    'umowa_o_prace' => 'Umowa o pracę',
                    'zlecenie'      => 'Umowa zlecenie',
                    'b2b'           => 'B2B',
                    'dzielo'        => 'Umowa o dzieło',
                ],
                'filter' => true
            ],
            [
                'id' => 'wymiar_etatu',
                'label' => 'Wymiar etatu',
                'type' => 'select',
                'options' => [
                    'pelny' => 'Pełny etat',
                    'pol'   => 'Pół etatu',
                    '1_4'   => '1/4 etatu',
                ],
                'filter' => true
            ],
        ],

        // Kategoria: Motoryzacja
        'motoryzacja' => [
            [
                'id'    => '_cena',
                'label' => 'Cena (zł)',
                'type'  => 'number'
            ],
            [
                'id'    => '_przebieg',
                'label' => 'Przebieg (km)',
                'type'  => 'number'
            ],
            [
                'id'    => '_rok',
                'label' => 'Rok produkcji',
                'type'  => 'number'
            ],
        ],

        // Kategoria: Nieruchomości
        'nieruchomosci' => [
            [
                'id'    => '_cena',
                'label' => 'Cena (zł)',
                'type'  => 'number'
            ],
            [
                'id'    => '_metraz',
                'label' => 'Metraż (m²)',
                'type'  => 'number'
            ],
            [
                'id'    => '_pokoje',
                'label' => 'Liczba pokoi',
                'type'  => 'number'
            ],
        ],
    ];
}

/**
 * DZIEDZICZENIE PÓL — kluczowa funkcja
 */
function lokivo_get_category_fields_for_term($term_id) {

    $fields_map = lokivo_get_category_fields();
    $fields     = [];

    $term = get_term($term_id, 'ogloszenia_kategoria');

    if (!$term || is_wp_error($term)) {
        return [];
    }

    // Pola kategorii bieżącej
    if (isset($fields_map[$term->slug])) {
        $fields = array_merge($fields, $fields_map[$term->slug]);
    }

    // Pola kategorii nadrzędnych
    $parent_id = $term->parent;

    while ($parent_id) {
        $parent = get_term($parent_id, 'ogloszenia_kategoria');

        if ($parent && !is_wp_error($parent)) {
            if (isset($fields_map[$parent->slug])) {
                $fields = array_merge($fields, $fields_map[$parent->slug]);
            }
            $parent_id = $parent->parent;
        } else {
            break;
        }
    }

    return $fields;
}


function lokivo_get_sorting_options_for_category($slug) {

    $sorting = [

        'praca' => [
            'date_desc' => 'Najnowsze',
            'date_asc'  => 'Najstarsze',
        ],

        'motoryzacja' => [
            'date_desc'  => 'Najnowsze',
            'price_asc'  => 'Najtańsze',
            'price_desc' => 'Najdroższe',
            'mileage_asc' => 'Najmniejszy przebieg',
            'mileage_desc' => 'Największy przebieg',
        ],

        'nieruchomosci' => [
            'date_desc'  => 'Najnowsze',
            'price_asc'  => 'Najtańsze',
            'price_desc' => 'Najdroższe',
            'area_desc'  => 'Największy metraż',
            'area_asc'   => 'Najmniejszy metraż',
        ],
    ];

    return $sorting[$slug] ?? [
        'date_desc' => 'Najnowsze',
        'date_asc'  => 'Najstarsze',
    ];
}

/**
 * META BOX — wyświetlanie pól w panelu WP (z dziedziczeniem)
 */
function lokivo_dynamic_meta_box($post) {

    $terms = wp_get_post_terms($post->ID, 'ogloszenia_kategoria');

    if (empty($terms)) {
        echo "Wybierz kategorię, aby zobaczyć pola.";
        return;
    }

    // Bierzemy pierwszą kategorię (jak OLX)
    $term = $terms[0];

    // Pobieramy pola z dziedziczeniem
    $fields = lokivo_get_category_fields_for_term($term->term_id);

    if (empty($fields)) {
        echo "Brak pól dla tej kategorii.";
        return;
    }

    foreach ($fields as $field) {

        $value = get_post_meta($post->ID, $field['id'], true);

        echo "<p><label><strong>{$field['label']}</strong></label><br>";

        if ($field['type'] === 'number') {
            echo "<input type='number' name='{$field['id']}' value='".esc_attr($value)."' style='width:100%;'>";
        }

        if ($field['type'] === 'select') {
            echo "<select name='{$field['id']}' style='width:100%;'>";
            echo "<option value=''>Wybierz...</option>";
            foreach ($field['options'] as $key => $label) {
                echo "<option value='$key' ".selected($value, $key, false).">$label</option>";
            }
            echo "</select>";
        }

        if ($field['type'] === 'checkboxes') {

            $saved_values = get_post_meta($post->ID, $field['id'], true);

            if (!is_array($saved_values)) {
                $saved_values = [];
            }

            foreach ($field['options'] as $key => $label) {
                $checked = in_array($key, $saved_values) ? 'checked' : '';
                echo "<label style='display:block; margin-bottom:4px;'>
                        <input type='checkbox' name='{$field['id']}[]' value='{$key}' {$checked}>
                        {$label}
                      </label>";
            }
        }

        echo "</p>";
    }
}

/**
 * Rejestracja meta boxa
 */
function lokivo_add_dynamic_meta_box() {
    add_meta_box(
        'lokivo_dynamic_fields',
        'Dane kategorii',
        'lokivo_dynamic_meta_box',
        'ogloszenie',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'lokivo_add_dynamic_meta_box');

/**
 * Zapisywanie dynamicznych pól
 */
function lokivo_save_dynamic_fields($post_id) {

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    $fields_map = lokivo_get_category_fields();

    foreach ($fields_map as $group) {
        foreach ($group as $field) {

            if ($field['type'] === 'checkboxes') {

                if (isset($_POST[$field['id']])) {
                    $clean = array_map('sanitize_text_field', (array) $_POST[$field['id']]);
                    update_post_meta($post_id, $field['id'], $clean);
                } else {
                    delete_post_meta($post_id, $field['id']);
                }

            } else {

                if (isset($_POST[$field['id']])) {
                    update_post_meta($post_id, $field['id'], sanitize_text_field($_POST[$field['id']]));
                }
            }
        }
    }
}
add_action('save_post', 'lokivo_save_dynamic_fields');
