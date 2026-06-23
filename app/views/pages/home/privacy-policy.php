<?php $_email = setting('contact_email', 'phantomsmokingonline@gmail.com'); ?>
<div class="page-hero">
  <div class="container">
    <h1 style="font-family:var(--font-heading);font-size:clamp(1.8rem,4vw,2.4rem);color:#fff;margin-bottom:10px">Privacy <span style="color:var(--color-secondary)">Policy</span></h1>
    <p style="color:rgba(255,255,255,0.65)">Last updated: <?= date('F Y') ?></p>
  </div>
</div>

<div class="container section" style="max-width:800px">
  <div style="line-height:1.9;color:var(--color-text-muted)">
    <p>Phantom Smoking ("we", "us", "our") is committed to protecting your personal data in accordance with UAE data protection laws and applicable regulations. This policy explains how we collect, use, and protect your information.</p>
    <?php foreach ([
      ['Data We Collect', 'We collect the following information when you use our website or place an order:<ul style="padding-left:20px;line-height:2.2;margin-top:8px"><li>Full name, email address, and phone number</li><li>Delivery address and billing information</li><li>Order history and purchase behaviour</li><li>Age verification data (as required by UAE law)</li><li>Device and browsing data (cookies, IP address)</li></ul>'],
      ['How We Use Your Data', 'Your data is used to:<ul style="padding-left:20px;line-height:2.2;margin-top:8px"><li>Process and fulfil your orders</li><li>Send order confirmations and delivery updates</li><li>Manage your account and reward points</li><li>Improve our website and customer experience</li><li>Send marketing communications (only with your consent)</li><li>Comply with UAE legal and regulatory requirements</li></ul>'],
      ['Data Sharing', 'We do not sell your personal data to third parties. We share your data only with:<ul style="padding-left:20px;line-height:2.2;margin-top:8px"><li>Delivery partners — to fulfil your order</li><li>Payment processors — to process transactions securely</li><li>UAE regulatory authorities — when required by law</li></ul>'],
      ['Cookies', 'We use cookies to improve your browsing experience, remember your cart, and analyse site traffic. You can disable cookies in your browser settings, though this may affect site functionality.'],
      ['Data Security', 'We implement industry-standard security measures including SSL encryption, secure servers, and access controls to protect your personal data from unauthorised access, disclosure, or loss.'],
      ['Your Rights', 'You have the right to access, correct, or request deletion of your personal data. To exercise these rights, contact us at <a href="mailto:' . e($_email) . '" style="color:var(--color-secondary)">' . e($_email) . '</a>.'],
      ['Contact', 'For any privacy-related queries, contact Phantom Smoking at:<br><a href="mailto:' . e($_email) . '" style="color:var(--color-secondary)">' . e($_email) . '</a><br><a href="tel:+971568335210" style="color:var(--color-secondary)">+971 56 833 5210</a><br>Dubai Marina, Marsa Dubai, Dubai, UAE'],
    ] as [$heading, $content]): ?>
    <h2 style="font-family:var(--font-heading);color:var(--color-primary);font-size:1.2rem;margin:28px 0 10px;padding-top:8px;border-top:1px solid var(--color-border)"><?= $heading ?></h2>
    <div><?= $content ?></div>
    <?php endforeach; ?>
  </div>
</div>
