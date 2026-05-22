<?php
if (!defined('ABSPATH')) exit;

/**
 * AJAX: zwraca pola dla danej kategorii (formularz dodawania/edycji)
 */
add_action('wp_ajax_lokivo_get_fields', 'lokivo_ajax_get_fields');
add_action('wp_ajax_nopriv_lokivo_get_fields', 'lokivo_ajax_get_fields');

function lokivo_ajax_get_fields() {

    $term_id = isset($_POST['term']) ? (int) $_POST['term'] : 0;
    $post_id = isset($_POST['ad_id']) ? (int) $_POST['ad_id'] : null;

    if (!$term_id) {
        echo '';
        wp_die();
    }

    ob_start();

    // używamy tego samego renderera co w formularzu
    lokivo_render_category_fields($term_id, 'form', $post_id);

    echo ob_get_clean();
    wp_die();
}

/*
|--------------------------------------------------------------------------
|  AJAX: pobieranie podkategorii
|--------------------------------------------------------------------------
*/
add_action('wp_ajax_lokivo_get_subcategories', 'lokivo_ajax_get_subcategories');
add_action('wp_ajax_nopriv_lokivo_get_subcategories', 'lokivo_ajax_get_subcategories');

function lokivo_ajax_get_subcategories() {

    $parent = isset($_POST['parent']) ? (int) $_POST['parent'] : 0;

    if (!$parent) {
        echo "<option value=''>Brak podkategorii</option>";
        wp_die();
    }

    $children = get_terms([
        'taxonomy' => 'ogloszenia_kategoria',
        'hide_empty' => false,
        'parent' => $parent
    ]);

    if (!$children) {
        echo "<option value=''>Brak podkategorii</option>";
        wp_die();
    }

    echo "<option value=''>Wybierz podkategorię</option>";

    foreach ($children as $child) {
        echo "<option value='{$child->term_id}'>".esc_html($child->name)."</option>";
    }

    wp_die();
}

/**
 * AJAX: filtrowanie listy ogłoszeń
 */
add_action('wp_ajax_lokivo_filter', 'lokivo_ajax_filter');
add_action('wp_ajax_nopriv_lokivo_filter', 'lokivo_ajax_filter');

function lokivo_ajax_filter() {

    $term_id = isset($_POST['term']) ? (int) $_POST['term'] : 0;
    $page    = isset($_POST['page']) ? (int) $_POST['page'] : 1;
    $filters = isset($_POST['filters']) && is_array($_POST['filters']) ? $_POST['filters'] : [];
    $sort    = isset($_POST['sort']) ? $_POST['sort'] : '';

    $args = [
        'post_type'      => 'ogloszenie',
        'posts_per_page' => 12,
        'paged'          => $page,
        'tax_query'      => [
            [
                'taxonomy' => 'ogloszenia_kategoria',
                'field'    => 'term_id',
                'terms'    => $term_id,
            ]
        ]
    ];

    $meta_query = lokivo_build_meta_query_from_array($filters);

    if (count($meta_query) > 1) {
        $args['meta_query'] = $meta_query;
    }

    if (!empty($sort)) {
        switch ($sort) {
            case 'date_asc':
                $args['orderby'] = 'date';
                $args['order']   = 'ASC';
                break;

            case 'date_desc':
                $args['orderby'] = 'date';
                $args['order']   = 'DESC';
                break;

            case 'price_asc':
                $args['meta_key'] = 'cena_od';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'ASC';
                break;

            case 'price_desc':
                $args['meta_key'] = 'cena_od';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;
        }
    }

    $q = new WP_Query($args);

    ob_start();

    if ($q->have_posts()) :
        while ($q->have_posts()) : $q->the_post(); ?>

            <article class="lokivo-card">
                <a href="<?php the_permalink(); ?>" class="lokivo-card-thumb">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('medium'); ?>
                    <?php else : ?>
                        <div class="lokivo-card-placeholder">Brak zdjęcia</div>
                    <?php endif; ?>
                </a>

                <div class="lokivo-card-body">
                    <a href="<?php the_permalink(); ?>">
                        <h2 class="lokivo-card-title"><?php the_title(); ?></h2>
                    </a>

                    <div class="lokivo-card-meta">
                        <span class="lokivo-card-date">
                            <?php echo get_the_date(); ?>
                        </span>
                    </div>

                    <div class="lokivo-card-fields">
                        <?php
                        if (defined('LOKIVO_OGLOSZENIA_PATH')) {
                            include LOKIVO_OGLOSZENIA_PATH . 'templates/parts/dynamic-fields.php';
                        }
                        ?>
                    </div>
                </div>
            </article>

        <?php endwhile;
    else :
        echo '<p>Brak ogłoszeń spełniających kryteria.</p>';
    endif;

    $html = ob_get_clean();
    wp_reset_postdata();

    wp_send_json([
        'html' => $html,
        'max'  => (int) $q->max_num_pages,
    ]);
}
