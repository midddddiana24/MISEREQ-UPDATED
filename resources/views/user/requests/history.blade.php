@php
$stats = [
'total' => auth()->user()->serviceRequests()->count(),
'completed' => auth()->user()->serviceRequests()->where('status', 'completed')->count(),
'cancelled' => auth()->user()->serviceRequests()->where('status', 'cancelled')->count(),
'rejected' => auth()->user()->serviceRequests()->where('status', 'rejected')->count(),
'closed' => auth()->user()->serviceRequests()->where('status', 'closed')->count(),
];
$currentFilter = request('filter', 'completed');
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Request History - MIS Service Request System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #D1884F;
            --primary-dark: #B87540;
            --secondary: #2C3E50;
            --light: #F8F9FA;
            --white: #FFFFFF;
            --shadow: rgba(0, 0, 0, 0.1);
            --warning: #f39c12;
            --info: #9b59b6;
            --success: #27ae60;
            --danger: #e74c3c;
        }

        @keyframes fadeInUp {
            from {
                -webkit-transform: translateY(20px);
                -ms-transform: translateY(20px);
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                -webkit-transform: translateY(0);
                -ms-transform: translateY(0);
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes scaleIn {
            from {
                -webkit-transform: scale(0.95);
                -ms-transform: scale(0.95);
                transform: scale(0.95);
                opacity: 0;
            }

            to {
                -webkit-transform: scale(1);
                -ms-transform: scale(1);
                transform: scale(1);
                opacity: 1;
            }
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', sans-serif;
            background: #F9FAFB;
            min-height: 100vh;
            color: #374151;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            display: flex;
        }

        .main-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: var(--white);
            border-right: 1px solid #E5E7EB;
            padding: 1.5rem 0;
            z-index: 1000;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            transition: all 0.3s ease;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .sidebar.collapsed {
            width: 80px;
            padding: 2rem 0.5rem;
        }

        .sidebar.hidden {
            -webkit-transform: translateX(-100%);
            -ms-transform: translateX(-100%);
            transform: translateX(-100%);
        }

        .sidebar-toggle {
            position: absolute;
            top: 1rem;
            right: -40px;
            background: var(--primary);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
            font-size: 1.2rem;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            transition: all 0.3s ease;
            display: block;
            z-index: 1001;
            box-shadow: 0 2px 8px var(--shadow);
        }

        .sidebar-toggle:hover {
            background: var(--primary-dark);
            box-shadow: 0 4px 12px var(--shadow);
        }

        .sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid #E5E7EB;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .sidebar-header {
            padding: 0 0.5rem 1.5rem;
            text-align: center;
        }

        .logo-section {
            display: -webkit-flex;
            display: -moz-flex;
            display: flex;
            -webkit-align-items: center;
            -moz-align-items: center;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .logo-section {
            -webkit-justify-content: center;
            -moz-justify-content: center;
            justify-content: center;
            gap: 0;
        }

        .logo {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 12px;
            display: -webkit-flex;
            display: -moz-flex;
            display: flex;
            -webkit-align-items: center;
            -moz-align-items: center;
            align-items: center;
            -webkit-justify-content: center;
            -moz-justify-content: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.5rem;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(209, 136, 79, 0.3);
            -webkit-flex-shrink: 0;
            -moz-flex-shrink: 0;
            flex-shrink: 0;
        }

        .logo:hover {
            -webkit-transform: scale(1.05);
            -ms-transform: scale(1.05);
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(209, 136, 79, 0.4);
        }

        .logo-text {
            font-size: 1rem;
            font-weight: 600;
            color: var(--secondary);
            line-height: 1.3;
            -webkit-transition: opacity 0.3s ease;
            -moz-transition: opacity 0.3s ease;
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .logo-text {
            display: none;
        }

        .user-info {
            display: -webkit-flex;
            display: -moz-flex;
            display: flex;
            -webkit-align-items: center;
            -moz-align-items: center;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: linear-gradient(135deg, #FFF8F0, #FFEEDD);
            border-radius: 12px;
            margin: 1.5rem;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            transition: all 0.3s ease;
            animation: fadeInUp 0.5s ease-out 0.1s both;
        }

        .sidebar.collapsed .user-info {
            -webkit-flex-direction: column;
            -moz-flex-direction: column;
            flex-direction: column;
            margin: 1rem 0.5rem;
            padding: 0.8rem 0.5rem;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 50%;
            display: -webkit-flex;
            display: -moz-flex;
            display: flex;
            -webkit-align-items: center;
            -moz-align-items: center;
            align-items: center;
            -webkit-justify-content: center;
            -moz-justify-content: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            -webkit-flex-shrink: 0;
            -moz-flex-shrink: 0;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(209, 136, 79, 0.3);
        }

        .user-details {
            -webkit-flex: 1;
            -moz-flex: 1;
            flex: 1;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .user-details {
            display: none;
        }

        .user-details h4 {
            color: var(--secondary);
            font-size: 0.95rem;
            margin-bottom: 0.2rem;
        }

        .user-details p {
            color: #666;
            font-size: 0.8rem;
        }

        .sidebar-menu {
            list-style: none;
            padding: 1.5rem 0;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .sidebar-menu {
            padding: 0.5rem 0;
        }

        .menu-item {
            margin-bottom: 0.5rem;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            transition: all 0.3s ease;
        }

        .menu-item a {
            display: -webkit-flex;
            display: -moz-flex;
            display: flex;
            -webkit-align-items: center;
            -moz-align-items: center;
            align-items: center;
            gap: 1rem;
            padding: 0.9rem 1.5rem;
            color: #666;
            text-decoration: none;
            -webkit-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            -moz-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            border-radius: 0 8px 8px 0;
        }

        .sidebar.collapsed .menu-item a {
            padding: 0.9rem;
            -webkit-justify-content: center;
            -moz-justify-content: center;
            justify-content: center;
            gap: 0;
        }

        .menu-item a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: var(--primary);
            -webkit-transform: scaleY(0);
            -ms-transform: scaleY(0);
            transform: scaleY(0);
            -webkit-transition: -webkit-transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            -moz-transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 0 4px 4px 0;
        }

        .menu-item a:hover,
        .menu-item a.active {
            background: rgba(209, 136, 79, 0.1);
            color: var(--primary);
            padding-left: 1.8rem;
        }

        .sidebar.collapsed .menu-item a:hover,
        .sidebar.collapsed .menu-item a.active {
            padding-left: 0.9rem;
            background: rgba(209, 136, 79, 0.15);
        }

        .menu-item a:hover::before,
        .menu-item a.active::before {
            -webkit-transform: scaleY(1);
            -ms-transform: scaleY(1);
            transform: scaleY(1);
        }

        .menu-icon {
            font-size: 1.3rem;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            transition: all 0.3s ease;
            -webkit-flex-shrink: 0;
            -moz-flex-shrink: 0;
            flex-shrink: 0;
        }

        .menu-item a:hover .menu-icon,
        .menu-item a.active .menu-icon {
            -webkit-transform: scale(1.1);
            -ms-transform: scale(1.1);
            transform: scale(1.1);
        }

        .content {
            margin-left: 260px;
            flex: 1;
            padding: 2rem 1rem;
            -webkit-transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            -moz-transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
            width: calc(100% - 260px);
            overflow-x: hidden;
        }

        .sidebar.collapsed~.content {
            margin-left: 80px;
            width: calc(100% - 80px);
        }

        .menu-toggle {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.6rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.2rem;
            margin-bottom: 1rem;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            transition: all 0.3s ease;
            display: none;
        }

        .menu-toggle:hover {
            background: var(--primary-dark);
        }

        .content {
            margin-left: 260px;
            flex: 1;
            padding: 2rem;
        }

        .header {
            display: -webkit-flex;
            display: -moz-flex;
            display: flex;
            -webkit-justify-content: space-between;
            -moz-justify-content: space-between;
            justify-content: space-between;
            -webkit-align-items: center;
            -moz-align-items: center;
            align-items: center;
            margin-bottom: 2rem;
        }

        h1 {
            color: var(--secondary);
            font-size: 2rem;
        }

        .back-btn {
            display: inline-block;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            -webkit-transition: color 0.3s ease;
            -moz-transition: color 0.3s ease;
            transition: color 0.3s ease;
        }

        .back-btn:hover {
            color: var(--primary-dark);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 8px var(--shadow);
            display: flex;
            align-items: center;
            gap: 1rem;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            transition: all 0.3s ease;
            animation: scaleIn 0.4s ease-out;
        }

        .stat-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .stat-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .stat-card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .stat-card:nth-child(4) {
            animation-delay: 0.4s;
        }

        .stat-card:hover {
            -webkit-transform: translateY(-4px);
            -ms-transform: translateY(-4px);
            transform: translateY(-4px);
            box-shadow: 0 4px 12px var(--shadow);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.total {
            background: rgba(209, 136, 79, 0.1);
            color: var(--primary);
        }

        .stat-icon.completed {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success);
        }

        .stat-icon.cancelled {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger);
        }

        .stat-icon.closed {
            background: rgba(44, 62, 80, 0.1);
            color: var(--secondary);
        }

        .stat-content h3 {
            color: #999;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }

        .stat-content .number {
            color: var(--secondary);
            font-size: 1.8rem;
            font-weight: 700;
        }

        .filters {
            display: flex;
            gap: 0.8rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 0.6rem 1.2rem;
            border: 2px solid #ddd;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            color: var(--secondary);
            text-decoration: none;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            transition: all 0.3s ease;
        }

        .filter-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .filter-btn:focus {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }

        .filter-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px var(--shadow);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background: var(--light);
            color: var(--secondary);
            font-weight: 600;
            white-space: nowrap;
        }

        tbody tr {
            -webkit-transition: background 0.3s ease;
            -moz-transition: background 0.3s ease;
            transition: background 0.3s ease;
        }

        tbody tr:hover {
            background: rgba(209, 136, 79, 0.05);
        }

        .status-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status-badge.completed {
            background: rgba(39, 174, 96, 0.2);
            color: #229954;
        }

        .status-badge.cancelled {
            background: rgba(231, 76, 60, 0.2);
            color: #c0392b;
        }

        .status-badge.rejected {
            background: rgba(231, 76, 60, 0.2);
            color: #c0392b;
        }

        .status-badge.closed {
            background: rgba(44, 62, 80, 0.2);
            color: #2c3e50;
        }

        .category-badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            background: rgba(209, 136, 79, 0.1);
            color: var(--primary);
        }

        .btn-view {
            background: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-view:hover {
            background: var(--primary-dark);
            -webkit-transform: translateY(-2px);
            -ms-transform: translateY(-2px);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(209, 136, 79, 0.3);
        }

        .btn-view:focus {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 0.5rem 0.8rem;
            border-radius: 6px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: var(--secondary);
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background: var(--light);
        }

        .pagination .active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .search-container {
            margin-bottom: 1.5rem;
            display: -webkit-flex;
            display: -moz-flex;
            display: flex;
            gap: 0.5rem;
        }

        .search-input {
            -webkit-flex: 1;
            -moz-flex: 1;
            flex: 1;
            padding: 0.8rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.95rem;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            transition: all 0.3s ease;
            max-width: 300px;
        }

        .search-input:focus {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(209, 136, 79, 0.1);
        }

        .search-input::placeholder {
            color: #999;
        }

        .new-request-btn {
            background: var(--primary);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            transition: all 0.3s ease;
        }

        .new-request-btn:hover {
            background: var(--primary-dark);
            -webkit-transform: translateY(-2px);
            -ms-transform: translateY(-2px);
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .content {
                margin-left: 260px;
                padding: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            h1 {
                font-size: 1.7rem;
            }

            th,
            td {
                padding: 0.8rem;
            }
        }

        @media (max-width: 768px) {
            .main-wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 1.5rem 1rem;
                display: none;
            }

            .sidebar.active {
                display: block;
            }

            .content {
                margin-left: 0;
                padding: 1rem 0.5rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }

            .stat-card {
                padding: 1rem;
            }

            h1 {
                font-size: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .header {
                flex-direction: column;
                gap: 1rem;
            }

            .new-request-btn {
                width: 100%;
                text-align: center;
            }

            .search-container {
                flex-direction: column;
            }

            .search-input {
                max-width: 100%;
                width: 100%;
            }

            .filters {
                gap: 0.5rem;
                flex-wrap: wrap;
            }

            .filter-btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
                flex: 1 1 auto;
                min-width: 80px;
            }

            table {
                font-size: 0.9rem;
                min-width: 550px;
            }

            th,
            td {
                padding: 0.7rem;
            }

            .status-badge {
                font-size: 0.8rem;
                padding: 0.3rem 0.6rem;
            }

            .btn-view {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }

            .filters {
                gap: 0.5rem;
            }

            .filter-btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 640px) {
            .content {
                padding: 0.8rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 0.8rem;
            }

            .stat-card {
                padding: 0.8rem;
            }

            h1 {
                font-size: 1.3rem;
                margin-bottom: 1.2rem;
            }

            .header {
                flex-direction: column;
                gap: 0.8rem;
            }

            .new-request-btn {
                width: 100%;
            }

            .search-container {
                flex-direction: column;
                margin-bottom: 1rem;
            }

            .search-input {
                max-width: 100%;
                width: 100%;
                padding: 0.7rem 0.8rem;
            }

            .filters {
                gap: 0.4rem;
                display: grid;
                grid-template-columns: 1fr 1fr;
            }

            .filter-btn {
                padding: 0.4rem 0.6rem;
                font-size: 0.8rem;
                width: 100%;
            }

            table {
                font-size: 0.85rem;
                min-width: 500px;
            }

            th,
            td {
                padding: 0.6rem;
            }

            .status-badge {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }

            .btn-view {
                padding: 0.35rem 0.7rem;
                font-size: 0.75rem;
                width: 100%;
                display: block;
            }

            .pagination {
                gap: 0.3rem;
            }

            .pagination a,
            .pagination span {
                padding: 0.4rem 0.6rem;
                font-size: 0.85rem;
            }

            .empty-state {
                padding: 2rem 0.5rem;
            }

            .empty-state-icon {
                font-size: 3rem;
            }
        }

        @media (max-width: 480px) {
            .filters {
                grid-template-columns: 1fr;
            }

            .filter-btn {
                padding: 0.5rem 1rem;
            }

            h1 {
                font-size: 1.1rem;
            }

            .new-request-btn {
                padding: 0.7rem 1rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .content {
                padding: 0.5rem;
            }

            .header {
                flex-direction: column;
                gap: 0.8rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }

            .stat-card {
                padding: 0.6rem;
            }

            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
            }

            .stat-content h3 {
                font-size: 0.75rem;
            }

            .stat-content .number {
                font-size: 1.4rem;
            }

            h1 {
                font-size: 1.1rem;
                margin-bottom: 1rem;
            }

            table {
                font-size: 0.8rem;
                min-width: 450px;
            }

            th,
            td {
                padding: 0.5rem;
            }

            .status-badge {
                font-size: 0.7rem;
                padding: 0.2rem 0.4rem;
            }

            .btn-view {
                padding: 0.3rem 0.6rem;
                font-size: 0.7rem;
                display: block;
                text-align: center;
                margin-top: 0.3rem;
            }

            .filters {
                gap: 0.3rem;
                flex-direction: column;
            }

            .filter-btn {
                padding: 0.5rem 1rem;
                font-size: 0.75rem;
                width: 100%;
            }

            .pagination {
                gap: 0.2rem;
            }

            .pagination a,
            .pagination span {
                padding: 0.3rem 0.5rem;
                font-size: 0.8rem;
            }

            .empty-state {
                padding: 1.5rem 0.5rem;
            }

            .empty-state-icon {
                font-size: 2.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <button class="sidebar-toggle" id="sidebarToggle" title="Collapse sidebar">◀</button>

            <div class="sidebar-header">
                <div class="logo-section">
                    <div class="logo">M</div>
                    <div class="logo-text">MIS Service Request</div>
                </div>
            </div>

            <div class="user-info">
                <div class="user-avatar">{{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}</div>
                <div class="user-details">
                    <h4>{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</h4>
                    <p>{{ auth()->user()->office ?? 'User' }}</p>
                </div>
            </div>

            <ul class="sidebar-menu">
                <li class="menu-item">
                    <a href="{{ route('dashboard') }}" class="{{ request()->route()->getName() === 'dashboard' ? 'active' : '' }}">
                        <span class="menu-icon">📊</span>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="{{ route('requests.create') }}" class="{{ request()->route()->getName() === 'requests.create' ? 'active' : '' }}">
                        <span class="menu-icon">➕</span>
                        <span>New Request</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="{{ route('user.messenger') }}" class="{{ request()->route()->getName() === 'user.messenger' ? 'active' : '' }}">
                        <span class="menu-icon">💬</span>
                        <span>Messages</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="{{ route('requests.index') }}" class="{{ request()->route()->getName() === 'requests.index' ? 'active' : '' }}">
                        <span class="menu-icon">📜</span>
                        <span>History</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="{{ route('user.settings') }}" class="{{ request()->route()->getName() === 'user.settings' ? 'active' : '' }}">
                        <span class="menu-icon">⚙️</span>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>

        </aside>

        <script></script>

        <!-- Main Content -->
        <div class="content" role="main" aria-label="Request History Content">
            <button class="menu-toggle" id="menuToggle" onclick="toggleSidebar()" aria-label="Toggle Navigation Menu">☰</button>
            <div class="header">
                <h1>Request History</h1>
                <a href="{{ route('requests.create') }}" class="new-request-btn" aria-label="Create New Service Request">+ New Request</a>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon total">📊</div>
                    <div class="stat-content">
                        <h3>Total Requests</h3>
                        <div class="number">{{ $stats['total'] }}</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon completed">✅</div>
                    <div class="stat-content">
                        <h3>Completed</h3>
                        <div class="number">{{ $stats['completed'] }}</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon cancelled">❌</div>
                    <div class="stat-content">
                        <h3>Cancelled</h3>
                        <div class="number">{{ $stats['cancelled'] }}</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon closed">🔒</div>
                    <div class="stat-content">
                        <h3>Closed</h3>
                        <div class="number">{{ $stats['closed'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="filters" role="toolbar" aria-label="Filter Requests by Status">
                <a href="{{ route('requests.index', ['filter' => 'all']) }}" class="filter-btn {{ $currentFilter === 'all' ? 'active' : '' }}" role="button" tabindex="0" aria-pressed="{{ $currentFilter === 'all' ? 'true' : 'false' }}">All</a>
                <a href="{{ route('requests.index', ['filter' => 'completed']) }}" class="filter-btn {{ $currentFilter === 'completed' ? 'active' : '' }}" role="button" tabindex="0" aria-pressed="{{ $currentFilter === 'completed' ? 'true' : 'false' }}">Completed</a>
                <a href="{{ route('requests.index', ['filter' => 'cancelled']) }}" class="filter-btn {{ $currentFilter === 'cancelled' ? 'active' : '' }}" role="button" tabindex="0" aria-pressed="{{ $currentFilter === 'cancelled' ? 'true' : 'false' }}">Cancelled</a>
                <a href="{{ route('requests.index', ['filter' => 'closed']) }}" class="filter-btn {{ $currentFilter === 'closed' ? 'active' : '' }}" role="button" tabindex="0" aria-pressed="{{ $currentFilter === 'closed' ? 'true' : 'false' }}">Closed</a>
            </div>

            <!-- Search Bar -->
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="🔍 Search by Control Number..." onkeyup="searchRequests()" aria-label="Search requests by control number" role="searchbox">
            </div>

            <!-- Requests Table -->
            <div class="table-responsive">
                <table role="table" aria-label="Service Requests History Table" aria-describedby="requests-description">
                    <caption id="requests-description" style="display: none;">A table of completed, cancelled, and closed service requests with their control number, category, dates, status, and actions</caption>
                    <thead>
                        <tr>
                            <th>Control Number</th>
                            <th>Category</th>
                            <th>Date Submitted</th>
                            <th>Completed Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                        <tr data-request-id="{{ $request->id }}" data-status="{{ $request->status }}">
                            <td><strong>{{ $request->control_number }}</strong></td>
                            <td>
                                @if($request->category)
                                <span class="category-badge">
                                    @php
                                    $categoryVal = strtolower($request->category);
                                    if ($categoryVal === 'network') {
                                    echo '🌐 Network';
                                    } elseif ($categoryVal === 'ict') {
                                    echo '💻 ICT';
                                    } elseif ($categoryVal === 'mis') {
                                    echo '📊 MIS';
                                    } elseif ($categoryVal === 'web') {
                                    echo '🌍 Web';
                                    } else {
                                    echo ucfirst(str_replace('_', ' ', $categoryVal));
                                    }
                                    @endphp
                                </span>
                                @else
                                <span class="category-badge">-</span>
                                @endif
                            </td>
                            <td>{{ $request->created_at->format('M d, Y') }}</td>
                            <td>{{ $request->updated_at->format('M d, Y') }}</td>
                            <td>
                                <span class="status-badge {{ $request->status }}" aria-label="Status: {{ ucfirst($request->status) }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('requests.show', $request->id) }}" class="btn-view" aria-label="View details for request {{ $request->control_number }}">View Details</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <div class="empty-state-icon">📜</div>
                                <p>No completed or closed requests yet.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $requests->links() }}
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const content = document.querySelector('.content');
            if (sidebar) {
                sidebar.classList.toggle('collapsed');
            }
        }

        function searchRequests() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const table = document.querySelector('table tbody');
            const rows = table.querySelectorAll('tr');
            let visibleCount = 0;

            rows.forEach(row => {
                const controlNumber = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';

                if (searchInput === '' || controlNumber.includes(searchInput)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide empty state message
            const emptyStateRow = Array.from(rows).find(row => row.querySelector('.empty-state'));
            if (emptyStateRow) {
                if (visibleCount === 0 && searchInput !== '') {
                    emptyStateRow.style.display = '';
                } else {
                    emptyStateRow.style.display = searchInput === '' ? 'none' : '';
                }
            }
        }

        // 🔔 Real-time Updates - Auto-refresh when status changes
        function checkForStatusUpdates() {
            const rows = document.querySelectorAll('table tbody tr[data-request-id]');
            if (rows.length === 0) return;

            const requestIds = Array.from(rows).map(row => row.getAttribute('data-request-id')).filter(Boolean);

            if (requestIds.length === 0) return;

            fetch('/api/requests/status-check', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        request_ids: requestIds
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.updates && Array.isArray(data.updates)) {
                        let hasChanges = false;

                        data.updates.forEach(update => {
                            const row = document.querySelector(`tr[data-request-id="${update.id}"]`);
                            if (row) {
                                const oldStatus = row.getAttribute('data-status');
                                if (oldStatus !== update.status) {
                                    hasChanges = true;
                                    row.setAttribute('data-status', update.status);

                                    // Update status badge with animation
                                    const statusBadge = row.querySelector('.status-badge');
                                    if (statusBadge) {
                                        statusBadge.className = 'status-badge ' + update.status;
                                        statusBadge.textContent = update.status.charAt(0).toUpperCase() + update.status.slice(1);
                                        statusBadge.style.animation = 'none';
                                        setTimeout(() => {
                                            statusBadge.style.animation = 'pulse 0.6s ease-out';
                                        }, 10);
                                    }
                                }
                            }
                        });

                        if (hasChanges) {
                            // Refresh stats if status changed
                            location.reload();
                        }
                    }
                })
                .catch(error => console.log('Status check - No updates'));
        }

        // Check for updates every 30 seconds
        window.addEventListener('load', function() {
            // Initial check after 5 seconds
            setTimeout(checkForStatusUpdates, 5000);

            // Periodic check every 30 seconds
            setInterval(checkForStatusUpdates, 30000);
        });
    </script>
</body>

</html>