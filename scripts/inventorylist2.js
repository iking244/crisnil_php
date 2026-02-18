document.getElementById("searchButton").addEventListener("click", function () {
    const asOfDate = document.getElementById("asOfDate").value;

    if (!asOfDate) {
        alert("Please select a date.");
        return;
    }

    // Simulate fetching data (replace with actual API/Database call)
    const mockData = [
        {
            productCode: "P001",
            description: "Product 1",
            quantity: 10,
            manufacturerCode: "M001",
            manufacturer: "Manufacturer A",
            distributor: "Distributor A",
            status: "Available",
            manufacturingDate: "2023-01-01",
            expiryDate: "2025-01-01",
            cost: 100,
            date: "2023-11-01"
        },
        {
            productCode: "P002",
            description: "Product 2",
            quantity: 20,
            manufacturerCode: "M002",
            manufacturer: "Manufacturer B",
            distributor: "Distributor B",
            status: "Out of Stock",
            manufacturingDate: "2023-03-15",
            expiryDate: "2025-03-15",
            cost: 200,
            date: "2023-11-10"
        }
    ];

    // Update the table
    const tableBody = document.getElementById("reportTableBody");
    tableBody.innerHTML = ""; // Clear previous data

    let totalCost = 0;

    mockData.forEach((item, index) => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${item.productCode}</td>
            <td>${item.description}</td>
            <td>${item.quantity}</td>
            <td>${item.manufacturerCode}</td>
            <td>${item.manufacturer}</td>
            <td>${item.distributor}</td>
            <td>${item.status}</td>
            <td>${item.manufacturingDate}</td>
            <td>${item.expiryDate}</td>
            <td>${item.cost}</td>
            <td>${item.date}</td>
            <td>
                <button class="edit-button" onclick="editItem(${index})">Edit</button>
                <button class="delete-button" onclick="deleteItem(${index})">Delete</button>
            </td>
        `;
        tableBody.appendChild(row);
        totalCost += item.cost;
    });

    document.getElementById("totalCost").value = totalCost;
    document.getElementById("itemsFound").value = mockData.length;
});

// Edit Functionality
function editItem(index) {
    alert(`Edit functionality for row ${index + 1} (Add edit logic here)`);

    // You can add a modal or inline editing functionality here.
    // For example, show an edit form with pre-filled values for this row.
}

// Delete Functionality
function deleteItem(index) {
    if (confirm(`Are you sure you want to delete row ${index + 1}?`)) {
        const tableBody = document.getElementById("reportTableBody");
        tableBody.deleteRow(index);

        // Optionally, also update your data source (e.g., database or array).
        alert(`Row ${index + 1} has been deleted.`);
    }
}

document.getElementById("exportButton").addEventListener("click", function () {
    alert("Export functionality coming soon!");
});
