<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Banner;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->requireAgeVerified();
        $product  = new Product();
        $category = new Category();
        $brand    = new Brand();
        $banner   = new Banner();

        $this->render('home/index', [
            'title'        => 'Phantom Smoking — Premium Tobacco, Vape & Shisha Dubai',
            'banners'      => $banner->getActive('hero'),
            'featured'     => $product->getFeaturedProducts(8),
            'new_arrivals' => $product->getNewArrivals(8),
            'best_sellers' => $product->getBestSellers(8),
            'on_sale'      => $product->getOnSaleProducts(8),
            'brands'       => $brand->getFeatured(12),
            'categories'   => $category->getMenuCategories(),
        ]);
    }

    public function about(): void
    {
        $this->requireAgeVerified();
        $this->render('home/about', ['title' => 'About Us — Phantom Smoking']);
    }

    public function contact(): void
    {
        $this->requireAgeVerified();
        $this->render('home/contact', ['title' => 'Contact Us — Phantom Smoking']);
    }

    public function contactSubmit(): void
    {
        $name    = sanitize_string($this->request->post('name', ''));
        $email   = $this->request->post('email', '');
        $subject = sanitize_string($this->request->post('subject', 'General Inquiry'));
        $message = sanitize_string($this->request->post('message', ''));
        if ($name && validate_email($email) && $message) {
            send_email(
                'info@phantomsmoking.com',
                "Contact Form: {$subject} — {$name}",
                "<p><b>From:</b> {$name} ({$email})</p><p><b>Subject:</b> {$subject}</p><p>" . nl2br($message) . "</p>"
            );
            $this->flash('success', 'Message sent! We will get back to you within 24 hours.');
        } else {
            $this->flash('error', 'Please fill all required fields correctly.');
        }
        $this->redirect('/contact');
    }

    public function faq(): void
    {
        $this->requireAgeVerified();
        $this->render('home/faq', ['title' => 'FAQ — Phantom Smoking']);
    }

    public function shippingPolicy(): void
    {
        $this->render('home/shipping-policy', ['title' => 'Shipping Policy — Phantom Smoking']);
    }

    public function returnsPolicy(): void
    {
        $this->render('home/returns-policy', ['title' => 'Returns Policy — Phantom Smoking']);
    }

    public function privacyPolicy(): void
    {
        $this->render('home/privacy-policy', ['title' => 'Privacy Policy — Phantom Smoking']);
    }

    public function terms(): void
    {
        $this->render('home/terms', ['title' => 'Terms & Conditions — Phantom Smoking']);
    }
}
