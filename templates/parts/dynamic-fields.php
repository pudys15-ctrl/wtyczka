<?php
if (!defined('ABSPATH')) exit;

/**
 * Wyświetlanie dynamicznych pól w karcie ogłoszenia (frontend)
 */

$post_id = get_the_ID();

// Pobierz kategorię główną ogłoszenia
$terms = wp_get_post_terms($post_id, 'ogloszenia_kategoria');

if (empty($terms)) {
    return;
}

// Bierzemy pierwszą kategorię (jak OLX)
$term = $terms[0];

// Pobieramy pola z dziedziczeniem
if (!function_exists('lokivo_get_category_fields_for_term')) {
    return;
}

$fields = lokivo_get_category_fields_for_term($term->term_id);

if (empty($fields)) {
    return;
}

echo '<div class="lokivo-dynamic-fields">';

foreach ($fields as $field) {

    $value = get_post_meta($post_id, $field['id'], true);

    // Pomijamy puste pola
    if ($value === '' || $value === [] || $value === null) {
        continue;
    }

    echo '<div class="lokivo-field-row">';
    echo '<span class="lokivo-field-label">' . esc_html($field['label']) . ':</span>';

    // Typ: number / select / text
    if ($field['type'] !== 'checkboxes') {
        echo '<span class="lokivo-field-value">' . esc_html($value) . '</span>';
    }

    // Typ: checkboxes (lista wartości)
    if ($field['type'] === 'checkboxes') {

        if (!is_array($value)) {
            $value = [$value];
        }

        $labels = [];

        foreach ($value as $v) {
            if (isset($field['options'][$v])) {
                $labels[] = $field['options'][$v];
            }
        }

        if (!empty($labels)) {
            echo '<span class="lokivo-field-value">' . esc_html(implode(', ', $labels)) . '</span>';
        }
    }

    echo '</div>';
}

echo '</div>';
