<?php
/**
 * pages/pricing.php — Page tarifs dynamique (PHP + CSS + HTML dans un seul fichier)
 */

// ✅ CONNEXION BDD — ces 2 lignes manquaient
require_once ROOT . '/Classes/Conn.php';
$pdo = Conn::getInstance()->getPdo();

// ---------- Helpers ----------
if (!function_exists('plan_price_display')) {
    function plan_price_display(float $price, string $currency = '$'): string
    {
        $decimals = (fmod($price, 1.0) == 0.0) ? 0 : 2;
        return number_format($price, $decimals, ',', ' ') . ' ' . $currency;
    }
}

if (!function_exists('plan_features_array')) {
    function plan_features_array(?string $raw): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            $lines = preg_split('/\r\n|\r|\n/', trim($raw));
            $data  = array_filter(array_map('trim', $lines));
        }

        $out = [];
        foreach ($data as $item) {
            if (is_string($item)) {
                $included = true;
                $text     = $item;
                if (str_starts_with($text, '-') || str_starts_with($text, '✗')) {
                    $included = false;
                    $text     = ltrim($text, '-✗ ');
                }
                $out[] = ['text' => $text, 'included' => $included];
            } elseif (is_array($item)) {
                $out[] = [
                    'text'     => (string)($item['text'] ?? $item['label'] ?? ''),
                    'included' => (bool)($item['included'] ?? true),
                ];
            }
        }
        return $out;
    }
}

if (!function_exists('plan_cta_url')) {
    function plan_cta_url(array $plan): string
    {
        if (!empty($plan['cta_url'])) {
            return $plan['cta_url'];
        }
        if ((float)$plan['price'] <= 0) {
            return 'index.php?p=studio&plan=' . urlencode($plan['name']);
        }
        return 'index.php?p=checkout&plan=' . urlencode($plan['name']);
    }
}

// ---------- Récupération des plans ----------
$plans = [];
try {
    $stmt = $pdo->query("
        SELECT id, name, label, price, currency, price_suffix, duration_days,
               description, features, badge, is_popular, cta_text, cta_url, sort_order
        FROM plans
        WHERE is_active = 1
        ORDER BY sort_order ASC, price ASC
    ");
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('[pricing] ' . $e->getMessage());
}
?>

<style>
    .pricing-container { max-width: 1100px; margin: 0 auto; text-align: center; padding: 40px 20px; }
    .pricing-title { font-size: 36px; margin-bottom: 10px; color: #fff; }
    .pricing-subtitle { color: #aaa; margin-bottom: 50px; font-size: 18px; }
    .pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; }
    .pricing-empty { color: #888; padding: 40px 0; font-size: 16px; }

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
    .plan-desc { color: #888; font-size: 14px; margin: 0 0 8px; line-height: 1.4; }

    .plan-features { list-style: none; padding: 0; margin: 20px 0; flex: 1; }
    .plan-features li {
        padding: 10px 0; color: #ccc; border-bottom: 1px solid #333;
        display: flex; gap: 10px; align-items: center;
    }
    .plan-features li i { color: #4ade80; font-size: 18px; }
    .plan-features li.excluded { color: #777; text-decoration: line-through; }
    .plan-features li.excluded i { color: #ff4b5c; text-decoration: none; }

    .btn-plan {
        display: block; width: 100%; padding: 14px; border-radius: 8px; text-align: center;
        text-decoration: none; font-weight: bold; transition: 0.2s; box-sizing: border-box;
    }
    .btn-free { background: #333; color: #fff; }
    .btn-free:hover { background: #444; }
    .btn-premium { background: var(--accent); color: #fff; }
    .btn-premium:hover { background: #e02532; }
</style>

<div class="pricing-container">
    <h1 class="pricing-title">Choisissez votre plan Créateur</h1>
    <p class="pricing-subtitle">Développez votre audience sur Kazuri avec les outils professionnels.</p>

    <?php if (empty($plans)): ?>
        <p class="pricing-empty">Aucun plan n'est disponible pour le moment.</p>
    <?php else: ?>
        <div class="pricing-grid">
            <?php foreach ($plans as $plan):
                $popular  = !empty($plan['is_popular']);
                $features = plan_features_array($plan['features'] ?? null);
                $label    = ($plan['label'] ?? '') !== '' ? $plan['label'] : ucfirst($plan['name']);
                $price    = (float)$plan['price'];
                $suffix   = $plan['price_suffix'] ?? '';
                $badge    = $plan['badge'] ?? '';
                $ctaText  = ($plan['cta_text'] ?? '') !== ''
                            ? $plan['cta_text']
                            : ($price > 0 ? 'Choisir ce plan' : 'Commencer gratuitement');
                $ctaUrl   = plan_cta_url($plan);
                ?>
                <div class="price-card <?= $popular ? 'popular' : '' ?>"
                     data-plan="<?= htmlspecialchars($plan['name'], ENT_QUOTES) ?>">

                    <?php if ($badge !== ''): ?>
                        <div class="popular-badge"><?= htmlspecialchars($badge) ?></div>
                    <?php endif; ?>

                    <div class="plan-name"><?= htmlspecialchars($label) ?></div>

                    <div class="plan-price">
                        <?= htmlspecialchars(plan_price_display($price, $plan['currency'] ?: '$')) ?>
                        <?php if ($suffix !== ''): ?>
                            <span><?= htmlspecialchars($suffix) ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($plan['description'])): ?>
                        <p class="plan-desc"><?= htmlspecialchars($plan['description']) ?></p>
                    <?php endif; ?>

                    <ul class="plan-features">
                        <?php foreach ($features as $f): ?>
                            <li class="<?= $f['included'] ? '' : 'excluded' ?>">
                                <i class="ph-bold <?= $f['included'] ? 'ph-check' : 'ph-x' ?>"></i>
                                <?= htmlspecialchars($f['text']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <a href="<?= htmlspecialchars($ctaUrl, ENT_QUOTES) ?>"
                       class="btn-plan <?= $popular ? 'btn-premium' : 'btn-free' ?>">
                        <?= htmlspecialchars($ctaText) ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>