<div class="admin-card">
  <div class="admin-card-header"><div class="admin-card-title">Store Settings</div></div>
  <div class="admin-card-body">
    <?php $s = flash_get('success'); if ($s): ?><div class="alert alert-success"><?= e($s) ?></div><?php endif; ?>

    <!-- Tabs -->
    <div style="display:flex;gap:4px;margin-bottom:24px;border-bottom:2px solid var(--color-border);padding-bottom:0">
      <?php foreach (['store'=>'Store Info','payments'=>'Payments','rewards'=>'Rewards & Shipping','maps'=>'Maps & Location','contact'=>'Contact Buttons','delivery_km'=>'Delivery by KM','other'=>'Other'] as $tab=>$label): ?>
      <button type="button" onclick="switchSettingsTab('<?= $tab ?>')"
        id="tab-btn-<?= $tab ?>"
        style="padding:10px 14px;font-weight:600;font-size:0.82rem;border:none;background:none;cursor:pointer;border-bottom:3px solid transparent;margin-bottom:-2px;color:var(--color-text-muted);white-space:nowrap"
        class="settings-tab-btn">
        <?= $label ?>
      </button>
      <?php endforeach; ?>
    </div>

    <form method="POST" action="/admin/settings">
      <?= csrf_field() ?>

      <!-- Store Info -->
      <div id="settings-store" class="settings-tab-panel">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
          <div>
            <div class="form-group"><label class="form-label">Store Name</label><input type="text" name="store_name" class="form-control" value="<?= e($settings['store_name'] ?? '') ?>"></div>
            <div class="form-group"><label class="form-label">Store Email</label><input type="email" name="store_email" class="form-control" value="<?= e($settings['store_email'] ?? '') ?>"></div>
            <div class="form-group"><label class="form-label">Store Phone</label><input type="text" name="store_phone" class="form-control" value="<?= e($settings['store_phone'] ?? '') ?>"></div>
            <div class="form-group"><label class="form-label">Store Address</label><input type="text" name="store_address" class="form-control" value="<?= e($settings['store_address'] ?? '') ?>"></div>
            <div class="form-group"><label class="form-label">VAT Registration Number</label><input type="text" name="vat_number" class="form-control" value="<?= e($settings['vat_number'] ?? '') ?>"></div>
          </div>
        </div>
        <button type="submit" class="btn btn-primary">Save Store Settings</button>
      </div>

      <!-- Payment Gateways -->
      <div id="settings-payments" class="settings-tab-panel" style="display:none">
        <div style="display:grid;gap:20px">

          <!-- COD -->
          <div class="admin-card" style="border:1px solid var(--color-border);box-shadow:none">
            <div class="admin-card-header" style="background:var(--color-bg-light)">
              <div style="display:flex;align-items:center;gap:12px">
                <i class="fas fa-money-bill-wave" style="color:var(--color-success);font-size:1.2rem"></i>
                <div class="admin-card-title">Cash on Delivery</div>
              </div>
              <label class="toggle-switch" style="position:relative;width:44px;height:24px">
                <input type="checkbox" name="cod_enabled" value="1" <?= !empty($settings['cod_enabled']) ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
              </label>
            </div>
            <div class="admin-card-body" style="padding:12px 20px">
              <p style="font-size:0.85rem;color:var(--color-text-muted);margin:0">No configuration needed. Enable/disable COD for customers.</p>
            </div>
          </div>

          <!-- Card on Delivery -->
          <div class="admin-card" style="border:1px solid var(--color-border);box-shadow:none">
            <div class="admin-card-header" style="background:var(--color-bg-light)">
              <div style="display:flex;align-items:center;gap:12px">
                <i class="fas fa-credit-card" style="color:var(--color-secondary);font-size:1.2rem"></i>
                <div class="admin-card-title">Card on Delivery</div>
              </div>
              <label class="toggle-switch" style="position:relative;width:44px;height:24px">
                <input type="checkbox" name="card_on_delivery_enabled" value="1" <?= !empty($settings['card_on_delivery_enabled']) ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
              </label>
            </div>
            <div class="admin-card-body" style="padding:12px 20px">
              <p style="font-size:0.85rem;color:var(--color-text-muted);margin:0">Customer pays by card at the door. No online payment required.</p>
            </div>
          </div>

          <!-- Payment Link on Delivery -->
          <div class="admin-card" style="border:1px solid var(--color-border);box-shadow:none">
            <div class="admin-card-header" style="background:var(--color-bg-light)">
              <div style="display:flex;align-items:center;gap:12px">
                <i class="fas fa-link" style="color:var(--color-secondary);font-size:1.2rem"></i>
                <div class="admin-card-title">Payment Link on Delivery</div>
              </div>
              <label class="toggle-switch" style="position:relative;width:44px;height:24px">
                <input type="checkbox" name="payment_link_on_delivery_enabled" value="1" <?= !empty($settings['payment_link_on_delivery_enabled']) ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
              </label>
            </div>
            <div class="admin-card-body" style="padding:12px 20px">
              <p style="font-size:0.85rem;color:var(--color-text-muted);margin:0">A payment link will be sent to the customer manually at the time of delivery.</p>
            </div>
          </div>

          <!-- Stripe -->
          <div class="admin-card" style="border:1px solid var(--color-border);box-shadow:none">
            <div class="admin-card-header" style="background:var(--color-bg-light)">
              <div style="display:flex;align-items:center;gap:12px">
                <i class="fab fa-stripe" style="color:#635bff;font-size:1.4rem"></i>
                <div>
                  <div class="admin-card-title">Stripe</div>
                  <div style="font-size:0.75rem;color:var(--color-text-muted)">Credit/Debit Cards — Global</div>
                </div>
              </div>
              <label class="toggle-switch" style="position:relative;width:44px;height:24px">
                <input type="checkbox" name="stripe_enabled" value="1" <?= !empty($settings['stripe_enabled']) ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
              </label>
            </div>
            <div class="admin-card-body">
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group"><label class="form-label">Publishable Key</label><input type="text" name="stripe_public_key" class="form-control" value="<?= e($settings['stripe_public_key'] ?? '') ?>" placeholder="pk_live_..."></div>
                <div class="form-group"><label class="form-label">Secret Key</label><input type="password" name="stripe_secret_key" class="form-control" value="<?= e($settings['stripe_secret_key'] ?? '') ?>" placeholder="sk_live_..."></div>
                <div class="form-group"><label class="form-label">Webhook Secret</label><input type="password" name="stripe_webhook_secret" class="form-control" value="<?= e($settings['stripe_webhook_secret'] ?? '') ?>" placeholder="whsec_..."></div>
              </div>
              <p style="font-size:0.78rem;color:var(--color-text-muted);margin-top:8px">Get keys from <a href="https://dashboard.stripe.com/apikeys" target="_blank" style="color:var(--color-secondary)">dashboard.stripe.com</a></p>
            </div>
          </div>

          <!-- Telr -->
          <div class="admin-card" style="border:1px solid var(--color-border);box-shadow:none">
            <div class="admin-card-header" style="background:var(--color-bg-light)">
              <div style="display:flex;align-items:center;gap:12px">
                <i class="fas fa-credit-card" style="color:#0066cc;font-size:1.2rem"></i>
                <div>
                  <div class="admin-card-title">Telr</div>
                  <div style="font-size:0.75rem;color:var(--color-text-muted)">Card Payments — UAE/Middle East</div>
                </div>
              </div>
              <label class="toggle-switch" style="position:relative;width:44px;height:24px">
                <input type="checkbox" name="telr_enabled" value="1" <?= !empty($settings['telr_enabled']) ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
              </label>
            </div>
            <div class="admin-card-body">
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group"><label class="form-label">Store ID</label><input type="text" name="telr_store_id" class="form-control" value="<?= e($settings['telr_store_id'] ?? '') ?>"></div>
                <div class="form-group"><label class="form-label">Auth Key</label><input type="password" name="telr_auth_key" class="form-control" value="<?= e($settings['telr_auth_key'] ?? '') ?>"></div>
                <div class="form-group"><label class="form-label">Mode</label>
                  <select name="telr_test_mode" class="form-control">
                    <option value="1" <?= !empty($settings['telr_test_mode']) ? 'selected' : '' ?>>Test Mode</option>
                    <option value="0" <?= empty($settings['telr_test_mode']) ? 'selected' : '' ?>>Live Mode</option>
                  </select>
                </div>
              </div>
              <p style="font-size:0.78rem;color:var(--color-text-muted);margin-top:8px">Get credentials from <a href="https://telr.com" target="_blank" style="color:var(--color-secondary)">telr.com</a></p>
            </div>
          </div>

          <!-- Tabby -->
          <div class="admin-card" style="border:1px solid var(--color-border);box-shadow:none">
            <div class="admin-card-header" style="background:var(--color-bg-light)">
              <div style="display:flex;align-items:center;gap:12px">
                <i class="fas fa-calendar-alt" style="color:#3dbf8c;font-size:1.2rem"></i>
                <div>
                  <div class="admin-card-title">Tabby</div>
                  <div style="font-size:0.75rem;color:var(--color-text-muted)">Buy Now Pay Later — 4 instalments</div>
                </div>
              </div>
              <label class="toggle-switch" style="position:relative;width:44px;height:24px">
                <input type="checkbox" name="tabby_enabled" value="1" <?= !empty($settings['tabby_enabled']) ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
              </label>
            </div>
            <div class="admin-card-body">
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group"><label class="form-label">Public Key</label><input type="text" name="tabby_public_key" class="form-control" value="<?= e($settings['tabby_public_key'] ?? '') ?>"></div>
                <div class="form-group"><label class="form-label">Secret Key</label><input type="password" name="tabby_secret_key" class="form-control" value="<?= e($settings['tabby_secret_key'] ?? '') ?>"></div>
                <div class="form-group"><label class="form-label">Merchant Code</label><input type="text" name="tabby_merchant_code" class="form-control" value="<?= e($settings['tabby_merchant_code'] ?? '') ?>"></div>
                <div class="form-group"><label class="form-label">Mode</label>
                  <select name="tabby_test_mode" class="form-control">
                    <option value="1" <?= !empty($settings['tabby_test_mode']) ? 'selected' : '' ?>>Test Mode</option>
                    <option value="0" <?= empty($settings['tabby_test_mode']) ? 'selected' : '' ?>>Live Mode</option>
                  </select>
                </div>
              </div>
              <p style="font-size:0.78rem;color:var(--color-text-muted);margin-top:8px">Get credentials from <a href="https://tabby.ai" target="_blank" style="color:var(--color-secondary)">tabby.ai</a></p>
            </div>
          </div>

          <!-- Tamara -->
          <div class="admin-card" style="border:1px solid var(--color-border);box-shadow:none">
            <div class="admin-card-header" style="background:var(--color-bg-light)">
              <div style="display:flex;align-items:center;gap:12px">
                <i class="fas fa-calendar-check" style="color:#ff6b35;font-size:1.2rem"></i>
                <div>
                  <div class="admin-card-title">Tamara</div>
                  <div style="font-size:0.75rem;color:var(--color-text-muted)">Buy Now Pay Later — 3 instalments</div>
                </div>
              </div>
              <label class="toggle-switch" style="position:relative;width:44px;height:24px">
                <input type="checkbox" name="tamara_enabled" value="1" <?= !empty($settings['tamara_enabled']) ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
              </label>
            </div>
            <div class="admin-card-body">
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group"><label class="form-label">API Token</label><input type="password" name="tamara_api_token" class="form-control" value="<?= e($settings['tamara_api_token'] ?? '') ?>"></div>
                <div class="form-group"><label class="form-label">Notification Key</label><input type="password" name="tamara_notification_key" class="form-control" value="<?= e($settings['tamara_notification_key'] ?? '') ?>"></div>
                <div class="form-group"><label class="form-label">Mode</label>
                  <select name="tamara_test_mode" class="form-control">
                    <option value="1" <?= !empty($settings['tamara_test_mode']) ? 'selected' : '' ?>>Sandbox</option>
                    <option value="0" <?= empty($settings['tamara_test_mode']) ? 'selected' : '' ?>>Production</option>
                  </select>
                </div>
              </div>
              <p style="font-size:0.78rem;color:var(--color-text-muted);margin-top:8px">Get credentials from <a href="https://tamara.co" target="_blank" style="color:var(--color-secondary)">tamara.co</a></p>
            </div>
          </div>

        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:20px">Save Payment Settings</button>
      </div>

      <!-- Rewards & Shipping -->
      <div id="settings-rewards" class="settings-tab-panel" style="display:none">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
          <div>
            <h4 style="margin-bottom:16px">Reward Points</h4>
            <div class="form-group"><label class="form-label">Points Earn Rate (per AED spent)</label><input type="number" name="reward_earn_rate" class="form-control" value="<?= e($settings['reward_earn_rate'] ?? '1') ?>"></div>
            <div class="form-group"><label class="form-label">Points Redeem Rate (pts per AED 10)</label><input type="number" name="reward_redeem_rate" class="form-control" value="<?= e($settings['reward_redeem_rate'] ?? '100') ?>"></div>
            <div class="form-group"><label class="form-label">Min Points to Redeem</label><input type="number" name="reward_min_redeem" class="form-control" value="<?= e($settings['reward_min_redeem'] ?? '500') ?>"></div>
            <div class="form-group"><label class="form-label">Welcome Bonus Points</label><input type="number" name="welcome_bonus_points" class="form-control" value="<?= e($settings['welcome_bonus_points'] ?? '200') ?>"></div>
            <div class="form-group"><label class="form-label">Review Bonus Points</label><input type="number" name="review_bonus_points" class="form-control" value="<?= e($settings['review_bonus_points'] ?? '50') ?>"></div>
          </div>
          <div>
            <h4 style="margin-bottom:16px">Shipping</h4>
            <div class="form-group"><label class="form-label">Free Shipping Threshold (AED)</label><input type="number" name="free_shipping_threshold" class="form-control" value="<?= e($settings['free_shipping_threshold'] ?? '100') ?>"></div>
          </div>
        </div>
        <button type="submit" class="btn btn-primary">Save Rewards & Shipping</button>
      </div>

      <!-- Other -->
      <div id="settings-other" class="settings-tab-panel" style="display:none">
        <div class="form-group"><label class="form-check"><input type="checkbox" name="age_gate_enabled" value="1" <?= !empty($settings['age_gate_enabled']) ? 'checked' : '' ?>> Enable Age Gate</label></div>
        <div class="form-group"><label class="form-check"><input type="checkbox" name="age_gate_require_dob" value="1" <?= !empty($settings['age_gate_require_dob']) ? 'checked' : '' ?>> Require Date of Birth</label></div>
        <div class="form-group"><label class="form-check"><input type="checkbox" name="maintenance_mode" value="1" <?= !empty($settings['maintenance_mode']) ? 'checked' : '' ?>> Maintenance Mode</label></div>
        <button type="submit" class="btn btn-primary">Save Other Settings</button>
      </div>

      <!-- Maps & Location -->
      <div id="settings-maps" class="settings-tab-panel" style="display:none">
        <h4 style="margin-bottom:16px">Google Maps — Store Location</h4>
        <div class="form-group"><label class="form-label">Google Maps API Key</label><input type="text" name="google_maps_api_key" class="form-control" value="<?= e($settings['google_maps_api_key'] ?? '') ?>" placeholder="AIza..."><div class="form-hint">Get from <a href="https://console.cloud.google.com" target="_blank" style="color:var(--color-secondary)">Google Cloud Console</a> → Maps JavaScript API</div></div>
        <div class="form-group"><label class="form-label">Google Maps Embed URL <span style="font-weight:400;color:var(--color-text-muted)">(alternative — no API key needed)</span></label><input type="text" name="google_maps_embed_url" class="form-control" value="<?= e($settings['google_maps_embed_url'] ?? '') ?>" placeholder="https://www.google.com/maps/embed?pb=..."><div class="form-hint">Go to Google Maps → Share → Embed a map → copy the src URL</div></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Store Latitude</label><input type="text" name="store_lat" class="form-control" value="<?= e($settings['store_lat'] ?? '25.0805') ?>" placeholder="25.0805"></div>
          <div class="form-group"><label class="form-label">Store Longitude</label><input type="text" name="store_lng" class="form-control" value="<?= e($settings['store_lng'] ?? '55.1403') ?>" placeholder="55.1403"></div>
        </div>
        <div class="form-group"><label class="form-label">Map Address Label</label><input type="text" name="store_map_address" class="form-control" value="<?= e($settings['store_map_address'] ?? '') ?>" placeholder="Dubai Marina, Dubai, UAE"></div>
        <?php if (!empty($settings['google_maps_embed_url'])): ?>
        <div style="margin-top:16px;border-radius:var(--radius-lg);overflow:hidden;border:1px solid var(--color-border)">
          <iframe src="<?= e($settings['google_maps_embed_url']) ?>" width="100%" height="200" style="border:0" allowfullscreen loading="lazy"></iframe>
        </div>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary" style="margin-top:16px">Save Map Settings</button>
      </div>

      <!-- Contact Buttons -->
      <div id="settings-contact" class="settings-tab-panel" style="display:none">
        <h4 style="margin-bottom:16px">Floating Contact Buttons</h4>
        <p style="color:var(--color-text-muted);font-size:0.88rem;margin-bottom:20px">These buttons appear fixed on the bottom-right of every page.</p>
        <div class="form-group"><label class="form-label">WhatsApp Number <span style="font-weight:400;color:var(--color-text-muted)">(digits only, with country code)</span></label><input type="text" name="whatsapp_number" class="form-control" value="<?= e($settings['whatsapp_number'] ?? '971568335210') ?>" placeholder="971568335210"></div>
        <div class="form-group"><label class="form-label">Contact Email</label><input type="email" name="contact_email" class="form-control" value="<?= e($settings['contact_email'] ?? '') ?>"></div>
        <button type="submit" class="btn btn-primary">Save Contact Settings</button>
      </div>

      <!-- Delivery by KM -->
      <div id="settings-delivery_km" class="settings-tab-panel" style="display:none">
        <h4 style="margin-bottom:16px">Distance-Based Delivery Charges</h4>
        <div class="form-group">
          <label class="form-check">
            <input type="checkbox" name="delivery_km_enabled" value="1" <?= !empty($settings['delivery_km_enabled']) ? 'checked' : '' ?>>
            Enable KM-based delivery pricing (overrides zone-based pricing)
          </label>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:16px">
          <div class="form-group"><label class="form-label">Base Fee (AED) <span style="font-weight:400;color:var(--color-text-muted)">— charged for any delivery</span></label><input type="number" name="delivery_base_fee" class="form-control" step="0.01" value="<?= e($settings['delivery_base_fee'] ?? '10') ?>"></div>
          <div class="form-group"><label class="form-label">Per KM Rate (AED)</label><input type="number" name="delivery_per_km_fee" class="form-control" step="0.01" value="<?= e($settings['delivery_per_km_fee'] ?? '2') ?>"></div>
          <div class="form-group"><label class="form-label">Free Delivery Under (km) <span style="font-weight:400;color:var(--color-text-muted)">— 0 = disabled</span></label><input type="number" name="delivery_free_km" class="form-control" step="0.1" value="<?= e($settings['delivery_free_km'] ?? '0') ?>"></div>
          <div class="form-group"><label class="form-label">Google Maps API Key <span style="font-weight:400;color:var(--color-text-muted)">(for Distance Matrix)</span></label><input type="text" name="google_maps_api_key" class="form-control" value="<?= e($settings['google_maps_api_key'] ?? '') ?>" placeholder="AIza..."></div>
        </div>
        <div style="background:var(--color-bg-light);border-radius:var(--radius);padding:16px;font-size:0.85rem;color:var(--color-text-muted);margin-top:8px">
          <strong>Formula:</strong> Delivery Fee = Base Fee + (Distance in KM × Per KM Rate)<br>
          <strong>Example:</strong> AED 10 base + AED 2/km × 5km = AED 20 total
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:16px">Save Delivery KM Settings</button>
      </div>

    </form>
  </div>
</div>

<script>
function switchSettingsTab(tab) {
  document.querySelectorAll('.settings-tab-panel').forEach(p => p.style.display = 'none');
  document.querySelectorAll('.settings-tab-btn').forEach(b => {
    b.style.borderBottomColor = 'transparent';
    b.style.color = 'var(--color-text-muted)';
  });
  document.getElementById('settings-' + tab).style.display = 'block';
  const btn = document.getElementById('tab-btn-' + tab);
  btn.style.borderBottomColor = 'var(--color-secondary)';
  btn.style.color = 'var(--color-primary)';
}
// Init
switchSettingsTab('store');
</script>
