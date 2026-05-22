<?php
/* Template: Add Ad */
get_header();

if (!is_user_logged_in()) {
    wp_redirect('/logowanie');
    exit;
}

// pobierz kategorie główne
$main_categories = get_terms([
    'taxonomy' => 'ogloszenia_kategoria',
    'hide_empty' => false,
    'parent' => 0,
]);
?>

<div class="lokivo-add-wrapper">

    <h1>Dodaj ogłoszenie</h1>

    <form method="post" enctype="multipart/form-data" class="lokivo-add-form">

        <label>
            <span>Tytuł ogłoszenia</span>
            <input type="text" name="title" required>
        </label>

        <label>
            <span>Opis</span>
            <textarea name="content" rows="6" required></textarea>
        </label>

        <label>
            <span>Kategoria</span>
            <select name="main_category" id="lokivo-main-category" required>
                <option value="">Wybierz kategorię</option>
                <?php foreach ($main_categories as $cat): ?>
                    <option value="<?php echo $cat->term_id; ?>">
                        <?php echo esc_html($cat->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            <span>Podkategoria</span>
            <select name="category" id="lokivo-sub-category" required disabled>
                <option value="">Najpierw wybierz kategorię</option>
            </select>
        </label>

        <div id="lokivo-dynamic-fields"></div>

        <h3>Galeria zdjęć</h3>

        <div id="lokivo-gallery">

            <div id="lokivo-gallery-list" class="lokivo-gallery-list"></div>

            <label class="lokivo-gallery-upload">
                <span>Dodaj zdjęcia</span>
                <input type="file" id="lokivo-gallery-input" name="images[]" multiple>
            </label>

            <input type="hidden" id="lokivo-gallery-order" name="gallery_order">
            <input type="hidden" id="lokivo-gallery-deleted" name="gallery_deleted">
        </div>

        <button type="submit" name="lokivo_add_ad" class="lokivo-add-btn">
            Dodaj ogłoszenie
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

    // ŁADOWANIE PÓL DLA PODKATEGORII
    $("#lokivo-sub-category").on("change", function(){

        let term = $(this).val();

        $("#lokivo-dynamic-fields").html("");

        if (!term) return;

        $.post(lokivoAjax.url, {
            action: "lokivo_get_fields",
            term: term
        }, function(res){
            $("#lokivo-dynamic-fields").html(res);
        });

    });

});
</script>

<script>
// GALERIA OLX STYLE (Twoja wersja, bez ruszania logiki)
document.addEventListener("DOMContentLoaded", function () {

    const list = document.getElementById("lokivo-gallery-list");
    const input = document.getElementById("lokivo-gallery-input");
    const orderField = document.getElementById("lokivo-gallery-order");
    const deletedField = document.getElementById("lokivo-gallery-deleted");

    let deleted = [];

    if (typeof Sortable !== "undefined") {
        Sortable.create(list, {
            animation: 150,
            onSort: updateOrder
        });
    }

    function updateOrder() {
        const ids = [...list.querySelectorAll(".lokivo-gallery-item")]
            .map(el => el.dataset.id);
        orderField.value = ids.join(",");
    }

    list.addEventListener("click", function (e) {
        if (e.target.classList.contains("lokivo-gallery-remove")) {
            const item = e.target.closest(".lokivo-gallery-item");
            deleted.push(item.dataset.id);
            deletedField.value = deleted.join(",");
            item.remove();
            updateOrder();
        }
    });

    input.addEventListener("change", function () {
        for (let file of input.files) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const div = document.createElement("div");
                div.className = "lokivo-gallery-item";
                div.dataset.id = "new-" + Math.random().toString(36).substr(2, 9);
                div.innerHTML = `
                    <img src="${e.target.result}">
                    <div class="lokivo-gallery-remove">×</div>
                `;
                list.appendChild(div);
                updateOrder();
            };
            reader.readAsDataURL(file);
        }
    });

});
</script>

<?php get_footer(); ?>
