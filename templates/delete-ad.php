<?php
/* Template: Delete Ad */
get_header();

if (!is_user_logged_in()) {
    wp_redirect('/logowanie');
    exit;
}

$current_user = wp_get_current_user();

$ad_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$ad    = get_post($ad_id);

if (!$ad || $ad->post_type !== 'ogloszenie') {
    wp_die('Ogłoszenie nie istnieje.');
}

if ($ad->post_author != $current_user->ID) {
    wp_die('Nie masz uprawnień do usunięcia tego ogłoszenia.');
}
?>

<div class="lokivo-delete-wrapper">

    <h1>Usuń ogłoszenie</h1>

    <p>Czy na pewno chcesz usunąć ogłoszenie:</p>

    <h2><?php echo esc_html($ad->post_title); ?></h2>

    <p>Tej operacji nie można cofnąć.</p>

    <form method="post">
        <button type="submit" name="lokivo_delete_confirm" class="lokivo-btn-delete-big">
            Tak, usuń ogłoszenie
        </button>

        <a href="/konto/panel" class="lokivo-btn-cancel">Anuluj</a>
    </form>

</div>

<?php get_footer(); ?>
