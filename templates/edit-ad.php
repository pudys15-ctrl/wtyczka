<?php
/* Template: Edit Ad */
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
    wp_die('Nie masz uprawnień do edycji tego ogłoszenia.');
}

// kategorie główne
$main_categories = get_terms([
    'taxonomy' => 'ogloszenia_kategoria',
    'hide_empty' => false,
    'parent' => 0,
]);

// aktualna kategoria (podkategoria)
$current_terms = wp_get_post_terms($ad_id, 'ogloszenia_kategoria');
$current_term_id = $current_terms ? $current_terms[0]->term_id : 0;
$current_term    = $current_term_id ? get_term($current_term_id, 'ogloszenia_kategoria') : null;
$current_parent_id = ($current_term && $current_term->parent) ? $current_term->parent : 0;

// podkategorie dla aktualnej kategorii głównej
$sub_categories = [];
if ($current_parent_id) {
    $sub_categories = get_terms([
        'taxonomy' => 'ogloszenia_kategoria',
        'hide_empty' => false,
        'parent' => $current_parent_id,
    ]);
}

$gallery = get_post_meta($ad_id, '_gallery', true);
$gallery = is_array($gallery) ? $gallery : [];
?>

<div class="lokivo-add-wrapper">

    <h1>Edytuj ogłoszenie</h1>

    <form method="post" enctype="multipart/form-data" class="lokivo-add-form">

        <label>
            <span>Tytuł ogłoszenia</span>
            <input type="text" name="title" value="<?php echo esc_attr($ad->post_title); ?>" required>
        </label>

        <label>
            <span>Opis</span>
            <textarea name="content" rows="6" required><?php echo esc_textarea($ad->post_content); ?></textarea>
        </label>

        <label>
            <span>Kategoria</span>
            <select name="main_category" id="lokivo-main-category" required>
                <option value="">Wybierz kategorię</option>
                <?php foreach ($main_categories as $cat): ?>
                    <option value="<?php echo $cat->term_id; ?>" <?php selected($current_parent_id, $cat->term_id); ?>>
                        <?php echo esc_html($cat->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            <span>Podkategoria</span>
            <select name="category" id="lokivo-sub-category" required <?php echo $current_parent_id ? '' : 'disabled'; ?>>
                <?php if (!$current_parent_id): ?>
                    <option value="">Najpierw wybierz kategorię</option>
                <?php else: ?>
                    <option value="">Wybierz podkategorię</option>
                    <?php foreach ($sub_categories as $sub): ?>
                        <option value="<?php echo $sub->term_id; ?>" <?php selected($current_term_id, $sub->term_id); ?>>
                            <?php echo esc_html($sub->name); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </label>

        <div id="lokivo-dynamic-fields">
            <?php
            if ($current_term_id && function_exists('lokivo_render_category_fields')) {
                lokivo_render_category_fields($current_term_id, 'form', $ad_id);
            }
            ?>
        </div>

        <h3>Galeria zdjęć</h3>

        <div id="lokivo-gallery">

            <div id="lokivo-gallery-list" class="lokivo-gallery-list">
                <?php foreach ($gallery as $img_id): ?>
                    <div class="lokivo-gallery-item" data-id="<?php echo $img_id; ?>">
                        <?php echo wp_get_attachment_image($img_id, 'thumbnail'); ?>
                        <div class="lokivo-gallery-remove">×</div>
                    </div>
                <?php endforeach; ?>
            </div>

            <label class="lokivo-gallery-upload">
                <span>Dodaj zdjęcia</span>
                <input type="file" id="lokivo-gallery-input" name="images[]" multiple>
            </label>

            <input type="hidden" id="ad_gallery" name="ad_gallery" value="<?php echo implode(',', $gallery); ?>">
            <input type="hidden" id="lokivo-gallery-order" name="gallery_order">
            <input type="hidden" id="lokivo-gallery-deleted" name="gallery_deleted">
        </div>

        <button type="submit" name="lokivo_edit_ad" class="lokivo-add-btn">
            Zapisz zmiany
        </button>

    </form>

</div>

<script>
jQuery(function($){

    // ŁADOWANIE PODKATEGORII
    $("#lokivo-main-category").on("change", function(){

        let parent = $(this).val();

        $("#lokivo-sub-category")
            .prop("disabled", true)
            .html("<option>Ładowanie...</option>");

        $("#lokivo-dynamic-fields").html("");

        if (!parent) {
            $("#lokivo-sub-category")
                .prop("disabled", true)
                .html("<option>Najpierw wybierz kategorię</option>");
            return;
        }

        $.post(lokivoAjax.url, {
            action: "lokivo_get_subcategories",
            parent: parent
        }, function(res){
            $("#lokivo-sub-category").html(res).prop("disabled", false);
        });

    });

    // ŁADOWANIE PÓL DLA PODKATEGORII (z wartościami przy edycji)
    $("#lokivo-sub-category").on("change", function(){

        let term = $(this).val();

        $("#lokivo-dynamic-fields").html("");

        if (!term) return;

        $.post(lokivoAjax.url, {
            action: "lokivo_get_fields",
            term: term,
            ad_id: <?php echo (int) $ad_id; ?>
        }, function(res){
            $("#lokivo-dynamic-fields").html(res);
        });

    });

});
</script>

<script>
// GALERIA – wersja, która już Ci działa
document.addEventListener("DOMContentLoaded", function () {

    const list = document.getElementById("lokivo-gallery-list");
    const input = document.getElementById("lokivo-gallery-input");
    const orderField = document.getElementById("lokivo-gallery-order");
    const deletedField = document.getElementById("lokivo-gallery-deleted");
    const galleryField = document.getElementById("ad_gallery");

    let deleted = [];

    function updateGalleryField() {
        const ids = [...list.querySelectorAll(".lokivo-gallery-item")]
            .map(el => el.dataset.id);
        galleryField.value = ids.join(",");
        orderField.value = ids.join(",");
    }

    if (typeof Sortable !== "undefined") {
        Sortable.create(list, {
            animation: 150,
            onSort: updateGalleryField
        });
    }

    updateGalleryField();

    list.addEventListener("click", function (e) {
        if (e.target.classList.contains("lokivo-gallery-remove")) {
            const item = e.target.closest(".lokivo-gallery-item");
            deleted.push(item.dataset.id);
            deletedField.value = deleted.join(",");
            item.remove();
            updateGalleryField();
        }
    });

    input.addEventListener("change", function () {

        const files = Array.from(input.files);

        files.forEach((file, index) => {

            const reader = new FileReader();

            reader.onload = function (e) {

                const div = document.createElement("div");
                div.className = "lokivo-gallery-item";
                div.dataset.id = "new-" + index;

                div.innerHTML = `
                    <img src="${e.target.result}">
                    <div class="lokivo-gallery-remove">×</div>
                `;

                list.appendChild(div);
                updateGalleryField();
            };

            reader.readAsDataURL(file);
        });
    });

});
</script>

<?php get_footer(); ?>
