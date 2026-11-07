<?php
// On récupère le plan depuis l'URL (ex: index.php?p=checkout&plan=premium)
$selectedPlan = $_GET['plan'] ?? 'premium';

// Simulation de validation de paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $_SESSION['plan'] = $selectedPlan; // On sauvegarde le plan
    header('Location: index.php?p=studio'); // Redirection vers le Studio
    exit;
}

$planName = $selectedPlan === 'premium' ? 'Premium' : 'Découverte';
$planPrice = $selectedPlan === 'premium' ? '9,99 $' : '0 $';
?>

<style>
    .checkout-container { max-width: 1000px; margin: 20px auto; display: grid; grid-template-columns: 1fr 1.2fr; gap: 32px; padding: 0 20px; }
    @media (max-width: 768px) { .checkout-container { grid-template-columns: 1fr; } }

    /* Résumé de la commande */
    .order-summary { background: #1e1e1e; border: 1px solid #333; border-radius: 16px; padding: 32px; height: fit-content; }
    .order-title { color: #fff; font-size: 22px; margin-bottom: 24px; border-bottom: 1px solid #333; padding-bottom: 16px; }
    .order-item { display: flex; justify-content: space-between; margin-bottom: 16px; color: #ccc; }
    .order-total { display: flex; justify-content: space-between; margin-top: 24px; padding-top: 16px; border-top: 2px solid #333; font-size: 20px; font-weight: bold; color: #fff; }
    .order-badge { background: rgba(74, 222, 128, 0.1); color: #4ade80; padding: 12px; border-radius: 8px; font-size: 13px; margin-top: 24px; display: flex; gap: 8px; align-items: center; }

    /* Formulaire de paiement */
    .payment-form { background: #1e1e1e; border: 1px solid #333; border-radius: 16px; padding: 32px; }
    .payment-title { color: #fff; font-size: 22px; margin-bottom: 24px; }
    .payment-methods { display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap; }
    .pm-btn { background: #272727; border: 1px solid #444; padding: 10px 16px; border-radius: 8px; color: #aaa; cursor: pointer; display: flex; align-items: center; gap: 8px; font-weight: 500; transition: 0.2s; }
    .pm-btn.active { border-color: var(--accent); color: #fff; background: rgba(255,45,58,0.1); }
    
    .input-group { margin-bottom: 16px; }
    .input-group label { display: block; color: #aaa; font-size: 13px; margin-bottom: 8px; }
    .input-group input, .input-group select { width: 100%; background: #121212; border: 1px solid #333; padding: 14px; border-radius: 8px; color: #fff; font-size: 15px; box-sizing: border-box; }
    .input-group input:focus, .input-group select:focus { border-color: var(--accent); outline: none; }
    
    .btn-confirm { background: var(--accent); color: #fff; width: 100%; padding: 16px; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 24px; transition: 0.2s; }
    .btn-confirm:hover { background: #e02532; }
    .secure-text { text-align: center; color: #888; font-size: 12px; margin-top: 16px; display: flex; justify-content: center; align-items: center; gap: 6px; }
</style>

<div class="checkout-container">
    
    <!-- RÉSUMÉ DE LA COMMANDE -->
    <div class="order-summary">
        <h2 class="order-title">Résumé de la commande</h2>
        <div class="order-item">
            <span>Plan <?= $planName ?></span>
            <span><?= $planPrice ?> / mois</span>
        </div>
        <div class="order-item">
            <span>Frais de dossier</span>
            <span>0,00 $</span>
        </div>
        <div class="order-total">
            <span>Total à payer</span>
            <span><?= $planPrice ?></span>
        </div>
        <div class="order-badge">
            <i class="ph-fill ph-check-circle" style="font-size: 20px;"></i>
            <span>Garantie satisfait ou remboursé 30 jours.</span>
        </div>
    </div>

    <!-- FORMULAIRE DE PAIEMENT -->
    <div class="payment-form">
        <h2 class="payment-title">Mode de paiement</h2>
        
        <form method="POST">
            <div class="payment-methods">
                <button type="button" class="pm-btn active"><i class="ph ph-device-mobile"></i> Mobile Money</button>
                <button type="button" class="pm-btn"><i class="ph ph-credit-card"></i> Carte Bancaire</button>
            </div>

            <!-- Champs Mobile Money (Simulation) -->
            <div class="input-group">
                <label>Opérateur Mobile</label>
                <select>
                    <option>Vodacom M-Pesa</option>
                    <option>Airtel Money</option>
                    <option>Orange Money</option>
                </select>
            </div>

            <div class="input-group">
                <label>Numéro de téléphone</label>
                <input type="tel" placeholder="+243 81 000 00 00" required>
            </div>

            <div class="input-group">
                <label>Nom complet sur le compte</label>
                <input type="text" placeholder="Ex: Hervé IRAGI" required>
            </div>

            <button type="submit" name="confirm_payment" class="btn-confirm">
                Payer <?= $planPrice ?> et activer le compte
            </button>

            <p class="secure-text">
                <i class="ph-fill ph-lock-key"></i> Paiement 100% sécurisé. Chiffrement SSL.
            </p>
        </form>
    </div>
</div>