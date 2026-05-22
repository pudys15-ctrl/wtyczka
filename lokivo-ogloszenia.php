<?php
/**
 * Plugin Name: Lokivo Ogłoszenia
 * Description: Podstawowa wersja wtyczki ogłoszeniowej.
 * Version: 1.1
 * Author: Pudys
 */

if (!defined('ABSPATH')) exit;

define('LOKIVO_OGLOSZENIA_PATH', plugin_dir_path(__FILE__));
// Panel administratora – pola kategorii
if (is_admin()) {
    require_once __DIR__ . '/admin/category-fields-panel.php';
}
/*
|--------------------------------------------------------------------------
|  ŁADOWANIE MODUŁÓW
|--------------------------------------------------------------------------
*/
require_once LOKIVO_OGLOSZENIA_PATH . 'includes/cpt-ogloszenia.php';
require_once LOKIVO_OGLOSZENIA_PATH . 'includes/taxonomies.php';

require_once LOKIVO_OGLOSZENIA_PATH . 'includes/filters/filters-full.php';
require_once LOKIVO_OGLOSZENIA_PATH . 'includes/filters/filters-fields.php';
require_once LOKIVO_OGLOSZENIA_PATH . 'includes/filters/filters-query.php';
require_once LOKIVO_OGLOSZENIA_PATH . 'includes/filters/filters-ajax.php';

require_once LOKIVO_OGLOSZENIA_PATH . 'includes/meta-save.php';
require_once LOKIVO_OGLOSZENIA_PATH . 'includes/featured-image.php';
require_once LOKIVO_OGLOSZENIA_PATH . 'includes/gallery-upload.php';

/*
|--------------------------------------------------------------------------
|  SZABLONY
|--------------------------------------------------------------------------
*/
add_filter('template_include', function($template) {

    if (is_singular('ogloszenie')) {
        return LOKIVO_OGLOSZENIA_PATH . 'templates/single-ogloszenie.php';
    }

    if (is_post_type_archive('ogloszenie')) {
        return LOKIVO_OGLOSZENIA_PATH . 'templates/archive-ogloszenie.php';
    }

    if (is_tax('ogloszenia_kategoria')) {
        return LOKIVO_OGLOSZENIA_PATH . 'templates/taxonomy-ogloszenia_kategoria.php';
    }

    if (is_page('logowanie')) {
        return LOKIVO_OGLOSZENIA_PATH . 'templates/login.php';
    }

    if (is_page('rejestracja')) {
        return LOKIVO_OGLOSZENIA_PATH . 'templates/register.php';
    }

    if (is_page('panel')) {
        return LOKIVO_OGLOSZENIA_PATH . 'templates/panel.php';
    }

    if (is_page('edytuj-ogloszenie')) {
        return LOKIVO_OGLOSZENIA_PATH . 'templates/edit-ad.php';
    }

    if (is_page('usun-ogloszenie')) {
        return LOKIVO_OGLOSZENIA_PATH . 'templates/delete-ad.php';
    }

    if (is_page('dodaj-ogloszenie')) {
        return LOKIVO_OGLOSZENIA_PATH . 'templates/add-ad.php';
    }

    return $template;
});

/*
|--------------------------------------------------------------------------
|  SKRYPTY
|--------------------------------------------------------------------------
*/
add_action('wp_enqueue_scripts', function() {

    wp_enqueue_style(
        'lokivo-style',
        plugins_url('assets/style.css', __FILE__)
    );

    wp_enqueue_script(
        'lokivo-js',
        plugins_url('assets/script.js', __FILE__),
        ['jquery'],
        null,
        true
    );

    wp_localize_script('lokivo-js', 'lokivoAjax', [
        'url' => admin_url('admin-ajax.php'),
    ]);
});

/*
|--------------------------------------------------------------------------
|  REJESTRACJA
|--------------------------------------------------------------------------
*/
add_action('init', function() {

    if (!isset($_POST['lokivo_register'])) return;

    $email    = sanitize_email($_POST['email']);
    $password = sanitize_text_field($_POST['password']);

    if (email_exists($email)) {
        wp_die('Ten e-mail jest już zajęty.');
    }

    $user_id = wp_create_user($email, $password, $email);

    if (is_wp_error($user_id)) {
        wp_die('Nie udało się utworzyć konta.');
    }

    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    wp_redirect('/konto/panel');
    exit;
});

/*
|--------------------------------------------------------------------------
|  LOGOWANIE
|--------------------------------------------------------------------------
*/
add_action('init', function() {

    if (!isset($_POST['lokivo_login'])) return;

    $email    = sanitize_email($_POST['email']);
    $password = sanitize_text_field($_POST['password']);

    $user = wp_authenticate($email, $password);

    if (is_wp_error($user)) {
        wp_redirect('/logowanie?error=1');
        exit;
    }

    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID);

    wp_redirect('/konto/panel');
    exit;
});

/*
|--------------------------------------------------------------------------
|  EDYCJA OGŁOSZENIA
|--------------------------------------------------------------------------
*/
add_action('template_redirect', function() {

    if (!isset($_POST['lokivo_edit_ad'])) return;

    $ad_id = (int) $_GET['id'];
    $ad    = get_post($ad_id);

    if (!$ad || $ad->post_type !== 'ogloszenie') return;
    if ($ad->post_author != get_current_user_id()) return;

    wp_update_post([
        'ID'           => $ad_id,
        'post_title'   => sanitize_text_field($_POST['title']),
        'post_content' => wp_kses_post($_POST['content']),
    ]);

    lokivo_save_ad_meta($ad_id, $_POST);
    lokivo_handle_featured_image_upload($ad_id, 'featured_image');
    lokivo_handle_gallery_upload($ad_id);

    // 🔥 DEBUG
    error_log("=== LOKIVO EDIT AD ===");
    error_log("POST: " . print_r($_POST, true));
    error_log("FILES: " . print_r($_FILES, true));

    wp_redirect('/konto/panel');
    exit;
});


/*
|--------------------------------------------------------------------------
|  USUWANIE OGŁOSZENIA
|--------------------------------------------------------------------------
*/
add_action('init', function() {

    if (!isset($_POST['lokivo_delete_confirm'])) return;

    $ad_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $ad    = get_post($ad_id);

    if (!$ad || $ad->post_type !== 'ogloszenie') return;
    if ($ad->post_author != get_current_user_id()) return;

    $gallery = get_post_meta($ad_id, '_gallery', true);
    if (is_array($gallery)) {
        foreach ($gallery as $img_id) {
            wp_delete_attachment($img_id, true);
        }
    }

    wp_delete_post($ad_id, true);

    wp_redirect('/konto/panel');
    exit;
});

/*
|--------------------------------------------------------------------------
|  DODAWANIE OGŁOSZENIA
|--------------------------------------------------------------------------
*/
add_action('init', function() {

    if (!isset($_POST['lokivo_add_ad'])) return;
    if (!is_user_logged_in()) return;

    $user_id = get_current_user_id();

    $ad_id = wp_insert_post([
        'post_type'   => 'ogloszenie',
        'post_title'  => sanitize_text_field($_POST['title']),
        'post_content'=> wp_kses_post($_POST['content']),
        'post_status' => 'publish',
        'post_author' => $user_id
    ]);

    if (!empty($_POST['category'])) {
        wp_set_post_terms($ad_id, [(int) $_POST['category']], 'ogloszenia_kategoria');
    }

    lokivo_save_ad_meta($ad_id, $_POST);
    lokivo_handle_featured_image_upload($ad_id, 'featured_image');
    lokivo_handle_gallery_upload($ad_id, 'images');

    wp_redirect('/konto/panel');
    exit;
});




add_action('wp_enqueue_scripts', function() {

    wp_enqueue_style(
        'lokivo-style',
        plugins_url('assets/style.css', __FILE__)
    );

    // SortableJS (wymagane do zmiany kolejności)
    wp_enqueue_script(
        'lokivo-sortable',
        'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js',
        [],
        null,
        true
    );

    wp_enqueue_script(
        'lokivo-js',
        plugins_url('assets/script.js', __FILE__),
        ['jquery', 'lokivo-sortable'],
        null,
        true
    );

    wp_localize_script('lokivo-js', 'lokivoAjax', [
        'url' => admin_url('admin-ajax.php'),
    ]);
});


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


