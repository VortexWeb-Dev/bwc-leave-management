document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("search");
  const leaveFilter = document.getElementById("leave-filter");
  const sortBy = document.getElementById("sort-by");
  const resetButton = document.getElementById("reset-filters");
  const activeFilters = document.getElementById("active-filters");
  const tableBody = document.getElementById("employee-table-body");
  const visibleCountEl = document.getElementById("visible-count");
  const totalCountEl = document.getElementById("total-count");
  const avgLeaveEl = document.getElementById("avg-leave");
  const loadingContainer = document.getElementById("loading-container");

  // Store the data and rows for filtering operations
  let employeeData = [];
  let rows = [];

  // Fetch data from data.php
  fetchData();

  function fetchData() {
    // Show loading animation
    loadingContainer.style.display = "flex";

    fetch("./data.php")
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then((data) => {
        employeeData = data;
        renderTable(data);

        // Hide loading animation with a slight delay for smoother transition
        setTimeout(() => {
          loadingContainer.style.display = "none";
          document.querySelector("table").classList.add("fade-in");
        }, 300);
      })
      .catch((error) => {
        console.error("Error fetching data:", error);
        tableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-red-500">
                            Error loading data. Please try again later.
                        </td>
                    </tr>
                `;
        loadingContainer.style.display = "none";
      });
  }

  function renderTable(data) {
    // Clear existing rows
    tableBody.innerHTML = "";

    // Generate table rows
    data.forEach((employee) => {
      const row = document.createElement("tr");
      row.className = "hover:bg-gray-50 transition-colors";
      row.dataset.id = employee.id;
      row.dataset.name = employee.name.toLowerCase();
      row.dataset.remaining = employee.remaining_leave;

      // Determine badge color based on remaining leave
      const badgeClass =
        employee.remaining_leave == 0
          ? "bg-red-100 text-red-800"
          : employee.remaining_leave <= 5
          ? "bg-yellow-100 text-yellow-800"
          : "bg-green-100 text-green-800";

      row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${employee.id}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${employee.name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">${employee.leave_taken}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass}">
                        ${employee.remaining_leave}
                    </span>
                </td>
            `;

      tableBody.appendChild(row);
    });

    // Update rows reference
    rows = Array.from(tableBody.querySelectorAll("tr"));

    // Update counts
    totalCountEl.textContent = data.length;
    updateEmployeeCount();

    // Apply initial filters
    applyFilters();
  }

  function updateEmployeeCount() {
    const visibleRows = rows.filter((row) => !row.classList.contains("hidden"));
    visibleCountEl.textContent = visibleRows.length;

    // Update average leave
    const totalRemaining = visibleRows.reduce(
      (sum, row) => sum + parseInt(row.dataset.remaining, 10),
      0
    );
    const avgLeave =
      visibleRows.length > 0
        ? (totalRemaining / visibleRows.length).toFixed(1)
        : "0.0";
    avgLeaveEl.textContent = avgLeave;
  }

  function applyFilters() {
    const searchTerm = searchInput.value.toLowerCase();
    const leaveOption = leaveFilter.value;
    const sortOption = sortBy.value;

    // Reset visibility
    rows.forEach((row) => row.classList.remove("hidden"));

    // Apply search filter
    if (searchTerm) {
      rows.forEach((row) => {
        const id = row.dataset.id;
        const name = row.dataset.name;
        if (!id.includes(searchTerm) && !name.includes(searchTerm)) {
          row.classList.add("hidden");
        }
      });
    }

    // Apply leave filter
    if (leaveOption !== "all") {
      rows.forEach((row) => {
        const remaining = parseInt(row.dataset.remaining, 10);
        if (
          (leaveOption === "no-leave" && remaining !== 0) ||
          (leaveOption === "low-leave" && (remaining === 0 || remaining > 5)) ||
          (leaveOption === "sufficient" && remaining < 6)
        ) {
          row.classList.add("hidden");
        }
      });
    }

    // Apply sorting
    const sortedRows = [...rows].sort((a, b) => {
      if (sortOption === "name-asc") {
        return a.dataset.name.localeCompare(b.dataset.name);
      } else if (sortOption === "name-desc") {
        return b.dataset.name.localeCompare(a.dataset.name);
      } else if (sortOption === "leave-asc") {
        return (
          parseInt(a.dataset.remaining, 10) - parseInt(b.dataset.remaining, 10)
        );
      } else if (sortOption === "leave-desc") {
        return (
          parseInt(b.dataset.remaining, 10) - parseInt(a.dataset.remaining, 10)
        );
      }
      return 0;
    });

    // Re-append sorted rows
    sortedRows.forEach((row) => tableBody.appendChild(row));

    // Update active filters display
    updateActiveFilters();

    // Update count
    updateEmployeeCount();
  }

  function updateActiveFilters() {
    const activeFiltersList = [];

    if (searchInput.value) {
      activeFiltersList.push(`Search: "${searchInput.value}"`);
    }

    if (leaveFilter.value !== "all") {
      const leaveText = leaveFilter.options[leaveFilter.selectedIndex].text;
      activeFiltersList.push(`Leave: ${leaveText}`);
    }

    if (sortBy.value !== "name-asc") {
      const sortText = sortBy.options[sortBy.selectedIndex].text;
      activeFiltersList.push(`Sort: ${sortText}`);
    }

    if (activeFiltersList.length > 0) {
      activeFilters.innerHTML = activeFiltersList
        .map(
          (filter) =>
            `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-800">${filter}</span>`
        )
        .join("");
      activeFilters.classList.remove("hidden");
    } else {
      activeFilters.classList.add("hidden");
      activeFilters.innerHTML = "";
    }
  }

  function resetFilters() {
    searchInput.value = "";
    leaveFilter.value = "all";
    sortBy.value = "name-asc";

    rows.forEach((row) => row.classList.remove("hidden"));
    updateActiveFilters();
    applyFilters();
  }

  // Event listeners
  searchInput.addEventListener("input", applyFilters);
  leaveFilter.addEventListener("change", applyFilters);
  sortBy.addEventListener("change", applyFilters);
  resetButton.addEventListener("click", resetFilters);

  // Add refresh button event listener
  document
    .getElementById("refresh-button")
    ?.addEventListener("click", fetchData);
});
