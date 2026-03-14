document.addEventListener("submit", function (e) {

    let form = e.target;

    if (!form.classList.contains("auto-loading-form")) return;

    let btn = form.querySelector("button[type='submit']");

    if (!btn) return;

    if (btn.disabled) {
        e.preventDefault();
        return;
    }

    btn.dataset.originalText = btn.innerHTML;

    btn.disabled = true;

    btn.innerHTML = `
        <span class="loading-spinner"></span>
    `;

});

document.addEventListener("click", function (e) {

    let btn = e.target.closest(".safe-action");

    if (!btn) return;

    if (btn.dataset.locked === "true") {
        e.preventDefault();
        return;
    }

    btn.dataset.locked = "true";

    btn.dataset.originalText = btn.innerHTML;

    btn.innerHTML = `
        <span class="loading-spinner"></span>
    `;

});
