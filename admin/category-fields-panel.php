<?php
if (!defined('ABSPATH')) exit;

/*
|--------------------------------------------------------------------------
|  Dodanie strony w panelu admina
|--------------------------------------------------------------------------
*/
add_action('admin_menu', function() {
    add_menu_page(
        'Pola kategorii ogłoszeń',
        'Pola kategorii',
        'manage_options',
        'lokivo-category-fields',
        'lokivo_admin_category_fields_page',
        'dashicons-list-view',
        58
    );
});

/*
|--------------------------------------------------------------------------
|  Główna strona panelu
|--------------------------------------------------------------------------
*/
function lokivo_admin_category_fields_page() {

    // ZAPIS
    if (!empty($_POST['lokivo_save_fields'])) {

        if (!wp_verify_nonce($_POST['lokivo_fields_nonce'], 'lokivo_save_fields')) {
            echo '<div class="error"><p>Błąd bezpieczeństwa — nie zapisano.</p></div>';
        } else {

            foreach ($_POST['fields'] as $term_id => $field_ids) {

                $field_ids = array_map('sanitize_text_field', $field_ids);

                update_term_meta($term_id, 'lokivo_fields', $field_ids);
            }

            echo '<div class="updated"><p>Zapisano ustawienia.</p></div>';
        }
    }

    // Pobierz kategorie główne
    $terms = get_terms([
        'taxonomy' => 'ogloszenia_kategoria',
        'hide_empty' => false,
        'parent' => 0
    ]);
    ?>

    <div class="wrap">
        <h1>Pola kategorii ogłoszeń</h1>

        <form method="post">
            <?php wp_nonce_field('lokivo_save_fields', 'lokivo_fields_nonce'); ?>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Kategoria</th>
                        <th>Pola</th>
                    </tr>
                </thead>
                <tbody>

                <?php foreach ($terms as $term): ?>
                    <?php lokivo_admin_render_term_row($term, 0); ?>
                <?php endforeach; ?>

                </tbody>
            </table>

            <p><button class="button button-primary" name="lokivo_save_fields">Zapisz</button></p>
        </form>
    </div>

<?php }

/*
|--------------------------------------------------------------------------
|  Render kategorii + podkategorii
|--------------------------------------------------------------------------
*/
function lokivo_admin_render_term_row($term, $level = 0) {

    $saved = get_term_meta($term->term_id, 'lokivo_fields', true);
    $saved = is_array($saved) ? $saved : [];

    // pobierz pola z funkcji głównej
    $fields = lokivo_get_category_filters_full($term->term_id);

    $indent = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $level);

    echo "<tr>";
    echo "<td><strong>{$indent}{$term->name}</strong></td>";
    echo "<td>";

    foreach ($fields as $field) {

        $field_id = $field['id'];
        $label    = $field['label'];

        $checked = in_array($field_id, $saved) ? 'checked' : '';

        echo "
        <label style='margin-right:20px; display:inline-block;'>
            <input type='checkbox' name='fields[{$term->term_id}][]' value='{$field_id}' {$checked}>
            {$label}
        </label>";
    }

    echo "</td>";
    echo "</tr>";

    // Podkategorie
    $children = get_terms([
        'taxonomy' => 'ogloszenia_kategoria',
        'hide_empty' => false,
        'parent' => $term->term_id
    ]);

    foreach ($children as $child) {
        lokivo_admin_render_term_row($child, $level + 1);
    }
}
