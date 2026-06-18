<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Order;

class OrderController extends Controller
{
    public function confirm(string $id): void
    {
        $order = (new Order())->getOrderWithItems((int)$id);
        if (!$order) { $this->redirect('/'); }

        // Logged-in user: must own this order
        if ($order['user_id']) {
            if (!Auth::check() || $order['user_id'] !== Auth::id()) {
                $this->redirect('/');
            }
        } else {
            // Guest order: verify via session token stored at checkout
            $allowed = \App\Core\Session::get('guest_order_ids', []);
            if (!in_array((int)$id, $allowed, true)) {
                $this->redirect('/');
            }
        }

        $this->render('checkout/confirm', ['title' => "Order Confirmed — Phantom Smoking", 'order' => $order]);
    }

    public function track(string $orderNumber): void
    {
        $order = (new Order())->getByOrderNumber($orderNumber);
        if (!$order) { $this->flash('error', 'Order not found.'); $this->redirect('/'); }

        // Logged-in user: must own this order
        if ($order['user_id']) {
            if (!Auth::check() || $order['user_id'] !== Auth::id()) {
                \App\Core\Session::flash('error', 'Please login to track this order.');
                $this->redirect('/login?redirect=' . urlencode('/track/' . $orderNumber));
            }
        } else {
            // Guest: must supply the email used at checkout
            $inputEmail = strtolower(trim($this->request->get('email', '')));
            if ($inputEmail) {
                $orderEmail = strtolower(trim($order['guest_email'] ?? ''));
                if (!$orderEmail || !hash_equals($orderEmail, $inputEmail)) {
                    $this->flash('error', 'Email does not match this order.');
                    $this->redirect('/track/' . $orderNumber . '?verify=1');
                }
            } else {
                // Show email verification form first
                $this->render('checkout/track', [
                    'title'       => 'Track Order — Phantom Smoking',
                    'order'       => null,
                    'verify_mode' => true,
                    'order_number'=> $orderNumber,
                ]);
                return;
            }
        }

        $order = (new Order())->getOrderWithItems($order['id']);
        $this->render('checkout/track', ['title' => "Track Order — Phantom Smoking", 'order' => $order, 'verify_mode' => false]);
    }
}
