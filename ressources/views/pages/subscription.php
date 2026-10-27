<?php
// Gestion de la sélection (Simulation)
if (isset($_GET['plan'])) {
    $_SESSION['plan'] = $_GET['plan'];
    header('Location: index.php?p=studio');
    exit;
}
?>

<style>
    .pricing-container { max-width: 1100px; margin: 0 auto; text-align: center; padding: 40px 20px; }
    .pricing-title { font-size: 36px; margin-bottom: 10px; color: #fff; }
    .pricing-subtitle { color: #aaa; margin-bottom: 50px; font-size: 18px; }
    .pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; }
    
    .price-card { 
        background: #1e1e1e; border: 1px solid #333; border-radius: 16px; padding: 32px; 
        text-align: left; display: flex; flex-direction: column; position: relative;
    }
    .price-card.popular { border-color: var(--accent); box-shadow: 0 0 20px rgba(255,45,58,0.15); }
    .popular-badge { 
        position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
        background: var(--accent); color: #fff; padding: 4px 12px; border-radius: 20px; 
        font-size: 12px; font-weight: bold; text-transform: uppercase; 
    }
    .plan-name { font-size: 24px; font-weight: bold; margin-bottom: 8px; color: #fff; }
    .plan-price { font-size: 42px; font-weight: 900; color: #fff; margin-bottom: 16px; }
    .plan-price span { font-size: 16px; color: #aaa; font-weight: normal; }
    .plan-features { list-style: none; padding: 0; margin: 20px 0; flex: 1; }
    .plan-features li { padding: 10px 0; color: #ccc; border-bottom: 1px solid #333; display: flex; gap: 10px; align-items: center; }
    .plan-features li i { color: #4ade80; font-size: 18px; }
    
    .btn-plan { 
        display: block; width: 100%; padding: 14px; border-radius: 8px; text-align: center; 
        text-decoration: none; font-weight: bold; transition: 0.2s; 
    }
    .btn-free { background: #333; color: #fff; }
    .btn-free:hover { background: #444; }
    .btn-premium { background: var(--accent); color: #fff; }
    .btn-premium:hover { background: #e02532; }
</style>

<div class="pricing-container">
    <h1 class="pricing-title">Choisissez votre plan Créateur</h1>
    <p class="pricing-subtitle">Développez votre audience sur Kazuri avec les outils professionnels.</p>

    <div class="pricing-grid">
        <!-- PLAN GRATUIT -->
        <div class="price-card">
            <div class="plan-name">Découverte</div>
            <div class="plan-price">0 $ <span>/ mois</span></div>
            <ul class="plan-features">
                <li><i class="ph-bold ph-check"></i> 3 vidéos par mois</li>
                <li><i class="ph-bold ph-check"></i> Qualité max 720p</li>
                <li><i class="ph-bold ph-check"></i> Analytics de base</li>
                <li><i class="ph-bold ph-x" style="color:#ff4b5c"></i> Monétisation bloquée</li>
            </ul>
            <a href="index.php?p=subscription&plan=free" class="btn-plan btn-free">Commencer gratuitement</a>
        </div>

        <!-- PLAN PREMIUM -->
        <div class="price-card popular">
            <div class="popular-badge">Recommandé</div>
            <div class="plan-name">Premium</div>
            <div class="plan-price">9,99 $ <span>/ mois</span></div>
            <ul class="plan-features">
                <li><i class="ph-bold ph-check"></i> Vidéos illimitées</li>
                <li><i class="ph-bold ph-check"></i> Qualité 4K Ultra HD</li>
                <li><i class="ph-bold ph-check"></i> Analytics avancés</li>
                <li><i class="ph-bold ph-check"></i> Monétisation (70% pour vous)</li>
            </ul>
            <a href="index.php?p=subscription&plan=premium" class="btn-plan btn-premium">Passer Premium</a>
        </div>
    </div>
</div>