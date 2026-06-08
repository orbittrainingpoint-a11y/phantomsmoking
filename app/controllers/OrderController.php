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
        if ($order['user_id'] && $order['user_id'] !== Auth::id()) { $this->redirect('/'); }
        $this->render('checkout/confirm', ['title' => "Order Confirmed — Phantom Smoking", 'order' => $order]);
    }

    public function track(string $orderNumber): void
    {
        $order = (new Order())->getByOrderNumber($orderNumber);
        if (!$order) { $this->flash('error', 'Order not found.'); $this->redirect('/'); }
        $order = (new Order())->getOrderWithItems($order['id']);
        $this->render('checkout/track', ['title' => "Track Order — Phantom Smoking", 'order' => $order]);
    }
}
