(function () {
    const input = document.getElementById('cadastral-address-input');
    const areaInput = document.getElementById('cadastral-area-input');
    const button = document.getElementById('cadastral-submit-btn');

    if (!input || !areaInput || !button) {
        return;
    }

    const lockButton = () => {
        button.disabled = true;
        button.setAttribute('aria-disabled', 'true');
    };

    const unlockButton = () => {
        button.disabled = false;
        button.setAttribute('aria-disabled', 'false');
    };

    lockButton();

    const syncButtonState = () => {
        const hasAddress = input.value.trim().length > 0;
        const areaValue = Number(areaInput.value);
        const hasValidArea = Number.isFinite(areaValue) && areaValue > 0;

        if (hasAddress && hasValidArea) {
            unlockButton();
            return;
        }

        lockButton();
    };

    input.addEventListener('input', syncButtonState);
    areaInput.addEventListener('input', syncButtonState);

    button.addEventListener('click', () => {
        lockButton();
    });
})();
