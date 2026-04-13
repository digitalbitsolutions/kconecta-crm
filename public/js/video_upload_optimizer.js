(function () {
    const configuredMaxVideoBytes = Number(window.KC_VIDEO_MAX_UPLOAD_BYTES);
    const MAX_VIDEO_BYTES = Number.isFinite(configuredMaxVideoBytes) && configuredMaxVideoBytes > 0
        ? configuredMaxVideoBytes
        : 40 * 1024 * 1024;
    const MAX_VIDEO_MB = Math.max(1, Math.round(MAX_VIDEO_BYTES / (1024 * 1024)));
    const VIDEO_BUTTON_TEXT = `Subir video (max. ${MAX_VIDEO_MB}MB) (opcional)`;
    const ALLOWED_MIME_TYPES = new Set([
        "video/mp4",
        "video/quicktime",
        "video/x-msvideo",
        "video/mpeg",
    ]);
    const ALLOWED_EXTENSIONS = new Set(["mp4", "mov", "avi", "mpeg", "mpg"]);

    const formatBytes = (bytes) => {
        if (!Number.isFinite(bytes) || bytes <= 0) {
            return "0 MB";
        }

        const units = ["B", "KB", "MB", "GB"];
        let value = bytes;
        let unitIndex = 0;

        while (value >= 1024 && unitIndex < units.length - 1) {
            value /= 1024;
            unitIndex += 1;
        }

        const digits = unitIndex === 0 ? 0 : value >= 10 ? 1 : 2;
        return `${value.toFixed(digits)} ${units[unitIndex]}`;
    };

    const getVideoExtension = (file) => {
        const parts = String(file?.name || "").toLowerCase().split(".");
        return parts.length > 1 ? parts.pop() : "";
    };

    const isAllowedVideoFile = (file) => {
        if (!file) {
            return false;
        }

        const mimeType = String(file.type || "").toLowerCase();
        const extension = getVideoExtension(file);
        return ALLOWED_MIME_TYPES.has(mimeType) || ALLOWED_EXTENSIONS.has(extension);
    };

    const ensureButtonLabel = (button) => {
        if (!button) {
            return null;
        }

        let label = button.querySelector("[data-video-button-label]");
        if (label) {
            label.textContent = VIDEO_BUTTON_TEXT;
            return label;
        }

        label = document.createElement("span");
        label.dataset.videoButtonLabel = "1";
        label.textContent = VIDEO_BUTTON_TEXT;

        const svg = button.querySelector("svg");
        Array.from(button.childNodes).forEach((node) => {
            if (node.nodeType === Node.TEXT_NODE) {
                button.removeChild(node);
            }
        });

        if (svg) {
            button.insertBefore(label, svg);
        } else {
            button.appendChild(label);
        }

        return label;
    };

    const ensureFeedbackElements = (container) => {
        let feedback = container.querySelector("[data-video-feedback]");
        if (!feedback) {
            feedback = document.createElement("div");
            feedback.className = "video-upload-feedback";
            feedback.dataset.videoFeedback = "1";
            feedback.innerHTML = [
                '<p class="video-upload-help" data-video-help></p>',
                '<p class="video-upload-status" data-video-status aria-live="polite"></p>',
                '<p class="video-upload-summary" data-video-summary></p>',
            ].join("");
            container.appendChild(feedback);
        }

        const help = feedback.querySelector("[data-video-help]");
        const status = feedback.querySelector("[data-video-status]");
        const summary = feedback.querySelector("[data-video-summary]");

        if (help && !help.textContent.trim()) {
            help.textContent = `El video se sube sin recortar ni optimizar. El archivo debe pesar ${MAX_VIDEO_MB}MB o menos.`;
        }

        return { feedback, help, status, summary };
    };

    const getSubmitButtons = (form) => Array.from(form.querySelectorAll('button[type="submit"]'));

    const setStatus = (elements, message, kind = "") => {
        if (!elements?.status) {
            return;
        }

        elements.status.textContent = message || "";
        elements.status.className = "video-upload-status";
        if (kind) {
            elements.status.classList.add(`is-${kind}`);
        }
    };

    const setSummary = (elements, message, kind = "") => {
        if (!elements?.summary) {
            return;
        }

        elements.summary.textContent = message || "";
        elements.summary.className = "video-upload-summary";
        if (kind) {
            elements.summary.classList.add(`is-${kind}`);
        }
    };

    const hidePlaceholderImage = (container) => {
        const image = container.querySelector("img");
        if (image) {
            image.style.display = "none";
        }
    };

    const showPlaceholderImage = (container) => {
        const image = container.querySelector("img");
        if (image) {
            image.style.display = "";
        }
    };

    const clearObjectUrl = (state) => {
        if (state.previewObjectUrl) {
            URL.revokeObjectURL(state.previewObjectUrl);
            state.previewObjectUrl = null;
        }
    };

    const restoreInitialPreview = (context, state) => {
        clearObjectUrl(state);

        if (state.initialPreviewSrc) {
            context.preview.src = state.initialPreviewSrc;
            context.preview.style.display = "";
            hidePlaceholderImage(context.container);
            return;
        }

        context.preview.removeAttribute("src");
        context.preview.style.display = "none";
        showPlaceholderImage(context.container);
    };

    const showPreviewFromFile = (context, state, file) => {
        clearObjectUrl(state);
        state.previewObjectUrl = URL.createObjectURL(file);
        context.preview.src = state.previewObjectUrl;
        context.preview.style.display = "block";
        hidePlaceholderImage(context.container);
    };

    const syncSubmitButtons = (context, state) => {
        const formIsValid = context.form.checkValidity();
        const shouldDisable = !formIsValid;

        context.submitButtons.forEach((button) => {
            button.disabled = shouldDisable;
            button.classList.toggle("is-size-blocked", shouldDisable);
        });
    };

    const updateFormState = (context, state) => {
        syncSubmitButtons(context, state);
    };

    const clearVideoError = (context, state) => {
        state.videoError = "";
        context.input.setCustomValidity("");
    };

    const setVideoError = (context, state, message) => {
        state.videoError = message;
        context.input.setCustomValidity(message);
        setStatus(context.feedback, message, "error");
        setSummary(context.feedback, "");
        restoreInitialPreview(context, state);
        updateFormState(context, state);
    };

    const handleVideoSelection = (context, state) => {
        const file = context.input.files?.[0] || null;

        if (!file) {
            clearVideoError(context, state);
            setStatus(context.feedback, "");
            setSummary(context.feedback, state.initialPreviewSrc ? "Se mantendra el video actual." : "");
            restoreInitialPreview(context, state);
            updateFormState(context, state);
            return;
        }

        if (!isAllowedVideoFile(file)) {
            setVideoError(context, state, "El video no es valido. Solo se permiten MP4, MOV, AVI o MPEG.");
            return;
        }

        if ((file.size || 0) > MAX_VIDEO_BYTES) {
            setVideoError(
                context,
                state,
                `El video pesa ${formatBytes(file.size || 0)} y supera el limite de ${MAX_VIDEO_MB}MB.`
            );
            return;
        }

        clearVideoError(context, state);
        showPreviewFromFile(context, state, file);
        setStatus(context.feedback, "Video listo para subir.", "success");
        setSummary(
            context.feedback,
            `${file.name} (${formatBytes(file.size || 0)})`,
            "success"
        );
        updateFormState(context, state);
    };

    const init = ({ inputId, previewId }) => {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);

        if (!input || !preview) {
            return;
        }

        const form = input.closest("form");
        const container = preview.closest(".container-main-template-input-simple") || preview.parentElement;

        if (!form || !container) {
            return;
        }

        const uploadButton = container.querySelector(".btn-upload-image");
        ensureButtonLabel(uploadButton);

        const context = {
            input,
            preview,
            form,
            container: preview.closest(".container-video") || preview.parentElement,
            feedback: ensureFeedbackElements(container),
            submitButtons: getSubmitButtons(form),
        };

        const state = {
            initialPreviewSrc: preview.getAttribute("src") || "",
            previewObjectUrl: null,
            videoError: "",
        };

        input.addEventListener("change", () => {
            handleVideoSelection(context, state);
        });

        form.addEventListener("input", () => updateFormState(context, state), true);
        form.addEventListener("change", () => updateFormState(context, state), true);
        form.addEventListener("click", () => {
            window.setTimeout(() => updateFormState(context, state), 0);
        }, true);
        form.addEventListener("kconecta:media-change", () => updateFormState(context, state));

        form.addEventListener("submit", (event) => {
            handleVideoSelection(context, state);
            if (!form.checkValidity()) {
                event.preventDefault();
                form.reportValidity();
            }
        });

        if (state.initialPreviewSrc) {
            setSummary(context.feedback, "Se mantendra el video actual.");
        }

        updateFormState(context, state);
    };

    window.KconectaVideoUploadOptimizer = { init };
})();
