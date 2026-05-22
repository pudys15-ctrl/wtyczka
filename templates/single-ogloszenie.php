<?php get_header(); ?>

<div class="lokivo-single">

    <h1><?php the_title(); ?></h1>

    <div class="lokivo-meta">
        <p><strong>Kategoria:</strong>
            <?php echo get_the_term_list(get_the_ID(), 'ogloszenia_kategoria', '', ', '); ?>
        </p>

        <p><strong>Lokalizacja:</strong>
            <?php echo get_the_term_list(get_the_ID(), 'ogloszenia_lokalizacja', '', ', '); ?>
        </p>
    </div>

    <div class="lokivo-gallery">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('large'); ?>
        <?php endif; ?>
    </div>

    <div class="lokivo-content">
        <?php the_content(); ?>
    </div>

    <h2>Dane ogłoszenia</h2>

    <?php include LOKIVO_OGLOSZENIA_PATH . 'templates/parts/dynamic-fields.php'; ?>

</div>

<?php get_footer(); ?>
