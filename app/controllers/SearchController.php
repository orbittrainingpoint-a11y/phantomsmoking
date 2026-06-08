<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

class SearchController extends Controller
{
    public function index(): void
    {
        $this->requireAgeVerified();

        $q          = sanitize_string($this->request->get('q', ''));
        $page       = max(1, (int)$this->request->get('page', 1));
        $newArrival = (int)$this->request->get('new_arrival', 0);
        $onSale     = (int)$this->request->get('on_sale', 0);
        $bestSeller = (int)$this->request->get('best_seller', 0);
        $featured   = (int)$this->request->get('featured', 0);
        $sort       = $this->request->get('sort', 'newest');

        $productModel = new Product();

        // Filter-based browsing (no search query needed)
        if ($newArrival || $onSale || $bestSeller || $featured) {
            $result = $productModel->getFilteredProducts([
                'new_arrival' => $newArrival,
                'on_sale'     => $onSale,
                'best_seller' => $bestSeller,
                'featured'    => $featured,
            ], $sort, $page);

            $pageTitle = match(true) {
                (bool)$newArrival => 'New Arrivals',
                (bool)$onSale     => 'On Sale',
                (bool)$bestSeller => 'Best Sellers',
                (bool)$featured   => 'Featured Products',
                default           => 'Products',
            };

            $this->render('shop/search', [
                'title'    => $pageTitle . ' — Phantom Smoking',
                'query'    => '',
                'heading'  => $pageTitle,
                'products' => $result,
            ]);
            return;
        }

        // Regular search
        $result = ['items' => [], 'total' => 0, 'total_pages' => 0, 'current_page' => 1];
        if (strlen($q) >= 2) {
            $result = $productModel->searchProducts($q, [], $page);
        }

        $this->render('shop/search', [
            'title'    => $q ? "Search: \"{$q}\" — Phantom Smoking" : 'Search — Phantom Smoking',
            'query'    => $q,
            'heading'  => $q ? "Results for \"{$q}\"" : 'Search Products',
            'products' => $result,
        ]);
    }
}
