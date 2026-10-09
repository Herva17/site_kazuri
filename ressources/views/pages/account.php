 <section class="content-section account-section" data-background="../assets/images/section-pattern01.png">
     <div class="container">
         <div class="row align-items-center account-layout">
             <div class="col-lg-7">
                 <div class="membership account-form">
                     <p class="account-eyebrow"><?= t('KAZURI · COMPTE') ?></p>
                     <h2><?= t('Rejoignez Kazuri') ?></h2>
                     <p class="account-intro"><?= t('Créez votre compte pour retrouver vos films et séries préférés.') ?></p>
                     <form class="account-form-fields">
                         <div class="form-group">
                             <label class="sr-only" for="account-name"><?= t('Nom complet') ?></label>
                             <input id="account-name" type="text" placeholder="<?= t('Nom complet') ?>" autocomplete="name">
                         </div>
                         <div class="form-group">
                             <label class="sr-only" for="account-email"><?= t('Adresse e-mail') ?></label>
                             <input id="account-email" type="email" placeholder="<?= t('Adresse e-mail') ?>" autocomplete="email">
                         </div>
                         <div class="form-group">
                             <label class="sr-only" for="account-password"><?= t('Mot de passe') ?></label>
                             <input id="account-password" type="password" placeholder="<?= t('Mot de passe') ?>" autocomplete="new-password">
                         </div>
                         <div class="form-group account-terms">
                             <label><input type="checkbox"> <?= t('J’accepte les conditions d’utilisation de Kazuri.') ?></label>
                         </div>
                         <div class="form-group">
                             <input type="submit" value="<?= t('CRÉER MON COMPTE') ?>">
                         </div>

                         <div class="or account-social">
                             <span><?= t('ou') ?></span>
                             <p><?= t('Continuer avec') ?></p>
                             <a href="#"><i class="lni lni-facebook-filled"></i>
                                 <small>Facebook</small>
                             </a> <a href="#"><i class="lni lni-google"></i>
                                 <small>Google</small>
                             </a>
                         </div>
                     </form>
                 </div>
             </div>
             <div class="col-lg-5">
                 <figure class="account-brand">
                     <img src="../assets/ico/Logo1_White.png" alt="">
                 </figure>
             </div>
         </div>
     </div>
 </section>