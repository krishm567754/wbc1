<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Castrol SmartHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        castrol: { 500: '#d50032', 600: '#b0002a', 900: '#7a0018' },
                        brand: { 500: '#009a49', 600: '#007a3a' },
                        dark: { 800: '#1e293b', 900: '#0f172a' }
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #f1f5f9; }
        .sidebar-link { transition: all 0.2s; border-left: 3px solid transparent; }
        .sidebar-link:hover, .sidebar-link.active { background-color: #1e293b; border-left-color: #d50032; color: white; }
        .glass-card { background: white; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body class="flex h-screen overflow-hidden bg-slate-50">

    <aside class="w-64 bg-dark-900 text-slate-300 flex-col shadow-2xl z-20 hidden md:flex">
        <div class="h-20 flex items-center px-6 bg-dark-900 border-b border-slate-700">
            <i class="fa-solid fa-oil-can text-castrol-500 text-3xl mr-3"></i>
            <div>
                <span class="font-bold text-xl text-white tracking-tight block">Castrol</span>
                <span class="text-sm font-bold text-brand-500 tracking-widest uppercase">SmartHub</span>
            </div>
        </div>

        <nav class="flex-1 py-6 space-y-1">
            <p class="px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Menu</p>
            <a href="index.php" class="sidebar-link flex items-center px-6 py-3 text-sm font-medium hover:text-white group">
                <i class="fa-solid fa-chart-pie w-6 text-center mr-3"></i> Dashboard
            </a>
            <a href="search.php" class="sidebar-link flex items-center px-6 py-3 text-sm font-medium hover:text-white group">
                <i class="fa-solid fa-search w-6 text-center mr-3"></i> Product Search
            </a>
            <a href="admin.php" class="sidebar-link flex items-center px-6 py-3 text-sm font-medium hover:text-white group">
                <i class="fa-solid fa-lock w-6 text-center mr-3"></i> Admin Panel
            </a>
        </nav>
        
        <div class="p-4 border-t border-slate-700 bg-dark-800">
            <div class="flex items-center">
                <div class="h-8 w-8 rounded-full bg-castrol-600 flex items-center justify-center text-white font-bold text-xs">D</div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-white">Distributor</p>
                    <p class="text-xs text-green-500">● Online</p>
                </div>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-4 md:hidden z-10">
            <div class="flex items-center">
                <i class="fa-solid fa-oil-can text-castrol-500 text-xl mr-2"></i>
                <span class="font-bold text-lg text-slate-800">Castrol SmartHub</span>
            </div>
            <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="text-slate-600 p-2">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
        </header>

        <div id="mobile-menu" class="hidden bg-dark-900 text-white md:hidden absolute top-16 left-0 w-full z-50 shadow-xl border-t border-slate-700">
            <a href="index.php" class="block px-4 py-3 border-b border-slate-700"><i class="fa-solid fa-chart-pie mr-2"></i> Dashboard</a>
            <a href="search.php" class="block px-4 py-3 border-b border-slate-700"><i class="fa-solid fa-search mr-2"></i> Search</a>
            <a href="admin.php" class="block px-4 py-3"><i class="fa-solid fa-lock mr-2"></i> Admin</a>
        </div>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-4 md:p-6">