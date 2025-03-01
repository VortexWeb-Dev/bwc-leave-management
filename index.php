<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BWC | Sales Rankings Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        .dropdown-content {
            display: none;
            position: absolute;
            min-width: 160px;
            z-index: 10;
            border-radius: 0.375rem;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-900">
    <div class="min-h-screen mx-auto">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <h1 class="text-xl font-semibold">BWC | Sales Rankings Dashboard</h1>
                    <div class="flex items-center space-x-4">
                        <div class="dropdown relative">
                            <button id="filterBtn" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                                <i class="fas fa-filter mr-2"></i>
                                <span id="currentFilter">All Time</span>
                                <i class="fas fa-chevron-down ml-2"></i>
                            </button>
                            <div class="dropdown-content right-0 mt-2 bg-white border border-gray-200 rounded-md shadow-lg">
                                <div class="py-1">
                                    <a href="#" class="filter-option block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-year="" data-month="">All Time</a>
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <a href="#" class="filter-option block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-year="2025" data-month="">2025 (All)</a>
                                    <a href="#" class="filter-option block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-year="2025" data-month="1">January 2025</a>
                                    <a href="#" class="filter-option block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-year="2025" data-month="2">February 2025</a>
                                    <a href="#" class="filter-option block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-year="2025" data-month="3">March 2025</a>
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <a href="#" class="filter-option block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-year="2024" data-month="">2024 (All)</a>
                                </div>
                            </div>
                        </div>
                        <button id="refreshBtn" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="fas fa-trophy text-blue-600"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-sm font-medium text-gray-500">Top Performer</h2>
                            <p id="topPerformer" class="text-xl font-semibold">Loading...</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center">
                        <div class="p-3 bg-emerald-100 rounded-full">
                            <i class="fas fa-check-circle text-emerald-600"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-sm font-medium text-gray-500">Total Closed Deals</h2>
                            <p id="totalDeals" class="text-xl font-semibold">Loading...</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center">
                        <div class="p-3 bg-violet-100 rounded-full">
                            <i class="fas fa-coins text-violet-600"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-sm font-medium text-gray-500">Total Value</h2>
                            <p id="totalValue" class="text-xl font-semibold">Loading...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rankings Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold">Sales Rankings</h2>
                    <p class="text-sm text-gray-500 mt-1" id="lastUpdated">Last updated: Loading...</p>
                </div>

                <div class="relative overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Closed Deals</th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                            </tr>
                        </thead>
                        <tbody id="rankingsTableBody" class="divide-y divide-gray-200">
                            <!-- Loading state -->
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
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-center h-12 items-center">
                    <h1 class="text-xs">
                        &copy; <?= date('Y'); ?> VortexWeb (<a href="https://vortexweb.cloud/" class="text-blue-600 hover:underline">https://vortexweb.cloud/</a>). All rights reserved.
                    </h1>
                </div>
            </div>
        </footer>
    </div>

    <script src="./script.js">
    </script>
</body>

</html>