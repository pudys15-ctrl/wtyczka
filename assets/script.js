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

        // ❌ NIE resetujemy inputa — to psuło upload
        // input.value = "";

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
