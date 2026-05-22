<?php

if ( ! defined( 'ABSPATH' ) ) exit;

function lokivo_register_taxonomies() {

    // Główne kategorie (np. Motoryzacja, Praca, Nieruchomości)
    register_taxonomy(
        'ogloszenia_kategoria',
        'ogloszenie',
        [
            'label' => 'Kategorie ogłoszeń',
            'hierarchical' => true,
            'rewrite' => ['slug' => 'kategoria'],
            'show_admin_column' => true,
        ]
    );

    // Lokalizacja (np. Lublin, Warszawa)
    register_taxonomy(
        'ogloszenia_lokalizacja',
        'ogloszenie',
        [
            'label' => 'Lokalizacja',
            'hierarchical' => true,
            'rewrite' => ['slug' => 'lokalizacja'],
            'show_admin_column' => true,
        ]
    );
}

add_action( 'init', 'lokivo_register_taxonomies' );

add_action('init', 'lokivo_insert_default_categories');

function lokivo_insert_default_categories() {

    $categories = [
        'motoryzacja' => [
            'Samochody',
            'Motocykle',
            'Części samochodowe',
            'Opony i felgi',
            'Dostawcze i ciężarowe'
        ],
        'nieruchomosci' => [
            'Mieszkania',
            'Domy',
            'Działki',
            'Lokale użytkowe',
            'Garaże'
        ],
        'praca' => [
            'Budowa / Remonty',
            'IT / Telekomunikacja',
            'Handel / Sprzedaż',
            'Produkcja',
            'Transport / Logistyka',
            'Gastronomia',
            'Praca dodatkowa'
        ],
        'dom_i_ogrod' => [
            'Meble',
            'AGD',
            'Ogród',
            'Wyposażenie wnętrz'
        ],
        'elektronika' => [
            'Telefony',
            'Komputery',
            'RTV',
            'Konsole i gry'
        ],
        'moda' => [
            'Odzież damska',
            'Odzież męska',
            'Obuwie',
            'Akcesoria'
        ],
        'rolnictwo' => [
            'Maszyny rolnicze',
            'Części rolnicze',
            'Zwierzęta gospodarskie'
        ],
        'zwierzeta' => [
            'Psy',
            'Koty',
            'Ptaki',
            'Akwarystyka'
        ],
        'dla_dzieci' => [
            'Zabawki',
            'Wózki',
            'Ubranka',
            'Foteliki'
        ],
        'sport_hobby' => [
            'Sport',
            'Turystyka',
            'Kolekcje'
        ],
        'muzyka_edukacja' => [
            'Instrumenty',
            'Korepetycje',
            'Książki'
        ],
        'uslugi_firmy' => [
            'Budowlane',
            'Transportowe',
            'Finansowe',
            'Marketingowe'
        ],
        'oddam_za_darmo' => [],
        'zamienie' => []
    ];

    foreach ($categories as $parent_slug => $children) {

        // Tworzymy kategorię główną
        $parent = wp_insert_term(
            ucfirst(str_replace('_', ' ', $parent_slug)),
            'ogloszenia_kategoria',
            ['slug' => $parent_slug]
        );

        if (is_wp_error($parent)) {
            $parent_id = get_term_by('slug', $parent_slug, 'ogloszenia_kategoria')->term_id;
        } else {
            $parent_id = $parent['term_id'];
        }

        // Tworzymy podkategorie
        foreach ($children as $child) {
            wp_insert_term(
                $child,
                'ogloszenia_kategoria',
                [
                    'parent' => $parent_id
                ]
            );
        }
    }
}
