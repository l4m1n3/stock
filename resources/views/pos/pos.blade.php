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
    .pos-item-name { font-size: 14px; font-weight: 500; color: #2d2d2d; }
    .pos-item-meta { font-size: 12px; color: #9e8fc0; margin-top: 2px; }
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
    .pos-add-btn:hover { background: var(--violet-hover); }
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
    .pos-cart { overflow-y: auto; max-height: 280px; flex: 1; }
    .cart-empty {
        padding: 50px 20px;
        text-align: center;
        color: #b0a0d0;
        font-size: 14px;
    }
    .cart-empty-icon { font-size: 36px; margin-bottom: 10px; }
    .cart-row {
        display: grid;
        grid-template-columns: 1fr auto auto auto;
        gap: 8px;
        align-items: center;
        padding: 10px 16px;
        border-bottom: 1px solid #f7f4ff;
    }
    .cart-row:last-child { border-bottom: none; }
    .cart-item-name { font-size: 13px; font-weight: 500; color: #2d2d2d; }
    .cart-item-unit { font-size: 11px; color: #9e8fc0; }
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
    .checkout-btn:active { transform: translateY(0); }
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

    /* Clear btn */
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
            <span style="font-size:12px;color:#9e8fc0;" id="item-count">{{ count($products) + count($services) }} articles</span>
        </div>

        <div class="pos-search">
            <input type="text" id="pos-search" placeholder="Rechercher un produit ou service..." oninput="posFilter()">
        </div>

        <div class="pos-tabs">
            <button class="pos-tab active" onclick="posTab(this,'all')">Tout</button>
            <button class="pos-tab" onclick="posTab(this,'produit')">Produits</button>
            <button class="pos-tab" onclick="posTab(this,'service')">Services</button>
        </div>

        <div class="pos-items" id="pos-items-list"></div>
    </div>

    {{-- ==================== PANIER ==================== --}}
    <div class="pos-panel">
        <div class="pos-panel-header">
            <span class="pos-panel-title">Panier en cours</span>
            <button class="clear-btn" onclick="posClearCart()">Vider le panier</button>
        </div>

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
                    <span class="pay-icon">📱</span> Amana
                </button>
                <button class="pay-btn" onclick="posSelectPay(this,'nita')">
                    <span class="pay-icon">💳</span> Nita
                </button>
            </div>

            <form id="pos-form" action="" method="POST">
                @csrf
                <input type="hidden" name="payment_method" id="input-pay-method" value="cash">
                <input type="hidden" name="cart_data" id="input-cart-data" value="[]">
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
                <h5 class="modal-title text-white fw-bold"><i class="fas fa-file-invoice me-2"></i>Confirmer la vente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
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
// ─── Catalogue injecté depuis Laravel ───────────────────────────────────────
const posCatalog = [
    @foreach($products as $p)
    {
        id: 'p{{ $p->id }}',
        name: @json($p->name),
        type: 'produit',
        price: {{ $p->price }},
        stock: {{ $p->stock_quantity }},
        dbId: {{ $p->id }}
    },
    @endforeach
    @foreach($services as $s)
    {
        id: 's{{ $s->id }}',
        name: @json($s->name),
        type: 'service',
        price: {{ $s->price }},
        stock: null,
        dbId: {{ $s->id }},
        serviceType: @json($s->type)
    },
    @endforeach
];

// ─── État ───────────────────────────────────────────────────────────────────
let posCart = [];
let posActiveTab = 'all';
let posPayMethod = 'cash';

// ─── Utilitaires ─────────────────────────────────────────────────────────────
function posFmt(n) {
    return n.toLocaleString('fr-FR') + ' FCFA';
}

function posStockBadge(item) {
    if (item.type === 'service') return '<span class="badge-stock badge-purple">Service</span>';
    if (item.stock > 5)  return `<span class="badge-stock badge-green">En stock (${item.stock})</span>`;
    if (item.stock > 0)  return `<span class="badge-stock badge-amber">Limité (${item.stock})</span>`;
    return '<span class="badge-stock badge-red">Rupture</span>';
}

// ─── Rendu catalogue ─────────────────────────────────────────────────────────
function posRenderItems() {
    const q = document.getElementById('pos-search').value.toLowerCase();
    const filtered = posCatalog.filter(i => {
        const matchTab = posActiveTab === 'all' || i.type === posActiveTab;
        const matchQ   = i.name.toLowerCase().includes(q);
        return matchTab && matchQ;
    });
    document.getElementById('item-count').textContent = filtered.length + ' articles';
    document.getElementById('pos-items-list').innerHTML = filtered.map(i => `
        <div class="pos-item">
            <div>
                <div class="pos-item-name">${i.name}</div>
                <div class="pos-item-meta">${posStockBadge(i)}</div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <span class="pos-item-price">${posFmt(i.price)}</span>
                <button class="pos-add-btn" onclick="posAddToCart('${i.id}')"
                    ${i.type === 'produit' && i.stock === 0 ? 'disabled' : ''}>+</button>
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

// ─── Gestion panier ──────────────────────────────────────────────────────────
function posAddToCart(id) {
    const item = posCatalog.find(i => i.id === id);
    const existing = posCart.find(c => c.id === id);
    if (existing) {
        existing.qty++;
    } else {
        posCart.push({ ...item, qty: 1 });
    }
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
    document.getElementById('pos-subtotal').textContent = posFmt(total);
    document.getElementById('pos-total').textContent = posFmt(total);
    document.getElementById('pos-summary').style.display = 'block';
    document.getElementById('pos-checkout-btn').disabled = false;
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

// ─── Paiement ────────────────────────────────────────────────────────────────
function posSelectPay(el, method) {
    posPayMethod = method;
    document.querySelectorAll('.pay-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
}

const payLabels = { cash: 'Espèces', amana: 'Amana Mobile Money', nita: 'Nita' };

// ─── Checkout ────────────────────────────────────────────────────────────────
function posCheckout() {
    if (!posCart.length) return;

    // Remplir la modal de confirmation
    const total = posCart.reduce((s, c) => s + c.price * c.qty, 0);
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
    document.getElementById('confirm-pay').textContent = payLabels[posPayMethod] || posPayMethod;

    new bootstrap.Modal(document.getElementById('confirmModal')).show();
}

function posSubmit() {
    // Remplir les champs cachés
    document.getElementById('input-pay-method').value = posPayMethod;
    document.getElementById('input-cart-data').value = JSON.stringify(
        posCart.map(c => ({
            type:      c.type,
            db_id:     c.dbId,
            qty:       c.qty,
            unit_price: c.price,
            total:     c.price * c.qty
        }))
    );

    // Fermer la modal et soumettre
    bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();

    // Toast + soumission
    const toast = document.getElementById('pos-toast');
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 2800);

    // Soumettre le formulaire
    document.getElementById('pos-form').submit();
}

// ─── Init ────────────────────────────────────────────────────────────────────
posRenderItems();
</script>

@endsection