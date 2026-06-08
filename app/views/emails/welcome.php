<!DOCTYPE html>
<html><head><meta charset="UTF-8"><style>body{font-family:Arial,sans-serif;background:#f5f0e8;margin:0;padding:20px}.wrap{max-width:560px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden}.header{background:#1A1A2E;padding:24px;text-align:center;color:#fff;font-size:1.4rem;font-weight:900}.header span{color:#C8963C}.body{padding:32px}.btn{display:inline-block;background:#C8963C;color:#fff;padding:14px 32px;border-radius:4px;text-decoration:none;font-weight:700;margin:20px 0}.footer{background:#1A1A2E;padding:16px;text-align:center;color:rgba(255,255,255,0.5);font-size:0.75rem}</style></head>
<body>
<div class="wrap">
  <div class="header">Phantom <span>Smoking</span></div>
  <div class="body">
    <h2>Welcome, <?= e($user['first_name']) ?>! 🎉</h2>
    <p>Your Phantom Smoking account has been created. You're now part of our premium tobacco &amp; vape community in Dubai.</p>
    <div style="background:#f5f0e8;border-radius:8px;padding:16px;margin:20px 0">
      <strong>Your Welcome Gift:</strong><br>
      Use code <strong style="color:#C8963C;font-size:1.1rem">WELCOME20</strong> for AED 20 off your first order!
    </div>
    <p>With your account you can:</p>
    <ul style="color:#6B7280;line-height:2">
      <li>Earn reward points on every purchase</li>
      <li>Track your orders in real-time</li>
      <li>Save your delivery addresses</li>
      <li>Access exclusive member deals</li>
    </ul>
    <a href="<?= url() ?>" class="btn">Start Shopping</a>
    <p style="color:#6B7280;font-size:0.82rem">⚠️ This store sells tobacco and nicotine products for adults 18+ only.</p>
  </div>
  <div class="footer">© <?= date('Y') ?> Phantom Smoking. Dubai, UAE.</div>
</div>
</body></html>
