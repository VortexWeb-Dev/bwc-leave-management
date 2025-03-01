<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BWC - Employee Leave Balance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            overflow: hidden;
        }

        .scrollbar::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        .scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 5px;
        }

        .scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 5px;
        }

        .scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Loading animation */
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border-left-color: #4f46e5;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }
        }
    </style>
</head>

<body class="bg-gray-50 text-slate-900 h-screen w-screen overflow-hidden">
    <div class="flex flex-col h-full w-full">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <h1 class="text-2xl font-bold text-slate-800">BWC</h1>
                    <span class="text-gray-400">|</span>
                    <h2 class="text-xl text-slate-600">Employee Leave Balance</h2>
                </div>
            </div>
        </header>

        <!-- Main content -->
        <main class="flex-1 overflow-hidden p-6">
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm h-full flex flex-col">
                <!-- Filters -->
                <div class="p-4 border-b border-gray-200 space-y-4">
                    <div class="flex flex-wrap gap-4 items-end">
                        <!-- Search -->
                        <div class="w-full md:w-64">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input
                                type="text"
                                id="search"
                                placeholder="Search by name or ID..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <!-- Filter by remaining leave -->
                        <div class="w-full md:w-48">
                            <label for="leave-filter" class="block text-sm font-medium text-gray-700 mb-1">Remaining Leave</label>
                            <select
                                id="leave-filter"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="all">All</option>
                                <option value="no-leave">No Leave (0)</option>
                                <option value="low-leave">Low Leave (1-5)</option>
                                <option value="sufficient">Sufficient (6+)</option>
                            </select>
                        </div>

                        <!-- Sort by -->
                        <div class="w-full md:w-48">
                            <label for="sort-by" class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                            <select
                                id="sort-by"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="name-asc">Name (A-Z)</option>
                                <option value="name-desc">Name (Z-A)</option>
                                <option value="leave-asc">Leave Remaining (Low-High)</option>
                                <option value="leave-desc">Leave Remaining (High-Low)</option>
                            </select>
                        </div>

                        <!-- Reset filters -->
                        <button
                            id="reset-filters"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            Reset Filters
                        </button>
                    </div>

                    <div id="active-filters" class="flex flex-wrap gap-2 text-sm hidden">
                        <!-- Active filters will be displayed here -->
                    </div>
                </div>

                <!-- Table container -->
                <div class="flex-1 overflow-auto scrollbar p-4 relative">
                    <!-- Loading state -->
                    <div id="loading-container" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10">
                        <div class="flex flex-col items-center">
                            <div class="spinner mb-3"></div>
                            <p class="text-gray-600 font-medium">Loading data...</p>
                        </div>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Taken</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining Leave</th>
                            </tr>
                        </thead>
                        <tbody id="employee-table-body" class="bg-white divide-y divide-gray-200">
                            <!-- Table rows will be dynamically generated here -->
                        </tbody>
                    </table>
                </div>

                <!-- Footer with stats -->
                <div class="p-4 border-t border-gray-200">
                    <div class="flex flex-wrap justify-between items-center text-sm text-gray-500">
                        <div id="employee-count">Showing <span class="font-medium text-gray-900" id="visible-count">0</span> of <span class="font-medium text-gray-900" id="total-count">0</span> employees</div>
                        <div>Average remaining leave: <span class="font-medium text-gray-900" id="avg-leave">0.0</span> days</div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 px-6 py-3">
            <div class="flex justify-between items-center text-sm text-gray-500">
                <div>© <?= date('Y'); ?> BWC. All rights reserved.</div>
                <div class="flex items-center space-x-1">
                    <span>Powered by</span>
                    <a href="https://vortexweb.cloud/" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                        VortexWeb
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>
            </div>
        </footer>
    </div>

    <script src="./script.js">
    </script>
</body>

</html>