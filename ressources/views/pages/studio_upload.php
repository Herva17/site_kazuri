<?php
// On s'assure que la classe est chargée
if (!class_exists('User')) {
    require_once dirname(__DIR__, 3) . '/Classes/UserClass.php';
}

$currentUser = User::current();
$userId = $currentUser ? $currentUser->id() : 0;

// Récupération des catégories depuis la base de données (si vous avez une classe Category)
// Sinon, on utilise une liste statique basée sur vos tables
try {
    $pdo = Conn::getInstance()->getPdo();
    $categories = $pdo->query("SELECT id, name FROM categories")->fetchAll();
    if (empty($categories)) {
        throw new Exception("Vide");
    }
} catch (Throwable $e) {
    // Fallback si la table est vide
    $categories = [
        ['id' => 1, 'name' => 'Films'],
        ['id' => 2, 'name' => 'Séries'],
        ['id' => 3, 'name' => 'Musique'],
        ['id' => 4, 'name' => 'Reels'],
        ['id' => 5, 'name' => 'Autres']
    ];
}
?>

<div class="dashboard-card">
    <h2 class="dashboard-title">Mettre en ligne une nouvelle vidéo</h2>
    <p style="color: #aaa; font-size: 14px; margin-bottom: 24px;">
        Remplissez les informations ci-dessous. Votre vidéo sera soumise à l'administration pour validation avant d'être publiée.
    </p>

    <div id="upload-alert" style="display:none; padding: 12px; border-radius: 8px; margin-bottom: 20px;"></div>

    <form id="uploadForm" enctype="multipart/form-data">
        <!-- Titre -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; color: #fff; margin-bottom: 8px; font-weight: 500;">Titre de la vidéo *</label>
            <input type="text" name="title" required placeholder="Ex: Roho Nguvu 2 - Bande annonce" 
                   style="width: 100%; padding: 14px; background: #121212; border: 1px solid #444; border-radius: 8px; color: #fff; box-sizing: border-box;">
        </div>

        <!-- Description -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; color: #fff; margin-bottom: 8px; font-weight: 500;">Description</label>
            <textarea name="description" rows="4" placeholder="Décrivez votre contenu..." 
                      style="width: 100%; padding: 14px; background: #121212; border: 1px solid #444; border-radius: 8px; color: #fff; box-sizing: border-box; resize: vertical;"></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Catégorie -->
            <div>
                <label style="display: block; color: #fff; margin-bottom: 8px; font-weight: 500;">Catégorie *</label>
                <select name="category_id" required style="width: 100%; padding: 14px; background: #121212; border: 1px solid #444; border-radius: 8px; color: #fff; box-sizing: border-box;">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Fichier Vidéo -->
            <div>
                <label style="display: block; color: #fff; margin-bottom: 8px; font-weight: 500;">Fichier Vidéo (MP4) *</label>
                <input type="file" name="video_file" accept="video/mp4,video/webm" required 
                       style="width: 100%; padding: 10px; background: #121212; border: 1px solid #444; border-radius: 8px; color: #aaa;">
            </div>
        </div>

        <!-- Miniature -->
        <div style="margin-bottom: 24px;">
            <label style="display: block; color: #fff; margin-bottom: 8px; font-weight: 500;">Miniature (Image JPG/PNG) *</label>
            <input type="file" name="thumbnail_file" accept="image/jpeg,image/png,image/webp" required 
                   style="width: 100%; padding: 10px; background: #121212; border: 1px solid #444; border-radius: 8px; color: #aaa;">
        </div>

        <!-- Champs cachés -->
        <input type="hidden" name="user_id" value="<?= $userId ?>">
        <input type="hidden" name="duration_seconds" id="video_duration" value="0">

        <!-- Bouton de soumission -->
        <button type="button" id="submitBtn" onclick="uploadVideo()" 
                style="background: var(--accent); color: #fff; border: none; padding: 14px 32px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 16px; width: 100%;">
            <i class="ph ph-upload-simple"></i> Soumettre la vidéo pour validation
        </button>
    </form>
</div>

<!-- Script pour gérer l'upload et lire la durée de la vidéo -->
<script>
    // 1. Fonction pour lire la durée de la vidéo avant l'envoi
    document.querySelector('input[name="video_file"]').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const video = document.createElement('video');
            video.preload = 'metadata';
            video.onloadedmetadata = function() {
                window.URL.revokeObjectURL(video.src);
                document.getElementById('video_duration').value = Math.round(video.duration);
            }
            video.src = URL.createObjectURL(file);
        }
    });

    // 2. Fonction d'envoi AJAX
    async function uploadVideo() {
        const form = document.getElementById('uploadForm');
        const btn = document.getElementById('submitBtn');
        const alertBox = document.getElementById('upload-alert');
        
        // Validation basique
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        btn.disabled = true;
        btn.innerHTML = 'Envoi en cours... (Patientez)';
        alertBox.style.display = 'none';

        const formData = new FormData(form);

        try {
            // Appel à l'API
            const response = await fetch('api/upload_video.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (!data.ok) throw new Error(data.error || 'Erreur lors de l\'envoi');

            // Succès
            alertBox.style.display = 'block';
            alertBox.style.background = 'rgba(74, 222, 128, 0.1)';
            alertBox.style.color = '#4ade80';
            alertBox.style.border = '1px solid rgba(74, 222, 128, 0.3)';
            alertBox.innerHTML = '✅ Vidéo envoyée avec succès ! Redirection...';

            setTimeout(() => {
                window.location.href = 'index.php?p=studio_contents';
            }, 2000);

        } catch (error) {
            alertBox.style.display = 'block';
            alertBox.style.background = 'rgba(255, 75, 92, 0.1)';
            alertBox.style.color = '#ff4b5c';
            alertBox.style.border = '1px solid rgba(255, 75, 92, 0.3)';
            alertBox.innerHTML = '❌ ' + error.message;
            
            btn.disabled = false;
            btn.innerHTML = '<i class="ph ph-upload-simple"></i> Soumettre la vidéo pour validation';
        }
    }
</script>