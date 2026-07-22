<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Enterprise OT Management') - ระบบบริหารจัดการการขอทำงานล่วงเวลา</title>
    
    <!-- Google Fonts: Sarabun & Prompt -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome & Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --primary-bg: #f8fafc;
            --sidebar-bg: #ffffff;
            --sidebar-border: #e2e8f0;
            --sidebar-hover: #f1f5f9;
            --sidebar-text: #475569;
            --sidebar-heading-text: #1e293b;
            --accent-color: #2563eb;
            --accent-hover: #1d4ed8;
            --font-family: 'Sarabun', sans-serif;
            --font-heading: 'Prompt', sans-serif;
        }

        body {
            font-family: var(--font-family);
            background-color: #f8fafc;
            color: #1e293b;
        }

        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: var(--font-heading);
        }

        /* Clean White Sidebar Styling */
        #wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        #sidebar-wrapper {
            min-width: 260px;
            max-width: 260px;
            background-color: #ffffff;
            color: #1e293b;
            border-right: 1px solid #e2e8f0;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
        }

        #sidebar-wrapper .sidebar-heading {
            padding: 1.1rem 1.25rem;
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            border-bottom: 1px solid #e2e8f0;
            background: #ffffff;
        }

        #sidebar-wrapper .list-group-item {
            background-color: transparent;
            color: #475569;
            border: none;
            padding: 0.75rem 1.25rem;
            margin: 2px 10px;
            font-size: 0.92rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        #sidebar-wrapper .list-group-item:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }

        #sidebar-wrapper .list-group-item.active {
            background-color: #2563eb;
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        }

        /* Page Content Styling */
        #page-content-wrapper {
            width: 100%;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        .navbar-custom {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03);
            padding: 0.75rem 1.5rem;
        }

        .card-custom {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            background: #ffffff;
        }

        .badge-role {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
            font-weight: 600;
            border-radius: 6px;
        }

        /* Pagination Styling */
        .pagination svg {
            max-width: 1rem !important;
            max-height: 1rem !important;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div id="wrapper">
        <!-- Sidebar -->
        @include('layouts.partials.sidebar')

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <!-- Navbar -->
            @include('layouts.partials.navbar')

            <!-- Main Content Container -->
            <main class="container-fluid p-4">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                        <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <div>{{ session('warning') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="bi bi-x-circle-fill me-2 fs-5"></i>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
