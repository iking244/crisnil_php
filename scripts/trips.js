let unassignedJobs = [];

document.addEventListener("DOMContentLoaded", function () {

    const modal = document.getElementById("dispatchActionModal");
    const createTab = document.getElementById("createTab");
    const addBtn = document.getElementById("addJobBtn");
    const tableBody = document.querySelector("#tripJobsTable tbody");

    if (!modal || !createTab || !addBtn || !tableBody) return;

    // 🔥 Scope warehouse select to CREATE TAB ONLY
    const warehouseSelect = createTab.querySelector("select[name='warehouse_id']");

    if (!warehouseSelect) {
        console.error("Warehouse select not found inside Create tab.");
        return;
    }

    function getSelectedJobIds() {
        return Array.from(
            document.querySelectorAll("#tripJobsTable select")
        )
            .map(s => s.value)
            .filter(v => v !== "");
    }

    // =============================
    // Fetch jobs when warehouse changes
    // =============================
    warehouseSelect.addEventListener("change", function () {

        const warehouseId = this.value;

        if (!warehouseId) {
            unassignedJobs = [];
            tableBody.innerHTML = "";
            return;
        }

        fetch(`../controllers/trips_controller.php?ajax=get_jobs&warehouse_id=${warehouseId}`)
            .then(res => res.json())
            .then(data => {

                unassignedJobs = Array.isArray(data) ? data : [];

                tableBody.innerHTML = "";

                if (unassignedJobs.length > 0) {
                    addJobRow();
                }
            })
            .catch(err => {
                console.error("Failed to fetch jobs:", err);
                unassignedJobs = [];
                tableBody.innerHTML = "";
            });
    });

    // =============================
    // Rebuild dropdown options
    // =============================
    function rebuildDropdownOptions() {

        const selected = getSelectedJobIds();
        const selects = document.querySelectorAll("#tripJobsTable select");

        selects.forEach(select => {

            const currentValue = select.value;

            select.innerHTML = '<option value="">Select Unassigned Job</option>';

            unassignedJobs.forEach(job => {

                if (!selected.includes(String(job.id)) || String(job.id) === currentValue) {

                    const option = document.createElement("option");
                    option.value = job.id;
                    option.textContent = job.label;
                    select.appendChild(option);
                }
            });

            select.value = currentValue;
        });
    }

    // =============================
    // Add Job Row
    // =============================
    function addJobRow() {

        if (!unassignedJobs.length ||
            getSelectedJobIds().length >= unassignedJobs.length) {
            return;
        }

        const row = document.createElement("tr");

        row.innerHTML = `
            <td>
                <select name="job_ids[]" class="form-select"></select>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger remove-row">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;

        tableBody.appendChild(row);

        rebuildDropdownOptions();

        row.querySelector("select")
            .addEventListener("change", rebuildDropdownOptions);
    }

    addBtn.addEventListener("click", addJobRow);

    document.addEventListener("click", function (e) {
        if (e.target.closest(".remove-row")) {
            e.target.closest("tr").remove();
            rebuildDropdownOptions();
        }
    });

    modal.addEventListener("shown.bs.modal", function () {
        if (warehouseSelect.value && tableBody.children.length === 0) {
            addJobRow();
        }
    });

});