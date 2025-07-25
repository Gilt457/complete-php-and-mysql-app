<?php
/**
 * Home Controller Class
 * 
 * Handles the main homepage and landing page functionality
 * for the Alibaba Clone application.
 */

require_once 'BaseController.php';
require_once __DIR__ . '/../classes/Product.php';

class HomeController extends BaseController
{
    private $productModel;
    
    protected function init()
    {
        $this->productModel = new Product();
    }
    
    /**
     * Display homepage
     */
    public function index()
    {
        try {
            // Get featured products
            $featuredProducts = $this->productModel->getFeaturedProducts(8);
            
            // Get latest products
            $latestProducts = $this->productModel->getLatestProducts(8);
            
            // Get categories
            $categories = $this->productModel->getCategories(6);
            
            // Get top-selling products
            $topSellingProducts = $this->productModel->getTopSellingProducts(6);
            
            $this->render('home', [
                'featuredProducts' => $featuredProducts,
                'latestProducts' => $latestProducts,
                'categories' => $categories,
                'topSellingProducts' => $topSellingProducts,
                'pageTitle' => 'Welcome to Alibaba Clone - Your Premier E-commerce Destination',
                'metaDescription' => 'Discover millions of products at competitive prices. Shop electronics, fashion, home goods and more at Alibaba Clone.'
            ]);
            
        } catch (Exception $e) {
            error_log("Homepage error: " . $e->getMessage());
            $this->handle500("Error loading homepage");
        }
    }
}
