const mockJobOrderData = [
    {
        productCode: "JO001",
        description: "Product A",
        quantity: 10,
        customerName: "Customer X",
        serialNumber: "001",
        date: "2024-11-01"
    },
    {
        productCode: "JO002",
        description: "Product B",
        quantity: 5,
        customerName: "Customer Y",
        serialNumber: "002",
        date: "2024-11-10"
    },
    {
        productCode: "JO003",
        description: "Product C",
        quantity: 8,
        customerName: "Customer Z",
        serialNumber: "003",
        date: "2024-11-15"
    }
];

// Function to fetch and display data
function fetchJobOrderData() {
    const fromDate = document.getElementById("fromDate").value;
    const toDate = document.getElementById("toDate").value;

    if (!fromDate || !toDate) {
        alert("Please select both 'From Date' and 'To Date'.");
        return;
    }

    // Filter mock data based on date range
    const filteredData = mockJobOrderData.filter(item => {
        return item.date >= fromDate && item.date <= toDate;
    });

    const tableBody = document.getElementById("jobOrderTable");
    tableBody.innerHTML = ""; // Clear existing rows

    if (filteredData.length === 0) {
        tableBody.innerHTML = "<tr><td colspan='7' style='text-align:center;'>No records found</td></tr>";
        return;
    }

    filteredData.forEach((item, index) => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${item.productCode}</td>
            <td>${item.description}</td>
            <td>${item.quantity}</td>
            <td>${item.customerName}</td>
            <td>${item.serialNumber}</td>
            <td>${item.date}</td>
            <td>
                <button class="action-btn edit-btn" onclick="editJobOrder(${index})">EDIT</button>
                <button class="action-btn delete-btn" onclick="deleteJobOrder(${index})">DELETE</button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Edit functionality
function editJobOrder(index) {
    alert(`Edit functionality for Job Order #${index + 1} (Implement logic here)`);
}

// Delete functionality
function deleteJobOrder(index) {
    if (confirm(`Are you sure you want to delete Job Order #${index + 1}?`)) {
        alert(`Job Order #${index + 1} deleted successfully!`);
    }
}