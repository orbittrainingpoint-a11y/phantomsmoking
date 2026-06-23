<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\User;
use App\Models\Order;
use App\Models\Wishlist;

class AccountController extends Controller
{
    public function __construct() { parent::__construct(); $this->requireAuth(); }

    public function dashboard(): void
    {
        $user  = Auth::user();
        $uid   = (int)Auth::id();
        $stats = (new User())->getStats($uid);
        $orders = (new Order())->getUserOrders($uid, 1, 5);
        $this->render('account/dashboard', ['title' => "My Account — Phantom Smoking", 'user' => $user, 'stats' => $stats, 'recent_orders' => $orders['items']]);
    }

    public function orders(): void
    {
        $page   = max(1, (int)$this->request->get('page', 1));
        $orders = (new Order())->getUserOrders((int)Auth::id(), $page);
        $this->render('account/orders', ['title' => "My Orders — Phantom Smoking", 'orders' => $orders]);
    }

    public function orderDetail(string $id): void
    {
        $order = (new Order())->getOrderWithItems((int)$id);
        if (!$order || $order['user_id'] !== (int)Auth::id()) { $this->redirect('/account/orders'); }
        $this->render('account/order-detail', ['title' => "Order #{$order['order_number']} — Phantom Smoking", 'order' => $order]);
    }

    public function profileForm(): void
    {
        $this->render('account/profile', ['title' => "My Profile — Phantom Smoking", 'user' => Auth::user(), 'csrf' => $this->csrfToken()]);
    }

    public function profileUpdate(): void
    {
        $data = [
            'first_name'            => sanitize_string($this->request->post('first_name', '')),
            'last_name'             => sanitize_string($this->request->post('last_name', '')),
            'phone'                 => sanitize_string($this->request->post('phone', '')),
            'date_of_birth'         => $this->request->post('date_of_birth') ?: null,
            'newsletter_subscribed' => (int)$this->request->post('newsletter', 0),
        ];
        (new User())->update((int)Auth::id(), $data);
        $this->flash('success', 'Profile updated successfully.');
        $this->redirect('/account/profile');
    }

    public function addresses(): void
    {
        $addresses = (new User())->getAddresses((int)Auth::id());
        $this->render('account/addresses', ['title' => "My Addresses — Phantom Smoking", 'addresses' => $addresses, 'csrf' => $this->csrfToken()]);
    }

    public function addAddress(): void
    {
        $data = [
            'label'          => sanitize_string($this->request->post('label', 'Home')),
            'full_name'      => sanitize_string($this->request->post('full_name', '')),
            'phone'          => sanitize_string($this->request->post('phone', '')),
            'address_line1'  => sanitize_string($this->request->post('address_line1', '')),
            'address_line2'  => sanitize_string($this->request->post('address_line2', '')),
            'area'           => sanitize_string($this->request->post('area', '')),
            'city'           => sanitize_string($this->request->post('city', 'Dubai')),
            'emirate'        => sanitize_string($this->request->post('emirate', 'Dubai')),
            'is_default'     => (int)$this->request->post('is_default', 0),
        ];
        (new User())->addAddress((int)Auth::id(), $data);
        $this->flash('success', 'Address added.');
        $this->redirect('/account/addresses');
    }

    public function updateAddress(string $id): void
    {
        $data = [
            'label'         => sanitize_string($this->request->post('label', 'Home')),
            'full_name'     => sanitize_string($this->request->post('full_name', '')),
            'phone'         => sanitize_string($this->request->post('phone', '')),
            'address_line1' => sanitize_string($this->request->post('address_line1', '')),
            'address_line2' => sanitize_string($this->request->post('address_line2', '')),
            'area'          => sanitize_string($this->request->post('area', '')),
            'city'          => sanitize_string($this->request->post('city', 'Dubai')),
            'emirate'       => sanitize_string($this->request->post('emirate', 'Dubai')),
            'is_default'    => (int)$this->request->post('is_default', 0),
        ];
        (new User())->updateAddress((int)$id, (int)Auth::id(), $data);
        $this->flash('success', 'Address updated.');
        $this->redirect('/account/addresses');
    }

    public function deleteAddress(string $id): void
    {
        (new User())->deleteAddress((int)$id, (int)Auth::id());
        $this->flash('success', 'Address removed.');
        $this->redirect('/account/addresses');
    }

    public function wishlist(): void
    {
        $items = (new Wishlist())->getUserWishlist((int)Auth::id());
        $this->render('account/wishlist', ['title' => "My Wishlist — Phantom Smoking", 'items' => $items]);
    }

    public function rewards(): void
    {
        $user    = Auth::user();
        $history = (new User())->getRewardHistory((int)Auth::id());
        $this->render('account/rewards', ['title' => "Reward Points — Phantom Smoking", 'user' => $user, 'history' => $history]);
    }

    public function changePasswordForm(): void
    {
        $this->render('account/change-password', ['title' => "Change Password — Phantom Smoking", 'csrf' => $this->csrfToken()]);
    }

    public function changePassword(): void
    {
        $user    = Auth::user();
        if (!$user) { $this->redirect('/login'); }
        $current = $this->request->post('current_password', '');
        $new     = $this->request->post('new_password', '');
        $confirm = $this->request->post('confirm_password', '');

        if (!password_verify($current, $user['password_hash'])) {
            $this->flash('error', 'Current password is incorrect.');
            $this->redirect('/account/change-password');
        }
        $errors = validate_password_strength($new);
        if ($new !== $confirm) $errors[] = 'Passwords do not match.';
        if (!empty($errors)) { $this->flash('error', $errors[0]); $this->redirect('/account/change-password'); }

        (new User())->update((int)Auth::id(), ['password_hash' => Auth::hashPassword($new)]);
        $this->flash('success', 'Password changed successfully.');
        $this->redirect('/account');
    }
}
