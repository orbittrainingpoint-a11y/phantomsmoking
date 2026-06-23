<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Order;
use App\Models\User;
use App\Models\Coupon;
use App\Models\Banner;
use App\Models\Review;
use App\Models\DeliveryZone;

class AdminController extends Controller
{
    public function __construct() { parent::__construct(); $this->requireAdmin(); }

    public function dashboard(): void
    {
        $stats   = (new Order())->getDashboardStats();
        $lowStock = (new Product())->getLowStockProducts(5);
        $recentOrders = (new Order())->getPaginated(1, 10);
        $salesData = (new Order())->getSalesReport(date('Y-m-d', strtotime('-30 days')), date('Y-m-d'), 'day');
        $this->render('admin/dashboard', ['title' => "Admin Dashboard — Phantom Smoking", 'stats' => $stats, 'low_stock' => $lowStock, 'recent_orders' => $recentOrders['items'], 'sales_data' => $salesData], 'admin');
    }

    public function products(): void
    {
        $page    = max(1, (int)$this->request->get('page', 1));
        $search  = $this->request->get('search', '');
        $status  = $this->request->get('status', '');
        $where   = '1=1'; $params = [];
        if ($search) { $where .= ' AND (p.name LIKE ? OR p.sku LIKE ?)'; $params = ["%$search%", "%$search%"]; }
        if ($status) { $where .= ' AND p.status = ?'; $params[] = $status; }
        $total = (int)$this->db->fetch("SELECT COUNT(*) as cnt FROM products p WHERE $where", $params)['cnt'];
        $offset = ($page - 1) * 20;
        $products = $this->db->fetchAll("SELECT p.*, c.name AS category_name, b.name AS brand_name, pi.image_path AS primary_image FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN brands b ON p.brand_id = b.id LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1 WHERE $where ORDER BY p.created_at DESC LIMIT 20 OFFSET ?", [...$params, $offset]);
        $categories = (new Category())->getMenuCategories();
        $brands = (new Brand())->getAll();
        $this->render('admin/products', ['title' => 'Products — Admin', 'products' => $products, 'total' => $total, 'page' => $page, 'search' => $search, 'categories' => $categories, 'brands' => $brands], 'admin');
    }

    public function productCreate(): void
    {
        $categories = $this->db->fetchAll('SELECT * FROM categories WHERE is_active = 1 ORDER BY name');
        $brands = (new Brand())->getAll();
        $this->render('admin/product-edit', ['title' => 'Add Product — Admin', 'product' => null, 'categories' => $categories, 'brands' => $brands, 'csrf' => $this->csrfToken()], 'admin');
    }

    public function productStore(): void
    {
        try {
            $data = $this->buildProductData();

            if (empty($data['name'])) {
                $this->flash('error', 'Product name is required.');
                $this->redirect('/admin/products/create');
            }
            if ($data['price'] <= 0) {
                $this->flash('error', 'Product price must be greater than 0.');
                $this->redirect('/admin/products/create');
            }

            $data['slug'] = $this->uniqueSlug($data['slug']);

            $productId = (new Product())->create($data);
            $this->handleProductImages($productId);
            $this->handleProductFlavours($productId);
            $this->handleVariantTypes($productId);
            $this->handleVariationsJson($productId);

            $this->flash('success', 'Product created successfully.');
            $this->redirect('/admin/products/' . $productId . '/edit');
        } catch (\Throwable $e) {
            error_log('[ProductStore] ' . $e->getMessage());
            $this->flash('error', 'Failed to create product: ' . $e->getMessage());
            $this->redirect('/admin/products/create');
        }
    }

    public function productEdit(string $id): void
    {
        $product = $this->db->fetch('SELECT * FROM products WHERE id = ?', [(int)$id]);
        if (!$product) { $this->redirect('/admin/products'); }
        $product['images']        = (new Product())->getImages((int)$id);
        $product['variants']       = (new Product())->getProductVariants((int)$id);
        $product['attributes']     = (new Product())->getAttributes((int)$id);
        $product['variant_types']  = (new Product())->getVariantTypes((int)$id);
        $categories = $this->db->fetchAll('SELECT * FROM categories WHERE is_active = 1 ORDER BY name');
        $brands = (new Brand())->getAll();
        $this->render('admin/product-edit', ['title' => 'Edit Product — Admin', 'product' => $product, 'categories' => $categories, 'brands' => $brands, 'csrf' => $this->csrfToken()], 'admin');
    }

    public function productUpdate(string $id): void
    {
        try {
            $data = $this->buildProductData();

            // Keep existing slug if name unchanged, else ensure uniqueness
            $existing = $this->db->fetch('SELECT slug FROM products WHERE id = ?', [(int)$id]);
            $newSlug  = slugify($this->request->post('name', ''));
            if ($existing && $existing['slug'] !== $newSlug) {
                $data['slug'] = $this->uniqueSlug($newSlug, (int)$id);
            }

            (new Product())->update((int)$id, $data);
            $this->handleProductImages((int)$id);
            $this->handleProductFlavours((int)$id);
            $this->handleVariantTypes((int)$id);
            $this->flash('success', 'Product updated successfully.');
            $this->redirect('/admin/products');
        } catch (\Throwable $e) {
            error_log('[ProductUpdate] ' . $e->getMessage());
            $this->flash('error', 'Failed to update product: ' . $e->getMessage());
            $this->redirect('/admin/products/' . $id . '/edit');
        }
    }

    public function productArchive(string $id): void
    {
        (new Product())->update((int)$id, ['status' => 'archived']);
        $this->flash('success', 'Product archived.');
        $this->redirect('/admin/products');
    }

    public function productRestore(string $id): void
    {
        (new Product())->update((int)$id, ['status' => 'active']);
        $this->flash('success', 'Product restored to active.');
        $this->redirect('/admin/products');
    }

    public function productDestroy(string $id): void
    {
        $pid = (int)$id;
        $images = $this->db->fetchAll('SELECT image_path FROM product_images WHERE product_id = ?', [$pid]);
        foreach ($images as $img) { delete_image($img['image_path']); }
        // Delete variant options first (FK constraint)
        $variants = $this->db->fetchAll('SELECT id FROM product_variants WHERE product_id = ?', [$pid]);
        foreach ($variants as $v) {
            $this->db->delete('product_variant_options', 'variant_id = ?', [$v['id']]);
        }
        $this->db->delete('product_variants',   'product_id = ?', [$pid]);
        $this->db->delete('product_images',     'product_id = ?', [$pid]);
        $this->db->delete('product_flavours',   'product_id = ?', [$pid]);
        $this->db->delete('product_attributes', 'product_id = ?', [$pid]);
        $this->db->delete('products',           'id = ?',         [$pid]);
        $this->flash('success', 'Product permanently deleted.');
        $this->redirect('/admin/products');
    }

    public function categories(): void
    {
        $categories = $this->db->fetchAll('SELECT c.*, p.name AS parent_name FROM categories c LEFT JOIN categories p ON c.parent_id = p.id ORDER BY c.position');
        $this->render('admin/categories', ['title' => 'Categories — Admin', 'categories' => $categories, 'csrf' => $this->csrfToken()], 'admin');
    }

    public function categoryStore(): void
    {
        $name = sanitize_string($this->request->post('name', ''));
        $image = null;
        if (!empty($_FILES['image']['name'])) {
            $image = save_category_image($_FILES['image']);
        }
        $this->db->insert('categories', [
            'parent_id'   => $this->request->post('parent_id') ?: null,
            'name'        => $name,
            'slug'        => slugify($name),
            'description' => sanitize_string($this->request->post('description', '')),
            'image'       => $image,
            'position'    => (int)$this->request->post('position', 0),
            'is_active'   => (int)$this->request->post('is_active', 1),
            'show_in_menu'=> (int)$this->request->post('show_in_menu', 1),
        ]);
        $this->flash('success', 'Category created.');
        $this->redirect('/admin/categories');
    }

    public function categoryUpdate(string $id): void
    {
        $name = sanitize_string($this->request->post('name', ''));
        $data = [
            'name'        => $name,
            'description' => sanitize_string($this->request->post('description', '')),
            'position'    => (int)$this->request->post('position', 0),
            'is_active'   => (int)$this->request->post('is_active', 1),
            'show_in_menu'=> (int)$this->request->post('show_in_menu', 1),
        ];
        if (!empty($_FILES['image']['name'])) {
            $image = save_category_image($_FILES['image']);
            if ($image) $data['image'] = $image;
        } elseif ($this->request->post('existing_image')) {
            $data['image'] = $this->request->post('existing_image');
        }
        $this->db->update('categories', $data, 'id = ?', [(int)$id]);
        $this->flash('success', 'Category updated.');
        $this->redirect('/admin/categories');
    }

    public function brands(): void
    {
        $brands = (new Brand())->getAll();
        $this->render('admin/brands', ['title' => 'Brands — Admin', 'brands' => $brands, 'csrf' => $this->csrfToken()], 'admin');
    }

    public function brandStore(): void
    {
        $name = sanitize_string($this->request->post('name', ''));
        $this->db->insert('brands', [
            'name'              => $name,
            'slug'              => slugify($name),
            'country_of_origin' => sanitize_string($this->request->post('country', '')),
            'is_featured'       => (int)$this->request->post('is_featured', 0),
            'is_active'         => 1,
        ]);
        $this->flash('success', 'Brand created.');
        $this->redirect('/admin/brands');
    }

    public function orders(): void
    {
        $page    = max(1, (int)$this->request->get('page', 1));
        $filters = ['status' => $this->request->get('status'), 'payment_status' => $this->request->get('payment_status'), 'search' => $this->request->get('search'), 'date_from' => $this->request->get('date_from'), 'date_to' => $this->request->get('date_to')];
        $orders  = (new Order())->getPaginated($page, 20, array_filter($filters));
        $this->render('admin/orders', ['title' => 'Orders — Admin', 'orders' => $orders, 'filters' => $filters], 'admin');
    }

    public function orderDetail(string $id): void
    {
        $order = (new Order())->getOrderWithItems((int)$id);
        if (!$order) { $this->redirect('/admin/orders'); }
        $this->render('admin/order-detail', ['title' => "Order #{$order['order_number']} — Admin", 'order' => $order, 'csrf' => $this->csrfToken()], 'admin');
    }

    public function orderStatus(string $id): void
    {
        $status = $this->request->post('status', '');
        $note   = sanitize_string($this->request->post('note', ''));
        (new Order())->updateStatus((int)$id, $status, $note, \App\Core\Auth::id());

        // Email customer + admin about the status change
        $order = (new Order())->getOrderWithItems((int)$id);
        if ($order) {
            $order['status_note'] = $note;
            send_order_status_email($order);
        }

        $this->flash('success', 'Order status updated.');
        $this->redirect('/admin/orders/' . $id);
    }

    public function customers(): void
    {
        $page   = max(1, (int)$this->request->get('page', 1));
        $search = $this->request->get('search', '');
        $customers = (new User())->getPaginated($page, 20, $search);
        $this->render('admin/customers', ['title' => 'Customers — Admin', 'customers' => $customers, 'search' => $search], 'admin');
    }

    public function customerDetail(string $id): void
    {
        $user   = (new User())->find((int)$id);
        if (!$user) { $this->redirect('/admin/customers'); }
        $orders = (new Order())->getUserOrders((int)$id, 1, 10);
        $this->render('admin/customer-detail', ['title' => "{$user['first_name']} {$user['last_name']} — Admin", 'customer' => $user, 'orders' => $orders['items'], 'csrf' => $this->csrfToken()], 'admin');
    }

    public function customerBan(string $id): void
    {
        $reason = sanitize_string($this->request->post('reason', ''));
        $user   = (new User())->find((int)$id);
        (new User())->update((int)$id, ['is_active' => $user['is_active'] ? 0 : 1, 'banned_reason' => $reason]);
        $this->flash('success', 'Customer status updated.');
        $this->redirect('/admin/customers/' . $id);
    }

    public function coupons(): void
    {
        $coupons = $this->db->fetchAll('SELECT * FROM coupons ORDER BY created_at DESC');
        $this->render('admin/coupons', ['title' => 'Coupons — Admin', 'coupons' => $coupons, 'csrf' => $this->csrfToken()], 'admin');
    }

    public function couponStore(): void
    {
        $this->db->insert('coupons', [
            'code'              => strtoupper(sanitize_string($this->request->post('code', ''))),
            'description'       => sanitize_string($this->request->post('description', '')),
            'type'              => $this->request->post('type', 'percentage'),
            'value'             => (float)$this->request->post('value', 0),
            'min_order_amount'  => (float)$this->request->post('min_order_amount', 0),
            'max_discount_amount'=> $this->request->post('max_discount_amount') ?: null,
            'usage_limit'       => $this->request->post('usage_limit') ?: null,
            'usage_per_user'    => (int)$this->request->post('usage_per_user', 1),
            'start_date'        => $this->request->post('start_date') ?: null,
            'end_date'          => $this->request->post('end_date') ?: null,
            'is_active'         => 1,
        ]);
        $this->flash('success', 'Coupon created.');
        $this->redirect('/admin/coupons');
    }

    public function couponDelete(string $id): void
    {
        $this->db->delete('coupons', 'id = ?', [(int)$id]);
        $this->flash('success', 'Coupon deleted.');
        $this->redirect('/admin/coupons');
    }

    public function banners(): void
    {
        $banners = $this->db->fetchAll('SELECT * FROM banners ORDER BY sort_order');
        $this->render('admin/banners', ['title' => 'Banners — Admin', 'banners' => $banners, 'csrf' => $this->csrfToken()], 'admin');
    }

    public function bannerStore(): void
    {
        $data = [
            'title'          => sanitize_string($this->request->post('title', '')),
            'subtitle'       => sanitize_string($this->request->post('subtitle', '')),
            'image_desktop'  => sanitize_string($this->request->post('image_desktop', '')),
            'image_mobile'   => sanitize_string($this->request->post('image_mobile', '')),
            'link_url'       => sanitize_string($this->request->post('link_url', '')),
            'link_text'      => sanitize_string($this->request->post('link_text', '')),
            'position'       => $this->request->post('position', 'hero'),
            'sort_order'     => (int)$this->request->post('sort_order', 0),
            'is_active'      => 1,
        ];
        if (!empty($_FILES['image_desktop']['name'])) {
            $path = save_product_image($_FILES['image_desktop'], 0);
            if ($path) $data['image_desktop'] = $path;
        }
        $this->db->insert('banners', $data);
        $this->flash('success', 'Banner created.');
        $this->redirect('/admin/banners');
    }

    public function bannerDelete(string $id): void
    {
        $this->db->delete('banners', 'id = ?', [(int)$id]);
        $this->flash('success', 'Banner deleted.');
        $this->redirect('/admin/banners');
    }

    public function reviews(): void
    {
        $page    = max(1, (int)$this->request->get('page', 1));
        $reviews = (new Review())->getPending($page);
        $this->render('admin/reviews', ['title' => 'Reviews — Admin', 'reviews' => $reviews, 'csrf' => $this->csrfToken()], 'admin');
    }

    public function reviewApprove(string $id): void
    {
        $this->db->update('product_reviews', ['status' => 'approved'], 'id = ?', [(int)$id]);
        $review = $this->db->fetch('SELECT product_id FROM product_reviews WHERE id = ?', [(int)$id]);
        if ($review) (new Product())->updateProductRating($review['product_id']);
        $this->flash('success', 'Review approved.');
        $this->redirect('/admin/reviews');
    }

    public function reviewReject(string $id): void
    {
        $this->db->update('product_reviews', ['status' => 'rejected'], 'id = ?', [(int)$id]);
        $this->flash('success', 'Review rejected.');
        $this->redirect('/admin/reviews');
    }

    public function reports(): void
    {
        $this->redirect('/admin/reports');
    }

    public function settings(): void
    {
        $settings = $this->db->fetchAll('SELECT * FROM settings');
        $settingsMap = array_column($settings, 'setting_value', 'setting_key');
        $this->render('admin/settings', ['title' => 'Settings — Admin', 'settings' => $settingsMap, 'csrf' => $this->csrfToken()], 'admin');
    }

    public function settingsUpdate(): void
    {
        $allowed = [
            'store_name','store_email','store_phone','store_address','vat_number',
            'reward_earn_rate','reward_redeem_rate','reward_min_redeem','free_shipping_threshold',
            'welcome_bonus_points','review_bonus_points',
            'default_shipping_fee','default_express_fee',
            // Maps & contact
            'google_maps_api_key','google_maps_embed_url','store_lat','store_lng','store_map_address',
            'whatsapp_number','contact_email',
            // Delivery KM
            'delivery_km_enabled','delivery_base_fee','delivery_per_km_fee','delivery_free_km',
            // Other
            'age_gate_enabled','age_gate_require_dob','maintenance_mode',
            // Payments
            'cod_enabled','card_on_delivery_enabled','payment_link_on_delivery_enabled',
            'stripe_enabled','stripe_public_key','stripe_secret_key','stripe_webhook_secret',
            'telr_enabled','telr_store_id','telr_auth_key','telr_test_mode',
            'tabby_enabled','tabby_public_key','tabby_secret_key','tabby_merchant_code','tabby_test_mode',
            'tamara_enabled','tamara_api_token','tamara_notification_key','tamara_test_mode',
        ];
        $checkboxes = ['cod_enabled','card_on_delivery_enabled','payment_link_on_delivery_enabled',
                       'stripe_enabled','telr_enabled','tabby_enabled','tamara_enabled',
                       'age_gate_enabled','age_gate_require_dob','maintenance_mode','delivery_km_enabled'];
        foreach ($allowed as $key) {
            $val = in_array($key, $checkboxes)
                ? (int)($this->request->post($key, 0) ? 1 : 0)
                : $this->request->post($key);
            if ($val === null) continue;
            $existing = $this->db->fetch('SELECT id FROM settings WHERE setting_key = ?', [$key]);
            if ($existing) $this->db->update('settings', ['setting_value' => sanitize_string((string)$val)], 'setting_key = ?', [$key]);
            else $this->db->insert('settings', ['setting_key' => $key, 'setting_value' => sanitize_string((string)$val)]);
        }
        $this->flash('success', 'Settings saved successfully.');
        $this->redirect('/admin/settings');
    }

    public function flavours(): void
    {
        $flavours = $this->db->fetchAll('SELECT * FROM flavours ORDER BY category, name');
        $this->render('admin/flavours', ['title' => 'Flavours — Admin', 'flavours' => $flavours, 'csrf' => $this->csrfToken()], 'admin');
    }

    public function flavourStore(): void
    {
        $name = sanitize_string($this->request->post('name', ''));
        $this->db->insert('flavours', ['name' => $name, 'slug' => slugify($name), 'category' => $this->request->post('category', 'general'), 'is_active' => (int)$this->request->post('is_active', 1)]);
        $this->flash('success', 'Flavour added.');
        $this->redirect('/admin/flavours');
    }

    public function flavourUpdate(string $id): void
    {
        $name = sanitize_string($this->request->post('name', ''));
        $this->db->update('flavours', ['name' => $name, 'slug' => slugify($name), 'category' => $this->request->post('category', 'general'), 'is_active' => (int)$this->request->post('is_active', 0)], 'id = ?', [(int)$id]);
        $this->flash('success', 'Flavour updated.');
        $this->redirect('/admin/flavours');
    }

    public function flavourDelete(string $id): void
    {
        $this->db->delete('flavours', 'id = ?', [(int)$id]);
        $this->flash('success', 'Flavour deleted.');
        $this->redirect('/admin/flavours');
    }

    public function bannerUpdate(string $id): void
    {
        $data = ['title' => sanitize_string($this->request->post('title', '')), 'subtitle' => sanitize_string($this->request->post('subtitle', '')), 'link_url' => sanitize_string($this->request->post('link_url', '')), 'link_text' => sanitize_string($this->request->post('link_text', '')), 'position' => $this->request->post('position', 'hero'), 'sort_order' => (int)$this->request->post('sort_order', 0), 'is_active' => (int)$this->request->post('is_active', 1)];
        if (!empty($_FILES['image_desktop_file']['name'])) { $path = save_product_image($_FILES['image_desktop_file'], 0); if ($path) $data['image_desktop'] = $path; } elseif ($this->request->post('image_desktop')) { $data['image_desktop'] = sanitize_string($this->request->post('image_desktop')); }
        $this->db->update('banners', $data, 'id = ?', [(int)$id]);
        $this->flash('success', 'Banner updated.');
        $this->redirect('/admin/banners');
    }

    public function brandUpdate(string $id): void
    {
        $name = sanitize_string($this->request->post('name', ''));
        $data = ['name' => $name, 'slug' => slugify($name), 'country_of_origin' => sanitize_string($this->request->post('country', '')), 'description' => sanitize_string($this->request->post('description', '')), 'website_url' => sanitize_string($this->request->post('website_url', '')), 'is_featured' => (int)$this->request->post('is_featured', 0), 'is_active' => (int)$this->request->post('is_active', 1)];
        if (!empty($_FILES['logo_file']['name'])) { $path = save_product_image($_FILES['logo_file'], 0); if ($path) $data['logo'] = $path; } elseif ($this->request->post('logo')) { $data['logo'] = sanitize_string($this->request->post('logo')); }
        $this->db->update('brands', $data, 'id = ?', [(int)$id]);
        $this->flash('success', 'Brand updated.');
        $this->redirect('/admin/brands');
    }

    public function brandDelete(string $id): void
    {
        $this->db->update('brands', ['is_active' => 0], 'id = ?', [(int)$id]);
        $this->flash('success', 'Brand hidden.');
        $this->redirect('/admin/brands');
    }

    public function couponUpdate(string $id): void
    {
        $this->db->update('coupons', ['code' => strtoupper(sanitize_string($this->request->post('code', ''))), 'description' => sanitize_string($this->request->post('description', '')), 'type' => $this->request->post('type', 'percentage'), 'value' => (float)$this->request->post('value', 0), 'min_order_amount' => (float)$this->request->post('min_order_amount', 0), 'max_discount_amount' => $this->request->post('max_discount_amount') ?: null, 'usage_limit' => $this->request->post('usage_limit') ?: null, 'usage_per_user' => (int)$this->request->post('usage_per_user', 1), 'start_date' => $this->request->post('start_date') ?: null, 'end_date' => $this->request->post('end_date') ?: null, 'is_active' => (int)$this->request->post('is_active', 1)], 'id = ?', [(int)$id]);
        $this->flash('success', 'Coupon updated.');
        $this->redirect('/admin/coupons');
    }

    public function deliveryZones(): void
    {
        $zones    = (new DeliveryZone())->getAll();
        $settings = array_column($this->db->fetchAll('SELECT setting_key, setting_value FROM settings'), 'setting_value', 'setting_key');
        $this->render('admin/delivery-zones', ['title' => 'Delivery Zones — Admin', 'zones' => $zones, 'settings' => $settings, 'csrf' => $this->csrfToken()], 'admin');
    }

    public function deliveryZoneStore(): void
    {
        $this->db->insert('delivery_zones', [
            'zone_name'               => sanitize_string($this->request->post('zone_name', '')),
            'emirate'                 => sanitize_string($this->request->post('emirate', '')),
            'standard_delivery_fee'   => (float)$this->request->post('standard_fee', 10),
            'express_delivery_fee'    => (float)$this->request->post('express_fee', 25),
            'free_shipping_threshold' => (float)$this->request->post('free_threshold', 100),
            'standard_days'           => sanitize_string($this->request->post('standard_days', '1-2 Days')),
            'express_hours'           => sanitize_string($this->request->post('express_hours', '1 Hour')),
            'is_express_available'    => (int)$this->request->post('is_express', 0),
            'is_active'               => 1,
        ]);
        $this->flash('success', 'Delivery zone added.');
        $this->redirect('/admin/delivery-zones');
    }

    public function deliveryZoneUpdate(string $id): void
    {
        $this->db->update('delivery_zones', [
            'zone_name'               => sanitize_string($this->request->post('zone_name', '')),
            'emirate'                 => sanitize_string($this->request->post('emirate', '')),
            'standard_delivery_fee'   => (float)$this->request->post('standard_fee', 10),
            'express_delivery_fee'    => (float)$this->request->post('express_fee', 25),
            'free_shipping_threshold' => (float)$this->request->post('free_threshold', 100),
            'standard_days'           => sanitize_string($this->request->post('standard_days', '1-2 Days')),
            'express_hours'           => sanitize_string($this->request->post('express_hours', '1 Hour')),
            'is_express_available'    => (int)$this->request->post('is_express', 0),
        ], 'id = ?', [(int)$id]);
        $this->flash('success', 'Delivery zone updated.');
        $this->redirect('/admin/delivery-zones');
    }

    public function deliveryZoneDelete(string $id): void
    {
        $this->db->update('delivery_zones', ['is_active' => 0], 'id = ?', [(int)$id]);
        $this->flash('success', 'Delivery zone removed.');
        $this->redirect('/admin/delivery-zones');
    }

    private function uniqueSlug(string $slug, int $excludeId = 0): string
    {
        $original = $slug;
        $i = 1;
        while (true) {
            $row = $this->db->fetch(
                'SELECT id FROM products WHERE slug = ? AND id != ?',
                [$slug, $excludeId]
            );
            if (!$row) break;
            $slug = $original . '-' . $i++;
        }
        return $slug;
    }

    private function handleVariationsJson(int $productId): void
    {
        $json = trim($this->request->post('variations_json', ''));
        if (!$json) return;
        // Size guard (HIGH-08)
        if (strlen($json) > 512 * 1024) {
            error_log('[handleVariationsJson] Payload too large for product ' . $productId);
            return;
        }
        $payload = json_decode($json, true);
        if (!$payload || empty($payload['variation_types'])) return;

        // Reuse the same save logic from ApiController
        $types  = $payload['variation_types'];
        $combos = $payload['combinations'] ?? [];

        // Wipe any existing variation data
        $this->db->delete('product_variation_types', 'product_id = ?', [$productId]);

        $typeIdMap = [];
        foreach ($types as $order => $type) {
            $typeName = sanitize_string($type['type_name'] ?? '');
            if (!$typeName) continue;
            $tid = $this->db->insert('product_variation_types', [
                'product_id'    => $productId,
                'type_name'     => $typeName,
                'display_order' => (int)$order,
            ]);
            $optMap = [];
            foreach ($type['options'] as $oOrder => $opt) {
                $val = sanitize_string($opt['option_value'] ?? '');
                if (!$val) continue;
                $oid = $this->db->insert('product_variation_options', [
                    'variation_type_id' => $tid,
                    'product_id'        => $productId,
                    'option_value'      => $val,
                    'display_order'     => (int)$oOrder,
                ]);
                $optMap[$val] = $oid;
            }
            $typeIdMap[$order] = ['tid' => $tid, 'optMap' => $optMap];
        }

        foreach ($combos as $combo) {
            $options = $combo['options'] ?? [];
            $l1val = $options[0] ?? null;
            if ($l1val === null || !isset($typeIdMap[0]['optMap'][$l1val])) continue;
            $this->db->insert('product_variation_combinations', [
                'product_id'       => $productId,
                'option_id_level1' => $typeIdMap[0]['optMap'][$l1val],
                'option_id_level2' => isset($options[1], $typeIdMap[1]['optMap'][$options[1]]) ? $typeIdMap[1]['optMap'][$options[1]] : null,
                'option_id_level3' => isset($options[2], $typeIdMap[2]['optMap'][$options[2]]) ? $typeIdMap[2]['optMap'][$options[2]] : null,
                'option_id_level4' => isset($options[3], $typeIdMap[3]['optMap'][$options[3]]) ? $typeIdMap[3]['optMap'][$options[3]] : null,
                'option_id_level5' => isset($options[4], $typeIdMap[4]['optMap'][$options[4]]) ? $typeIdMap[4]['optMap'][$options[4]] : null,
                'price'            => (float)($combo['price'] ?? 0),
                'stock'            => (int)($combo['stock']   ?? 0),
                'sku'              => sanitize_string($combo['sku'] ?? '') ?: null,
                'is_active'        => 1,
            ]);
        }
    }

    private function handleVariantTypes(int $productId): void
    {
        $raw = $this->request->post('variant_types', []);
        if (!is_array($raw)) return;
        (new Product())->saveVariantTypes($productId, $raw);
    }

    private function handleProductFlavours(int $productId): void
    {
        try {
            $this->db->delete('product_flavours', 'product_id = ?', [$productId]);
            $flavourIds = $this->request->post('flavours', []);
            if (!empty($flavourIds) && is_array($flavourIds)) {
                foreach ($flavourIds as $fid) {
                    $this->db->insert('product_flavours', ['product_id' => $productId, 'flavour_id' => (int)$fid]);
                }
            }
        } catch (\Throwable $e) {
            // Non-fatal — flavours table may not exist yet
            error_log('[ProductFlavours] ' . $e->getMessage());
        }
    }

    private function buildProductData(): array
    {
        return [
            'category_id'       => (int)$this->request->post('category_id', 0),
            'brand_id'          => $this->request->post('brand_id') ?: null,
            'name'              => sanitize_string($this->request->post('name', '')),
            'slug'              => slugify($this->request->post('name', '')),
            'sku'               => strtoupper(sanitize_string($this->request->post('sku', ''))),
            'short_description' => sanitize_string($this->request->post('short_description', '')),
            'description'       => $this->request->post('description', ''),
            'price'             => (float)$this->request->post('price', 0),
            'compare_at_price'  => $this->request->post('compare_at_price') ?: null,
            'cost_price'        => $this->request->post('cost_price') ?: null,
            'stock_quantity'    => (int)$this->request->post('stock_quantity', 0),
            'low_stock_threshold'=> (int)$this->request->post('low_stock_threshold', 5),
            'product_type'      => $this->request->post('product_type', 'simple'),
            'status'            => $this->request->post('status', 'active'),
            'is_featured'       => (int)$this->request->post('is_featured', 0),
            'is_new_arrival'    => (int)$this->request->post('is_new_arrival', 0),
            'is_best_seller'    => (int)$this->request->post('is_best_seller', 0),
            'nicotine_content_mg'=> $this->request->post('nicotine_content_mg') ?: null,
            'puff_count'        => $this->request->post('puff_count') ?: null,
            'volume_ml'         => $this->request->post('volume_ml') ?: null,
            'flavor_profile'    => sanitize_string($this->request->post('flavor_profile', '')),
            'cigar_size'        => sanitize_string($this->request->post('cigar_size', '')),
            'cigar_strength'    => $this->request->post('cigar_strength') ?: null,
            'cigar_wrapper'     => sanitize_string($this->request->post('cigar_wrapper', '')),
            'cigar_origin'      => sanitize_string($this->request->post('cigar_origin', '')),
            'meta_title'        => sanitize_string($this->request->post('meta_title', '')),
            'meta_description'  => sanitize_string($this->request->post('meta_description', '')),
            'reward_points'     => (int)$this->request->post('reward_points', 0),
            'shisha_weight'     => $this->request->post('shisha_weight') ?: null,
            'hookah_height'     => sanitize_string($this->request->post('hookah_height', '')),
        ];
    }

    private function handleProductImages(int $productId): void
    {
        if (empty($_FILES['images']['name'][0])) return;

        // Ensure upload directory exists
        $uploadDir = PUBLIC_PATH . '/uploads/products/' . $productId . '/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            throw new \RuntimeException("Cannot create upload directory. Check folder permissions on the server.");
        }

        foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
            if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
            $file = [
                'name'     => $_FILES['images']['name'][$i],
                'tmp_name' => $tmp,
                'size'     => $_FILES['images']['size'][$i],
                'error'    => $_FILES['images']['error'][$i],
            ];
            $path = save_product_image($file, $productId);
            if ($path) {
                $isPrimary = ($i === 0 && !$this->db->fetch(
                    'SELECT id FROM product_images WHERE product_id = ? AND is_primary = 1',
                    [$productId]
                )) ? 1 : 0;
                $this->db->insert('product_images', [
                    'product_id' => $productId,
                    'image_path' => $path,
                    'alt_text'   => $this->request->post('name', ''),
                    'is_primary' => $isPrimary,
                    'position'   => $i,
                ]);
            }
        }
    }
}
