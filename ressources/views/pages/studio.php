<?php
// Sécurité : on s'assure que la classe User est chargée
if (!class_exists('User')) {
    require_once dirname(__DIR__, 3) . '/Classes/UserClass.php';
}

$currentUser = User::current();
if (!$currentUser) {
    header('Location: index.php?p=account');
    exit;
}

$userName = htmlspecialchars($currentUser->fullName());
$firstName = htmlspecialchars($currentUser->firstName());
$plan = $_SESSION['plan'] ?? 'free';
$isPremium = ($plan === 'premium');
?>

<!-- BANDEAU DE BIENVENUE -->
<div style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 28px; margin: 0 0 8px; color: #fff;">Bonjour, <?= $firstName ?> 👋</h1>
        <p style="color: #aaa; margin: 0; font-size: 15px;">
            Voici ce qui se passe sur votre chaîne aujourd'hui.
        </p>
    </div>
    <div>
        <span style="background: <?= $isPremium ? 'var(--accent)' : '#333' ?>; color: #fff; padding: 6px 12px; border-radius: 6px; font-weight: bold; text-transform: uppercase; font-size: 12px; border: 1px solid <?= $isPremium ? 'var(--accent)' : '#444' ?>;">
            Plan <?= $plan ?>
        </span>
        <?php if (!$isPremium): ?>
            <a href="index.php?p=subscription" style="color: #3ea6ff; text-decoration: none; font-size: 13px; margin-left: 12px; font-weight: 500;">Passer Premium →</a>
        <?php endif; ?>
    </div>
</div>

<!-- CARTES STATISTIQUES (KPIs) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 32px;">
    <!-- Vues -->
    <div class="dashboard-card" style="margin: 0; padding: 20px;">
        <div style="color: #aaa; font-size: 13px; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Vues (28 j)</div>
        <div style="font-size: 36px; font-weight: bold; color: #fff; line-height: 1;">12 450</div>
        <div style="color: #4ade80; font-size: 13px; margin-top: 12px; display: flex; align-items: center; gap: 4px;">
            <i class="ph-bold ph-trend-up"></i> +12% vs mois dernier
        </div>
    </div>
    
    <!-- Temps de visionnage -->
    <div class="dashboard-card" style="margin: 0; padding: 20px;">
        <div style="color: #aaa; font-size: 13px; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Temps de visionnage</div>
        <div style="font-size: 36px; font-weight: bold; color: #fff; line-height: 1;">845 <span style="font-size: 16px; color: #aaa;">h</span></div>
        <div style="color: #4ade80; font-size: 13px; margin-top: 12px; display: flex; align-items: center; gap: 4px;">
            <i class="ph-bold ph-trend-up"></i> +5% vs mois dernier
        </div>
    </div>

    <!-- Abonnés -->
    <div class="dashboard-card" style="margin: 0; padding: 20px;">
        <div style="color: #aaa; font-size: 13px; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Abonnés</div>
        <div style="font-size: 36px; font-weight: bold; color: #fff; line-height: 1;">+245</div>
        <div style="color: #4ade80; font-size: 13px; margin-top: 12px; display: flex; align-items: center; gap: 4px;">
            <i class="ph-bold ph-trend-up"></i> +18% vs mois dernier
        </div>
    </div>

    <!-- Revenus (Simulation) -->
    <div class="dashboard-card" style="margin: 0; padding: 20px; <?= $isPremium ? 'border-color: rgba(74, 222, 128, 0.3);' : 'opacity: 0.5;' ?>">
        <div style="color: #aaa; font-size: 13px; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Revenus estimés</div>
        <div style="font-size: 36px; font-weight: bold; color: #fff; line-height: 1;">
            <?= $isPremium ? '148,50 $' : '— $' ?>
        </div>
        <div style="color: <?= $isPremium ? '#4ade80' : '#ff4b5c' ?>; font-size: 13px; margin-top: 12px;">
            <?= $isPremium ? '▲ +8% ce mois' : 'Monétisation désactivée' ?>
        </div>
    </div>
</div>

<!-- DERNIÈRES VIDÉOS (TABLEAU) -->
<div class="dashboard-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="dashboard-title" style="margin: 0;">Contenus récents</h2>
        <a href="#" style="color: #3ea6ff; text-decoration: none; font-size: 14px; font-weight: 500;">Voir tout</a>
    </div>
    
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="min-width: 250px;">Vidéo</th>
                    <th>Visibilité</th>
                    <th>Date</th>
                    <th>Vues</th>
                    <th>Likes</th>
                    <th>Commentaires</th>
                </tr>
            </thead>
            <tbody>
                <!-- Ligne 1 -->
                <tr>
                    <td>
                        <div style="display: flex; gap: 16px; align-items: center;">
                            <img src="https://picsum.photos/seed/kopmago/200/112" class="video-thumb-sm" alt="Miniature">
                            <div>
                                <div style="font-weight: 600; color: #fff; margin-bottom: 4px;">KOPMAGO - Le film</div>
                                <div style="font-size: 12px; color: #aaa;">45:00</div>
                            </div>
                        </div>
                    </td>
                    <td><span style="color: #4ade80; font-size: 13px; display: flex; align-items: center; gap: 6px;"><i class="ph-fill ph-globe"></i> Public</span></td>
                    <td style="color: #aaa;">Il y a 2 jours</td>
                    <td>412 k</td>
                    <td>12 k</td>
                    <td>845</td>
                </tr>
                
                <!-- Ligne 2 -->
                <tr>
                    <td>
                        <div style="display: flex; gap: 16px; align-items: center;">
                            <img src="https://picsum.photos/seed/roho/200/112" class="video-thumb-sm" alt="Miniature">
                            <div>
                                <div style="font-weight: 600; color: #fff; margin-bottom: 4px;">Roho Nguvu 2</div>
                                <div style="font-size: 12px; color: #aaa;">1:12:00</div>
                            </div>
                        </div>
                    </td>
                    <td><span style="color: #4ade80; font-size: 13px; display: flex; align-items: center; gap: 6px;"><i class="ph-fill ph-globe"></i> Public</span></td>
                    <td style="color: #aaa;">Il y a 5 jours</td>
                    <td>89 k</td>
                    <td>3.2 k</td>
                    <td>120</td>
                </tr>

                <!-- Ligne 3 -->
                <tr>
                    <td>
                        <div style="display: flex; gap: 16px; align-items: center;">
                            <img src="https://picsum.photos/seed/bhurende/200/112" class="video-thumb-sm" alt="Miniature">
                            <div>
                                <div style="font-weight: 600; color: #fff; margin-bottom: 4px;">Bhurende (Teaser)</div>
                                <div style="font-size: 12px; color: #aaa;">2:30</div>
                            </div>
                        </div>
                    </td>
                    <td><span style="color: #aaa; font-size: 13px; display: flex; align-items: center; gap: 6px;"><i class="ph ph-lock-key"></i> Privé</span></td>
                    <td style="color: #aaa;">À l'instant</td>
                    <td>—</td>
                    <td>—</td>
                    <td>—</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- BANNIÈRE D'ACTION -->
<div class="dashboard-card" style="background: linear-gradient(45deg, #1e1e1e, #2a2a2a); border-color: #444; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h3 style="color: #fff; margin: 0 0 4px;">Prêt à publier une nouvelle vidéo ?</h3>
        <p style="color: #aaa; margin: 0; font-size: 14px;">Partagez votre talent avec votre audience dès maintenant.</p>
    </div>
    <a href="#" style="background: var(--accent); color: #fff; padding: 10px 24px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-flex; align-items: center; gap: 8px;">
        <i class="ph ph-upload-simple"></i> Mettre en ligne
    </a>
</div>