<div class="page-hero">
  <div class="container">
    <h1 style="font-family:var(--font-heading);font-size:clamp(1.8rem,4vw,2.4rem);color:#fff;margin-bottom:10px">Terms & <span style="color:var(--color-secondary)">Conditions</span></h1>
    <p style="color:rgba(255,255,255,0.65)">Last updated: <?= date('F Y') ?></p>
  </div>
</div>

<div class="container section" style="max-width:800px">
  <div style="line-height:1.9;color:var(--color-text-muted)">

    <p>By accessing and using the Phantom Smoking website and placing orders, you agree to be bound by these Terms & Conditions. Please read them carefully before using our services.</p>

    <?php foreach ([
      ['Age Restriction', 'You must be 18 years of age or older to purchase from Phantom Smoking. By confirming your age on our website, you declare that you are of legal age. We reserve the right to cancel any order and request proof of age upon delivery. Our delivery team will not hand over orders to individuals who appear to be under 18 years of age.'],
      ['Product Information', 'All product descriptions, images, and specifications on our website are provided in good faith and are accurate to the best of our knowledge. Prices are displayed in UAE Dirhams (AED) and are inclusive of 5% UAE VAT. Phantom Smoking reserves the right to change prices, product availability, and specifications without prior notice.'],
      ['Orders & Payment', 'An order is confirmed only upon successful payment or acceptance of Cash on Delivery terms. Phantom Smoking reserves the right to cancel or refuse any order due to stock unavailability, pricing errors, suspected fraud, or failure to meet age verification requirements. In such cases, a full refund will be issued.'],
      ['Delivery', 'Delivery times are estimates and may vary due to factors outside our control. Phantom Smoking is not liable for delays caused by incorrect delivery information provided by the customer, adverse weather conditions, or other force majeure events.'],
      ['Returns & Refunds', 'Returns are accepted within 7 days of delivery for unopened, sealed products in original condition. Please refer to our Returns Policy for full details. Refunds are processed within 5–7 business days to the original payment method.'],
      ['Intellectual Property', 'All content on the Phantom Smoking website, including logos, images, text, and design, is the property of Phantom Smoking and is protected by applicable intellectual property laws. Unauthorised use, reproduction, or distribution is strictly prohibited.'],
      ['Limitation of Liability', 'Phantom Smoking shall not be liable for any indirect, incidental, or consequential damages arising from the use of our products or services. Our total liability shall not exceed the value of the order in question.'],
      ['Health Warning', '⚠️ Tobacco and nicotine products are harmful to health. Smoking causes cancer, heart disease, stroke, lung diseases, and other serious conditions. Phantom Smoking complies with UAE Federal Law No. 15 of 2009 on Tobacco Control and all applicable UAE regulations regarding the sale and marketing of tobacco and nicotine products.'],
      ['Governing Law', 'These Terms & Conditions are governed by and construed in accordance with the laws of the United Arab Emirates. Any disputes arising from these terms shall be subject to the exclusive jurisdiction of the courts of Dubai, UAE.'],
      ['Contact', 'For any questions regarding these Terms & Conditions, please contact us at <a href="mailto:info@phantomsmoking.com" style="color:var(--color-secondary)">info@phantomsmoking.com</a> or call <a href="tel:+971562177081" style="color:var(--color-secondary)">+971 56 217 7081</a>.'],
    ] as [$heading, $content]): ?>
    <h2 style="font-family:var(--font-heading);color:var(--color-primary);font-size:1.2rem;margin:28px 0 10px;padding-top:8px;border-top:1px solid var(--color-border)"><?= $heading ?></h2>
    <p><?= $content ?></p>
    <?php endforeach; ?>

  </div>
</div>
