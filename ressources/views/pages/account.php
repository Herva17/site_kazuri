<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/Classes/UserClass.php';

// On récupère l'utilisateur courant
$current = User::current();

// Petit helper de traduction si absent
if (!function_exists('t')) {
    function t(string $s): string { return $s; }
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Compte — Kazuri</title>
<link rel="stylesheet" href="assets/style.css">

<style>
  /* --- GLOBAL & CONTRASTE --- */
  .account-section {
    color: var(--text, #ffffff);
    padding: 40px 0;
  }
  
  .form-group {
    margin-bottom: 18px;
  }

  .account-tabs{display:flex;gap:8px;margin-bottom:24px;border-bottom:1px solid var(--line,#333)}
  .account-tabs button{
    background:none;border:0;padding:12px 18px;font-weight:600;cursor:pointer;
    color:var(--muted,#aaa);border-bottom:2px solid transparent;transition:all .2s;
  }
  .account-tabs button.on{color:var(--acc,#ff2d3a);border-bottom-color:var(--acc,#ff2d3a)}

  /* Messages */
  .account-error {
    color: #ff4b5c; font-size: 14px; margin: 8px 0 16px; padding: 12px; border-radius: 8px;
    background: rgba(255,75,92,.1); display: none; border: 1px solid rgba(255,75,92,.3);
  }
  .account-error.show { display: block; }

  .account-success {
    color: #4ade80; font-size: 14px; margin: 8px 0 16px; padding: 12px; border-radius: 8px;
    background: rgba(74, 222, 128, 0.1); display: none; border: 1px solid rgba(74, 222, 128, 0.3);
  }
  .account-success.show { display: block; }

  .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
  @media(max-width:600px){.form-row{grid-template-columns:1fr}}

  /* --- LABELS & INPUTS --- */
  .account-form-fields label:not(.sr-only) {
    display: block;
    margin-bottom: 8px;
    color: #dddddd;
    font-size: 14px;
  }

  .account-form-fields select,
  .account-form-fields input:not([type="radio"]):not([type="checkbox"]),
  .account-form-fields textarea{
    width:100%;padding:14px 16px;border-radius:8px;
    border:1px solid var(--line,#444);background:var(--bg2,#1a1d24);
    color: #ffffff !important;
    font-size:15px;transition:border-color .2s;
  }
  
  .account-form-fields input::placeholder,
  .account-form-fields textarea::placeholder {
    color: #888888 !important;
  }

  .account-form-fields input:focus,
  .account-form-fields select:focus{
    border-color:var(--acc,#ff2d3a);outline:none;
  }

  .account-form-fields input[type=submit]{
    cursor:pointer;background:var(--acc,#ff2d3a);color:#fff;border:none;
    font-weight:700;letter-spacing:.5px;transition:opacity .2s;
    padding: 15px 20px; border-radius: 8px;
  }
  .account-form-fields input[type=submit]:hover{opacity:.9}
  .account-form-fields input[type=submit]:disabled{opacity:.6;cursor:not-allowed}
  .account-pass-hint{font-size:12px;color:#aaa;margin-top:6px}

  /* --- SECTION AVATAR --- */
  .avatar-label {
    display: block;
    margin-bottom: 10px;
    font-size: 14px;
    color: #dddddd;
  }
  .account-avatar-pick {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
  }
  .avatar-option {
    cursor: pointer;
    position: relative;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    border: 3px solid transparent;
    transition: all .2s ease;
    padding: 2px;
    background: #1a1d24;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .avatar-option img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
  }
  .avatar-option:hover {
    transform: scale(1.05);
    border-color: rgba(255, 45, 58, 0.5);
  }
  .avatar-option.on {
    border-color: var(--acc, #ff2d3a);
    box-shadow: 0 0 0 2px rgba(255, 45, 58, 0.2);
  }

  /* --- CHECKBOX PERSONNALISÉE --- */
  .account-terms label, .login-extra label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-size: 14px;
    color: #dddddd;
  }
  .account-terms input[type="checkbox"], .login-extra input[type="checkbox"] {
    appearance: none;
    -webkit-appearance: none;
    width: 20px;
    height: 20px;
    border: 2px solid #555;
    border-radius: 4px;
    background: transparent;
    cursor: pointer;
    position: relative;
    flex-shrink: 0;
    transition: all .2s;
  }
  .account-terms input[type="checkbox"]:checked, .login-extra input[type="checkbox"]:checked {
    background: var(--acc, #ff2d3a);
    border-color: var(--acc, #ff2d3a);
  }
  .account-terms input[type="checkbox"]:checked::after, .login-extra input[type="checkbox"]:checked::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 14px;
    font-weight: bold;
  }
  
  /* --- LOGIN SPECIFIQUE --- */
  .login-extra {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    font-size: 14px;
  }
  .login-extra a {
    color: var(--acc, #ff2d3a);
    text-decoration: none;
    transition: opacity .2s;
  }
  .login-extra a:hover {
    opacity: 0.8;
    text-decoration: underline;
  }

  .sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border-width: 0;
  }
  
  .social-separator {
    display: flex;
    align-items: center;
    text-align: center;
    margin: 24px 0 16px;
    color: #888;
    font-size: 14px;
  }
  .social-separator::before, .social-separator::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #444;
  }
  .social-separator span { padding: 0 12px; }
  
  .account-social a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #fff;
    text-decoration: none;
    padding: 10px 16px;
    border: 1px solid #444;
    border-radius: 8px;
    background: rgba(255,255,255,0.03);
    transition: all .2s;
    font-size: 14px;
    margin-right: 8px;
  }
  .account-social a:hover {
    background: rgba(255,255,255,0.08);
    border-color: #666;
  }

  /* --- SELECT PAYS avec drapeaux --- */
  #country {
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: 40px;
    font-family: "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif;
  }
</style>
</head>
<body>

<section class="content-section account-section" data-background="assets/images/section-pattern01.png">
  <div class="container">
    <div class="row align-items-center account-layout">
      <div class="col-lg-7">
        <div class="membership account-form">
          <p class="account-eyebrow"><?= t('KAZURI · COMPTE') ?></p>
          <h2 id="auth-title" style="color: #fff; margin-bottom: 10px;"><?= t('Rejoignez Kazuri') ?></h2>
          <p class="account-intro" id="auth-subtitle" style="color: #aaa; margin-bottom: 24px;">
            <?= t('Créez votre compte pour retrouver vos films et séries préférés.') ?>
          </p>

          <!-- Onglets -->
          <div class="account-tabs">
            <button id="tabSignup" class="on" onclick="setMode('signup')"><?= t('Inscription') ?></button>
            <button id="tabLogin" onclick="setMode('login')"><?= t('Connexion') ?></button>
          </div>

          <!-- Messages -->
          <div id="auth-error" class="account-error"></div>
          <div id="auth-success" class="account-success"></div>

          <form class="account-form-fields" id="auth-form" onsubmit="return false">

            <!-- ===== INSCRIPTION UNIQUEMENT ===== -->
            <div id="signup-fields">
              <div class="form-row">
                <div class="form-group">
                  <label class="sr-only" for="first_name"><?= t('Prénom') ?></label>
                  <input id="first_name" name="first_name" type="text"
                         placeholder="<?= t('Prénom') ?>" autocomplete="given-name">
                </div>
                <div class="form-group">
                  <label class="sr-only" for="last_name"><?= t('Nom') ?></label>
                  <input id="last_name" name="last_name" type="text"
                         placeholder="<?= t('Nom') ?>" autocomplete="family-name">
                </div>
              </div>
            </div>

            <!-- ===== TOUJOURS VISIBLE ===== -->
            <div class="form-group">
              <label class="sr-only" for="email"><?= t('Adresse e-mail') ?></label>
              <input id="email" name="email" type="email"
                     placeholder="<?= t('Adresse e-mail') ?>" autocomplete="email" required>
            </div>

            <div class="form-group">
              <label class="sr-only" for="password"><?= t('Mot de passe') ?></label>
              <input id="password" name="password" type="password"
                     placeholder="<?= t('Mot de passe (6 caractères minimum)') ?>"
                     autocomplete="new-password" required minlength="6">
              <div class="account-pass-hint" id="pass-hint"><?= t('6 caractères minimum') ?></div>
            </div>

            <!-- ===== INSCRIPTION UNIQUEMENT ===== -->
            <div class="form-group" id="confirm-group">
              <label class="sr-only" for="password_confirm"><?= t('Confirmer le mot de passe') ?></label>
              <input id="password_confirm" name="password_confirm" type="password"
                     placeholder="<?= t('Confirmer le mot de passe') ?>"
                     autocomplete="new-password">
            </div>

            <div class="form-row" id="signup-extra">
              <div class="form-group">
                <label class="sr-only" for="phone"><?= t('Téléphone (Mobile Money)') ?></label>
                <input id="phone" name="phone" type="tel"
                       placeholder="<?= t('Téléphone (+243 …)') ?>" autocomplete="tel">
              </div>
              <div class="form-group">
                <label class="sr-only" for="country"><?= t('Pays') ?></label>
                <select id="country" name="country" autocomplete="country">
                  <option value=""><?= t('Sélectionnez votre pays') ?></option>
                  <option value="Afrique du Sud">🇿🇦 Afrique du Sud</option>
                  <option value="Algérie">🇩🇿 Algérie</option>
                  <option value="Angola">🇦🇴 Angola</option>
                  <option value="Bénin">🇧🇯 Bénin</option>
                  <option value="Botswana">🇧🇼 Botswana</option>
                  <option value="Burkina Faso">🇧🇫 Burkina Faso</option>
                  <option value="Burundi">🇧🇮 Burundi</option>
                  <option value="Cameroun">🇨🇲 Cameroun</option>
                  <option value="Cap-Vert">🇨🇻 Cap-Vert</option>
                  <option value="Comores">🇰🇲 Comores</option>
                  <option value="Côte d'Ivoire">🇨🇮 Côte d'Ivoire</option>
                  <option value="Djibouti">🇩🇯 Djibouti</option>
                  <option value="Égypte">🇪🇬 Égypte</option>
                  <option value="Érythrée">🇪🇷 Érythrée</option>
                  <option value="Eswatini">🇸🇿 Eswatini</option>
                  <option value="Éthiopie">🇪🇹 Éthiopie</option>
                  <option value="Gabon">🇬🇦 Gabon</option>
                  <option value="Gambie">🇬🇲 Gambie</option>
                  <option value="Ghana">🇬🇭 Ghana</option>
                  <option value="Guinée">🇬🇳 Guinée</option>
                  <option value="Guinée équatoriale">🇬🇶 Guinée équatoriale</option>
                  <option value="Guinée-Bissau">🇬🇼 Guinée-Bissau</option>
                  <option value="Kenya">🇰🇪 Kenya</option>
                  <option value="Lesotho">🇱🇸 Lesotho</option>
                  <option value="Liberia">🇱🇷 Liberia</option>
                  <option value="Libye">🇱🇾 Libye</option>
                  <option value="Madagascar">🇲🇬 Madagascar</option>
                  <option value="Malawi">🇲🇼 Malawi</option>
                  <option value="Mali">🇲🇱 Mali</option>
                  <option value="Maroc">🇲🇦 Maroc</option>
                  <option value="Maurice">🇲🇺 Maurice</option>
                  <option value="Mauritanie">🇲🇷 Mauritanie</option>
                  <option value="Mozambique">🇲🇿 Mozambique</option>
                  <option value="Namibie">🇳🇦 Namibie</option>
                  <option value="Niger">🇳🇪 Niger</option>
                  <option value="Nigeria">🇳🇬 Nigeria</option>
                  <option value="Ouganda">🇺🇬 Ouganda</option>
                  <option value="République Centrafricaine">🇨🇫 République Centrafricaine</option>
                  <option value="République du Congo">🇨🇬 République du Congo</option>
                  <option value="République Démocratique du Congo">🇨🇩 République Démocratique du Congo</option>
                  <option value="Rwanda">🇷🇼 Rwanda</option>
                  <option value="Sao Tomé-et-Principe">🇸🇹 Sao Tomé-et-Principe</option>
                  <option value="Sénégal">🇸🇳 Sénégal</option>
                  <option value="Seychelles">🇸🇨 Seychelles</option>
                  <option value="Sierra Leone">🇸🇱 Sierra Leone</option>
                  <option value="Somalie">🇸🇴 Somalie</option>
                  <option value="Soudan">🇸🇩 Soudan</option>
                  <option value="Soudan du Sud">🇸🇸 Soudan du Sud</option>
                  <option value="Tanzanie">🇹🇿 Tanzanie</option>
                  <option value="Tchad">🇹🇩 Tchad</option>
                  <option value="Togo">🇹🇬 Togo</option>
                  <option value="Tunisie">🇹🇳 Tunisie</option>
                  <option value="Zambie">🇿🇲 Zambie</option>
                  <option value="Zimbabwe">🇿🇼 Zimbabwe</option>
                </select>
              </div>
            </div>

            <!-- ✅ LE CHOIX DU RÔLE -->
            <div class="form-group" id="role-group">
              <label for="role"><?= t('Je souhaite…') ?></label>
              <select id="role" name="role">
                <option value="user"><?= t('Regarder, écouter, aimer (Visiteur)') ?></option>
                <option value="contributor"><?= t('Contribuer (Publier des vidéos)') ?></option>
              </select>
            </div>

            <div class="form-group" id="avatar-group">
              <label class="avatar-label"><?= t('Photo de profil') ?></label>
              <div class="account-avatar-pick" id="avatars">
                <?php
                $avs = ['av_user','av_amani','av_grace','av_kaz','av_ade','av_fatou'];
                foreach ($avs as $i => $a):
                  $imgPath = "assets/img/{$a}.jpg";
                ?>
                <label class="avatar-option <?= $i === 0 ? 'on' : '' ?>">
                  <input type="radio" name="avatar_choice" value="<?= $imgPath ?>" <?= $i === 0 ? 'checked' : '' ?> class="sr-only" onchange="pickAvatar(this)">
                  <img src="<?= $imgPath ?>" alt="Avatar" onerror="this.src='https://ui-avatars.com/api/?name=<?= substr($a, 3, 1) ?>&background=333&color=fff'">
                </label>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- ===== CONNEXION UNIQUEMENT ===== -->
            <div id="login-extra" class="login-extra">
              <label>
                <input type="checkbox" id="remember" name="remember">
                <?= t('Se souvenir de moi') ?>
              </label>
              <a href="#"><?= t('Mot de passe oublié ?') ?></a>
            </div>

            <!-- ===== INSCRIPTION UNIQUEMENT ===== -->
            <div class="form-group account-terms" id="terms-group">
              <label>
                <input type="checkbox" id="terms" name="terms">
                <?= t('J\'accepte les conditions d\'utilisation de Kazuri.') ?>
              </label>
            </div>

            <!-- ===== TOUJOURS VISIBLE ===== -->
            <div class="form-group" style="margin-top: 24px;">
              <input type="submit" id="submitBtn"
                     value="<?= t('CRÉER MON COMPTE') ?>" onclick="submitAuth()">
            </div>

            <!-- ===== RESEAUX SOCIAUX (TOUJOURS VISIBLE) ===== -->
            <div class="social-separator" id="social-group">
              <span><?= t('ou continuer avec') ?></span>
            </div>
            <div class="account-social" style="display: flex; gap: 8px; flex-wrap: wrap;">
              <a href="#"><i class="lni lni-facebook-filled"></i> Facebook</a>
              <a href="#"><i class="lni lni-google"></i> Google</a>
            </div>
            
          </form>
        </div>
      </div>

      <div class="col-lg-5">
        <figure class="account-brand" style="text-align: center;">
          <img 
            src="../assets/ico/Logo1_White.png" 
            alt="Kazuri" 
            onerror="
              if (!this.dataset.tried) {
                this.dataset.tried = '1';
                this.src = 'assets/ico/Logo1_White.png';
              } else {
                this.style.display = 'none';
                this.parentElement.innerHTML = '<div style=\'color:#ff2d3a;font-size:42px;font-weight:900;text-align:center;letter-spacing:4px;\'>KAZURI</div>';
              }
            ">
        </figure>
      </div>
    </div>
  </div>
</section>

<script>
/* ================================================================
   Toggle login / signup
   ================================================================ */
let mode = 'signup';

function setMode(m) {
  mode = m;
  document.getElementById('tabSignup').classList.toggle('on', m === 'signup');
  document.getElementById('tabLogin').classList.toggle('on', m === 'login');

  const show = (id, visible) => {
    const el = document.getElementById(id);
    if (el) el.style.display = visible ? '' : 'none';
  };

  // Champs visibles en signup UNIQUEMENT
  show('signup-fields', m === 'signup');
  show('signup-extra', m === 'signup');
  show('confirm-group', m === 'signup');
  show('role-group', m === 'signup');
  show('avatar-group', m === 'signup');
  show('terms-group', m === 'signup');
  
  // Champs visibles en login UNIQUEMENT
  show('login-extra', m === 'login');
  show('pass-hint', m === 'signup');

  // Changement des textes
  document.getElementById('auth-title').textContent =
    m === 'signup' ? 'Rejoignez Kazuri' : 'Bon retour !';
  document.getElementById('auth-subtitle').textContent =
    m === 'signup'
      ? 'Créez votre compte pour retrouver vos films et séries préférés.'
      : 'Connectez-vous pour retrouver votre bibliothèque.';
  document.getElementById('submitBtn').value =
    m === 'signup' ? 'CRÉER MON COMPTE' : 'SE CONNECTER';

  // On ajuste le required sur les champs cachés
  document.getElementById('first_name').required = (m === 'signup');
  document.getElementById('last_name').required  = (m === 'signup');
  document.getElementById('password_confirm').required = (m === 'signup');
  
  // Attribut d'autocomplétion adapté
  document.getElementById('password').autocomplete = (m === 'signup') ? 'new-password' : 'current-password';

  // On cache les messages quand on change d'onglet
  document.getElementById('auth-error').classList.remove('show');
  document.getElementById('auth-success').classList.remove('show');
}

/* ================================================================
   Sélection d'avatar
   ================================================================ */
function pickAvatar(input) {
  document.querySelectorAll('.avatar-option').forEach(l => l.classList.remove('on'));
  input.parentElement.classList.add('on');
}

/* ================================================================
   Affichage d'erreur / succès
   ================================================================ */
function showError(msg) {
  const el = document.getElementById('auth-error');
  el.textContent = msg;
  el.classList.add('show');
}

function showSuccess(msg) {
  const el = document.getElementById('auth-success');
  el.textContent = msg;
  el.classList.add('show');
}

/* ================================================================
   Soumission du formulaire (AJAX)
   ================================================================ */
async function submitAuth() {
  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  document.getElementById('auth-error').classList.remove('show');
  document.getElementById('auth-success').classList.remove('show');

  try {
    const fd = new FormData();
    fd.set('action', mode === 'login' ? 'login' : 'register');
    fd.set('email',    document.getElementById('email').value.trim());
    fd.set('password', document.getElementById('password').value);

    if (mode === 'signup') {
      const p1 = document.getElementById('password').value;
      const p2 = document.getElementById('password_confirm').value;
      if (p1 !== p2) throw new Error('Les mots de passe ne correspondent pas.');
      if (p1.length < 6) throw new Error('Mot de passe : 6 caractères minimum.');
      if (!document.getElementById('terms').checked) {
        throw new Error('Vous devez accepter les conditions d\'utilisation.');
      }

      fd.set('first_name', document.getElementById('first_name').value.trim());
      fd.set('last_name',  document.getElementById('last_name').value.trim());
      fd.set('role',       document.getElementById('role').value); // ✅ Envoi du rôle
      fd.set('phone',      document.getElementById('phone').value.trim());
      fd.set('country',    document.getElementById('country').value.trim());
      
      const selectedAvatar = document.querySelector('input[name="avatar_choice"]:checked');
      if (selectedAvatar) fd.set('avatar', selectedAvatar.value);
    } else {
      fd.set('remember', document.getElementById('remember').checked ? '1' : '0');
    }

    const res = await fetch('api/auth.php', { method: 'POST', body: fd });
    const data = await res.json();

    if (!data.ok) throw new Error(data.error || 'Erreur inconnue');

    // ✅ SI C'EST UNE INSCRIPTION
    if (mode === 'signup') {
      showSuccess(data.message || 'Votre compte a été créé avec succès. Connexion en cours...');
      btn.value = 'Connexion en cours...';
      
      setTimeout(() => {
        location.href = data.redirect;
      }, 2500);
      
    } else {
      btn.value = 'Redirection…';
      location.href = data.redirect;
    }

  } catch (ex) {
    showError(ex.message);
    btn.disabled = false;
    btn.value = mode === 'signup' ? 'CRÉER MON COMPTE' : 'SE CONNECTER';
  }
}

/* ================================================================
   INITIALISATION AU CHARGEMENT DE LA PAGE
   ================================================================ */
window.addEventListener('DOMContentLoaded', () => {
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('registered') === '1') {
    setMode('login');
    showSuccess('Votre compte a bien été créé. Vous pouvez maintenant vous connecter.');
  } else {
    setMode('signup');
  }
});
</script>

</body>
</html>