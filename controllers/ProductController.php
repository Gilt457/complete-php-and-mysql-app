<?php
/**
 * Product Controller Class
 * 
 * Handles all product-related operations for the Alibaba Clone application.
 * This controller manages product listings, details, search, categories,
 * and administrative functions.
 * 
 * Features:
 * - Product catalog display
 * - Product search and filtering
 * - Category management
 * - Product details
 * - Admin product management
 * - Product reviews and ratings
 */

require_once 'BaseController.php';
require_once __DIR__ . '/../classes/Product.php';

class ProductController extends BaseController
{
    private $productModel;
    
    /**
     * Initialize the controller
     */
    protected function init()
    {
        $this->productModel = new Product();
    }
    
    /**
     * Display product catalog/listing
     * Route: /products
     */
    public function index()
    {
        try {
            $page = $_GET['page'] ?? 1;
            $category = $_GET['category'] ?? null;
            $search = $_GET['search'] ?? null;
            $sortBy = $_GET['sort'] ?? 'name';
            $order = $_GET['order'] ?? 'ASC';
            $limit = 20;
            
            // Get products with pagination
            $products = $this->productModel->getProducts([
                'page' => $page,
                'limit' => $limit,
                'category' => $category,
                'search' => $search,
                'sort_by' => $sortBy,
                'order' => $order
            ]);
            
            // Get categories for filter
            $categories = $this->productModel->getCategories();
            
            // Get total count for pagination
            $totalProducts = $this->productModel->getTotalProducts([
                'category' => $category,
                'search' => $search
            ]);
            
            $totalPages = ceil($totalProducts / $limit);
            
            $this->render('products/index', [
                'products' => $products,
                'categories' => $categories,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'currentCategory' => $category,
                'currentSearch' => $search,
                'currentSort' => $sortBy,
                'currentOrder' => $order,
                'pageTitle' => 'Products - Alibaba Clone'
            ]);
            
        } catch (Exception $e) {
            error_log("Product listing error: " . $e->getMessage());
            $this->handle500("Error loading products");
        }
    }
    
    /**
     * Display product details
     * Route: /product/{id}
     */
    public function show($id)
    {
        try {
            $product = $this->productModel->getProductById($id);
            
            if (!$product) {
                $this->handle404();
                return;
            }
            
            // Get related products
            $relatedProducts = $this->productModel->getRelatedProducts($id, $product['category_id'], 4);
            
            // Get product reviews
            $reviews = $this->productModel->getProductReviews($id);
            
            // Get product images
            $images = $this->productModel->getProductImages($id);
            
            // Update view count
            $this->productModel->incrementViewCount($id);
            
            $this->render('products/show', [
                'product' => $product,
                'relatedProducts' => $relatedProducts,
                'reviews' => $reviews,
                'images' => $images,
                'pageTitle' => $product['name'] . ' - Alibaba Clone'
            ]);
            
        } catch (Exception $e) {
            error_log("Product detail error: " . $e->getMessage());
            $this->handle500("Error loading product details");
        }
    }
    
    /**
     * Search products (AJAX endpoint)
     * Route: /products/search
     */
    public function search()
    {
        if (!$this->isAjax()) {
            $this->redirect('/products');
            return;
        }
        
        try {
            $query = $_GET['q'] ?? '';
            $category = $_GET['category'] ?? null;
            $minPrice = $_GET['min_price'] ?? null;
            $maxPrice = $_GET['max_price'] ?? null;
            $limit = $_GET['limit'] ?? 10;
            
            $results = $this->productModel->searchProducts([
                'query' => $query,
                'category' => $category,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'limit' => $limit
            ]);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $results,
                'total' => count($results)
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Search failed'
            ], 500);
        }
    }
    
    /**
     * Display products by category
     * Route: /category/{id}
     */
    public function category($categoryId)
    {
        try {
            $category = $this->productModel->getCategoryById($categoryId);
            
            if (!$category) {
                $this->handle404();
                return;
            }
            
            $page = $_GET['page'] ?? 1;
            $sortBy = $_GET['sort'] ?? 'name';
            $order = $_GET['order'] ?? 'ASC';
            $limit = 20;
            
            $products = $this->productModel->getProductsByCategory($categoryId, [
                'page' => $page,
                'limit' => $limit,
                'sort_by' => $sortBy,
                'order' => $order
            ]);
            
            $totalProducts = $this->productModel->getTotalProductsByCategory($categoryId);
            $totalPages = ceil($totalProducts / $limit);
            
            $this->render('products/category', [
                'category' => $category,
                'products' => $products,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'currentSort' => $sortBy,
                'currentOrder' => $order,
                'pageTitle' => $category['name'] . ' - Alibaba Clone'
            ]);
            
        } catch (Exception $e) {
            error_log("Category products error: " . $e->getMessage());
            $this->handle500("Error loading category products");
        }
    }
    
    // ======================
    // ADMIN METHODS
    // ======================
    
    /**
     * Admin: Display all products for management
     * Route: /admin/products
     */
    public function adminIndex()
    {
        $this->requireAdmin();
        
        try {
            $page = $_GET['page'] ?? 1;
            $search = $_GET['search'] ?? null;
            $status = $_GET['status'] ?? null;
            $limit = 20;
            
            $products = $this->productModel->getAllProductsForAdmin([
                'page' => $page,
                'limit' => $limit,
                'search' => $search,
                'status' => $status
            ]);
            
            $totalProducts = $this->productModel->getTotalProductsForAdmin([
                'search' => $search,
                'status' => $status
            ]);
            
            $totalPages = ceil($totalProducts / $limit);
            
            $this->render('admin/products/index', [
                'products' => $products,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'currentSearch' => $search,
                'currentStatus' => $status,
                'pageTitle' => 'Manage Products - Admin'
            ], 'admin');
            
        } catch (Exception $e) {
            error_log("Admin products error: " . $e->getMessage());
            $this->handle500("Error loading admin products");
        }
    }
    
    /**
     * Admin: Show create product form
     * Route: /admin/products/create
     */
    public function adminCreate()
    {
        $this->requireAdmin();
        
        if ($this->getRequestMethod() === 'POST') {
            $this->adminStore();
            return;
        }
        
        $categories = $this->productModel->getCategories();
        
        $this->render('admin/products/create', [
            'categories' => $categories,
            'pageTitle' => 'Create Product - Admin'
        ], 'admin');
    }
    
    /**
     * Admin: Store new product
     */
    private function adminStore()
    {
        $this->requireAdmin();
        
        try {
            $data = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'short_description' => $_POST['short_description'] ?? '',
                'price' => $_POST['price'] ?? 0,
                'cost_price' => $_POST['cost_price'] ?? 0,
                'sale_price' => $_POST['sale_price'] ?? null,
                'category_id' => $_POST['category_id'] ?? null,
                'sku' => $_POST['sku'] ?? '',
                'stock_quantity' => $_POST['stock_quantity'] ?? 0,
                'weight' => $_POST['weight'] ?? null,
                'dimensions' => $_POST['dimensions'] ?? null,
                'status' => $_POST['status'] ?? 'active',
                'featured' => isset($_POST['featured']) ? 1 : 0,
                'tags' => $_POST['tags'] ?? '',
                'meta_title' => $_POST['meta_title'] ?? '',
                'meta_description' => $_POST['meta_description'] ?? ''
            ];
            
            // Validate data
            $validator = new Validator();
            $rules = [
                'name' => 'required|min:3|max:255',
                'description' => 'required|min:10',
                'price' => 'required|numeric|min:0',
                'category_id' => 'required|numeric',
                'sku' => 'required|unique:products,sku',
                'stock_quantity' => 'required|numeric|min:0'
            ];
            
            if (!$validator->validate($data, $rules)) {
                $this->setFlashMessage('error', 'Please fix the validation errors');
                $this->redirect('/admin/products/create');
                return;
            }
            
            $productId = $this->productModel->createProduct($data);
            
            // Handle image uploads
            if (isset($_FILES['images']) && $_FILES['images']['error'][0] !== UPLOAD_ERR_NO_FILE) {
                $this->productModel->uploadProductImages($productId, $_FILES['images']);
            }
            
            $this->setFlashMessage('success', 'Product created successfully');
            $this->redirect('/admin/products');
            
        } catch (Exception $e) {
            error_log("Product creation error: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error creating product');
            $this->redirect('/admin/products/create');
        }
    }
    
    /**
     * Admin: Show edit product form
     * Route: /admin/products/{id}/edit
     */
    public function adminEdit($id)
    {
        $this->requireAdmin();
        
        if ($this->getRequestMethod() === 'POST') {
            $this->adminUpdate($id);
            return;
        }
        
        try {
            $product = $this->productModel->getProductById($id);
            
            if (!$product) {
                $this->handle404();
                return;
            }
            
            $categories = $this->productModel->getCategories();
            $images = $this->productModel->getProductImages($id);
            
            $this->render('admin/products/edit', [
                'product' => $product,
                'categories' => $categories,
                'images' => $images,
                'pageTitle' => 'Edit Product - Admin'
            ], 'admin');
            
        } catch (Exception $e) {
            error_log("Product edit error: " . $e->getMessage());
            $this->handle500("Error loading product for editing");
        }
    }
    
    /**
     * Admin: Update product
     */
    private function adminUpdate($id)
    {
        $this->requireAdmin();
        
        try {
            $data = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'short_description' => $_POST['short_description'] ?? '',
                'price' => $_POST['price'] ?? 0,
                'cost_price' => $_POST['cost_price'] ?? 0,
                'sale_price' => $_POST['sale_price'] ?? null,
                'category_id' => $_POST['category_id'] ?? null,
                'sku' => $_POST['sku'] ?? '',
                'stock_quantity' => $_POST['stock_quantity'] ?? 0,
                'weight' => $_POST['weight'] ?? null,
                'dimensions' => $_POST['dimensions'] ?? null,
                'status' => $_POST['status'] ?? 'active',
                'featured' => isset($_POST['featured']) ? 1 : 0,
                'tags' => $_POST['tags'] ?? '',
                'meta_title' => $_POST['meta_title'] ?? '',
                'meta_description' => $_POST['meta_description'] ?? ''
            ];
            
            $result = $this->productModel->updateProduct($id, $data);
            
            if ($result) {
                // Handle new image uploads
                if (isset($_FILES['images']) && $_FILES['images']['error'][0] !== UPLOAD_ERR_NO_FILE) {
                    $this->productModel->uploadProductImages($id, $_FILES['images']);
                }
                
                $this->setFlashMessage('success', 'Product updated successfully');
            } else {
                $this->setFlashMessage('error', 'Error updating product');
            }
            
            $this->redirect('/admin/products');
            
        } catch (Exception $e) {
            error_log("Product update error: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error updating product');
            $this->redirect('/admin/products/' . $id . '/edit');
        }
    }
    
    /**
     * Admin: Delete product
     * Route: /admin/products/{id}/delete
     */
    public function adminDelete($id)
    {
        $this->requireAdmin();
        
        try {
            $result = $this->productModel->deleteProduct($id);
            
            if ($this->isAjax()) {
                $this->jsonResponse([
                    'success' => $result,
                    'message' => $result ? 'Product deleted successfully' : 'Error deleting product'
                ]);
            } else {
                $this->setFlashMessage(
                    $result ? 'success' : 'error',
                    $result ? 'Product deleted successfully' : 'Error deleting product'
                );
                $this->redirect('/admin/products');
            }
            
        } catch (Exception $e) {
            error_log("Product deletion error: " . $e->getMessage());
            
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Error deleting product'], 500);
            } else {
                $this->setFlashMessage('error', 'Error deleting product');
                $this->redirect('/admin/products');
            }
        }
    }
}
