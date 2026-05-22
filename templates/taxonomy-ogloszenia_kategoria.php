<?php
get_header();
$term   = get_queried_object();
$term_id = $term->term_id;
$parent = $term->parent ? get_term($term->parent) : null;
?>
<div class="site-content primary">

    <div class="lokivo-breadcrumbs">
        <a href="<?php echo home_url('/ogloszenia'); ?>">Ogłoszenia</a>

        <?php if ($parent) : ?>
            <span> / </span>
            <a href="<?php echo esc_url(get_term_link($parent)); ?>">
                <?php echo esc_html($parent->name); ?>
            </a>
        <?php endif; ?>

        <span> / </span>
        <span><?php echo esc_html($term->name); ?></span>
    </div>

    <div class="lokivo-cat-layout">

        <aside class="lokivo-cat-sidebar">

            <?php if ($parent) : ?>
                <a href="<?php echo esc_url(get_term_link($parent)); ?>" class="lokivo-back-btn">
                    ← Powrót do: <?php echo esc_html($parent->name); ?>
                </a>
            <?php else : ?>
                <a href="<?php echo home_url('/ogloszenia'); ?>" class="lokivo-back-btn">
                    ← Powrót do kategorii głównych
                </a>
            <?php endif; ?>

            <h2><?php echo esc_html($term->name); ?></h2>

            <?php
            $children = get_terms([
                'taxonomy'   => 'ogloszenia_kategoria',
                'parent'     => $term_id,
                'hide_empty' => false,
            ]);

            if ($children) : ?>
                <div class="lokivo-box">
                    <h3>Podkategorie</h3>
                    <ul class="lokivo-subcats">
                        <?php foreach ($children as $child) : ?>
                            <li>
                                <a href="<?php echo esc_url(get_term_link($child)); ?>">
                                    <?php echo esc_html($child->name); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="lokivo-box">
                <h3>Filtry</h3>

                <form method="get" class="lokivo-filters-form">

                    <?php
                    // ⭐ NOWY SYSTEM FILTRÓW – OBSŁUGA CHECKBOXÓW, SELECTÓW, NUMBER
                    if (function_exists('lokivo_render_category_fields')) {
                        lokivo_render_category_fields($term_id, 'filters');
                    }
                    ?>

                    <button type="submit" class="lokivo-filter-btn">Filtruj</button>
                </form>
            </div>

        </aside>

        <main class="lokivo-cat-main">

            <?php 
            // SORTOWANIE
            $sorting = [
                'date_desc' => 'Najnowsze',
                'date_asc'  => 'Najstarsze',
                'price_asc' => 'Cena rosnąco',
                'price_desc'=> 'Cena malejąco',
            ];
            $current = $_GET['sort'] ?? '';
            ?>

            <input type="hidden" id="lokivo-term-id" value="<?php echo (int) $term_id; ?>">

            <div class="lokivo-sort">
                <form method="get">
                    <select id="lokivo-sort" name="sort" onchange="this.form.submit()">
                        <option value="">Sortuj według...</option>

                        <?php foreach ($sorting as $key => $label): ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($current, $key); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <?php
                    // zachowaj filtry
                    foreach ($_GET as $k => $v) {
                        if ($k === 'sort') continue;

                        if (is_array($v)) {
                            foreach ($v as $vv) {
                                echo '<input type="hidden" name="'.esc_attr($k).'[]" value="'.esc_attr($vv).'">';
                            }
                        } else {
                            echo '<input type="hidden" name="'.esc_attr($k).'" value="'.esc_attr($v).'">';
                        }
                    }
                    ?>
                </form>
            </div>

            <header class="lokivo-cat-header">
                <h1><?php echo esc_html($term->name); ?></h1>
                <?php if (!empty($term->description)) : ?>
                    <p class="lokivo-cat-desc"><?php echo esc_html($term->description); ?></p>
                <?php endif; ?>
            </header>

            <div class="lokivo-cards">

                <?php if (have_posts()) : ?>
                    <?php while (have_posts()) : the_post(); ?>

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
                                    include LOKIVO_OGLOSZENIA_PATH . 'templates/parts/dynamic-fields.php';
                                    ?>
                                </div>
                            </div>
                        </article>

                    <?php endwhile; ?>

                    <div class="lokivo-pagination">
                        <?php the_posts_pagination(); ?>
                    </div>

                <?php else : ?>

                    <p>Brak ogłoszeń w tej kategorii.</p>

                <?php endif; ?>

            </div>

        </main>

    </div>
</div>

<?php get_footer(); ?>
