<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title> @yield('title', 'Ilyken Manager')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-violet: #6B46C1;
            --light-violet: #9F7AEA;
        }
        body { background-color: #f8f9ff; }
        .sidebar {
            background: linear-gradient(135deg, var(--primary-violet), #4C1D95);
            min-height: 100vh;
            position: fixed;
            width: 260px;
            color: white;
        }
        .main-content { margin-left: 260px; padding: 20px; }
        .nav-link { color: white !important; }
        .nav-link.active, .nav-link:hover { background-color: rgba(255,255,255,0.15); border-radius: 8px; }
        .card-kpi { border: none; border-radius: 16px; box-shadow: 0 5px 20px rgba(107,70,193,0.12); }
        .btn-violet { background-color: var(--primary-violet); border: none; color: white; }
        .btn-violet:hover { background-color: #5A3AA6; }
        .header-violet { background: linear-gradient(135deg, var(--primary-violet), #9F7AEA); color: white; }
        .table th { background-color: #f0ebff; color: var(--primary-violet); }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar p-3">
    <div class="text-center mb-5">
        <h2 class="fw-bold">ILYKEN</h2>
        <p class="mb-0">Manager</p>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item"><a href="{{route('dashboard')}}" class="nav-link active"><i class="fas fa-home me-3"></i> Tableau de bord</a></li>
        <li class="nav-item"><a href="{{route('sales.index')}}" class="nav-link"><i class="fas fa-cash-register me-3"></i> Point de Vente</a></li>
        <li class="nav-item"><a href="{{ route('stock.index') }}" class="nav-link"><i class="fas fa-boxes me-3"></i> Gestion du Stock</a></li>
        <li class="nav-item"><a href="{{ route('products.index') }}" class="nav-link"><i class="fas fa-list me-3"></i> Produits & Services</a></li>
        <li class="nav-item"><a href="#factures" class="nav-link"><i class="fas fa-file-invoice me-3"></i> Facturation</a></li>
        <li class="nav-item"><a href="#finances" class="nav-link"><i class="fas fa-chart-bar me-3"></i> Finances & Rapports</a></li>
        <li class="nav-item mt-4"><a href="#" class="nav-link"><i class="fas fa-users me-3"></i> Utilisateurs</a></li>
    </ul>
    <div class="position-absolute bottom-0 p-3">
        <small>Version 1.0 • 17 Avril 2026</small>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">

    <!-- Top Navbar -->
    <nav class="header-violet navbar navbar-expand-lg rounded-3 mb-4 p-3">
        <div class="container-fluid">
            <h4 class="mb-0 fw-bold">Ilyken Manager</h4>
            <div class="d-flex align-items-center gap-4">
                <input type="text" class="form-control w-400" placeholder="Rechercher produit, service ou client...">
                <i class="fas fa-bell fa-lg"></i>
                <div class="d-flex align-items-center gap-2">
                    <img src="https://via.placeholder.com/45" class="rounded-circle border border-white" alt="">
                    <div>
                        <strong>Mahamane Lamine</strong><br>
                        <small>Administrateur</small>
                    </div>
                </div>
            </div>
        </div>
    </nav>

  @yield('content')

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'],
            datasets: [{ label: 'Ventes', data: [120000,185000,210000,245000,198000,312000,278000], borderColor: '#6B46C1', tension: 0.4 }]
        },
        options: { responsive: true, plugins: { legend: { display: false }}}
    });
</script>
</body>
</html>