<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $title ?? 'Dashboard' ?> - <?= APP_NAME ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fef2f3',
                            100: '#fde6e8',
                            200: '#fbd0d5',
                            300: '#f7aab2',
                            400: '#f17a89',
                            500: '#e74c60',
                            600: '#d02e47',
                            700: '#8b1538',
                            800: '#7a1530',
                            900: '#6a142b',
                        },
                        roman: {
                            marble: '#faf9f7',
                            gold: '#d4af37',
                            wine: '#8b1538',
                            navy: '#1e3a5f',
                            bronze: '#6b4423',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Custom CSS Styles -->
    <style>
        /* Custom components - Pure CSS */
        .stat-card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            padding: 1.5rem;
            border-left-width: 4px;
        }
        .stat-card-primary { border-left-color: #d02e47; }
        .stat-card-success { border-left-color: #10b981; }
        .stat-card-warning { border-left-color: #f59e0b; }
        .stat-card-danger { border-left-color: #ef4444; }

        .card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }
        .card-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 1.5rem 1.5rem 1rem 1.5rem;
        }
        .card-body {
            padding: 1.5rem;
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
        }

        .table {
            width: 100%;
            font-size: 0.875rem;
            text-align: left;
            color: #374151;
            border-collapse: collapse;
        }
        .table thead {
            background-color: #f9fafb;
            font-size: 0.75rem;
            color: #374151;
            text-transform: uppercase;
        }
        .table th {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            text-align: left;
            vertical-align: middle;
        }
        .table td {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        .table tbody tr {
            transition: background-color 0.2s;
        }
        .table tbody tr:hover {
            background-color: #f9fafb;
        }
        
        /* Ensure proper spacing and alignment */
        .min-w-full {
            min-width: 100%;
        }
        .divide-y > * + * {
            border-top-width: 1px;
        }
        .divide-gray-200 > * + * {
            border-color: #e5e7eb;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 40;
            width: 16rem;
            height: 100vh;
            transition: transform 0.3s;
            background-color: #1f2937;
        }
        .sidebar-nav {
            height: 100%;
            padding: 1rem 0.75rem;
            overflow-y: auto;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.5rem;
            color: #d1d5db;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
        .sidebar-link:hover {
            background-color: #374151;
            color: white;
        }
        .sidebar-link.active {
            background-color: #374151;
            color: white;
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            border-width: 1px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background-color: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
        }
        .alert-error {
            background-color: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }
        .alert-warning {
            background-color: #fefce8;
            border-color: #fde68a;
            color: #854d0e;
        }
        .alert-info {
            background-color: #eff6ff;
            border-color: #bfdbfe;
            color: #1e40af;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-primary {
            background-color: #d02e47;
            color: white;
        }
        .btn-primary:hover {
            background-color: #8b1538;
        }
        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #4b5563;
        }
        .btn-outline {
            background-color: transparent;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        .btn-outline:hover {
            background-color: #f3f4f6;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .badge-primary { background-color: #fde6e8; color: #7a1530; }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }

        /* Utility badge colors */
        .bg-green-100 { background-color: #d1fae5 !important; }
        .text-green-800 { color: #065f46 !important; }
        .bg-red-100 { background-color: #fee2e2 !important; }
        .text-red-800 { color: #991b1b !important; }
        .bg-gray-100 { background-color: #f3f4f6 !important; }
        .text-gray-800 { color: #1f2937 !important; }
        .bg-purple-100 { background-color: #e9d5ff !important; }
        .text-purple-800 { color: #6b21a8 !important; }
        .bg-blue-100 { background-color: #dbeafe !important; }
        .text-blue-800 { color: #1e40af !important; }
        .bg-indigo-100 { background-color: #e0e7ff !important; }
        .text-indigo-800 { color: #3730a3 !important; }
        .bg-yellow-100 { background-color: #fef3c7 !important; }
        .text-yellow-800 { color: #854d0e !important; }
        
        /* Stat card colors */
        .bg-blue-500 { background-color: #3b82f6 !important; }
        .bg-green-500 { background-color: #10b981 !important; }
        .bg-purple-500 { background-color: #a855f7 !important; }
        .bg-yellow-500 { background-color: #eab308 !important; }
        .text-white { color: #ffffff !important; }
        
        /* Border colors */
        .border-gray-200 { border-color: #e5e7eb !important; }

        .form-input {
            width: 100%;
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
        .form-input:focus {
            outline: none;
            border-color: #d02e47;
            box-shadow: 0 0 0 3px rgba(208, 46, 71, 0.1);
        }

        .dropdown-menu {
            position: absolute;
            right: 0;
            z-index: 10;
            margin-top: 0.5rem;
            width: 12rem;
            transform-origin: top right;
            border-radius: 0.375rem;
            background-color: white;
            padding: 0.25rem 0;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            border: 1px solid #e5e7eb;
        }
        .dropdown-item {
            display: block;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            color: #374151;
            transition: background-color 0.2s;
        }
        .dropdown-item:hover {
            background-color: #f3f4f6;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
    </style>

    <!-- CSS -->
    <link rel="stylesheet" href="/css/output.css">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900" x-data="{ sidebarOpen: false, darkMode: false }" :class="{ 'dark': darkMode }">

    <!-- Sidebar -->
    <?php
    $user = $user ?? $_SESSION['user'] ?? [];
    include __DIR__ . '/../components/sidebar.php';
    ?>

    <!-- Main Content -->
    <div class="lg:pl-64">

        <!-- Top Navigation -->
        <?php include __DIR__ . '/../components/navbar.php'; ?>

        <!-- Page Content -->
        <main class="py-6 px-4 sm:px-6 lg:px-8">

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash'])): ?>
                <?php
                    $flash = $_SESSION['flash'];
                    unset($_SESSION['flash']);
                ?>
                <div x-data="{ show: true }"
                     x-show="show"
                     x-transition
                     class="mb-6 alert alert-<?= $flash['type'] ?>">
                    <div class="flex items-center justify-between">
                        <p><?= htmlspecialchars($flash['message']) ?></p>
                        <button @click="show = false" class="ml-4 text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Breadcrumbs -->
            <?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
                <nav class="breadcrumb mb-6">
                    <?php foreach ($breadcrumbs as $index => $crumb): ?>
                        <div class="breadcrumb-item">
                            <?php if ($index > 0): ?>
                                <span class="breadcrumb-separator">/</span>
                            <?php endif; ?>
                            <?php if (isset($crumb['url'])): ?>
                                <a href="<?= $crumb['url'] ?>" class="text-primary-600 hover:text-primary-700">
                                    <?= htmlspecialchars($crumb['label']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-gray-500"><?= htmlspecialchars($crumb['label']) ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>

            <!-- Page Header -->
            <?php if (isset($pageTitle)): ?>
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        <?= htmlspecialchars($pageTitle) ?>
                    </h1>
                    <?php if (isset($pageDescription)): ?>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            <?= htmlspecialchars($pageDescription) ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Main Content Area -->
            <?= $content ?>

        </main>
    </div>

    <!-- Footer -->
    <footer class="lg:pl-64 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-4 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row justify-between items-center text-sm text-gray-600 dark:text-gray-400">
            <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. Todos los derechos reservados.</p>
            <p>Versión 1.0.0</p>
        </div>
    </footer>

    <!-- Scripts -->
    <!-- app-bundle.js comentado - usando Alpine.js desde CDN en su lugar -->
    <!-- <script src="/js/app-bundle.js"></script> -->

    <!-- Additional Scripts -->
    <?php if (isset($scripts)): ?>
        <?= $scripts ?>
    <?php endif; ?>
</body>
</html>
