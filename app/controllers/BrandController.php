<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Brand;
use App\Models\Product;

class BrandController extends Controller
{
    public function index(): void
    {
        $this->requireAgeVerified();
        $brands = (new Brand())->getAll();
        $this->render('shop/brands', ['title' => "All Brands — Phantom Smoking", 'brands' => $brands]);
    }

    public function show(string $slug): void
    {
        $this->requireAgeVerified();
        $brand = (new Brand())->getBySlug($slug);
        if (!$brand) { http_response_code(404); $this->render('errors/404', ['title' => '404']); return; }

        $page = max(1, (int)$this->request->get('page', 1));
        $productModel = new Product();
        $products = $this->db->fetchAll(
            'SELECT p.*, pi.image_path AS primary_image FROM products p
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE p.brand_id = ? AND p.status = "active" ORDER BY p.total_sold DESC LIMIT 24',
            [$brand['id']]
        );
        $this->render('shop/brand', [
            'title'   => ($brand['meta_title'] ?: "Buy {$brand['name']} Products Online UAE | Phantom Smoking"),
            'brand'   => $brand,
            'products'=> $products,
        ]);
    }
}
