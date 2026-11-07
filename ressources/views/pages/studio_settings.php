<div class="dashboard-card">
    <h2 class="dashboard-title">Personnalisation de la chaîne</h2>
    
    <div style="display: flex; gap: 32px; align-items: flex-start;">
        <!-- Avatar -->
        <div style="text-align: center;">
            <div style="width: 100px; height: 100px; border-radius: 50%; background: var(--accent); display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: bold; color: #fff; margin-bottom: 12px;">
                HK
            </div>
            <button style="background: none; border: 1px solid #444; color: #fff; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 13px;">Changer</button>
        </div>

        <!-- Formulaire -->
        <div style="flex: 1; display: flex; flex-direction: column; gap: 16px;">
            <div>
                <label style="display: block; color: #aaa; font-size: 13px; margin-bottom: 8px;">Nom de la chaîne</label>
                <input type="text" value="Hervé IRAGI" style="width: 100%; padding: 12px; background: #121212; border: 1px solid #444; border-radius: 8px; color: #fff;">
            </div>
            <div>
                <label style="display: block; color: #aaa; font-size: 13px; margin-bottom: 8px;">Description</label>
                <textarea rows="3" style="width: 100%; padding: 12px; background: #121212; border: 1px solid #444; border-radius: 8px; color: #fff;">Créateur de contenu sur Kazuri. Passionné de cinéma et de culture africaine.</textarea>
            </div>
            <div>
                <label style="display: block; color: #aaa; font-size: 13px; margin-bottom: 8px;">Pays</label>
                <select style="width: 100%; padding: 12px; background: #121212; border: 1px solid #444; border-radius: 8px; color: #fff;">
                    <option>RD Congo</option>
                    <option>France</option>
                    <option>Belgique</option>
                </select>
            </div>
            <div style="margin-top: 12px;">
                <button style="background: var(--accent); color: #fff; border: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; cursor: pointer;">Enregistrer les modifications</button>
            </div>
        </div>
    </div>
</div>