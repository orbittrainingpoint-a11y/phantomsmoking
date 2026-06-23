<?php
if (!function_exists('send_email')) {
    function send_email(string $to, string $subject, string $body, string $toName = ''): bool
    {
        return (new \App\Core\Mailer())->send($to, $subject, $body, $toName);
    }
}

if (!function_exists('send_otp_email')) {
    function send_otp_email(string $email, string $otp, string $purpose = 'login'): bool
    {
        $purposeLabel = $purpose === 'register' ? 'verify your new account' : 'complete your login';
        $appName      = $_ENV['APP_NAME'] ?? 'Phantom Smoking';
        $body = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#0f0f1a;font-family:Arial,sans-serif">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#0f0f1a;padding:40px 0">
    <tr><td align="center">
      <table width="520" cellpadding="0" cellspacing="0" style="background:#1a1a2e;border-radius:12px;overflow:hidden;border:1px solid rgba(212,175,55,0.2)">
        <tr>
          <td style="background:linear-gradient(135deg,#1a1a2e,#2a2a4e);padding:32px;text-align:center;border-bottom:2px solid #d4af37">
            <div style="font-size:1.6rem;font-weight:700;color:#fff;letter-spacing:1px">
              {$appName}
            </div>
            <div style="color:#d4af37;font-size:0.85rem;margin-top:4px">DUBAI, UAE</div>
          </td>
        </tr>
        <tr>
          <td style="padding:40px 36px">
            <h2 style="color:#fff;margin:0 0 8px;font-size:1.4rem">Your Verification Code</h2>
            <p style="color:rgba(255,255,255,0.6);margin:0 0 32px;font-size:0.95rem">
              Use the code below to {$purposeLabel}. It expires in <strong style="color:#d4af37">10 minutes</strong>.
            </p>
            <div style="background:#0f0f1a;border:2px dashed #d4af37;border-radius:12px;padding:28px;text-align:center;margin-bottom:32px">
              <div style="font-size:2.8rem;font-weight:700;letter-spacing:16px;color:#d4af37;font-family:monospace">{$otp}</div>
            </div>
            <p style="color:rgba(255,255,255,0.4);font-size:0.82rem;margin:0">
              If you did not request this code, please ignore this email. Do not share this code with anyone.
            </p>
          </td>
        </tr>
        <tr>
          <td style="padding:20px 36px;border-top:1px solid rgba(255,255,255,0.08);text-align:center">
            <p style="color:rgba(255,255,255,0.3);font-size:0.75rem;margin:0">
              © {$appName} · Dubai, UAE · For adults 18+ only
            </p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
        $subject = $purpose === 'register'
            ? "Verify your {$appName} account — {$otp}"
            : "Your {$appName} login code — {$otp}";
        return send_email($email, $subject, $body);
    }
}

if (!function_exists('send_whatsapp')) {
    function send_whatsapp(string $to, string $message): bool
    {
        $instance = $_ENV['WHATSAPP_INSTANCE'] ?? '';
        $token    = $_ENV['WHATSAPP_TOKEN'] ?? '';
        if (!$instance || !$token) return false;

        // Normalize number — strip +, spaces, dashes
        $to = preg_replace('/[^0-9]/', '', $to);
        // Ensure UAE country code
        if (str_starts_with($to, '0')) $to = '971' . substr($to, 1);
        if (!str_starts_with($to, '971') && strlen($to) === 9) $to = '971' . $to;

        $url  = "https://api.ultramsg.com/{$instance}/messages/chat";
        $data = http_build_query(['token' => $token, 'to' => $to, 'body' => $message]);

        $ctx = stream_context_create(['http' => [
            'method'  => 'POST',
            'header'  => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $data,
            'timeout' => 10,
        ]]);
        $result = @file_get_contents($url, false, $ctx);
        if ($result === false) return false;
        $json = json_decode($result, true);
        return !empty($json['sent']) || !empty($json['id']);
    }
}

if (!function_exists('send_order_whatsapp')) {
    function send_order_whatsapp(array $order, array $items): void
    {
        $appUrl  = rtrim($_ENV['APP_URL'] ?? '', '/');
        $phone   = $order['shipping_phone'] ?? '';
        if (!$phone) return;

        // Build items summary (max 5 lines to keep message short)
        $itemLines = '';
        foreach (array_slice($items, 0, 5) as $item) {
            $variant = !empty($item['variant_name']) ? " ({$item['variant_name']})" : '';
            $itemLines .= "  • {$item['product_name']}{$variant} x{$item['quantity']} — AED " . number_format($item['total_price'], 2) . "\n";
        }
        if (count($items) > 5) $itemLines .= '  • ... and ' . (count($items) - 5) . " more items\n";

        $total    = 'AED ' . number_format($order['total_amount'], 2);
        $payment  = payment_method_label($order['payment_method']);
        $delivery = ucwords(str_replace('_', ' ', $order['delivery_type']));
        $trackUrl = $appUrl . '/track/' . $order['order_number'];

        $message = "🛍️ *Order Confirmed — Phantom Smoking*\n\n"
            . "Hello {$order['shipping_name']}! Your order has been confirmed.\n\n"
            . "📦 *Order:* {$order['order_number']}\n"
            . "💰 *Total:* {$total}\n"
            . "💳 *Payment:* {$payment}\n"
            . "🚚 *Delivery:* {$delivery}\n\n"
            . "*Items:*\n{$itemLines}\n"
            . "🔗 Track your order: {$trackUrl}\n\n"
            . "Questions? Reply to this message or call +971 55 954 5800\n"
            . "Thank you for shopping with Phantom Smoking! 🙏";

        send_whatsapp($phone, $message);
    }
}

if (!function_exists('send_order_confirmation')) {
    function send_order_confirmation(array $order, array $items): void
    {
        ob_start();
        include __DIR__ . '/../views/emails/order-confirm.php';
        $body = ob_get_clean();

        $customerEmail = $order['guest_email'] ?? '';
        if (!$customerEmail && !empty($order['user_id'])) {
            $db            = \App\Core\Database::getInstance();
            $user          = $db->fetch('SELECT email FROM users WHERE id = ?', [$order['user_id']]);
            $customerEmail = $user['email'] ?? '';
        }

        $subject = 'Order Confirmed — ' . $order['order_number'] . ' | Phantom Smoking';

        // Send to customer
        if ($customerEmail) {
            send_email($customerEmail, $subject, $body, $order['shipping_name']);
        }

        // Send copy to admin
        $adminEmail = $_ENV['ADMIN_NOTIFY_EMAIL'] ?? 'phantomsmokingonline@gmail.com';
        send_email($adminEmail, '[New Order] ' . $subject, $body, 'Phantom Smoking Admin');

        // Send WhatsApp to customer
        send_order_whatsapp($order, $items);
    }
}

if (!function_exists('send_order_status_email')) {
    function send_order_status_email(array $order): void
    {
        $statusLabels = [
            'confirmed'        => 'Order Confirmed',
            'processing'       => 'Order Being Processed',
            'packed'           => 'Order Packed',
            'out_for_delivery' => 'Out for Delivery',
            'delivered'        => 'Order Delivered',
            'cancelled'        => 'Order Cancelled',
            'returned'         => 'Order Returned',
        ];
        $status      = $order['status'] ?? '';
        $statusLabel = $statusLabels[$status] ?? ucwords(str_replace('_', ' ', $status));
        $appName     = $_ENV['APP_NAME'] ?? 'Phantom Smoking';
        $trackUrl    = rtrim($_ENV['APP_URL'] ?? '', '/') . '/track/' . $order['order_number'];

        $statusColor = match($status) {
            'delivered'        => '#10b981',
            'cancelled'        => '#ef4444',
            'returned'         => '#6b7280',
            'out_for_delivery' => '#3b82f6',
            default            => '#C8963C',
        };

        $noteHtml = !empty($order['status_note'])
            ? '<div style="background:#f9f6f1;border-left:3px solid #C8963C;padding:10px 14px;margin:16px 0;font-size:0.88rem;color:#374151">' . htmlspecialchars($order['status_note']) . '</div>'
            : '';

        $body = <<<HTML
<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f0ede8;font-family:Arial,sans-serif">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0ede8;padding:40px 0">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:10px;overflow:hidden">
      <tr>
        <td style="background:#1A1A2E;padding:24px 32px;text-align:center;border-bottom:3px solid {$statusColor}">
          <div style="font-size:1.4rem;font-weight:900;color:#fff;letter-spacing:1px">{$appName}</div>
          <div style="color:rgba(255,255,255,0.5);font-size:0.78rem;margin-top:4px">DUBAI, UAE</div>
        </td>
      </tr>
      <tr>
        <td style="padding:32px">
          <div style="text-align:center;margin-bottom:24px">
            <div style="display:inline-block;background:{$statusColor};color:#fff;padding:8px 22px;border-radius:20px;font-size:0.9rem;font-weight:700">{$statusLabel}</div>
          </div>
          <p style="color:#374151;font-size:0.95rem;margin:0 0 8px">Hi <strong>{$order['shipping_name']}</strong>,</p>
          <p style="color:#6B7280;font-size:0.88rem;margin:0 0 20px">Your order status has been updated.</p>
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#f9f6f1;border-radius:8px;margin-bottom:20px">
            <tr><td style="padding:14px 18px;font-size:0.85rem;color:#6B7280;border-bottom:1px solid #e5e7eb">Order Number</td>
                <td style="padding:14px 18px;font-size:0.88rem;font-weight:700;color:#1A1A2E;text-align:right;border-bottom:1px solid #e5e7eb">{$order['order_number']}</td></tr>
            <tr><td style="padding:14px 18px;font-size:0.85rem;color:#6B7280">Status</td>
                <td style="padding:14px 18px;text-align:right"><span style="background:{$statusColor};color:#fff;padding:3px 12px;border-radius:20px;font-size:0.8rem;font-weight:700">{$statusLabel}</span></td></tr>
          </table>
          {$noteHtml}
          <div style="text-align:center;margin:24px 0">
            <a href="{$trackUrl}" style="background:#C8963C;color:#fff;padding:12px 28px;border-radius:4px;text-decoration:none;font-weight:700;font-size:0.9rem">Track Your Order →</a>
          </div>
          <p style="color:#9CA3AF;font-size:0.8rem;text-align:center;margin:0">
            Questions? WhatsApp us at <a href="https://wa.me/971568335210" style="color:#C8963C">+971 56 833 5210</a>
            or email <a href="mailto:phantomsmokingonline@gmail.com" style="color:#C8963C">phantomsmokingonline@gmail.com</a>
          </p>
        </td>
      </tr>
      <tr>
        <td style="background:#1A1A2E;padding:16px 32px;text-align:center;color:rgba(255,255,255,0.35);font-size:0.72rem">
          © {$appName} · Dubai, UAE · For adults 18+ only
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</body></html>
HTML;

        // Get customer email
        $customerEmail = $order['guest_email'] ?? '';
        if (!$customerEmail && !empty($order['user_id'])) {
            $db            = \App\Core\Database::getInstance();
            $user          = $db->fetch('SELECT email FROM users WHERE id = ?', [$order['user_id']]);
            $customerEmail = $user['email'] ?? '';
        }

        $subject = $statusLabel . ' — ' . $order['order_number'] . ' | ' . $appName;

        // Send to customer
        if ($customerEmail) {
            send_email($customerEmail, $subject, $body, $order['shipping_name']);
        }

        // Send copy to admin
        $adminEmail = $_ENV['ADMIN_NOTIFY_EMAIL'] ?? 'phantomsmokingonline@gmail.com';
        send_email($adminEmail, '[Status Update] ' . $subject, $body, 'Phantom Smoking Admin');
    }
}

if (!function_exists('send_password_reset')) {
    function send_password_reset(string $email, string $token): void
    {
        $resetUrl = url('reset-password/' . $token);
        ob_start();
        include __DIR__ . '/../views/emails/password-reset.php';
        $body = ob_get_clean();
        send_email($email, "Reset Your Password — Phantom Smoking", $body);
    }
}

if (!function_exists('send_welcome_email')) {
    function send_welcome_email(array $user): void
    {
        ob_start();
        include __DIR__ . '/../views/emails/welcome.php';
        $body = ob_get_clean();
        send_email($user['email'], "Welcome to Phantom Smoking!", $body, $user['first_name']);
    }
}
