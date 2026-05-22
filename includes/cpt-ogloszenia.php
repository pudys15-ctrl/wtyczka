<?php

if ( ! defined( 'ABSPATH' ) ) exit;

function lokivo_register_cpt() {

    $labels = [
        'name' => 'Ogłoszenia',
        'singular_name' => 'Ogłoszenie',
        'add_new' => 'Dodaj ogłoszenie',
        'add_new_item' => 'Dodaj nowe ogłoszenie',
        'edit_item' => 'Edytuj ogłoszenie',
        'new_item' => 'Nowe ogłoszenie',
        'view_item' => 'Zobacz ogłoszenie',
        'search_items' => 'Szukaj ogłoszeń',
    ];

    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => false,
        'rewrite' => ['slug' => 'ogloszenie'],
        'supports' => ['title', 'editor', 'thumbnail', 'author'],
        'menu_icon' => 'dashicons-megaphone',
    ];

    register_post_type( 'ogloszenie', $args );
}

add_action( 'init', 'lokivo_register_cpt' );
