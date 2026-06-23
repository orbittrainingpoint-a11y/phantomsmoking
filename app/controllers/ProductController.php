<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Product;
use App\Models\Review;
use App\Models\Wishlist;

class ProductController extends Controller
{
    public function show(string $slug): void
    {
        $this->requireAgeVerified();
        $productModel = new Product();
        $product = $productModel->getProductBySlug($slug);
        if (!$product) { http_response_code(404); $this->render('errors/404', ['title' => '404']); return; }

        $reviewModel = new Review();
        $reviews     = $reviewModel->getProductReviews($product['id']);
        $ratingDist  = $reviewModel->getRatingDistribution($product['id']);
        $related     = $productModel->getRelatedProducts($product['id'], 6);

        $inWishlist = false;
        if (Auth::check()) {
            $inWishlist = (new Wishlist())->isInWishlist((int)Auth::id(), $product['id']);
        }

        // Recently viewed (cookie-based)
        $recentlyViewed = [];
        if (!empty($_COOKIE['recently_viewed'])) {
            $ids = array_filter(array_map('intval', explode(',', $_COOKIE['recently_viewed'])));
            $ids = array_diff($ids, [$product['id']]);
            $ids = array_slice($ids, 0, 7);
            if ($ids) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $recentlyViewed = $this->db->fetchAll(
                    "SELECT p.*, pi.image_path AS primary_image FROM products p LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1 WHERE p.id IN ($placeholders)",
                    $ids
                );
            }
        }
        $newIds = array_merge([$product['id']], array_column($recentlyViewed, 'id'));
        setcookie('recently_viewed', implode(',', array_slice($newIds, 0, 8)), time() + 86400 * 30, '/');

        $this->render('product/detail', [
            'title'          => ($product['meta_title'] ?: "{$product['name']} | Buy Online UAE | Phantom Smoking"),
            'product'        => $product,
            'reviews'        => $reviews,
            'rating_dist'    => $ratingDist,
            'related'        => $related,
            'recently_viewed'=> $recentlyViewed,
            'in_wishlist'    => $inWishlist,
            'csrf'           => $this->csrfToken(),
        ]);
    }
}
