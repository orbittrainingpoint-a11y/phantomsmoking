<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;

class CategoryController extends Controller
{
    public function show(string $category): void
    {
        $this->requireAgeVerified();
        $catModel = new Category();
        $cat = $catModel->getBySlug($category);
        if (!$cat) { http_response_code(404); $this->render('errors/404', ['title' => '404']); return; }

        $filters = [
            'brand_id'      => $this->request->get('brand'),
            'min_price'     => $this->request->get('min_price'),
            'max_price'     => $this->request->get('max_price'),
            'in_stock'      => $this->request->get('in_stock'),
            'on_sale'       => $this->request->get('on_sale'),
            'new_arrival'   => $this->request->get('new_arrival'),
            'nicotine'      => $this->request->get('nicotine'),
            'cigar_strength'=> $this->request->get('cigar_strength'),
            'rating'        => $this->request->get('rating'),
        ];
        $sort = $this->request->get('sort', 'featured');
        $page = max(1, (int)$this->request->get('page', 1));

        $productModel = new Product();
        $result = $productModel->getProductsByCategory($cat['id'], array_filter($filters), $sort, $page);
        $brands = $this->db->fetchAll('SELECT DISTINCT b.id, b.name FROM brands b JOIN products p ON p.brand_id = b.id WHERE p.category_id = ? AND p.status = "active"', [$cat['id']]);

        $this->render('shop/category', [
            'title'    => ($cat['meta_title'] ?: "Buy {$cat['name']} Online UAE | Phantom Smoking"),
            'category' => $cat,
            'products' => $result,
            'brands'   => $brands,
            'filters'  => $filters,
            'sort'     => $sort,
        ]);
    }

    public function deals(): void
    {
        $this->requireAgeVerified();
        $productModel = new Product();
        $page = max(1, (int)$this->request->get('page', 1));
        $result = $productModel->getOnSaleProducts(24);
        $this->render('shop/deals', ['title' => "Deals & Offers — Phantom Smoking", 'products' => $result]);
    }
}
