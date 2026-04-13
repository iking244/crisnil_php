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

    const btn = e.target.closest(".safe-action");

    if (!btn) return;

    // only protect navigation links
    if (btn.tagName !== "A") return;

    if (btn.dataset.loading === "true") {
        e.preventDefault();
        return;
    }

    btn.dataset.loading = "true";

    btn.dataset.originalText = btn.innerHTML;

    btn.innerHTML = `<span class="loading-spinner"></span>`;

});