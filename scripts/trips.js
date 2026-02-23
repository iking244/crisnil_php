let unassignedJobs = [];

document.addEventListener("DOMContentLoaded", function () {

    const modal = document.getElementById("dispatchActionModal");
    const createTab = document.getElementById("createTab");
    const addBtn = document.getElementById("addJobBtn");
    const tableBody = document.querySelector("#tripJobsTable tbody");

    if (!modal || !createTab || !addBtn || !tableBody) return;

    const warehouseSelect = createTab.querySelector("select[name='warehouse_id']");
    if (!warehouseSelect) return;

    function getSelectedJobIds() {
        return Array.from(tableBody.querySelectorAll("select"))
            .map(s => s.value)
            .filter(Boolean);
    }

    async function loadJobsByWarehouse(warehouseId) {

        try {
            const response = await fetch(
                `../controllers/trips_controller.php?ajax=get_jobs&warehouse_id=${encodeURIComponent(warehouseId)}`
            );

            if (!response.ok) {
                throw new Error("Network response not OK");
            }

            const data = await response.json();

            unassignedJobs = Array.isArray(data) ? data : [];

            tableBody.innerHTML = "";

            if (unassignedJobs.length > 0) {
                addJobRow();
            }

        } catch (error) {
            console.error("Failed to fetch jobs:", error);
            unassignedJobs = [];
            tableBody.innerHTML = "";
        }
    }

    warehouseSelect.addEventListener("change", function () {

        const warehouseId = this.value;

        if (!warehouseId) {
            unassignedJobs = [];
            tableBody.innerHTML = "";
            return;
        }

        loadJobsByWarehouse(warehouseId);
    });

    function rebuildDropdownOptions() {

        const selected = getSelectedJobIds();
        const selects = tableBody.querySelectorAll("select");

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
            loadJobsByWarehouse(warehouseSelect.value);
        }
    });

});


const smartWarehouseSelect = document.getElementById("smartWarehouse");
const runSmartAssignBtn = document.getElementById("runSmartAssign");
const smartSummary = document.getElementById("smartSummary");

if (smartWarehouseSelect && runSmartAssignBtn && smartSummary) {

    runSmartAssignBtn.addEventListener("click", async function () {

        runSmartAssignBtn.disabled = true;
        runSmartAssignBtn.innerText = "Processing...";

        try {

            const response = await fetch(
                "../api/logistics_order/auto_assign_clusters.php",
                { method: "POST" }
            );

            if (!response.ok) {
                throw new Error("Server error");
            }

            const data = await response.json();

            if (data.success) {

                alert("Smart assignment completed!");
                window.location.href = "trips.php";

            } else {
                alert(data.error || "Smart assignment failed.");
            }

        } catch (error) {
            console.error("Smart assignment error:", error);
            alert("Something went wrong.");
        }

        runSmartAssignBtn.disabled = false;
        runSmartAssignBtn.innerText = "Run Smart Assignment";

    });
}

document.addEventListener("DOMContentLoaded", function () {
    loadRecentTrips();
});

async function loadRecentTrips() {

    try {

        const response = await fetch(
            "../controllers/trips_controller.php?ajax=recent_trips"
        );

        const data = await response.json();

        const tbody = document.getElementById("recentTripsTable");
        tbody.innerHTML = "";

        data.forEach(trip => {

            const statusClass = trip.status.toLowerCase();
            const formattedDate = trip.eta
                ? new Date(trip.eta).toLocaleString()
                : "-";

            const row = `
                <tr>
                    <td><strong>#T${trip.trip_id}</strong></td>
                    <td>${trip.stops}</td>
                    <td>${trip.truck_plate_number ?? '-'}</td>
                    <td>${trip.driver_name ?? '-'}</td>
                    <td>
                        <span class="status-badge ${statusClass}">
                            ${formatStatus(trip.status)}
                        </span>
                    </td>
                    <td>${formattedDate}</td>
                </tr>
            `;

            tbody.insertAdjacentHTML("beforeend", row);
        });

    } catch (error) {
        console.error("Failed to load trips:", error);
    }
}

function formatStatus(status) {
    return status
        .replace("_", " ")
        .replace(/\b\w/g, c => c.toUpperCase());
}