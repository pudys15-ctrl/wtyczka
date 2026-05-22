<?php
if (!defined('ABSPATH')) exit;

/**
 * Renderuje pola filtrów lub formularza dodawania/edycji
 * $context = 'form' lub 'filters'
 */
function lokivo_render_category_fields($term_id, $context = 'form', $values = []) {

    $fields = lokivo_get_category_filters_full($term_id);
    if (!$fields) return;

    foreach ($fields as $field) {

        $id    = esc_attr($field['id']);
        $label = esc_html($field['label']);
        $type  = $field['type'];

        // Pobieranie wartości
        if ($context === 'filters') {
            $value = $_GET[$id] ?? '';
        } else {
            $value = get_post_meta(get_the_ID(), $id, true);
        }

        echo "<div class='lokivo-field lokivo-field-{$id}'>";
        echo "<label><span>{$label}</span>";

        /*
        |--------------------------------------------------------------------------
        | SELECT
        |--------------------------------------------------------------------------
        */
        if ($type === 'select') {
            echo "<select name='{$id}'>";
            echo "<option value=''>Wybierz...</option>";

            foreach ($field['options'] as $k => $v) {
                $selected = ($value == $k) ? 'selected' : '';
                echo "<option value='".esc_attr($k)."' {$selected}>".esc_html($v)."</option>";
            }

            echo "</select>";
        }

        /*
        |--------------------------------------------------------------------------
        | NUMBER
        |--------------------------------------------------------------------------
        */
        elseif ($type === 'number') {
            echo "<input type='number' name='{$id}' value='".esc_attr($value)."'>";
        }

        /*
        |--------------------------------------------------------------------------
        | TEXT
        |--------------------------------------------------------------------------
        */
        elseif ($type === 'text') {
            echo "<input type='text' name='{$id}' value='".esc_attr($value)."'>";
        }

        /*
        |--------------------------------------------------------------------------
        | CHECKBOXES (WIELOKROTNY WYBÓR)
        |--------------------------------------------------------------------------
        */
        elseif ($type === 'checkboxes') {

            // Wartość musi być tablicą
            $current_values = [];
            if ($context === 'filters') {
                $current_values = isset($_GET[$id]) ? (array) $_GET[$id] : [];
            } else {
                $current_values = is_array($value) ? $value : [];
            }

            echo "<div class='lokivo-checkbox-group'>";

            foreach ($field['options'] as $k => $v) {

                $checked = in_array($k, $current_values) ? 'checked' : '';

                echo "
                <label class='lokivo-checkbox'>
                    <input type='checkbox' name='{$id}[]' value='".esc_attr($k)."' {$checked}>
                    ".esc_html($v)."
                </label>";
            }

            echo "</div>";
        }

        echo "</label>";
        echo "</div>";
    }
}

/*
|--------------------------------------------------------------------------
|  Pobiera pola kategorii + pól kategorii nadrzędnej
|--------------------------------------------------------------------------
*/
function lokivo_get_category_fields_full($term_id) {

    $fields = [];

    // pobierz pola kategorii
    $fields_current = get_term_meta($term_id, 'lokivo_fields', true);
    if (is_array($fields_current)) {
        $fields = array_merge($fields, $fields_current);
    }

    // sprawdź, czy ma rodzica
    $term = get_term($term_id, 'ogloszenia_kategoria');

    if ($term && $term->parent) {

        $fields_parent = get_term_meta($term->parent, 'lokivo_fields', true);

        if (is_array($fields_parent)) {
            // pola nadrzędne mają być PIERWSZE
            $fields = array_merge($fields_parent, $fields);
        }
    }

    return $fields;
}
