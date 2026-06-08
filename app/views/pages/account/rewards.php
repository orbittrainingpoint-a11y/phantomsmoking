<div class="container section">
  <div class="account-layout">
    <?php include dirname(__DIR__) . '/../components/account-sidebar.php'; ?>
    <div class="account-content">
      <div class="account-page-title">Reward Points</div>
      <div class="rewards-balance">
        <div class="rewards-points"><?= number_format($user['reward_points']) ?></div>
        <div class="rewards-label">Available Points</div>
        <div style="margin-top:12px;font-size:0.88rem;opacity:0.8">100 points = AED 10 discount · Minimum 500 points to redeem</div>
      </div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px">
        <div class="stat-card"><div class="stat-value" style="color:var(--color-success)">+<?= number_format(array_sum(array_column(array_filter($history, fn($h) => $h['type'] === 'earned'), 'points'))) ?></div><div class="stat-label">Total Earned</div></div>
        <div class="stat-card"><div class="stat-value" style="color:var(--color-error)"><?= number_format(abs(array_sum(array_column(array_filter($history, fn($h) => $h['type'] === 'redeemed'), 'points')))) ?></div><div class="stat-label">Total Redeemed</div></div>
        <div class="stat-card"><div class="stat-value"><?= number_format($user['reward_points']) ?></div><div class="stat-label">Current Balance</div></div>
      </div>
      <h3 style="font-family:var(--font-heading);margin-bottom:16px">Points History</h3>
      <?php if (empty($history)): ?>
      <div class="empty-state"><i class="fas fa-star"></i><h3>No points yet</h3><p>Start shopping to earn reward points</p></div>
      <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Date</th><th>Description</th><th>Points</th><th>Balance</th></tr></thead>
          <tbody>
            <?php foreach ($history as $h): ?>
            <tr>
              <td><?= format_date($h['created_at']) ?></td>
              <td><?= e($h['description']) ?></td>
              <td style="color:<?= $h['points'] > 0 ? 'var(--color-success)' : 'var(--color-error)' ?>;font-weight:700"><?= $h['points'] > 0 ? '+' : '' ?><?= $h['points'] ?></td>
              <td><?= number_format($h['balance_after']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
