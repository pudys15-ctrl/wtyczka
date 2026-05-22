<?php
if (!defined('ABSPATH')) exit;

/*
|--------------------------------------------------------------------------
|  Budowanie meta_query na podstawie filtrów
|--------------------------------------------------------------------------
|  Obsługuje:
|  - checkboxy (OR)
|  - selecty ( = )
|  - number (>=, <=)
|  - pola typu cena_od, cena_do, wynagrodzenie_od, wynagrodzenie_do
|--------------------------------------------------------------------------
*/

function lokivo_build_meta_query_from_array($source) {

    $meta_query = ['relation' => 'AND'];

    foreach ($source as $key => $value) {

        if ($value === '' || $value === null || $value === []) {
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        |  Checkboxy (tablice) → OR
        |--------------------------------------------------------------------------
        */
        if (is_array($value)) {

            $sub = ['relation' => 'OR'];

            foreach ($value as $v) {
                $sub[] = [
                    'key'     => $key,
                    'value'   => $v,       // ⭐ poprawione — bez cudzysłowów
                    'compare' => 'LIKE',
                ];
            }

            $meta_query[] = $sub;
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        |  Pola zakresowe (cena, wynagrodzenie)
        |--------------------------------------------------------------------------
        */
        if (str_ends_with($key, '_od')) {
            $meta_query[] = [
                'key'     => $key,
                'value'   => (int) $value,
                'compare' => '>=',
                'type'    => 'NUMERIC',
            ];
            continue;
        }

        if (str_ends_with($key, '_do')) {
            $meta_query[] = [
                'key'     => $key,
                'value'   => (int) $value,
                'compare' => '<=',
                'type'    => 'NUMERIC',
            ];
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        |  Zwykłe pola (select, text)
        |--------------------------------------------------------------------------
        */
        $meta_query[] = [
            'key'     => $key,
            'value'   => sanitize_text_field($value),
            'compare' => '=',
        ];
    }

    return $meta_query;
}

/*
|--------------------------------------------------------------------------
|  Filtrowanie głównej pętli (widok kategorii)
|--------------------------------------------------------------------------
*/

add_action('pre_get_posts', function($q) {

    if (!is_tax('ogloszenia_kategoria') || !$q->is_main_query() || is_admin()) {
        return;
    }

    $source = $_GET;

    // usuń parametry techniczne
    unset($source['sort'], $source['paged']);

    $meta_query = lokivo_build_meta_query_from_array($source);

    if (count($meta_query) > 1) {
        $q->set('meta_query', $meta_query);
    }

    /*
    |--------------------------------------------------------------------------
    |  Sortowanie
    |--------------------------------------------------------------------------
    */
    if (isset($_GET['sort'])) {

        switch ($_GET['sort']) {

            case 'date_asc':
                $q->set('orderby', 'date');
                $q->set('order', 'ASC');
                break;

            case 'date_desc':
                $q->set('orderby', 'date');
                $q->set('order', 'DESC');
                break;

            case 'price_asc':
                $q->set('meta_key', 'cena_od');
                $q->set('orderby', 'meta_value_num');
                $q->set('order', 'ASC');
                break;

            case 'price_desc':
                $q->set('meta_key', 'cena_od');
                $q->set('orderby', 'meta_value_num');
                $q->set('order', 'DESC');
                break;
        }
    }
});
