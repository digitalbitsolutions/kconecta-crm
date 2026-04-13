const dispatchMediaChange = (target) => {
    const dispatchTarget = target instanceof HTMLElement ? target : document;
    dispatchTarget.dispatchEvent(new CustomEvent("kconecta:media-change", { bubbles: true }));
};

const preview_image = (input_image, element_image) => {
    const tag_input_image = document.getElementById(input_image);
    const image_preview = document.getElementById(element_image);
    const file = new FileReader();
    tag_input_image.addEventListener("change", () =>{
        if (tag_input_image.files[0]){
            file.onload = function(e){
                image_preview.src = e.target.result;
            };
            file.readAsDataURL(tag_input_image.files[0]);
        }
        dispatchMediaChange(tag_input_image);
    })
}


const preview_image_auto = (input_image, ctn_images) => {
    const tag_input_image = document.getElementById(input_image);
    const image_container = document.getElementById(ctn_images);
    tag_input_image.addEventListener("change", () => {
        image_container.innerHTML = "";
        Array.from(tag_input_image.files).forEach(file => {
            const fileReader = new FileReader();
            fileReader.onload = (e) => {
                const img_element = document.createElement("img");
                img_element.src = e.target.result;
                image_container.appendChild(img_element);
            };
            fileReader.readAsDataURL(file);
        });
        dispatchMediaChange(tag_input_image);
    });
};

const loadVideoOptimizerScript = () => {
    if (window.KconectaVideoUploadOptimizer) {
        return Promise.resolve(window.KconectaVideoUploadOptimizer);
    }

    if (window.__kconectaVideoOptimizerPromise) {
        return window.__kconectaVideoOptimizerPromise;
    }

    window.__kconectaVideoOptimizerPromise = new Promise((resolve, reject) => {
        const script = document.createElement("script");
        script.src = `${window.location.origin}/js/video_upload_optimizer.js`;
        script.onload = () => resolve(window.KconectaVideoUploadOptimizer);
        script.onerror = () => reject(new Error("No se pudo cargar el validador de video."));
        document.head.appendChild(script);
    });

    return window.__kconectaVideoOptimizerPromise;
};

const preview_video = (id_input_file, id_container) =>{
    loadVideoOptimizerScript()
        .then((optimizer) => optimizer?.init({
            inputId: id_input_file,
            previewId: id_container,
        }))
        .catch((error) => {
            console.error(error);
        });
}
