<?php get_header(); ?>

<h1 class="lokivo-archive-title">Kategorie ogłoszeń</h1>

<div class="lokivo-category-grid">

<?php
$terms = get_terms([
    'taxonomy' => 'ogloszenia_kategoria',
    'parent'   => 0,
    'hide_empty' => false
]);

foreach ($terms as $term) :
    $link = get_term_link($term);
?>
    <a class="lokivo-category-box" href="<?php echo esc_url($link); ?>">
        <div class="lokivo-category-inner">
            <h2><?php echo esc_html($term->name); ?></h2>
        </div>
    </a>

<?php endforeach; ?>

</div>

<?php get_footer(); ?>
