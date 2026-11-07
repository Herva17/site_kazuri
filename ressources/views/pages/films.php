<h2 class="section-title">Films</h2>
<div class="video-grid">
    <?php for($i=1; $i<=6; $i++): ?>
    <a href="index.php?p=detail&id=<?= $i ?>" class="video-card">
        <div class="thumbnail-container">
            <img src="https://picsum.photos/seed/film<?= $i ?>/600/338" alt="Film">
            <span class="duration-badge">1:45:00</span>
        </div>
        <div class="video-info">
            <img src="https://ui-avatars.com/api/?name=F+<?= $i ?>&background=333&color=fff" class="channel-avatar">
            <div class="video-text">
                <h3 class="video-title">Film Épique Gomatracien <?= $i ?></h3>
                <p class="video-meta">Kazuri Studio</p>
                <p class="video-meta">412 k vues · il y a 2 jours</p>
            </div>
        </div>
    </a>
    <?php endfor; ?>
</div>