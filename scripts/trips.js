document.addEventListener("DOMContentLoaded", function () {

    const modal = document.getElementById("dispatchActionModal");
    const addBtn = document.getElementById("addJobBtn");
    const tableBody = document.querySelector("#tripJobsTable tbody");

    if (!modal || !addBtn || !tableBody) return;

    function getSelectedJobIds() {
        return Array.from(
            document.querySelectorAll("#tripJobsTable select")
        )
            .map(s => s.value)
            .filter(v => v !== "");
    }

    function rebuildDropdownOptions() {

        const selected = getSelectedJobIds();
        const selects = document.querySelectorAll("#tripJobsTable select");

        selects.forEach(select => {

            const currentValue = select.value;

            select.innerHTML = '<option value="">Select Unassigned Job</option>';

            if (!window.unassignedJobs) return;

            window.unassignedJobs.forEach(job => {

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

    function addJobRow() {

        if (!window.unassignedJobs || 
            getSelectedJobIds().length >= window.unassignedJobs.length) {
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
        if (tableBody.children.length === 0) {
            addJobRow();
        }
    });

});