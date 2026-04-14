const imagesContainer = document.getElementById("container-images");
const inputImagen = document.getElementById("more_images");
const mediaForm = imagesContainer && imagesContainer.closest
    ? imagesContainer.closest("form")
    : document.querySelector("form[enctype='multipart/form-data']");

const notifyMediaChange = () => {
    const target = mediaForm || imagesContainer || document;
    target.dispatchEvent(new CustomEvent("kconecta:media-change", { bubbles: true }));
};

const appendPendingDeleteInput = (imageId) => {
    if (!mediaForm || !imageId) {
        return;
    }

    const selector = `input[name="delete_more_images[]"][value="${imageId}"]`;
    if (mediaForm.querySelector(selector)) {
        return;
    }

    const hiddenInput = document.createElement("input");
    hiddenInput.type = "hidden";
    hiddenInput.name = "delete_more_images[]";
    hiddenInput.value = imageId;
    hiddenInput.dataset.pendingDelete = "1";
    mediaForm.appendChild(hiddenInput);
};

let cont_img = 1;

if (imagesContainer && inputImagen) {
    inputImagen.addEventListener("change", () => {
        const selectedFiles = Array.from(inputImagen.files || []);

        if (!selectedFiles.length) {
            return;
        }

        selectedFiles.forEach((file) => {
            const inputClass = "ref-img-" + cont_img;
            mostrarPreview(file, inputClass);

            // Crear un nuevo input file "clonado" solo con esa imagen
            const nuevoInput = document.createElement("input");
            nuevoInput.type = "file";
            nuevoInput.name = "more_images[]";
            nuevoInput.style.display = "none";
            nuevoInput.classList.add(inputClass);

            // Crear un DataTransfer para meter el archivo dentro del nuevo input
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            nuevoInput.files = dataTransfer.files;

            // Agregar el input al formulario
            imagesContainer.appendChild(nuevoInput);
            cont_img += 1;
        });

        // Resetear input original
        inputImagen.value = "";
        notifyMediaChange();
    });
}

function mostrarPreview(file, class_block) {
    if (!imagesContainer) {
        return;
    }

    const div_main = document.createElement("div");
    div_main.classList.add("container-main-view-block-image");
    const div_ctn_img = document.createElement("div");
    div_ctn_img.classList.add("container-image-view-more-image");
    const div_btn_del = document.createElement("div");
    div_btn_del.classList.add("container-button-actions");
    const btn_delete = document.createElement("div");
    btn_delete.classList.add("button");
    btn_delete.classList.add("btn-delete-more-image-front");
    btn_delete.type = "button";
    btn_delete.textContent = "Eliminar";
    btn_delete.dataset.classinput = class_block;

    div_main.appendChild(div_ctn_img);
    div_btn_del.appendChild(btn_delete);
    div_main.appendChild(div_btn_del);

    const reader = new FileReader();
    reader.onload = function (e) {
        const img = document.createElement("img");
        img.src = e.target.result;

        div_ctn_img.appendChild(img);
        div_main.appendChild(div_ctn_img);
        div_btn_del.appendChild(btn_delete);
        div_main.appendChild(div_btn_del);

        imagesContainer.insertAdjacentElement("beforeend", div_main);
    };
    reader.readAsDataURL(file);
}

const btns_delete_img = document.querySelectorAll(".btn-delete-more-image");
btns_delete_img.forEach((btn) => {
    btn.addEventListener("click", () => {
        const confirmDelete = confirm("Eliminar esta imagen?");
        if (!confirmDelete) {
            return;
        }

        appendPendingDeleteInput(btn.dataset.id);
        btn.parentElement.parentElement.remove();
        notifyMediaChange();
    });
});

document.addEventListener("click", (event) => {
    if (event.target.classList.contains("btn-delete-more-image-front")) {
        const confirmDelete = confirm("Eliminar esta imagen?");
        if (!confirmDelete) {
            return;
        }
        document.querySelectorAll("." + event.target.dataset.classinput).forEach((d) => {
            d.remove();
        });
        event.target.parentElement.parentElement.remove();
        notifyMediaChange();
    }
});

document.addEventListener("click", (event) => {
    const deleteCoverButton = event.target.closest(".btn-delete-cover-image");
    if (deleteCoverButton) {
        const confirmDelete = confirm("Eliminar la imagen de portada?");
        if (!confirmDelete) {
            return;
        }

        const deleteCoverInput = document.getElementById("delete_cover_image");
        const coverPreview = document.getElementById("preview_cover_image");
        const coverInput = document.getElementById("cover_image");
        const placeholderImage = deleteCoverButton.dataset.placeholder;

        if (deleteCoverInput) {
            deleteCoverInput.value = "1";
        }

        if (coverInput) {
            coverInput.value = "";
        }

        if (coverPreview && deleteCoverButton.dataset.placeholder) {
            coverPreview.src = placeholderImage;
        }

        deleteCoverButton.remove();
        notifyMediaChange();
    }
});
