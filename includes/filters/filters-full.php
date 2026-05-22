<?php
if (!defined('ABSPATH')) exit;

/**
 * Pełna lista filtrów dla kategorii
 * Jedno źródło prawdy dla:
 * - formularza dodawania/edycji
 * - filtrów w wyszukiwarce
 * - logiki WP_Query
 */
function lokivo_get_category_filters_full($term_id) {

    $term = get_term($term_id, 'ogloszenia_kategoria');
    if (!$term) return [];

    // pełna lista pól dla kategorii głównych
    $filters = [

        'praca' => [
            [
                'id' => 'rodzaj_umowy',
                'label' => 'Rodzaj umowy',
                'type' => 'checkboxes',
                'options' => [
                    'umowa_o_prace' => 'Umowa o pracę',
                    'zlecenie'      => 'Umowa zlecenie',
                    'dzielo'        => 'Umowa o dzieło',
                    'b2b'           => 'B2B',
                ]
            ],
            [
                'id' => 'wymiar_etatu',
                'label' => 'Wymiar etatu',
                'type' => 'checkboxes',
                'options' => [
                    'pelny' => 'Pełny etat',
                    'pol'   => '1/2 etatu',
                    '1_4'   => '1/4 etatu',
                ]
            ],
            [
                'id' => 'doswiadczenie',
                'label' => 'Doświadczenie',
                'type' => 'select',
                'options' => [
                    'bez'    => 'Bez doświadczenia',
                    'junior' => 'Junior',
                    'mid'    => 'Mid',
                    'senior' => 'Senior',
                ]
            ],
            [
                'id' => 'tryb_pracy',
                'label' => 'Tryb pracy',
                'type' => 'select',
                'options' => [
                    'stacjonarna' => 'Stacjonarna',
                    'hybrydowa'   => 'Hybrydowa',
                    'zdalna'      => 'Zdalna',
                ]
            ],
            [
                'id' => 'wynagrodzenie_od',
                'label' => 'Wynagrodzenie od',
                'type' => 'number'
            ],
            [
                'id' => 'wynagrodzenie_do',
                'label' => 'Wynagrodzenie do',
                'type' => 'number'
            ],
        ],

        'motoryzacja' => [
            [
                'id' => 'marka',
                'label' => 'Marka',
                'type' => 'select',
                'options' => [
                    'audi'       => 'Audi',
                    'bmw'        => 'BMW',
                    'mercedes'   => 'Mercedes',
                    'volkswagen' => 'Volkswagen',
                ]
            ],
            [
                'id' => 'model',
                'label' => 'Model',
                'type' => 'text'
            ],
            [
                'id' => 'rok_produkcji',
                'label' => 'Rok produkcji',
                'type' => 'number'
            ],
            [
                'id' => 'przebieg',
                'label' => 'Przebieg (km)',
                'type' => 'number'
            ],
            [
                'id' => 'paliwo',
                'label' => 'Paliwo',
                'type' => 'select',
                'options' => [
                    'benzyna'     => 'Benzyna',
                    'diesel'      => 'Diesel',
                    'hybryda'     => 'Hybryda',
                    'elektryczny' => 'Elektryczny',
                ]
            ],
            [
                'id' => 'skrzynia',
                'label' => 'Skrzynia biegów',
                'type' => 'select',
                'options' => [
                    'manual'  => 'Manualna',
                    'automat' => 'Automatyczna',
                ]
            ],
            [
                'id' => 'cena_od',
                'label' => 'Cena od',
                'type' => 'number'
            ],
            [
                'id' => 'cena_do',
                'label' => 'Cena do',
                'type' => 'number'
            ],
        ],

        'nieruchomosci' => [
            [
                'id' => 'rodzaj_nieruchomosci',
                'label' => 'Rodzaj nieruchomości',
                'type' => 'select',
                'options' => [
                    'mieszkanie' => 'Mieszkanie',
                    'dom'        => 'Dom',
                    'dzialka'    => 'Działka',
                    'lokal'      => 'Lokal użytkowy',
                ]
            ],
            [
                'id' => 'powierzchnia',
                'label' => 'Powierzchnia (m²)',
                'type' => 'number'
            ],
            [
                'id' => 'pokoje',
                'label' => 'Liczba pokoi',
                'type' => 'select',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4+',
                ]
            ],
            [
                'id' => 'pietro',
                'label' => 'Piętro',
                'type' => 'select',
                'options' => [
                    'parter' => 'Parter',
                    '1'      => '1',
                    '2'      => '2',
                    '3'      => '3+',
                ]
            ],
            [
                'id' => 'cena_od',
                'label' => 'Cena od',
                'type' => 'number'
            ],
            [
                'id' => 'cena_do',
                'label' => 'Cena do',
                'type' => 'number'
            ],
        ],
    ];

    // jeśli to kategoria główna → zwróć jej pola
    if ($term->parent == 0) {
        return $filters[$term->slug] ?? [];
    }

    // jeśli to podkategoria → pobierz pola kategorii nadrzędnej
    $parent = get_term($term->parent, 'ogloszenia_kategoria');

    return $filters[$parent->slug] ?? [];
}



