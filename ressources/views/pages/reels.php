<h2 class="section-title">Reels</h2>
<div class="video-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
    <?php for($i=1; $i<=5; $i++): ?>
    <a href="index.php?p=detail&id=r<?= $i ?>" class="video-card">
        <div class="thumbnail-container" style="aspect-ratio: 9/16;">
            <img src="https://picsum.photos/seed/reel<?= $i ?>/400/711" alt="Reel">
            <span class="duration-badge">0:45</span>
        </div>
        <div class="video-info">
            <div class="video-text">
                <h3 class="video-title">Danse Afro à Lagos <?= $i ?></h3>
                <p class="video-meta">1.2 M vues</p>
            </div>
        </div>
    </a>
    <?php endfor; ?>
</div>