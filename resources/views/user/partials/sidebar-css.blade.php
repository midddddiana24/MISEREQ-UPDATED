        /* ============================================
           SIDEBAR STYLES
           ============================================ */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: #FFFFFF;
            border-right: 1px solid #E5E7EB;
            padding: 1.5rem 0;
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .sidebar.collapsed {
            width: 80px;
            padding: 2rem 0.5rem;
        }

        .sidebar.hidden {
            transform: translateX(-100%);
        }

        .sidebar.show {
            transform: translateX(0);
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
            transition: all 0.3s ease;
            display: block;
            z-index: 1001;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .sidebar-toggle:hover {
            background: var(--primary-dark);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid #E5E7EB;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .sidebar-header {
            padding: 0 0.5rem 1.5rem;
            text-align: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .logo-section {
            justify-content: center;
            gap: 0;
        }

        .logo {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(209, 136, 79, 0.3);
            flex-shrink: 0;
        }

        .logo:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(209, 136, 79, 0.4);
        }

        .logo-text {
            font-size: 1rem;
            font-weight: 600;
            color: var(--secondary);
            line-height: 1.3;
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .logo-text {
            display: none;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #F3F4F6;
            border-radius: 12px;
            margin: 1.5rem;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .user-info {
            flex-direction: column;
            margin: 1rem 0.5rem;
            padding: 0.8rem 0.5rem;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(209, 136, 79, 0.3);
        }

        .user-details {
            flex: 1;
            min-width: 0;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .user-details {
            display: none;
        }

        .user-details h4 {
            color: var(--secondary);
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 0.2rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-details p {
            color: #666;
            font-size: 0.8rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-menu {
            list-style: none;
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .sidebar-menu {
            padding: 0.5rem 0;
        }

        .menu-item {
            margin-bottom: 0.25rem;
        }

        .menu-item a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.85rem 1.5rem;
            color: #555;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            border-radius: 0 10px 10px 0;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .sidebar.collapsed .menu-item a {
            padding: 0.85rem;
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
            transform: scaleY(0);
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
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
            padding-left: 0.85rem;
            background: rgba(209, 136, 79, 0.15);
        }

        .menu-item a:hover::before,
        .menu-item a.active::before {
            transform: scaleY(1);
        }

        .menu-icon {
            font-size: 1.25rem;
            transition: transform 0.25s ease;
            flex-shrink: 0;
        }

        .menu-item a:hover .menu-icon,
        .menu-item a.active .menu-icon {
            transform: scale(1.1);
        }

        .menu-label {
            transition: opacity 0.3s ease;
            white-space: nowrap;
        }

        .sidebar.collapsed .menu-label {
            display: none;
        }

        /* Sidebar divider */
        .sidebar-divider {
            height: 1px;
            background: #E5E7EB;
            margin: 0.75rem 1.5rem;
        }

        .sidebar.collapsed .sidebar-divider {
            margin: 0.75rem 0.5rem;
        }

        /* Main content offset */
        .main-content {
            margin-left: 260px;
            padding: 2rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
            width: calc(100% - 260px);
            overflow-x: hidden;
        }

        .main-content.collapsed {
            margin-left: 80px;
            width: calc(100% - 80px);
        }

        /* Menu toggle for mobile */
        .menu-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--secondary);
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: none;
            margin-right: 0.75rem;
        }

        .menu-toggle:hover {
            background: rgba(209, 136, 79, 0.1);
            color: var(--primary);
        }

        /* ============================================
           RESPONSIVE SIDEBAR
           ============================================ */
        @media (max-width: 968px) {
            .sidebar {
                transform: translateX(-100%);
                width: 260px;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .sidebar-toggle {
                display: none;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 1rem;
            }

            .main-content.collapsed {
                margin-left: 0;
                width: 100%;
            }

            .menu-toggle {
                display: block;
            }
        }

