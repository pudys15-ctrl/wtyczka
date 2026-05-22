<?php
/* Template: User Panel */
get_header();

// blokada dla niezalogowanych
if (!is_user_logged_in()) {
    wp_redirect('/logowanie');
    exit;
}

$current_user = wp_get_current_user();

// pobierz ogłoszenia użytkownika
$args = [
    'post_type'      => 'ogloszenie',
    'posts_per_page' => -1,
    'author'         => $current_user->ID,
    'post_status'    => ['publish', 'pending', 'draft']
];

$ads = get_posts($args);
?>

<div class="lokivo-panel-wrapper">

    <aside class="lokivo-panel-sidebar">
        <div class="lokivo-panel-user">
            <div class="lokivo-panel-avatar">
                <img src="https://www.svgrepo.com/show/382106/user-circle.svg" alt="">
            </div>
            <h3><?php echo esc_html($current_user->display_name ?: $current_user->user_email); ?></h3>
            <p><?php echo esc_html($current_user->user_email); ?></p>
        </div>

        <nav class="lokivo-panel-nav">
            <a href="#" class="active">Moje ogłoszenia</a>
            <a href="/konto/dodaj-ogloszenie">Dodaj ogłoszenie</a>
            <a href="/konto/ustawienia">Ustawienia</a>
            <a href="<?php echo wp_logout_url('/'); ?>">Wyloguj</a>
        </nav>
    </aside>

    <main class="lokivo-panel-main">

        <h1>Moje ogłoszenia</h1>

        <?php if (!$ads): ?>
            <p>Nie masz jeszcze żadnych ogłoszeń.</p>
        <?php else: ?>

            <div class="lokivo-panel-ads">

                <?php foreach ($ads as $ad): ?>
                    <div class="lokivo-panel-ad">

                        <div class="lokivo-panel-ad-thumb">
                            <a href="<?php echo get_permalink($ad->ID); ?>">
                                <?php if (has_post_thumbnail($ad->ID)): ?>
                                    <?php echo get_the_post_thumbnail($ad->ID, 'medium'); ?>
                                <?php else: ?>
                                    <div class="lokivo-panel-noimg">Brak zdjęcia</div>
                                <?php endif; ?>
                            </a>
                        </div>

                        <div class="lokivo-panel-ad-body">
                            <h2>
                                <a href="<?php echo get_permalink($ad->ID); ?>">
                                    <?php echo esc_html($ad->post_title); ?>
                                </a>
                            </h2>

                            <div class="lokivo-panel-ad-meta">
                                <span>Status: <strong><?php echo esc_html($ad->post_status); ?></strong></span>
                                <span>Data: <?php echo get_the_date('', $ad->ID); ?></span>
                            </div>

                            <div class="lokivo-panel-ad-actions">
                                <a href="/konto/edytuj-ogloszenie?id=<?php echo $ad->ID; ?>" class="lokivo-btn-edit">Edytuj</a>
                                <a href="/konto/usun-ogloszenie?id=<?php echo $ad->ID; ?>" class="lokivo-btn-delete">Usuń</a>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </main>

</div>

<?php get_footer(); ?>
