<h2 class="section-title">Séries</h2>
<div class="video-grid">
    <?php for($i=1; $i<=4; $i++): ?>
    <a href="index.php?p=detail&id=s<?= $i ?>" class="video-card">
        <div class="thumbnail-container">
            <img src="https://picsum.photos/seed/serie<?= $i ?>/600/338" alt="Série">
            <span class="duration-badge">Saison 1 · 10 ép.</span>
        </div>
        <div class="video-info">
            <img src="https://ui-avatars.com/api/?name=S+<?= $i ?>&background=333&color=fff" class="channel-avatar">
            <div class="video-text">
                <h3 class="video-title">Majaribu - Saison <?= $i ?></h3>
                <p class="video-meta">Kazuri Studio</p>
                <p class="video-meta">89 k vues · il y a 5 jours</p>
            </div>
        </div>
    </a>
    <?php endfor; ?>
</div>