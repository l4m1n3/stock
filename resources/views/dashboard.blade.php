  <!-- ==================== SECTION DASHBOARD ==================== -->
@extends('layouts.app')

@section('title', 'Dashboard')
@section('content')
    <section id="dashboard">
        <h2 class="mb-4">Tableau de Bord</h2>
        
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card card-kpi p-4 bg-white">
                    <i class="fas fa-shopping-bag fa-3x text-light-violet mb-3"></i>
                    <h4 class="fw-bold text-violet">248 500 FCFA</h4>
                    <p class="mb-0">Ventes aujourd’hui</p>
                    <small class="text-success">+22% depuis hier</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-kpi p-4 bg-white">
                    <i class="fas fa-clipboard-list fa-3x text-light-violet mb-3"></i>
                    <h4 class="fw-bold text-violet">12</h4>
                    <p class="mb-0">Commandes en cours</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-kpi p-4 bg-white">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h4 class="fw-bold text-danger">5</h4>
                    <p class="mb-0">Articles en alerte stock</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-kpi p-4 bg-white">
                    <i class="fas fa-chart-line fa-3x text-light-violet mb-3"></i>
                    <h4 class="fw-bold text-violet">4 125 000 FCFA</h4>
                    <p class="mb-0">Chiffre du mois</p>
                </div>
            </div>
        </div>

        <div class="alert alert-warning">
            <strong>Alertes Stock :</strong> Bouquets de fleurs (reste 8 unités) • Tapis personnalisés (reste 5 unités) • Chocolat pour bouquets (reste 12 unités)
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-white"><h5>Ventes des 7 derniers jours</h5></div>
                    <div class="card-body">
                        <canvas id="salesChart" height="80"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header bg-white"><h5>Dernières Ventes</h5></div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between"><span>Aïcha Traoré - Bouquet privé</span><strong>95 000 FCFA</strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>Moussa Diallo - Décoration mariage</span><strong>185 000 FCFA</strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>Fatou Koné - Kit cadeau</span><strong>45 000 FCFA</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
@endsection