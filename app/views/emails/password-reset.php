<!DOCTYPE html>
<html><head><meta charset="UTF-8"><style>body{font-family:Arial,sans-serif;background:#f5f0e8;margin:0;padding:20px}.wrap{max-width:560px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden}.header{background:#1A1A2E;padding:24px;text-align:center;color:#fff;font-size:1.4rem;font-weight:900}.header span{color:#C8963C}.body{padding:32px}.btn{display:inline-block;background:#C8963C;color:#fff;padding:14px 32px;border-radius:4px;text-decoration:none;font-weight:700;margin:20px 0}.footer{background:#1A1A2E;padding:16px;text-align:center;color:rgba(255,255,255,0.5);font-size:0.75rem}</style></head>
<body>
<div class="wrap">
  <div class="header">Phantom <span>Smoking</span></div>
  <div class="body">
    <h2>Reset Your Password</h2>
    <p>We received a request to reset your password. Click the button below to set a new password. This link expires in 1 hour.</p>
    <a href="<?= $resetUrl ?>" class="btn">Reset Password</a>
    <p style="color:#6B7280;font-size:0.85rem">If you didn't request this, you can safely ignore this email. Your password won't change.</p>
    <p style="color:#6B7280;font-size:0.82rem">Or copy this link: <?= $resetUrl ?></p>
  </div>
  <div class="footer">© <?= date('Y') ?> Phantom Smoking. Dubai, UAE.</div>
</div>
</body></html>
