(function () {
    const configuredMaxVideoBytes = Number(window.KC_VIDEO_MAX_UPLOAD_BYTES);
    const MAX_VIDEO_BYTES = Number.isFinite(configuredMaxVideoBytes) && configuredMaxVideoBytes > 0
        ? configuredMaxVideoBytes
        : 150 * 1024 * 1024;
    const MAX_VIDEO_MB = Math.max(1, Math.round(MAX_VIDEO_BYTES / (1024 * 1024)));
    const VIDEO_BUTTON_TEXT = `Subir video (max. ${MAX_VIDEO_MB}MB) (opcional)`;
    const FFMPEG_SCRIPT_URL = `${window.location.origin}/vendor/ffmpeg/ffmpeg.js`;
    const FFMPEG_CORE_URL = `${window.location.origin}/vendor/ffmpeg/ffmpeg-core.js`;
    const FFMPEG_WASM_URL = `${window.location.origin}/vendor/ffmpeg/ffmpeg-core.wasm`;
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

    const formatSeconds = (seconds) => {
        if (!Number.isFinite(seconds) || seconds <= 0) {
            return "0 s";
        }

        if (seconds < 60) {
            return `${seconds.toFixed(seconds >= 10 ? 0 : 1)} s`;
        }

        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.round(seconds % 60);
        return `${minutes} min ${remainingSeconds.toString().padStart(2, "0")} s`;
    };

    const getVideoExtension = (file) => {
        const parts = String(file?.name || "").toLowerCase().split(".");
        return parts.length > 1 ? parts.pop() : "";
    };

    const getSafeFileBaseName = (file) => {
        const parts = String(file?.name || "video").split(".");
        if (parts.length > 1) {
            parts.pop();
        }

        return parts.join(".").trim() || "video";
    };

    const getOutputExtension = (file) => {
        const extension = getVideoExtension(file);
        return ALLOWED_EXTENSIONS.has(extension) ? extension : "mp4";
    };

    const getOutputMimeType = (extension) => ({
        mp4: "video/mp4",
        mov: "video/quicktime",
        avi: "video/x-msvideo",
        mpeg: "video/mpeg",
        mpg: "video/mpeg",
    })[extension] || "video/mp4";

    const isAllowedVideoFile = (file) => {
        if (!file) {
            return false;
        }

        const mimeType = String(file.type || "").toLowerCase();
        const extension = getVideoExtension(file);
        return ALLOWED_MIME_TYPES.has(mimeType) || ALLOWED_EXTENSIONS.has(extension);
    };

    const createManagedObjectUrl = (file) => {
        const url = URL.createObjectURL(file);

        return {
            url,
            revoke() {
                URL.revokeObjectURL(url);
            },
        };
    };

    const loadVideoMetadata = (file) => new Promise((resolve, reject) => {
        const managedUrl = createManagedObjectUrl(file);
        const tempVideo = document.createElement("video");

        tempVideo.preload = "metadata";
        tempVideo.muted = true;
        tempVideo.playsInline = true;

        const cleanup = () => {
            tempVideo.removeAttribute("src");
            tempVideo.load();
            managedUrl.revoke();
        };

        tempVideo.onloadedmetadata = () => {
            const duration = Number(tempVideo.duration);
            cleanup();

            if (!Number.isFinite(duration) || duration <= 0) {
                reject(new Error("No se pudo leer la duracion del video."));
                return;
            }

            resolve({ duration });
        };

        tempVideo.onerror = () => {
            cleanup();
            reject(new Error("No se pudo preparar el video para recortarlo."));
        };

        tempVideo.src = managedUrl.url;
    });

    const setInputFile = (input, file) => {
        const transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;
    };

    const clearInputFile = (input) => {
        input.value = "";
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
                '<div class="video-upload-actions" data-video-actions hidden></div>',
            ].join("");
            container.appendChild(feedback);
        }

        const help = feedback.querySelector("[data-video-help]");
        const status = feedback.querySelector("[data-video-status]");
        const summary = feedback.querySelector("[data-video-summary]");
        const actions = feedback.querySelector("[data-video-actions]");

        if (help && !help.textContent.trim()) {
            help.textContent = `El video debe pesar ${MAX_VIDEO_MB}MB o menos. Si supera ese tamano, puedes elegir otro archivo o autorizar un recorte automatico antes de subirlo.`;
        }

        return { feedback, help, status, summary, actions };
    };

    const ensureActionButtons = (elements) => {
        if (!elements?.actions) {
            return null;
        }

        let chooseButton = elements.actions.querySelector("[data-video-action='choose']");
        let trimButton = elements.actions.querySelector("[data-video-action='trim']");

        if (!chooseButton || !trimButton) {
            elements.actions.innerHTML = "";

            chooseButton = document.createElement("button");
            chooseButton.type = "button";
            chooseButton.className = "button video-upload-action is-secondary";
            chooseButton.dataset.videoAction = "choose";
            chooseButton.textContent = "Elegir otro video";

            trimButton = document.createElement("button");
            trimButton.type = "button";
            trimButton.className = "button video-upload-action is-primary";
            trimButton.dataset.videoAction = "trim";
            trimButton.textContent = "Autorizar recorte";

            elements.actions.appendChild(chooseButton);
            elements.actions.appendChild(trimButton);
        }

        return { chooseButton, trimButton };
    };

    const showActions = (elements, visible) => {
        if (!elements?.actions) {
            return;
        }

        elements.actions.hidden = !visible;
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

    const setActionButtonsDisabled = (context, disabled) => {
        const buttons = ensureActionButtons(context.feedback);
        if (!buttons) {
            return;
        }

        buttons.chooseButton.disabled = disabled;
        buttons.trimButton.disabled = disabled;
    };

    const syncSubmitButtons = (context, state) => {
        const formIsValid = context.form.checkValidity();
        const shouldDisable = !formIsValid || state.isProcessing;

        context.submitButtons.forEach((button) => {
            button.disabled = shouldDisable;
            button.classList.toggle("is-size-blocked", !formIsValid);
            button.classList.toggle("is-video-processing", state.isProcessing);
        });

        setActionButtonsDisabled(context, state.isProcessing);
    };

    const updateFormState = (context, state) => {
        syncSubmitButtons(context, state);
    };

    const clearVideoError = (context, state) => {
        state.videoError = "";
        context.input.setCustomValidity("");
    };

    const setVideoError = (context, state, message, kind = "error") => {
        state.videoError = message;
        context.input.setCustomValidity(message);
        setStatus(context.feedback, message, kind);
        updateFormState(context, state);
    };

    const resetOversizeChoice = (context, state) => {
        state.originalFile = null;
        state.trimmedFile = null;
        showActions(context.feedback, false);
    };

    const describeOversizeVideo = (file) => `${file.name} pesa ${formatBytes(file.size || 0)} y supera el limite de ${MAX_VIDEO_MB}MB.`;

    const loadFfmpegScript = () => {
        if (window.FFmpegWASM?.FFmpeg) {
            return Promise.resolve(window.FFmpegWASM);
        }

        if (window.__kconectaFfmpegScriptPromise) {
            return window.__kconectaFfmpegScriptPromise;
        }

        window.__kconectaFfmpegScriptPromise = new Promise((resolve, reject) => {
            const script = document.createElement("script");
            script.src = FFMPEG_SCRIPT_URL;
            script.onload = () => {
                if (window.FFmpegWASM?.FFmpeg) {
                    resolve(window.FFmpegWASM);
                    return;
                }

                reject(new Error("No se encontro FFmpeg en el navegador."));
            };
            script.onerror = () => reject(new Error("No se pudo cargar el motor de recorte de video."));
            document.head.appendChild(script);
        });

        return window.__kconectaFfmpegScriptPromise;
    };

    const getFfmpeg = async (state) => {
        if (state.ffmpeg?.loaded) {
            return state.ffmpeg;
        }

        const ffmpegLibrary = await loadFfmpegScript();
        const ffmpeg = state.ffmpeg || new ffmpegLibrary.FFmpeg();

        if (!ffmpeg.loaded) {
            await ffmpeg.load({
                coreURL: FFMPEG_CORE_URL,
                wasmURL: FFMPEG_WASM_URL,
            });
        }

        state.ffmpeg = ffmpeg;
        return ffmpeg;
    };

    const trimVideoToLimit = async (file, state, onProgress) => {
        const { duration } = await loadVideoMetadata(file);
        const ffmpeg = await getFfmpeg(state);
        const extension = getOutputExtension(file);
        const mimeType = getOutputMimeType(extension);
        const inputName = `input.${extension}`;
        const outputName = `output.${extension}`;
        const sourceBytes = new Uint8Array(await file.arrayBuffer());

        await ffmpeg.writeFile(inputName, sourceBytes);

        let targetDuration = Math.max(1, duration * (MAX_VIDEO_BYTES / Math.max(file.size || 1, 1)) * 0.96);

        try {
            for (let attempt = 1; attempt <= 4; attempt += 1) {
                const effectiveDuration = Math.min(duration, Math.max(1, targetDuration));
                onProgress(`Recortando video... intento ${attempt}/4 (${formatSeconds(effectiveDuration)} aprox.).`);

                try {
                    await ffmpeg.deleteFile(outputName);
                } catch (error) {
                    // Ignoramos si todavia no existe el archivo de salida.
                }

                await ffmpeg.exec([
                    "-i", inputName,
                    "-t", effectiveDuration.toFixed(2),
                    "-c", "copy",
                    outputName,
                ]);

                const outputBytes = await ffmpeg.readFile(outputName);
                const trimmedBlob = new Blob([outputBytes], { type: mimeType });

                if (trimmedBlob.size <= MAX_VIDEO_BYTES) {
                    return {
                        file: new File(
                            [trimmedBlob],
                            `${getSafeFileBaseName(file)}-recortado.${extension}`,
                            { type: mimeType, lastModified: Date.now() }
                        ),
                        duration,
                        targetDuration: effectiveDuration,
                    };
                }

                const shrinkRatio = MAX_VIDEO_BYTES / Math.max(trimmedBlob.size, 1);
                targetDuration = Math.max(1, effectiveDuration * shrinkRatio * 0.92);
            }
        } finally {
            try {
                await ffmpeg.deleteFile(inputName);
            } catch (error) {
                // Ignoramos si FFmpeg ya limpio el archivo.
            }

            try {
                await ffmpeg.deleteFile(outputName);
            } catch (error) {
                // Ignoramos si no se genero salida.
            }
        }

        throw new Error("No se pudo dejar el video por debajo del limite permitido.");
    };

    const handleOversizeVideo = (context, state, file) => {
        state.originalFile = file;
        state.trimmedFile = null;
        showPreviewFromFile(context, state, file);
        setVideoError(
            context,
            state,
            `${describeOversizeVideo(file)} Elige otro archivo o autoriza recortarlo.`,
            "warning"
        );
        setSummary(
            context.feedback,
            `Si autorizas el recorte, conservaremos el inicio del video hasta que pese ${MAX_VIDEO_MB}MB o menos.`,
            "warning"
        );
        showActions(context.feedback, true);
    };

    const applyValidVideo = (context, state, file, summaryMessage = "") => {
        state.originalFile = file;
        state.trimmedFile = file;
        clearVideoError(context, state);
        showPreviewFromFile(context, state, file);
        showActions(context.feedback, false);
        setStatus(context.feedback, "Archivo correcto.", "success");
        setSummary(
            context.feedback,
            summaryMessage || `${file.name} (${formatBytes(file.size || 0)})`,
            "success"
        );
        updateFormState(context, state);
    };

    const chooseAnotherVideo = (context, state) => {
        resetOversizeChoice(context, state);
        clearVideoError(context, state);
        clearInputFile(context.input);
        setStatus(context.feedback, `Selecciona otro video que pese ${MAX_VIDEO_MB}MB o menos.`, "warning");
        setSummary(context.feedback, "");
        restoreInitialPreview(context, state);
        updateFormState(context, state);
        context.input.click();
    };

    const applyNoVideoSelected = (context, state) => {
        resetOversizeChoice(context, state);
        clearVideoError(context, state);
        restoreInitialPreview(context, state);

        if (state.initialPreviewSrc) {
            setStatus(context.feedback, "Se mantendra el video actual.", "success");
            setSummary(context.feedback, "");
        } else {
            setStatus(context.feedback, "El video es opcional. Puedes guardar sin adjuntarlo.", "success");
            setSummary(context.feedback, "");
        }

        updateFormState(context, state);
    };

    const authorizeTrim = async (context, state) => {
        if (!state.originalFile) {
            return;
        }

        state.isProcessing = true;
        setVideoError(context, state, "Procesando el video para ajustarlo al limite permitido...", "loading");
        setSummary(context.feedback, "Este paso puede tardar varios minutos, segun el peso del archivo.", "warning");
        updateFormState(context, state);

        try {
            const result = await trimVideoToLimit(
                state.originalFile,
                state,
                (message) => setStatus(context.feedback, message, "loading")
            );

            setInputFile(context.input, result.file);
            applyValidVideo(
                context,
                state,
                result.file,
                `${result.file.name} (${formatBytes(result.file.size || 0)}). Recortado automaticamente a ~${formatSeconds(result.targetDuration)}.`
            );
        } catch (error) {
            handleOversizeVideo(context, state, state.originalFile);
            setStatus(
                context.feedback,
                error?.message || "No se pudo recortar el video automaticamente.",
                "error"
            );
            setSummary(
                context.feedback,
                "Prueba con otro archivo o vuelve a intentar el recorte con un video mas corto.",
                "error"
            );
        } finally {
            state.isProcessing = false;
            updateFormState(context, state);
        }
    };

    const handleVideoSelection = (context, state) => {
        const file = context.input.files?.[0] || null;

        if (!file) {
            applyNoVideoSelected(context, state);
            return;
        }

        if (!isAllowedVideoFile(file)) {
            showActions(context.feedback, false);
            resetOversizeChoice(context, state);
            setVideoError(context, state, "El video no es valido. Solo se permiten MP4, MOV, AVI o MPEG.");
            setSummary(context.feedback, "");
            restoreInitialPreview(context, state);
            return;
        }

        if ((file.size || 0) > MAX_VIDEO_BYTES) {
            handleOversizeVideo(context, state, file);
            return;
        }

        applyValidVideo(context, state, file);
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
            ffmpeg: null,
            initialPreviewSrc: preview.getAttribute("src") || "",
            isProcessing: false,
            originalFile: null,
            previewObjectUrl: null,
            trimmedFile: null,
            videoError: "",
        };

        const actionButtons = ensureActionButtons(context.feedback);
        if (actionButtons) {
            actionButtons.chooseButton.addEventListener("click", () => chooseAnotherVideo(context, state));
            actionButtons.trimButton.addEventListener("click", () => {
                authorizeTrim(context, state).catch((error) => {
                    console.error(error);
                });
            });
        }

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
            if (!form.checkValidity() || state.isProcessing) {
                event.preventDefault();
                form.reportValidity();
            }
        });

        showActions(context.feedback, false);
        applyNoVideoSelected(context, state);
    };

    window.KconectaVideoUploadOptimizer = { init };
})();
