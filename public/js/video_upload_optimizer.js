(function () {
    const MAX_INPUT_BYTES = 200 * 1024 * 1024;
    const DURATION_WARNING_SECONDS = 120;
    const MAX_FINAL_VIDEO_BYTES = 32 * 1024 * 1024;
    const MAX_TOTAL_POST_BYTES = 40 * 1024 * 1024;
    const OUTPUT_MIME = "video/mp4";
    const OUTPUT_EXTENSION = "mp4";
    const OUTPUT_NAME = "optimized-video.mp4";
    const VIDEO_BUTTON_TEXT = "Subir video (max. 200MB antes de optimizar) (opcional)";
    const OUTPUT_TOTAL_BYTES_PER_SECOND = ((1500 + 96) * 1000) / 8;
    const PRETRIM_SAFETY_RATIO = 0.92;
    const INPUT_ACCEPT =
        ".mp4,.mov,.avi,.mpeg,video/mp4,video/quicktime,video/x-msvideo,video/mpeg";
    const ALLOWED_MIME_TYPES = new Set([
        "video/mp4",
        "video/quicktime",
        "video/x-msvideo",
        "video/mpeg",
    ]);
    const ALLOWED_EXTENSIONS = new Set(["mp4", "mov", "avi", "mpeg", "mpg"]);
    const FFMPEG_ASSET_BASE = `${window.location.origin}/vendor/ffmpeg`;
    const FFMPEG_CONFIG = {
        coreURL: `${FFMPEG_ASSET_BASE}/ffmpeg-core.js`,
        wasmURL: `${FFMPEG_ASSET_BASE}/ffmpeg-core.wasm`,
    };

    const ffmpegRuntime = {
        scriptPromise: null,
        loadPromise: null,
        instance: null,
    };

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

    const formatDuration = (seconds) => {
        const totalSeconds = Math.max(0, Math.round(seconds));
        const minutes = Math.floor(totalSeconds / 60);
        const remain = totalSeconds % 60;
        return `${minutes}:${String(remain).padStart(2, "0")}`;
    };

    const escapeSelectorValue = (value) => {
        if (window.CSS && typeof window.CSS.escape === "function") {
            return window.CSS.escape(value);
        }

        return String(value).replace(/["\\]/g, "\\$&");
    };

    const getVideoExtension = (file) => {
        const parts = String(file?.name || "").toLowerCase().split(".");
        return parts.length > 1 ? parts.pop() : "";
    };

    const isAllowedVideoFile = (file) => {
        if (!file) {
            return false;
        }

        const mimeType = file.type || "";
        const extension = getVideoExtension(file);
        return ALLOWED_MIME_TYPES.has(mimeType) || ALLOWED_EXTENSIONS.has(extension);
    };

    const createDataTransferWithFile = (file) => {
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        return dataTransfer.files;
    };

    const getUtf8Bytes = (value) => new TextEncoder().encode(String(value ?? "")).length;

    const ensureButtonLabel = (button) => {
        if (!button) {
            return null;
        }

        let label = button.querySelector("[data-video-button-label]");
        if (label) {
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
                '<div class="video-upload-progress" data-video-progress hidden>',
                '  <div class="video-upload-progress-bar" data-video-progress-bar></div>',
                "</div>",
            ].join("");
            container.appendChild(feedback);
        }

        const help = feedback.querySelector("[data-video-help]");
        const status = feedback.querySelector("[data-video-status]");
        const summary = feedback.querySelector("[data-video-summary]");
        const progress = feedback.querySelector("[data-video-progress]");
        const progressBar = feedback.querySelector("[data-video-progress-bar]");

        if (help && !help.textContent.trim()) {
            help.textContent =
                "El video se valida y optimiza en tu dispositivo. Max. 200MB antes de optimizar. Si el resultado final no cabe, se recortara automaticamente para intentar que pueda subirse. Los videos de mas de 2 minutos pueden tardar mas.";
        }

        return { feedback, help, status, summary, progress, progressBar };
    };

    const ensurePayloadElements = (form, submitButton) => {
        const submitContainer =
            submitButton?.closest(".box") || submitButton?.parentElement || form;
        let payload = submitContainer.querySelector("[data-payload-feedback]");
        if (!payload) {
            payload = document.createElement("div");
            payload.className = "payload-size-feedback";
            payload.dataset.payloadFeedback = "1";
            payload.innerHTML = [
                '<p class="payload-size-summary" data-payload-summary></p>',
                '<p class="payload-size-status" data-payload-status></p>',
            ].join("");
            submitContainer.insertBefore(payload, submitButton || submitContainer.firstChild);
        }

        return {
            container: payload,
            summary: payload.querySelector("[data-payload-summary]"),
            status: payload.querySelector("[data-payload-status]"),
        };
    };

    const getSubmitButtons = (form) => Array.from(form.querySelectorAll('button[type="submit"]'));

    const syncSubmitButtons = (buttons, state) => {
        buttons.forEach((button) => {
            if (!button) {
                return;
            }

            if (!button.dataset.videoOptimizerSnapshot) {
                button.dataset.videoOptimizerSnapshot = button.disabled ? "1" : "0";
            }

            const baseDisabled = button.dataset.videoOptimizerSnapshot === "1";
            button.disabled = baseDisabled || state.processing || state.payloadTooLarge;
            button.classList.toggle("is-video-processing", state.processing);
            button.classList.toggle("is-size-blocked", state.payloadTooLarge);
        });
    };

    const setStatus = (elements, message, kind) => {
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

    const setPayloadStatus = (elements, summary, status = "", kind = "") => {
        if (elements?.summary) {
            elements.summary.textContent = summary || "";
        }

        if (elements?.status) {
            elements.status.textContent = status || "";
            elements.status.className = "payload-size-status";
            if (kind) {
                elements.status.classList.add(`is-${kind}`);
            }
        }
    };

    const setProgress = (elements, progress, visible) => {
        if (!elements?.progress || !elements?.progressBar) {
            return;
        }

        if (!visible) {
            elements.progress.hidden = true;
            elements.progressBar.style.width = "0%";
            return;
        }

        elements.progress.hidden = false;
        const bounded = Math.min(100, Math.max(0, Math.round(progress * 100)));
        elements.progressBar.style.width = `${bounded}%`;
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

    const readVideoMetadata = (file) =>
        new Promise((resolve, reject) => {
            const video = document.createElement("video");
            const objectUrl = URL.createObjectURL(file);
            const cleanup = () => {
                URL.revokeObjectURL(objectUrl);
                video.removeAttribute("src");
                video.load();
            };

            video.preload = "metadata";
            video.muted = true;

            video.onloadedmetadata = () => {
                const duration = Number(video.duration);
                cleanup();
                if (!Number.isFinite(duration) || duration <= 0) {
                    reject(new Error("No se pudo leer la duración del video."));
                    return;
                }

                resolve({
                    duration,
                    width: Number(video.videoWidth) || 0,
                    height: Number(video.videoHeight) || 0,
                });
            };

            video.onerror = () => {
                cleanup();
                reject(new Error("No se pudieron leer los metadatos del video."));
            };

            video.src = objectUrl;
        });

    const readFileAsUint8Array = async (file) => new Uint8Array(await file.arrayBuffer());

    const loadFfmpegScript = () => {
        if (window.FFmpegWASM && window.FFmpegWASM.FFmpeg) {
            return Promise.resolve();
        }

        if (ffmpegRuntime.scriptPromise) {
            return ffmpegRuntime.scriptPromise;
        }

        ffmpegRuntime.scriptPromise = new Promise((resolve, reject) => {
            const existing = document.querySelector(
                `script[data-ffmpeg-loader="1"][src="${escapeSelectorValue(
                    `${FFMPEG_ASSET_BASE}/ffmpeg.js`
                )}"]`
            );
            if (existing) {
                existing.addEventListener("load", () => resolve(), { once: true });
                existing.addEventListener("error", () => reject(new Error("No se pudo cargar FFmpeg.")), {
                    once: true,
                });
                return;
            }

            const script = document.createElement("script");
            script.src = `${FFMPEG_ASSET_BASE}/ffmpeg.js`;
            script.dataset.ffmpegLoader = "1";
            script.onload = () => resolve();
            script.onerror = () => reject(new Error("No se pudo cargar FFmpeg."));
            document.head.appendChild(script);
        });

        return ffmpegRuntime.scriptPromise;
    };

    const getFfmpeg = async (progressHandler) => {
        await loadFfmpegScript();

        if (!window.FFmpegWASM || !window.FFmpegWASM.FFmpeg) {
            throw new Error("FFmpeg no está disponible en este navegador.");
        }

        if (!ffmpegRuntime.instance) {
            ffmpegRuntime.instance = new window.FFmpegWASM.FFmpeg();
        }

        if (!ffmpegRuntime.instance.loaded) {
            if (!ffmpegRuntime.loadPromise) {
                ffmpegRuntime.loadPromise = ffmpegRuntime.instance.load(FFMPEG_CONFIG);
            }

            await ffmpegRuntime.loadPromise;
        }

        if (progressHandler) {
            ffmpegRuntime.instance.on("progress", progressHandler);
        }

        return ffmpegRuntime.instance;
    };

    const getTotalUploadBytes = (form) => {
        let total = 0;
        Array.from(form.querySelectorAll('input[type="file"]')).forEach((input) => {
            Array.from(input.files || []).forEach((file) => {
                total += file.size || 0;
            });
        });
        return total;
    };

    const getOtherUploadBytes = (form, excludedInput) => {
        let total = 0;

        Array.from(form.querySelectorAll('input[type="file"]')).forEach((input) => {
            if (input === excludedInput) {
                return;
            }

            Array.from(input.files || []).forEach((file) => {
                total += file.size || 0;
            });
        });

        return total;
    };

    const getAllowedVideoBytes = (form, videoInput) =>
        Math.min(
            MAX_FINAL_VIDEO_BYTES,
            Math.max(0, MAX_TOTAL_POST_BYTES - getOtherUploadBytes(form, videoInput) - estimateFormTextBytes(form))
        );

    const estimateFormTextBytes = (form) => {
        let total = 0;
        const formData = new FormData(form);

        Array.from(formData.entries()).forEach(([key, value]) => {
            total += getUtf8Bytes(key);
            if (value instanceof File) {
                return;
            }

            total += getUtf8Bytes(value);
        });

        return total;
    };

    const getEstimatedFormBytes = (form) => estimateFormTextBytes(form) + getTotalUploadBytes(form);

    const getFallbackTotalBytes = (form, videoInput, originalVideoFile) => {
        let total = estimateFormTextBytes(form);

        Array.from(form.querySelectorAll('input[type="file"]')).forEach((input) => {
            if (input === videoInput) {
                if (originalVideoFile) {
                    total += originalVideoFile.size || 0;
                }
                return;
            }

            Array.from(input.files || []).forEach((file) => {
                total += file.size || 0;
            });
        });

        return total;
    };

    const buildOutputFileName = (file) => {
        const fileName = String(file?.name || OUTPUT_NAME);
        const nameParts = fileName.split(".");
        if (nameParts.length > 1) {
            nameParts.pop();
        }

        const baseName = nameParts.join(".").trim() || "video";
        return `${baseName}.${OUTPUT_EXTENSION}`;
    };

    const getVideoMimeFromUpload = (file) => file?.type || "";

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
        } else {
            context.preview.removeAttribute("src");
            context.preview.style.display = "none";
            showPlaceholderImage(context.container);
        }
    };

    const showPreviewFromFile = (context, state, file) => {
        clearObjectUrl(state);
        state.previewObjectUrl = URL.createObjectURL(file);
        context.preview.src = state.previewObjectUrl;
        context.preview.style.display = "block";
        hidePlaceholderImage(context.container);
    };

    const clearSelectedVideo = (context, state) => {
        context.input.value = "";
        state.mode = null;
        state.selectedFile = null;
        state.trimmedDurationSeconds = null;
        restoreInitialPreview(context, state);
        setSummary(context.feedback, "");
        context.form.dispatchEvent(new CustomEvent("kconecta:media-change", { bubbles: true }));
    };

    const fallbackToOriginalFile = (context, state, originalFile, reason) => {
        const mimeType = getVideoMimeFromUpload(originalFile);
        if (!ALLOWED_MIME_TYPES.has(mimeType) && !ALLOWED_EXTENSIONS.has(getVideoExtension(originalFile))) {
            throw new Error("El formato del video no está permitido.");
        }

        if ((originalFile.size || 0) > MAX_FINAL_VIDEO_BYTES) {
            throw new Error(reason || "El video original sigue superando el límite de 32MB por archivo.");
        }

        const totalWithOriginal = getFallbackTotalBytes(context.form, context.input, originalFile);
        if (totalWithOriginal > MAX_TOTAL_POST_BYTES) {
            throw new Error("El envío total supera el límite permitido de 40MB.");
        }

        context.input.files = createDataTransferWithFile(originalFile);
        showPreviewFromFile(context, state, originalFile);
        state.mode = "fallback";
        state.selectedFile = originalFile;
        state.trimmedDurationSeconds = null;
        setProgress(context.feedback, 0, false);
        setStatus(
            context.feedback,
            `No se pudo optimizar el video. Se subira el original (${formatBytes(
                originalFile.size || 0
            )}) porque ya cumple el limite del servidor.`,
            "success"
        );
    };

    const optimizeVideo = async (file, context, options = {}) => {
        const progressHandler = ({ progress }) => {
            if (typeof progress === "number" && Number.isFinite(progress)) {
                setProgress(context.feedback, progress, true);
                setStatus(
                    context.feedback,
                    options.maxDurationSeconds
                        ? "Recortando y optimizando video en tu dispositivo..."
                        : "Optimizando video en tu dispositivo...",
                    "loading"
                );
            }
        };

        const ffmpeg = await getFfmpeg(progressHandler);
        const inputName = `input.${getVideoExtension(file) || "mp4"}`;
        const outputName = OUTPUT_NAME;

        try {
            await ffmpeg.writeFile(inputName, await readFileAsUint8Array(file));
            const command = [
                "-i",
                inputName,
                "-map",
                "0:v:0",
                "-map",
                "0:a:0?",
            ];

            if (options.maxDurationSeconds) {
                command.push("-t", String(options.maxDurationSeconds));
            }

            command.push(
                "-vf",
                "scale=1280:720:force_original_aspect_ratio=decrease:force_divisible_by=2,fps=24",
                "-c:v",
                "libx264",
                "-preset",
                "veryfast",
                "-pix_fmt",
                "yuv420p",
                "-b:v",
                "1500k",
                "-c:a",
                "aac",
                "-b:a",
                "96k",
                "-movflags",
                "+faststart",
                outputName
            );

            const exitCode = await ffmpeg.exec(command, 600000);

            if (exitCode !== 0) {
                throw new Error("FFmpeg no pudo optimizar el video.");
            }

            const outputData = await ffmpeg.readFile(outputName);
            if (!(outputData instanceof Uint8Array) || outputData.length === 0) {
                throw new Error("No se pudo generar el video optimizado.");
            }

            return new File([outputData], buildOutputFileName(file), {
                type: OUTPUT_MIME,
                lastModified: Date.now(),
            });
        } finally {
            try {
                await ffmpeg.deleteFile(inputName);
            } catch (error) {
                console.debug(error);
            }

            try {
                await ffmpeg.deleteFile(outputName);
            } catch (error) {
                console.debug(error);
            }

            ffmpeg.off("progress", progressHandler);
        }
    };

    const validateSelection = async (file) => {
        if (!isAllowedVideoFile(file)) {
            throw new Error("El formato del video no está permitido.");
        }

        if ((file.size || 0) > MAX_INPUT_BYTES) {
            throw new Error("El video no puede superar 200MB antes de optimizarse.");
        }

        const metadata = await readVideoMetadata(file);
        return {
            ...metadata,
            durationWarning:
                metadata.duration > DURATION_WARNING_SECONDS
                    ? `Este video dura ${formatDuration(
                          metadata.duration
                      )}. Se intentara optimizar igual, pero puede tardar mas en este dispositivo.`
                    : "",
        };
    };

    const estimateTrimDuration = (sourceDuration, currentBytes, targetBytes) => {
        if (!Number.isFinite(sourceDuration) || sourceDuration <= 0) {
            return 0;
        }

        if (!Number.isFinite(currentBytes) || currentBytes <= 0 || !Number.isFinite(targetBytes) || targetBytes <= 0) {
            return 0;
        }

        const ratio = Math.min(1, targetBytes / currentBytes);
        const seconds = Math.floor(sourceDuration * ratio * 0.92);
        return Math.max(5, Math.min(Math.floor(sourceDuration) - 1, seconds));
    };

    const estimateInitialDurationLimit = (sourceDuration, allowedBytes) => {
        if (!Number.isFinite(sourceDuration) || sourceDuration <= 0 || !Number.isFinite(allowedBytes) || allowedBytes <= 0) {
            return 0;
        }

        const safeSeconds = Math.floor((allowedBytes * PRETRIM_SAFETY_RATIO) / OUTPUT_TOTAL_BYTES_PER_SECOND);
        return Math.max(5, Math.min(Math.floor(sourceDuration), safeSeconds));
    };

    const optimizeWithAutoTrim = async (file, context, metadata) => {
        const allowedBytes = getAllowedVideoBytes(context.form, context.input);
        if (allowedBytes <= 0) {
            throw new Error("El envio total supera el limite permitido de 40MB antes de adjuntar el video.");
        }

        const initialDurationLimit = estimateInitialDurationLimit(metadata.duration, allowedBytes);
        const shouldPreTrim =
            initialDurationLimit >= 5 &&
            Number.isFinite(metadata.duration) &&
            metadata.duration > initialDurationLimit;

        if (shouldPreTrim) {
            setStatus(
                context.feedback,
                `Por el tamano disponible del registro, optimizaremos el video recortandolo primero a ${formatDuration(
                    initialDurationLimit
                )}.`,
                "warning"
            );
        }

        const optimizedFile = await optimizeVideo(file, context, shouldPreTrim ? {
            maxDurationSeconds: initialDurationLimit,
        } : {});
        if ((optimizedFile.size || 0) <= allowedBytes) {
            return {
                file: optimizedFile,
                mode: shouldPreTrim ? "trimmed" : "optimized",
                trimmedDurationSeconds: shouldPreTrim ? initialDurationLimit : null,
            };
        }

        if (!Number.isFinite(metadata.duration) || metadata.duration <= 5) {
            throw new Error("El video optimizado excede los limites permitidos.");
        }

        let sourceDuration = shouldPreTrim ? initialDurationLimit : metadata.duration;
        let candidateBytes = optimizedFile.size || 0;
        let trimDuration = estimateTrimDuration(sourceDuration, candidateBytes, allowedBytes);

        for (let attempt = 0; attempt < 3 && trimDuration >= 5; attempt += 1) {
            setProgress(context.feedback, 0, true);
            setStatus(
                context.feedback,
                `El video completo no cabe. Lo recortaremos automaticamente a ${formatDuration(
                    trimDuration
                )} para que se pueda subir.`,
                "warning"
            );

            const trimmedFile = await optimizeVideo(file, context, {
                maxDurationSeconds: trimDuration,
            });

            if ((trimmedFile.size || 0) <= allowedBytes) {
                return {
                    file: trimmedFile,
                    mode: "trimmed",
                    trimmedDurationSeconds: trimDuration,
                };
            }

            sourceDuration = trimDuration;
            candidateBytes = trimmedFile.size || 0;
            const nextDuration = estimateTrimDuration(sourceDuration, candidateBytes, allowedBytes);
            if (nextDuration >= trimDuration) {
                break;
            }

            trimDuration = nextDuration;
        }

        throw new Error("Ni optimizando ni recortando automaticamente el video logra caber en el limite permitido.");
    };

    const buildContext = (inputId, previewId) => {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (!input || !preview) {
            return null;
        }

        const form = input.closest("form");
        const container =
            input.closest(".container-main-template-input-simple") ||
            preview.closest(".container-main-template-input-simple");
        if (!form || !container) {
            return null;
        }

        const labelButton = container.querySelector(".btn-upload-image");
        const feedback = ensureFeedbackElements(container);
        const payload = ensurePayloadElements(form, form.querySelector(".container-button-save"));
        return {
            input,
            preview,
            form,
            container,
            feedback,
            payload,
            submitButtons: getSubmitButtons(form),
            labelButton,
        };
    };

    const init = ({ inputId, previewId }) => {
        const context = buildContext(inputId, previewId);
        if (!context || context.input.dataset.videoOptimizerReady === "1") {
            return;
        }

        context.input.dataset.videoOptimizerReady = "1";
        context.input.accept = INPUT_ACCEPT;

        if (context.labelButton) {
            const label = ensureButtonLabel(context.labelButton);
            if (label) {
                label.textContent = VIDEO_BUTTON_TEXT;
            }
        }

        const state = {
            previewObjectUrl: null,
            initialPreviewSrc: context.preview.getAttribute("src") || "",
            mode: null,
            selectedFile: null,
            trimmedDurationSeconds: null,
            summaryDurationSeconds: null,
            payloadTooLarge: false,
            processing: false,
        };

        const updatePayloadSummary = () => {
            const estimatedBytes = getEstimatedFormBytes(context.form);
            const summary = `Tamano del registro: Texto + imagenes + video: ${formatBytes(estimatedBytes)}`;
            if (estimatedBytes > MAX_TOTAL_POST_BYTES) {
                state.payloadTooLarge = true;
                setPayloadStatus(
                    context.payload,
                    summary,
                    "El registro es muy grande para enviarse. Elimina algunas imagenes o reduce/recorta el video para continuar.",
                    "error"
                );
            } else {
                state.payloadTooLarge = false;
                setPayloadStatus(
                    context.payload,
                    summary,
                    `Limite total disponible: ${formatBytes(MAX_TOTAL_POST_BYTES)}.`,
                    "success"
                );
            }

            syncSubmitButtons(context.submitButtons, state);
            return !state.payloadTooLarge;
        };

        const validateCurrentPayload = () => {
            const isValid = updatePayloadSummary();
            if (!isValid) {
                setStatus(
                    context.feedback,
                    `El envio total supera el limite permitido de 40MB (${formatBytes(getEstimatedFormBytes(context.form))}).`,
                    "error"
                );
            }

            return isValid;
        };

        context.form.addEventListener("kconecta:media-change", () => {
            if (!state.selectedFile || state.processing) {
                updatePayloadSummary();
                return;
            }

            if (validateCurrentPayload()) {
                if (state.mode === "optimized") {
                    setStatus(
                        context.feedback,
                        `Video optimizado y listo para subir (${formatBytes(state.selectedFile.size || 0)}).`,
                        "success"
                    );
                } else if (state.mode === "trimmed") {
                    setStatus(
                        context.feedback,
                        `Video recortado a ${formatDuration(
                            state.trimmedDurationSeconds || 0
                        )} y listo para subir (${formatBytes(state.selectedFile.size || 0)}).`,
                        "success"
                    );
                } else if (state.mode === "fallback") {
                    setStatus(
                        context.feedback,
                        `Video valido y listo para subir sin optimizar (${formatBytes(
                            state.selectedFile.size || 0
                        )}).`,
                        "success"
                    );
                }

                if (Number.isFinite(state.summaryDurationSeconds) && state.summaryDurationSeconds > 0) {
                    setSummary(
                        context.feedback,
                        state.mode === "fallback"
                            ? `Video listo: Tamano: ${formatBytes(
                                  state.selectedFile.size || 0
                              )} | Duracion: ${formatDuration(state.summaryDurationSeconds)}`
                            : `Video optimizado: Tamano: ${formatBytes(
                                  state.selectedFile.size || 0
                              )} | Duracion: ${formatDuration(state.summaryDurationSeconds)}`,
                        "success"
                    );
                }
            }
        });

        const onPayloadChange = (event) => {
            if (event?.target === context.input) {
                return;
            }

            updatePayloadSummary();
        };

        context.form.addEventListener("input", onPayloadChange);
        context.form.addEventListener("change", onPayloadChange);

        context.form.addEventListener("submit", (event) => {
            if (state.processing) {
                event.preventDefault();
                setStatus(context.feedback, "Espera a que termine la optimizacion del video.", "loading");
                return;
            }

            if (!validateCurrentPayload()) {
                event.preventDefault();
            }
        });

        context.input.addEventListener("change", async (event) => {
            const file = event.target.files[0];
            if (!file) {
                clearSelectedVideo(context, state);
                setProgress(context.feedback, 0, false);
                setStatus(context.feedback, "", "");
                setSummary(context.feedback, "");
                return;
            }

            state.processing = true;
            context.input.disabled = true;
            syncSubmitButtons(context.submitButtons, state);
            setProgress(context.feedback, 0, false);
            setStatus(context.feedback, "Preparando video...", "loading");
            setSummary(context.feedback, "");

            try {
                const metadata = await validateSelection(file);
                const validationMessage = metadata.durationWarning
                    ? `${metadata.durationWarning} Archivo detectado: ${formatBytes(file.size || 0)}.`
                    : `Video valido (${formatDuration(metadata.duration)}, ${formatBytes(
                          file.size || 0
                      )}). Preparando optimizacion...`;

                setStatus(
                    context.feedback,
                    validationMessage,
                    metadata.durationWarning ? "warning" : "loading"
                );

                let finalFile = null;
                let finalMode = "optimized";
                let trimmedDurationSeconds = null;

                try {
                    const optimizationResult = await optimizeWithAutoTrim(file, context, metadata);
                    finalFile = optimizationResult.file;
                    finalMode = optimizationResult.mode;
                    trimmedDurationSeconds = optimizationResult.trimmedDurationSeconds;
                } catch (optimizationError) {
                    fallbackToOriginalFile(context, state, file, optimizationError.message);
                    finalFile = file;
                    finalMode = "fallback";
                }

                if (finalMode !== "fallback") {
                    context.input.files = createDataTransferWithFile(finalFile);
                    showPreviewFromFile(context, state, finalFile);
                    state.mode = finalMode;
                    state.selectedFile = finalFile;
                    state.trimmedDurationSeconds = trimmedDurationSeconds;
                    const finalMetadata = await readVideoMetadata(finalFile);
                    state.summaryDurationSeconds = finalMetadata.duration;
                    setProgress(context.feedback, 1, true);
                    setStatus(
                        context.feedback,
                        finalMode === "trimmed"
                            ? `Video recortado a ${formatDuration(
                                  trimmedDurationSeconds || 0
                              )} y listo para subir (${formatBytes(finalFile.size || 0)}).`
                            : `Video optimizado y listo para subir (${formatBytes(finalFile.size || 0)}).`,
                        "success"
                    );
                    setSummary(
                        context.feedback,
                        `Video optimizado: Tamano: ${formatBytes(
                            finalFile.size || 0
                        )} | Duracion: ${formatDuration(finalMetadata.duration)}`,
                        "success"
                    );
                } else {
                    const finalMetadata = await readVideoMetadata(file);
                    state.summaryDurationSeconds = finalMetadata.duration;
                    setSummary(
                        context.feedback,
                        `Video listo: Tamano: ${formatBytes(file.size || 0)} | Duracion: ${formatDuration(
                            finalMetadata.duration
                        )}`,
                        "success"
                    );
                }

                context.form.dispatchEvent(new CustomEvent("kconecta:media-change", { bubbles: true }));
            } catch (error) {
                clearSelectedVideo(context, state);
                setProgress(context.feedback, 0, false);
                setStatus(context.feedback, error.message || "No se pudo procesar el video.", "error");
                setSummary(context.feedback, "");
            } finally {
                state.processing = false;
                context.input.disabled = false;
                syncSubmitButtons(context.submitButtons, state);
                updatePayloadSummary();
            }
        });

        updatePayloadSummary();
    };

    window.KconectaVideoUploadOptimizer = { init };
})();
