@extends('layouts.app')

@section('title', 'Point de Vente')

@section('content')

<style>
    :root {
        --violet: #6B46C1;
        --violet-light: #EEEDFE;
        --violet-hover: #5A3AA6;
    }
    .pos-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    .pos-panel {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e8e4f7;
        box-shadow: 0 4px 20px rgba(107,70,193,0.07);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .pos-panel-header {
        padding: 14px 18px;
        border-bottom: 1px solid #f0ebff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #faf8ff;
    }
    .pos-panel-title {
        font-size: 12px;
        font-weight: 600;
        color: var(--violet);
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    /* Search */
    .pos-search {
        padding: 12px 16px;
        border-bottom: 1px solid #f0ebff;
    }
    .pos-search input {
        width: 100%;
        padding: 9px 14px;
        border: 1px solid #e0d9f7;
        border-radius: 10px;
        font-size: 14px;
        background: #faf8ff;
        color: #2d2d2d;
        outline: none;
        transition: border-color 0.15s;
    }
    .pos-search input:focus { border-color: var(--violet); }

    /* Tabs */
    .pos-tabs {
        display: flex;
        gap: 6px;
        padding: 10px 16px;
        border-bottom: 1px solid #f0ebff;
        flex-wrap: wrap;
    }
    .pos-tab {
        padding: 5px 14px;
        border-radius: 99px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        border: 1px solid #e0d9f7;
        background: transparent;
        color: #7c6fa0;
        transition: all 0.15s;
    }
    .pos-tab.active, .pos-tab:hover {
        background: var(--violet);
        color: white;
        border-color: var(--violet);
    }

    /* Items list */
    .pos-items { overflow-y: auto; max-height: 360px; }
    .pos-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 11px 16px;
        border-bottom: 1px solid #f7f4ff;
        cursor: pointer;
        transition: background 0.1s;
    }
    .pos-item:hover { background: #faf8ff; }
    .pos-item:last-child { border-bottom: none; }
    .pos-item-name  { font-size: 14px; font-weight: 500; color: #2d2d2d; }
    .pos-item-meta  { font-size: 12px; color: #9e8fc0; margin-top: 2px; }
    .pos-item-price { font-size: 14px; font-weight: 600; color: var(--violet); white-space: nowrap; }
    .pos-add-btn {
        width: 28px; height: 28px;
        border-radius: 50%;
        background: var(--violet);
        color: white;
        border: none;
        cursor: pointer;
        font-size: 18px;
        line-height: 1;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        margin-left: 10px;
        transition: background 0.15s;
    }
    .pos-add-btn:hover    { background: var(--violet-hover); }
    .pos-add-btn:disabled { background: #ccc; cursor: not-allowed; }

    /* Badges stock */
    .badge-stock {
        display: inline-block;
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 99px;
        margin-top: 3px;
    }
    .badge-green  { background: #EAF3DE; color: #3B6D11; }
    .badge-amber  { background: #FAEEDA; color: #854F0B; }
    .badge-red    { background: #FCEBEB; color: #A32D2D; }
    .badge-purple { background: var(--violet-light); color: var(--violet); }

    /* Cart */
    .pos-cart { overflow-y: auto; max-height: 220px; flex: 1; }
    .cart-empty {
        padding: 36px 20px;
        text-align: center;
        color: #b0a0d0;
        font-size: 14px;
    }
    .cart-empty-icon { font-size: 32px; margin-bottom: 8px; }
    .cart-row {
        display: grid;
        grid-template-columns: 1fr auto auto auto;
        gap: 8px;
        align-items: center;
        padding: 10px 16px;
        border-bottom: 1px solid #f7f4ff;
    }
    .cart-row:last-child { border-bottom: none; }
    .cart-item-name  { font-size: 13px; font-weight: 500; color: #2d2d2d; }
    .cart-item-unit  { font-size: 11px; color: #9e8fc0; }
    .qty-ctrl { display: flex; align-items: center; gap: 5px; }
    .qty-btn {
        width: 24px; height: 24px;
        border-radius: 6px;
        border: 1px solid #e0d9f7;
        background: #faf8ff;
        cursor: pointer;
        font-size: 15px;
        display: flex; align-items: center; justify-content: center;
        color: var(--violet);
        transition: background 0.1s;
    }
    .qty-btn:hover { background: var(--violet-light); }
    .qty-val { font-size: 13px; min-width: 22px; text-align: center; font-weight: 600; color: #2d2d2d; }
    .cart-item-total { font-size: 13px; font-weight: 600; color: #2d2d2d; white-space: nowrap; }
    .cart-del-btn {
        background: none; border: none; cursor: pointer;
        color: #c0b0d8; font-size: 15px; padding: 2px 4px;
        transition: color 0.1s;
    }
    .cart-del-btn:hover { color: #E24B4A; }

    /* Summary */
    .pos-summary {
        padding: 14px 16px;
        border-top: 1px solid #f0ebff;
        background: #faf8ff;
    }
    .summary-line {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        color: #9e8fc0;
        margin-bottom: 6px;
    }
    .summary-total {
        display: flex;
        justify-content: space-between;
        font-size: 17px;
        font-weight: 700;
        color: var(--violet);
        padding-top: 10px;
        border-top: 1px solid #e0d9f7;
        margin-top: 4px;
    }

    /* ═══════════════════ CLIENT SECTION ═══════════════════ */
    .client-section {
        padding: 12px 16px;
        border-bottom: 1px solid #f0ebff;
        background: #fff;
    }
    .client-section-label {
        font-size: 11px;
        font-weight: 600;
        color: #9e8fc0;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .client-anonymous-toggle {
        font-size: 11px;
        color: #b0a0d0;
        background: none;
        border: none;
        cursor: pointer;
        text-decoration: underline;
        padding: 0;
        transition: color 0.1s;
    }
    .client-anonymous-toggle:hover { color: var(--violet); }

    .client-chip {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        background: var(--violet-light);
        border: 1.5px solid var(--violet);
        border-radius: 10px;
        animation: chipIn 0.15s ease;
    }
    @keyframes chipIn {
        from { opacity: 0; transform: scale(0.97); }
        to   { opacity: 1; transform: scale(1); }
    }
    .client-chip-avatar {
        width: 32px; height: 32px;
        border-radius: 50%;
        background: var(--violet);
        color: white;
        font-size: 12px;
        font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .client-chip-name  { font-size: 13px; font-weight: 600; color: #2d2d2d; }
    .client-chip-phone { font-size: 11px; color: #9e8fc0; }
    .client-chip-remove {
        margin-left: auto;
        background: none; border: none;
        color: #b0a0d0; font-size: 16px; cursor: pointer;
        padding: 2px 4px; line-height: 1;
        transition: color 0.1s;
    }
    .client-chip-remove:hover { color: #E24B4A; }

    .client-search-wrap { position: relative; }
    .client-search-input {
        width: 100%;
        padding: 9px 14px 9px 36px;
        border: 1px solid #e0d9f7;
        border-radius: 10px;
        font-size: 13px;
        background: #faf8ff;
        color: #2d2d2d;
        outline: none;
        transition: border-color 0.15s;
        box-sizing: border-box;
    }
    .client-search-input:focus { border-color: var(--violet); }
    .client-search-icon {
        position: absolute;
        left: 11px; top: 50%;
        transform: translateY(-50%);
        color: #c0b0d8;
        font-size: 13px;
        pointer-events: none;
    }
    .client-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0; right: 0;
        background: white;
        border: 1px solid #e0d9f7;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(107,70,193,0.12);
        z-index: 200;
        max-height: 200px;
        overflow-y: auto;
        display: none;
    }
    .client-dropdown.open { display: block; }
    .client-dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f7f4ff;
        transition: background 0.1s;
    }
    .client-dropdown-item:last-child { border-bottom: none; }
    .client-dropdown-item:hover { background: #faf8ff; }
    .client-dropdown-avatar {
        width: 28px; height: 28px;
        border-radius: 50%;
        background: var(--violet-light);
        color: var(--violet);
        font-size: 11px; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .client-dropdown-name  { font-size: 13px; font-weight: 500; color: #2d2d2d; }
    .client-dropdown-phone { font-size: 11px; color: #9e8fc0; }
    .client-dropdown-empty {
        padding: 14px;
        text-align: center;
        color: #b0a0d0;
        font-size: 13px;
    }
    .client-dropdown-create {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 12px;
        cursor: pointer;
        background: #faf8ff;
        border-top: 1px solid #f0ebff;
        color: var(--violet);
        font-size: 13px;
        font-weight: 500;
        transition: background 0.1s;
    }
    .client-dropdown-create:hover { background: var(--violet-light); }

    .client-new-form {
        display: none;
        margin-top: 8px;
        padding: 10px 12px;
        background: #faf8ff;
        border: 1px dashed #c8bef0;
        border-radius: 10px;
        gap: 8px;
        flex-direction: column;
        animation: chipIn 0.15s ease;
    }
    .client-new-form.open { display: flex; }
    .client-new-form input {
        flex: 1;
        padding: 7px 10px;
        border: 1px solid #e0d9f7;
        border-radius: 8px;
        font-size: 13px;
        color: #2d2d2d;
        background: white;
        outline: none;
        transition: border-color 0.15s;
        width: 100%;
        box-sizing: border-box;
    }
    .client-new-form input:focus { border-color: var(--violet); }
    .client-new-form-row { display: flex; gap: 8px; }
    .client-new-actions  { display: flex; gap: 8px; justify-content: flex-end; }
    .btn-new-client-confirm {
        padding: 6px 14px;
        background: var(--violet);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
    }
    .btn-new-client-confirm:hover { background: var(--violet-hover); }
    .btn-new-client-cancel {
        padding: 6px 12px;
        background: white;
        color: #888;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 12px;
        cursor: pointer;
    }
    .btn-new-client-cancel:hover { background: #f4f2fc; }

    .client-anon-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        background: #f4f2fc;
        border: 1px dashed #d0c8ee;
        border-radius: 10px;
        font-size: 12px;
        color: #9e8fc0;
        font-style: italic;
    }

    /* Payment */
    .pos-payment { padding: 14px 16px; }
    .pay-label {
        font-size: 11px;
        font-weight: 600;
        color: #9e8fc0;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 10px;
    }
    .pay-methods {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 8px;
        margin-bottom: 14px;
    }
    .pay-btn {
        padding: 10px 6px;
        border-radius: 10px;
        border: 1.5px solid #e0d9f7;
        background: #fff;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        color: #9e8fc0;
        transition: all 0.15s;
        display: flex; flex-direction: column;
        align-items: center; gap: 4px;
    }
    .pay-btn:hover { border-color: var(--violet); color: var(--violet); }
    .pay-btn.selected {
        border-color: var(--violet);
        background: var(--violet-light);
        color: var(--violet);
        border-width: 2px;
    }
    .pay-icon { font-size: 18px; }

    /* Checkout button */
    .checkout-btn {
        width: 100%;
        padding: 14px;
        background: var(--violet);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s, transform 0.1s;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        letter-spacing: 0.01em;
    }
    .checkout-btn:hover:not(:disabled) { background: var(--violet-hover); transform: translateY(-1px); }
    .checkout-btn:active  { transform: translateY(0); }
    .checkout-btn:disabled {
        background: #e8e4f7;
        color: #b0a0d0;
        cursor: not-allowed;
        transform: none;
    }

    /* Toast */
    #pos-toast {
        position: fixed;
        top: 20px; right: 20px;
        background: #3B6D11;
        color: white;
        padding: 12px 22px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 500;
        display: none;
        z-index: 9999;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease;
    }
    @keyframes slideIn {
        from { opacity:0; transform: translateY(-10px); }
        to   { opacity:1; transform: translateY(0); }
    }

    .clear-btn {
        font-size: 12px;
        color: #b0a0d0;
        background: none;
        border: none;
        cursor: pointer;
        transition: color 0.1s;
    }
    .clear-btn:hover { color: #E24B4A; }

    @media (max-width: 768px) {
        .pos-grid { grid-template-columns: 1fr; }
    }
</style>

<div id="pos-toast">✓ Facture générée avec succès</div>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Point de Vente</h4>
        <small class="text-muted">Caisse enregistreuse — {{ now()->format('d/m/Y') }}</small>
    </div>
    <span class="badge rounded-pill" style="background:var(--violet-light);color:var(--violet);font-size:13px;padding:8px 16px;">
        Session ouverte
    </span>
</div>

<div class="pos-grid">

    {{-- ==================== CATALOGUE ==================== --}}
    <div class="pos-panel">
        <div class="pos-panel-header">
            <span class="pos-panel-title">Catalogue</span>
            <span style="font-size:12px;color:#9e8fc0;" id="item-count">
                {{ count($productsJson) + count($servicesJson) + count($confectionsJson) }} articles
            </span>
        </div>

        <div class="pos-search">
            <input type="text" id="pos-search" placeholder="Rechercher un produit ou service..." oninput="posFilter()">
        </div>

        <div class="pos-tabs">
            <button class="pos-tab active" onclick="posTab(this,'all')">Tout</button>
            <button class="pos-tab" onclick="posTab(this,'produit')">Produits</button>
            <button class="pos-tab" onclick="posTab(this,'service')">Services</button>
            <button class="pos-tab" onclick="posTab(this,'confection')">Confections</button>
        </div>

        <div class="pos-items" id="pos-items-list"></div>
    </div>

    {{-- ==================== PANIER ==================== --}}
    <div class="pos-panel">
        <div class="pos-panel-header">
            <span class="pos-panel-title">Panier en cours</span>
            <button class="clear-btn" onclick="posClearCart()">Vider le panier</button>
        </div>

        {{-- ─── CLIENT ───────────────────────────────────────────────────── --}}
        <div class="client-section">
            <div class="client-section-label">
                <span><i class="fas fa-user me-1"></i> Client</span>
                <button class="client-anonymous-toggle" type="button" onclick="posToggleAnonymous()">
                    Vente anonyme
                </button>
            </div>

            {{-- État : anonyme --}}
            <div id="client-anon-state" style="display:none;">
                <div class="client-anon-badge">
                    <i class="fas fa-user-slash"></i>
                    Vente sans client enregistré
                    <button type="button" onclick="posExitAnonymous()"
                        style="background:none;border:none;cursor:pointer;color:var(--violet);font-size:11px;margin-left:4px;padding:0;font-weight:600;">
                        Annuler
                    </button>
                </div>
            </div>

            {{-- État : sélection (défaut) --}}
            <div id="client-select-state">

                {{-- Chip du client sélectionné --}}
                <div id="client-chip" style="display:none;"></div>

                {{-- Recherche autocomplete --}}
                <div class="client-search-wrap" id="client-search-wrap">
                    <i class="fas fa-search client-search-icon"></i>
                    <input
                        type="text"
                        id="client-search-input"
                        class="client-search-input"
                        placeholder="Rechercher un client existant…"
                        oninput="posClientSearch(this.value)"
                        onfocus="posClientSearch(this.value)"
                        autocomplete="off"
                    >
                    <div class="client-dropdown" id="client-dropdown"></div>
                </div>

                {{-- Formulaire nouveau client (inline) --}}
                <div class="client-new-form" id="client-new-form">
                    <div class="client-new-form-row">
                        <input type="text" id="new-client-name"  placeholder="Nom complet *">
                        <input type="text" id="new-client-phone" placeholder="Téléphone">
                    </div>
                    <div class="client-new-actions">
                        <button class="btn-new-client-cancel"  type="button" onclick="posCloseNewForm()">Annuler</button>
                        <button class="btn-new-client-confirm" type="button" onclick="posConfirmNewClient()">
                            <i class="fas fa-check me-1"></i> Valider
                        </button>
                    </div>
                </div>

            </div>
        </div>
        {{-- ──────────────────────────────────────────────────────────────── --}}

        <div class="pos-cart" id="pos-cart">
            <div class="cart-empty">
                <div class="cart-empty-icon">🛒</div>
                Panier vide — ajoutez des articles
            </div>
        </div>

        <div class="pos-summary" id="pos-summary" style="display:none;">
            <div class="summary-line"><span>Sous-total</span><span id="pos-subtotal">0 FCFA</span></div>
            <div class="summary-total"><span>Total TTC</span><span id="pos-total">0 FCFA</span></div>
        </div>

        <div class="pos-payment">
            <div class="pay-label">Mode de paiement</div>
            <div class="pay-methods">
                <button class="pay-btn selected" onclick="posSelectPay(this,'cash')">
                    <span class="pay-icon">💵</span> Espèces
                </button>
                <button class="pay-btn" onclick="posSelectPay(this,'amana')">
                    <span class="pay-icon">📱</span> Amanata
                </button>
                <button class="pay-btn" onclick="posSelectPay(this,'nita')">
                    <span class="pay-icon">📲</span> MyNita
                </button>
                <button class="pay-btn" onclick="posSelectPay(this,'western_union')">
                    <span class="pay-icon">🌍</span> Western Union
                </button>
                <button class="pay-btn" onclick="posSelectPay(this,'moneygram')">
                    <span class="pay-icon">💳</span> MoneyGram
                </button>
                <button class="pay-btn" onclick="posSelectPay(this,'wave')">
                    <span class="pay-icon">🌊</span> Wave
                </button>
            </div>

            <form id="pos-form" action="{{ route('sales.store') }}" method="POST">
                @csrf
                <input type="hidden" name="payment_method" id="input-pay-method"   value="cash">
                <input type="hidden" name="cart_data"      id="input-cart-data"    value="[]">
                <input type="hidden" name="client_id"      id="input-client-id"    value="">
                <input type="hidden" name="client_name"    id="input-client-name"  value="">
                <input type="hidden" name="client_phone"   id="input-client-phone" value="">
                <input type="hidden" name="is_anonymous"   id="input-is-anonymous" value="0">

                <button type="button" class="checkout-btn" id="pos-checkout-btn" disabled onclick="posCheckout()">
                    <i class="fas fa-file-invoice me-2"></i> Générer Facture & Imprimer
                </button>
            </form>
        </div>
    </div>

</div>

{{-- ==================== MODAL CONFIRMATION ==================== --}}
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:linear-gradient(135deg,#6B46C1,#9F7AEA);border-radius:16px 16px 0 0;">
                <h5 class="modal-title text-white fw-bold">
                    <i class="fas fa-file-invoice me-2"></i>Confirmer la vente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">

                {{-- Récap client --}}
                <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded-3"
                     style="background:#faf8ff;border:1px solid #e8e4f7;">
                    <div id="confirm-client-avatar"
                         style="width:34px;height:34px;border-radius:50%;background:var(--violet-light);color:var(--violet);
                                font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        —
                    </div>
                    <div>
                        <div id="confirm-client-name"  style="font-size:13px;font-weight:600;color:#2d2d2d;">—</div>
                        <div id="confirm-client-phone" style="font-size:11px;color:#9e8fc0;"></div>
                    </div>
                </div>

                <div id="confirm-items" class="mb-3"></div>

                <div class="d-flex justify-content-between align-items-center p-3 rounded-3" style="background:#faf8ff;">
                    <span class="fw-semibold" style="color:#6B46C1;">Total</span>
                    <span class="fw-bold fs-5" style="color:#6B46C1;" id="confirm-total">0 FCFA</span>
                </div>
                <div class="mt-3 p-2 rounded-3 text-center" style="background:#f0ebff;color:#6B46C1;font-size:14px;">
                    <i class="fas fa-money-bill me-1"></i> Paiement : <strong id="confirm-pay"></strong>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Annuler</button>
                <button class="btn rounded-3 px-4 text-white fw-semibold" style="background:var(--violet);" onclick="posSubmit()">
                    <i class="fas fa-check me-2"></i>Confirmer & Imprimer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ─── Données injectées depuis le contrôleur (pré-sérialisées côté PHP) ────────
const posCatalog = {!! json_encode(array_merge($productsJson, $servicesJson, $confectionsJson)) !!};
const posClients = {!! json_encode($clientsJson) !!};

// ─── État global ──────────────────────────────────────────────────────────────
let posCart      = [];
let posActiveTab = 'all';
let posPayMethod = 'cash';

// { mode: 'none' | 'existing' | 'new' | 'anonymous', id, name, phone }
let posClient = { mode: 'none', id: null, name: '', phone: '' };

// ─── Utilitaires ──────────────────────────────────────────────────────────────
function posFmt(n) {
    return n.toLocaleString('fr-FR') + ' FCFA';
}

function posInitials(name) {
    const parts = (name || '').trim().split(' ');
    return ((parts[0]?.[0] ?? '') + (parts[1]?.[0] ?? '')).toUpperCase() || '?';
}

function posStockBadge(item) {
    if (item.type === 'service') return '<span class="badge-stock badge-purple">Service</span>';
    if (item.type === 'confection') {
        if (item.stock === 0) return '<span class="badge-stock badge-red">Composants épuisés</span>';
        if (item.stock <= 3)  return `<span class="badge-stock badge-amber">Stock limité (${item.stock})</span>`;
        return '<span class="badge-stock badge-green">Disponible</span>';
    }
    if (item.stock > 5) return `<span class="badge-stock badge-green">En stock (${item.stock})</span>`;
    if (item.stock > 0) return `<span class="badge-stock badge-amber">Limité (${item.stock})</span>`;
    return '<span class="badge-stock badge-red">Rupture</span>';
}

// ─── CLIENT — autocomplete ────────────────────────────────────────────────────
let clientSearchTimer = null;

function posClientSearch(q) {
    clearTimeout(clientSearchTimer);
    clientSearchTimer = setTimeout(() => _posClientRender(q), 120);
}

function _posClientRender(q) {
    const dd      = document.getElementById('client-dropdown');
    const trimmed = q.trim().toLowerCase();

    const results = trimmed
        ? posClients.filter(c =>
              c.name.toLowerCase().includes(trimmed) ||
              (c.phone && c.phone.includes(trimmed))
          )
        : posClients.slice(0, 8);

    let html = '';

    if (!results.length && trimmed) {
        html += `<div class="client-dropdown-empty">Aucun client trouvé pour « ${q} »</div>`;
    } else {
        html += results.map(c => `
            <div class="client-dropdown-item"
                 onclick="posSelectExistingClient(${c.id}, ${JSON.stringify(c.name)}, ${JSON.stringify(c.phone)})">
                <div class="client-dropdown-avatar">${posInitials(c.name)}</div>
                <div>
                    <div class="client-dropdown-name">${c.name}</div>
                    <div class="client-dropdown-phone">${c.phone || '—'}</div>
                </div>
            </div>
        `).join('');
    }

    html += `
        <div class="client-dropdown-create" onclick="posOpenNewForm()">
            <i class="fas fa-user-plus" style="font-size:14px;"></i>
            Créer un nouveau client
        </div>
    `;

    dd.innerHTML = html;
    dd.classList.add('open');
}

// Fermer le dropdown au clic extérieur
document.addEventListener('click', e => {
    if (!e.target.closest('#client-search-wrap')) {
        document.getElementById('client-dropdown').classList.remove('open');
    }
});

function posSelectExistingClient(id, name, phone) {
    posClient = { mode: 'existing', id, name, phone };
    posRenderClientChip();
    document.getElementById('client-dropdown').classList.remove('open');
    document.getElementById('client-search-input').value = '';
}

// ─── CLIENT — nouveau (formulaire inline) ─────────────────────────────────────
function posOpenNewForm() {
    document.getElementById('client-dropdown').classList.remove('open');
    document.getElementById('client-search-wrap').style.display = 'none';
    document.getElementById('client-new-form').classList.add('open');
    document.getElementById('new-client-name').focus();
}

function posCloseNewForm() {
    document.getElementById('client-new-form').classList.remove('open');
    document.getElementById('client-search-wrap').style.display = 'block';
    document.getElementById('new-client-name').value  = '';
    document.getElementById('new-client-phone').value = '';
}

function posConfirmNewClient() {
    const name  = document.getElementById('new-client-name').value.trim();
    const phone = document.getElementById('new-client-phone').value.trim();
    if (!name) {
        document.getElementById('new-client-name').style.borderColor = '#E24B4A';
        document.getElementById('new-client-name').focus();
        return;
    }
    document.getElementById('new-client-name').style.borderColor = '';
    posClient = { mode: 'new', id: null, name, phone };
    posCloseNewForm();
    posRenderClientChip();
}

// ─── CLIENT — chip affiché une fois sélectionné ───────────────────────────────
function posRenderClientChip() {
    const chip = document.getElementById('client-chip');
    const wrap = document.getElementById('client-search-wrap');

    if (posClient.mode === 'none') {
        chip.style.display = 'none';
        wrap.style.display = 'block';
        return;
    }

    const newBadge = posClient.mode === 'new'
        ? '<span style="font-size:10px;background:#FEF3C7;color:#92400E;padding:1px 6px;border-radius:99px;margin-left:6px;">Nouveau</span>'
        : '';

    chip.innerHTML = `
        <div class="client-chip">
            <div class="client-chip-avatar">${posInitials(posClient.name)}</div>
            <div>
                <div class="client-chip-name">${posClient.name}${newBadge}</div>
                <div class="client-chip-phone">${posClient.phone || 'Pas de téléphone'}</div>
            </div>
            <button class="client-chip-remove" type="button" onclick="posClearClient()" title="Retirer">✕</button>
        </div>
    `;
    chip.style.display = 'block';
    wrap.style.display = 'none';
    document.getElementById('client-new-form').classList.remove('open');
}

function posClearClient() {
    posClient = { mode: 'none', id: null, name: '', phone: '' };
    posRenderClientChip();
    document.getElementById('client-anon-state').style.display   = 'none';
    document.getElementById('client-select-state').style.display = 'block';
}

// ─── CLIENT — vente anonyme ───────────────────────────────────────────────────
function posToggleAnonymous() {
    posClient = { mode: 'anonymous', id: null, name: '', phone: '' };
    document.getElementById('client-anon-state').style.display   = 'block';
    document.getElementById('client-select-state').style.display = 'none';
}

function posExitAnonymous() {
    posClient = { mode: 'none', id: null, name: '', phone: '' };
    document.getElementById('client-anon-state').style.display   = 'none';
    document.getElementById('client-select-state').style.display = 'block';
    posRenderClientChip();
}

// ─── Catalogue ────────────────────────────────────────────────────────────────
function posRenderItems() {
    const q        = document.getElementById('pos-search').value.toLowerCase();
    const filtered = posCatalog.filter(i => {
        const matchTab = posActiveTab === 'all' || i.type === posActiveTab;
        const matchQ   = i.name.toLowerCase().includes(q);
        return matchTab && matchQ;
    });

    document.getElementById('item-count').textContent = filtered.length + ' articles';
    document.getElementById('pos-items-list').innerHTML = filtered.map(i => `
        <div class="pos-item">
            <div style="flex:1;">
                <div class="pos-item-name">${i.name}</div>
                <div class="pos-item-meta">${posStockBadge(i)}</div>
                ${i.type === 'confection' && i.composition?.length
                    ? `<div style="font-size:11px;color:#b0a0d0;margin-top:3px;">
                           ${i.composition.map(c => `${c.name} ×${c.qty}`).join(' · ')}
                       </div>`
                    : ''}
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <span class="pos-item-price">${posFmt(i.price)}</span>
                <button class="pos-add-btn" onclick="posAddToCart('${i.id}')"
                    ${(i.type === 'produit' || i.type === 'confection') && i.stock === 0 ? 'disabled' : ''}>+</button>
            </div>
        </div>
    `).join('');
}

function posFilter() { posRenderItems(); }

function posTab(el, tab) {
    document.querySelectorAll('.pos-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    posActiveTab = tab;
    posRenderItems();
}

// ─── Panier ───────────────────────────────────────────────────────────────────
function posAddToCart(id) {
    const item     = posCatalog.find(i => i.id === id);
    const existing = posCart.find(c => c.id === id);
    if (existing) existing.qty++;
    else posCart.push({ ...item, qty: 1 });
    posRenderCart();
}

function posRenderCart() {
    const el = document.getElementById('pos-cart');

    if (!posCart.length) {
        el.innerHTML = '<div class="cart-empty"><div class="cart-empty-icon">🛒</div>Panier vide — ajoutez des articles</div>';
        document.getElementById('pos-summary').style.display = 'none';
        document.getElementById('pos-checkout-btn').disabled = true;
        return;
    }

    el.innerHTML = posCart.map(c => `
        <div class="cart-row">
            <div>
                <div class="cart-item-name">${c.name}</div>
                <div class="cart-item-unit">${posFmt(c.price)} / unité</div>
            </div>
            <div class="qty-ctrl">
                <button class="qty-btn" onclick="posChangeQty('${c.id}',-1)">−</button>
                <span class="qty-val">${c.qty}</span>
                <button class="qty-btn" onclick="posChangeQty('${c.id}',1)">+</button>
            </div>
            <span class="cart-item-total">${posFmt(c.price * c.qty)}</span>
            <button class="cart-del-btn" onclick="posRemoveFromCart('${c.id}')">✕</button>
        </div>
    `).join('');

    const total = posCart.reduce((s, c) => s + c.price * c.qty, 0);
    document.getElementById('pos-subtotal').textContent   = posFmt(total);
    document.getElementById('pos-total').textContent      = posFmt(total);
    document.getElementById('pos-summary').style.display  = 'block';
    document.getElementById('pos-checkout-btn').disabled  = false;
}

function posChangeQty(id, delta) {
    const item = posCart.find(c => c.id === id);
    if (!item) return;
    item.qty += delta;
    if (item.qty <= 0) posRemoveFromCart(id);
    else posRenderCart();
}

function posRemoveFromCart(id) {
    posCart = posCart.filter(c => c.id !== id);
    posRenderCart();
}

function posClearCart() {
    posCart = [];
    posRenderCart();
}

// ─── Paiement ─────────────────────────────────────────────────────────────────
function posSelectPay(el, method) {
    posPayMethod = method;
    document.querySelectorAll('.pay-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
}

const payLabels = {
    cash:          'Espèces',
    amana:         'Amanata',
    nita:          'MyNita',
    western_union: 'Western Union',
    moneygram:     'MoneyGram',
    wave:          'Wave',
};

// ─── Checkout — ouvre la modal de confirmation ────────────────────────────────
function posCheckout() {
    if (!posCart.length) return;

    const total     = posCart.reduce((s, c) => s + c.price * c.qty, 0);
    const isAnon    = posClient.mode === 'anonymous';
    const hasClient = posClient.mode === 'existing' || posClient.mode === 'new';

    const avatarEl = document.getElementById('confirm-client-avatar');
    const nameEl   = document.getElementById('confirm-client-name');
    const phoneEl  = document.getElementById('confirm-client-phone');

    if (isAnon) {
        avatarEl.textContent      = '?';
        avatarEl.style.background = '#f4f2fc';
        avatarEl.style.color      = '#b0a0d0';
        nameEl.textContent        = 'Vente anonyme';
        nameEl.style.fontStyle    = 'italic';
        nameEl.style.color        = '#b0a0d0';
        phoneEl.textContent       = '';
    } else if (hasClient) {
        avatarEl.textContent      = posInitials(posClient.name);
        avatarEl.style.background = 'var(--violet-light)';
        avatarEl.style.color      = 'var(--violet)';
        nameEl.textContent        = posClient.name + (posClient.mode === 'new' ? ' (nouveau)' : '');
        nameEl.style.fontStyle    = 'normal';
        nameEl.style.color        = '#2d2d2d';
        phoneEl.textContent       = posClient.phone || '';
    } else {
        avatarEl.textContent      = '—';
        avatarEl.style.background = '#f4f2fc';
        avatarEl.style.color      = '#b0a0d0';
        nameEl.textContent        = 'Aucun client sélectionné';
        nameEl.style.fontStyle    = 'italic';
        nameEl.style.color        = '#b0a0d0';
        phoneEl.textContent       = '';
    }

    document.getElementById('confirm-items').innerHTML = posCart.map(c => `
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <div>
                <strong>${c.name}</strong>
                <small class="text-muted ms-2">x${c.qty}</small>
            </div>
            <span class="fw-semibold">${posFmt(c.price * c.qty)}</span>
        </div>
    `).join('');

    document.getElementById('confirm-total').textContent = posFmt(total);
    document.getElementById('confirm-pay').textContent   = payLabels[posPayMethod] || posPayMethod;

    new bootstrap.Modal(document.getElementById('confirmModal')).show();
}

// ─── Soumission finale ────────────────────────────────────────────────────────
function posSubmit() {
    document.getElementById('input-pay-method').value = posPayMethod;
    document.getElementById('input-cart-data').value  = JSON.stringify(
        posCart.map(c => ({
            type:       c.type,
            db_id:      c.dbId,
            qty:        c.qty,
            unit_price: c.price,
            total:      c.price * c.qty,
        }))
    );

    const isAnon = posClient.mode === 'anonymous';
    document.getElementById('input-is-anonymous').value  = isAnon ? '1' : '0';
    document.getElementById('input-client-id').value     = posClient.id    ?? '';
    document.getElementById('input-client-name').value   = posClient.name  ?? '';
    document.getElementById('input-client-phone').value  = posClient.phone ?? '';

    bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();

    const toast = document.getElementById('pos-toast');
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 2800);

    document.getElementById('pos-form').submit();
}

// ─── Init ─────────────────────────────────────────────────────────────────────
posRenderItems();
</script>

@endsection