<?php
// On s'assure que la classe User est chargée
if (!class_exists('User')) {
    require_once dirname(__DIR__, 3) . '/Classes/UserClass.php';
}

// ==========================================
// TRAITEMENT DES ACTIONS
// ==========================================
// 1. Suppression d'un utilisateur
if (isset($_GET['delete'])) {
    $idToDelete = (int)$_GET['delete'];
    
    // Sécurité : on empêche l'admin de se supprimer lui-même
    $currentUser = User::current();
    if ($currentUser && $currentUser->id() === $idToDelete) {
        echo "<script>window.location.href='index.php?p=admin_users&msg=self_delete_error';</script>";
        exit;
    }

    User::deleteUser($idToDelete);
    echo "<script>window.location.href='index.php?p=admin_users&msg=deleted';</script>";
    exit;
}

// 2. Modification du rôle (Admin uniquement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $idToUpdate = (int)$_POST['user_id'];
    $newRole = $_POST['role'] ?? 'user';
    User::updateUserRole($idToUpdate, $newRole);
    echo "<script>window.location.href='index.php?p=admin_users&msg=updated';</script>";
    exit;
}

// ==========================================
// RÉCUPÉRATION ET SÉPARATION DES DONNÉES (3 CATÉGORIES)
// ==========================================
$allUsers = User::getAllUsers();
$admins = [];
$contributors = [];
$visitors = [];

foreach ($allUsers as $u) {
    $userRoles = $u['roles'] ? explode(',', $u['roles']) : [];
    
    if (in_array('superadmin', $userRoles) || in_array('admin', $userRoles)) {
        $admins[] = $u;
    } elseif (in_array('contributor', $userRoles)) {
        $contributors[] = $u;
    } else {
        // Tout le reste (user, visitor, subscriber, etc.) va dans Visiteurs
        $visitors[] = $u;
    }
}

$currentUserId = User::current() ? User::current()->id() : 0;
?>

<!-- Message de succès / erreur -->
<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'deleted'): ?>
        <div style="background: rgba(74, 222, 128, 0.1); color: #4ade80; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(74, 222, 128, 0.3);">
            ✅ L'utilisateur a été supprimé avec succès.
        </div>
    <?php elseif ($_GET['msg'] === 'updated'): ?>
        <div style="background: rgba(74, 222, 128, 0.1); color: #4ade80; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(74, 222, 128, 0.3);">
            ✅ Le rôle a été mis à jour avec succès.
        </div>
    <?php elseif ($_GET['msg'] === 'self_delete_error'): ?>
        <div style="background: rgba(255, 75, 92, 0.1); color: #ff4b5c; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(255, 75, 92, 0.3);">
            ❌ Vous ne pouvez pas supprimer votre propre compte.
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- ========================================== -->
<!-- ONGLETS DE NAVIGATION                      -->
<!-- ========================================== -->
<div style="display: flex; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid #333; padding-bottom: 12px; overflow-x: auto;">
    <button class="btn btn-primary" onclick="switchTab('tab-admins', this)">Administrateurs (<?= count($admins) ?>)</button>
    <button class="btn btn-outline" onclick="switchTab('tab-contributors', this)">Contributeurs (<?= count($contributors) ?>)</button>
    <button class="btn btn-outline" onclick="switchTab('tab-visitors', this)">Visiteurs (<?= count($visitors) ?>)</button>
</div>

<!-- ========================================== -->
<!-- SECTION 1 : ADMINISTRATEURS                -->
<!-- ========================================== -->
<div id="tab-admins" class="tab-content">
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 class="card-title" style="margin: 0;">Gestion des Administrateurs</h2>
            <button class="btn btn-primary" onclick="alert('Ouvrir le formulaire d\'ajout d\'Admin')">
                <i class="ph ph-plus"></i> Ajouter un Admin
            </button>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nom complet</th>
                        <th>Adresse Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($admins)): ?>
                        <tr><td colspan="5" style="text-align:center; color:#aaa;">Aucun administrateur trouvé.</td></tr>
                    <?php else: ?>
                        <?php foreach ($admins as $u): 
                            $isSuper = strpos($u['roles'], 'superadmin') !== false;
                        ?>
                        <tr>
                            <td style="font-weight: bold; color: #fff;">
                                <?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?>
                                <?= ($u['id'] == $currentUserId) ? ' <span style="font-size:10px; background:#444; padding:2px 6px; border-radius:4px; margin-left:8px;">Vous</span>' : '' ?>
                            </td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <form method="POST" style="display: flex; gap: 8px; align-items: center;">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <select name="role" style="padding: 6px; border-radius: 4px; background: #121212; color: #fff; border: 1px solid #444; outline:none;">
                                        <option value="superadmin" <?= $isSuper ? 'selected' : '' ?>>Super Admin</option>
                                        <option value="admin" <?= !$isSuper ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                    <button type="submit" name="update_role" class="btn btn-outline" style="padding: 6px 10px;"><i class="ph ph-check"></i> Modifier</button>
                                </form>
                            </td>
                            <td><?= $u['is_active'] ? '<span style="color:#4ade80;">Actif</span>' : '<span style="color:#ff4b5c;">Bloqué</span>' ?></td>
                            <td>
                                <?php if ($u['id'] != $currentUserId): ?>
                                    <button class="btn btn-danger" onclick="confirmDelete('<?= $u['id'] ?>', '<?= htmlspecialchars($u['first_name']) ?>')"><i class="ph ph-trash"></i> Supprimer</button>
                                <?php else: ?>
                                    <span style="color:#aaa; font-size:12px; padding: 8px;">Protégé</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- SECTION 2 : CONTRIBUTEURS                  -->
<!-- ========================================== -->
<div id="tab-contributors" class="tab-content" style="display: none;">
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 class="card-title" style="margin: 0;">Créateurs & Contributeurs</h2>
            <span style="color: #aaa; font-size: 14px;">Seule la suppression est autorisée.</span>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nom complet</th>
                        <th>Adresse Email</th>
                        <th>Statut</th>
                        <th>Inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contributors)): ?>
                        <tr><td colspan="5" style="text-align:center; color:#aaa;">Aucun contributeur trouvé.</td></tr>
                    <?php else: ?>
                        <?php foreach ($contributors as $u): ?>
                        <tr>
                            <td style="font-weight: bold; color: #fff;"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= $u['is_active'] ? '<span style="color:#4ade80;">Actif</span>' : '<span style="color:#ff4b5c;">Bloqué</span>' ?></td>
                            <td style="color: #aaa;"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-danger" onclick="confirmDelete('<?= $u['id'] ?>', '<?= htmlspecialchars($u['first_name']) ?>')"><i class="ph ph-trash"></i> Supprimer</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- SECTION 3 : VISITEURS / UTILISATEURS       -->
<!-- ========================================== -->
<div id="tab-visitors" class="tab-content" style="display: none;">
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 class="card-title" style="margin: 0;">Visiteurs & Abonnés</h2>
            <span style="color: #aaa; font-size: 14px;">Seule la suppression est autorisée.</span>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nom complet</th>
                        <th>Adresse Email</th>
                        <th>Statut</th>
                        <th>Inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($visitors)): ?>
                        <tr><td colspan="5" style="text-align:center; color:#aaa;">Aucun visiteur trouvé.</td></tr>
                    <?php else: ?>
                        <?php foreach ($visitors as $u): ?>
                        <tr>
                            <td style="font-weight: bold; color: #fff;"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= $u['is_active'] ? '<span style="color:#4ade80;">Actif</span>' : '<span style="color:#ff4b5c;">Bloqué</span>' ?></td>
                            <td style="color: #aaa;"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-danger" onclick="confirmDelete('<?= $u['id'] ?>', '<?= htmlspecialchars($u['first_name']) ?>')"><i class="ph ph-trash"></i> Supprimer</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Script JavaScript -->
<script>
    function switchTab(tabId, btn) {
        // Masquer tous les contenus
        document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
        // Afficher le contenu ciblé
        document.getElementById(tabId).style.display = 'block';

        // Gérer la surbrillance des boutons
        const tabs = btn.parentElement.querySelectorAll('button');
        tabs.forEach(b => {
            b.classList.remove('btn-primary');
            b.classList.add('btn-outline');
        });
        btn.classList.remove('btn-outline');
        btn.classList.add('btn-primary');
    }

    function confirmDelete(userId, userName) {
        if (confirm("Êtes-vous sûr de vouloir supprimer définitivement l'utilisateur " + userName + " ? Cette action est irréversible.")) {
            window.location.href = 'index.php?p=admin_users&delete=' + userId;
        }
    }
</script>