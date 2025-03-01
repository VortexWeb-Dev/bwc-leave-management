document.addEventListener("DOMContentLoaded", function () {
  let currentYear = null;
  let currentMonth = null;

  // Load initial data
  fetchRankingsData();

  // Set up filter options
  document.querySelectorAll(".filter-option").forEach((option) => {
    option.addEventListener("click", function (e) {
      e.preventDefault();
      currentYear = this.getAttribute("data-year");
      currentMonth = this.getAttribute("data-month");

      // Update filter display
      updateFilterDisplay();

      // Fetch new data
      fetchRankingsData();
    });
  });

  // Set up refresh button
  document.getElementById("refreshBtn").addEventListener("click", function () {
    fetchRankingsData();
  });

  function updateFilterDisplay() {
    let filterText = "All Time";

    if (currentYear) {
      if (currentMonth) {
        const monthNames = [
          "January",
          "February",
          "March",
          "April",
          "May",
          "June",
          "July",
          "August",
          "September",
          "October",
          "November",
          "December",
        ];
        filterText = `${monthNames[parseInt(currentMonth) - 1]} ${currentYear}`;
      } else {
        filterText = `${currentYear} (All)`;
      }
    }

    document.getElementById("currentFilter").textContent = filterText;
  }

  function fetchRankingsData() {
    // Show loading state
    document.getElementById("rankingsTableBody").innerHTML = `
            <tr>
                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                    <div class="flex justify-center items-center space-x-2">
                        <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Loading rankings...</span>
                    </div>
                </td>
            </tr>
        `;

    // Build URL with query parameters
    let url = "data.php";
    const params = new URLSearchParams();

    if (currentYear) {
      params.append("year", currentYear);
    }

    if (currentMonth) {
      params.append("month", currentMonth);
    }

    if (params.toString()) {
      url += "?" + params.toString();
    }

    // Fetch data from data.php
    fetch(url)
      .then((response) => response.json())
      .then((result) => {
        if (result.success) {
          renderRankings(result.data);
          updateStats(result.data);
          updateLastUpdated(result.timestamp);
        } else {
          showError("Failed to load rankings data");
        }
      })
      .catch((error) => {
        console.error("Error fetching data:", error);
        showError("Error connecting to the server");
      });
  }

  function renderRankings(data) {
    const tableBody = document.getElementById("rankingsTableBody");

    if (data.length === 0) {
      tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                        No data available for the selected time period
                    </td>
                </tr>
            `;
      return;
    }

    let html = "";

    data.forEach((item, index) => {
      // Determine row highlight
      let rowClass = "";
      let rankBadgeClass = "bg-gray-100 text-gray-800";

      if (item.RANK === 1) {
        rowClass = "bg-yellow-50";
        rankBadgeClass = "bg-yellow-100 text-yellow-800";
      } else if (item.RANK === 2) {
        rowClass = "bg-gray-50";
        rankBadgeClass = "bg-gray-100 text-gray-800";
      } else if (item.RANK === 3) {
        rowClass = "bg-orange-50";
        rankBadgeClass = "bg-orange-100 text-orange-800";
      }

      html += `
                <tr class="${rowClass}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${rankBadgeClass}">
                            ${item.RANK}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-medium">${item.NAME}</div>
                        <div class="text-xs text-gray-500">ID: ${
                          item.USER_ID
                        }</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        ${item.CLOSED_DEALS}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        ${formatCurrency(item.TOTAL_VALUE)}
                    </td>
                </tr>
            `;
    });

    tableBody.innerHTML = html;
  }

  function updateStats(data) {
    // Calculate total deals and value
    let totalDeals = 0;
    let totalValue = 0;
    let topPerformer = "None";

    if (data.length > 0) {
      data.forEach((item) => {
        totalDeals += parseInt(item.CLOSED_DEALS);
        totalValue += parseFloat(item.TOTAL_VALUE);
      });

      // Get top performer (first in the list since it's already sorted)
      if (data[0].CLOSED_DEALS > 0) {
        topPerformer = data[0].NAME;
      }
    }

    // Update the stats
    document.getElementById("totalDeals").textContent = totalDeals;
    document.getElementById("totalValue").textContent =
      formatCurrency(totalValue);
    document.getElementById("topPerformer").textContent = topPerformer;
  }

  function updateLastUpdated(timestamp) {
    if (timestamp) {
      document.getElementById("lastUpdated").textContent =
        "Last updated: " + formatTimestamp(timestamp);
    }
  }

  function showError(message) {
    document.getElementById("rankingsTableBody").innerHTML = `
            <tr>
                <td colspan="4" class="px-6 py-4 text-center text-red-500">
                    <i class="fas fa-exclamation-circle mr-2"></i> ${message}
                </td>
            </tr>
        `;
  }

  function formatCurrency(value) {
    return new Intl.NumberFormat("en-US", {
      style: "currency",
      currency: "AED",
      minimumFractionDigits: 0,
    }).format(value);
  }

  function formatTimestamp(timestamp) {
    return new Date(timestamp).toLocaleString();
  }
});
