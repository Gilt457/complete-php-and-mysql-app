<?php
/**
 * Product Model Class
 * 
 * This class handles all product-related database operations and business logic.
 * It provides methods for product management, categories, inventory, and search.
 * 
 * Features:
 * - Product CRUD operations
 * - Category management
 * - Inventory tracking
 * - Product search and filtering
 * - Image management
 * - Price calculations
 */

require_once 'Database.php';
require_once 'Validator.php';
require_once __DIR__ . '/../config/constants.php';

class Product
{
    private $db;
    private $validator;
    
    // Product properties
    private $id;
    private $name;
    private $description;
    private $price;
    private $category;
    private $stock;
    private $image;
    private $status;
    private $createdAt;
    private $updatedAt;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->validator = new Validator();
    }
    
    /**
     * Create a new product
     * 
     * @param array $productData Product data
     * @return array Result with success status and message
     */
    public function create($productData)
    {
        try {
            // Validate input data
            $validation = $this->validateProductData($productData);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validation['errors']
                ];
            }
            
            // Prepare product data for insertion
            $insertData = [
                'name' => $productData['name'],
                'description' => $productData['description'],
                'price' => $productData['price'],
                'category_id' => $productData['categoryId'],
                'stock_quantity' => $productData['stock'] ?? 0,
                'sku' => $productData['sku'] ?? $this->generateSKU(),
                'status' => $productData['status'] ?? STATUS_ACTIVE,
                'created_at' => date(DATETIME_FORMAT),
                'updated_at' => date(DATETIME_FORMAT)
            ];
            
            // Handle image upload if provided
            if (isset($productData['image']) && !empty($productData['image']['name'])) {
                $imageResult = $this->handleImageUpload($productData['image']);
                if ($imageResult['success']) {
                    $insertData['image'] = $imageResult['filename'];
                } else {
                    return $imageResult;
                }
            }
            
            // Insert product into database
            $productId = $this->db->insert('products', $insertData);
            
            if ($productId) {
                return [
                    'success' => true,
                    'message' => 'Product created successfully',
                    'product_id' => $productId
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create product'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Product creation error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Product creation failed due to server error'
            ];
        }
    }
    
    /**
     * Get product by ID
     * 
     * @param int $productId Product ID
     * @return array|false Product data or false if not found
     */
    public function getById($productId)
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = :id";
        
        return $this->db->fetch($sql, ['id' => $productId]);
    }
    
    /**
     * Get all products with pagination
     * 
     * @param int $page Page number
     * @param int $limit Items per page
     * @param array $filters Search filters
     * @return array Products list with pagination info
     */
    public function getAll($page = 1, $limit = DEFAULT_ITEMS_PER_PAGE, $filters = [])
    {
        $offset = ($page - 1) * $limit;
        $whereClause = "1=1";
        $params = [];
        
        // Apply filters
        if (!empty($filters['category'])) {
            $whereClause .= " AND p.category_id = :category";
            $params['category'] = $filters['category'];
        }
        
        if (!empty($filters['search'])) {
            $whereClause .= " AND (p.name LIKE :search OR p.description LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($filters['status'])) {
            $whereClause .= " AND p.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['min_price'])) {
            $whereClause .= " AND p.price >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $whereClause .= " AND p.price <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }
        
        // Order by clause
        $orderBy = "p.created_at DESC";
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_asc':
                    $orderBy = "p.price ASC";
                    break;
                case 'price_desc':
                    $orderBy = "p.price DESC";
                    break;
                case 'name_asc':
                    $orderBy = "p.name ASC";
                    break;
                case 'name_desc':
                    $orderBy = "p.name DESC";
                    break;
            }
        }
        
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE {$whereClause} 
                ORDER BY {$orderBy}
                LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        $products = $this->db->fetchAll($sql, $params);
        
        // Get total count
        $totalSql = "SELECT COUNT(*) FROM products p WHERE {$whereClause}";
        unset($params['limit'], $params['offset']);
        $total = $this->db->fetchColumn($totalSql, $params);
        
        return [
            'products' => $products,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Update product
     * 
     * @param int $productId Product ID
     * @param array $productData Updated product data
     * @return array Update result
     */
    public function update($productId, $productData)
    {
        try {
            // Validate update data
            $validation = $this->validateProductData($productData, true);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validation['errors']
                ];
            }
            
            // Prepare update data
            $updateData = [
                'name' => $productData['name'],
                'description' => $productData['description'],
                'price' => $productData['price'],
                'category_id' => $productData['categoryId'],
                'stock_quantity' => $productData['stock'],
                'status' => $productData['status'],
                'updated_at' => date(DATETIME_FORMAT)
            ];
            
            // Handle image upload if provided
            if (isset($productData['image']) && !empty($productData['image']['name'])) {
                $imageResult = $this->handleImageUpload($productData['image']);
                if ($imageResult['success']) {
                    // Delete old image
                    $oldProduct = $this->getById($productId);
                    if ($oldProduct && !empty($oldProduct['image'])) {
                        $this->deleteImage($oldProduct['image']);
                    }
                    $updateData['image'] = $imageResult['filename'];
                } else {
                    return $imageResult;
                }
            }
            
            // Update product
            $affected = $this->db->update('products', $updateData, 'id = :id', ['id' => $productId]);
            
            if ($affected > 0) {
                return [
                    'success' => true,
                    'message' => 'Product updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No changes made or product not found'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Product update error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Product update failed due to server error'
            ];
        }
    }
    
    /**
     * Delete product
     * 
     * @param int $productId Product ID
     * @return array Delete result
     */
    public function delete($productId)
    {
        try {
            // Get product data to delete associated image
            $product = $this->getById($productId);
            
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Product not found'
                ];
            }
            
            // Delete product from database
            $affected = $this->db->delete('products', 'id = :id', ['id' => $productId]);
            
            if ($affected > 0) {
                // Delete associated image
                if (!empty($product['image'])) {
                    $this->deleteImage($product['image']);
                }
                
                return [
                    'success' => true,
                    'message' => 'Product deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete product'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Product deletion error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Product deletion failed due to server error'
            ];
        }
    }
    
    /**
     * Update product stock
     * 
     * @param int $productId Product ID
     * @param int $quantity New quantity
     * @return array Update result
     */
    public function updateStock($productId, $quantity)
    {
        try {
            $updateData = [
                'stock_quantity' => $quantity,
                'updated_at' => date(DATETIME_FORMAT)
            ];
            
            $affected = $this->db->update('products', $updateData, 'id = :id', ['id' => $productId]);
            
            if ($affected > 0) {
                return [
                    'success' => true,
                    'message' => 'Stock updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Product not found'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Stock update error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Stock update failed due to server error'
            ];
        }
    }
    
    /**
     * Get products by category
     * 
     * @param int $categoryId Category ID
     * @param int $limit Number of products to return
     * @return array Products list
     */
    public function getByCategory($categoryId, $limit = 10)
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id = :category_id AND p.status = :status
                ORDER BY p.created_at DESC 
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, [
            'category_id' => $categoryId,
            'status' => STATUS_ACTIVE,
            'limit' => $limit
        ]);
    }
    
    /**
     * Search products
     * 
     * @param string $query Search query
     * @param int $limit Number of results
     * @return array Search results
     */
    public function search($query, $limit = 20)
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE (p.name LIKE :query OR p.description LIKE :query OR p.sku LIKE :query)
                AND p.status = :status
                ORDER BY p.name ASC 
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, [
            'query' => '%' . $query . '%',
            'status' => STATUS_ACTIVE,
            'limit' => $limit
        ]);
    }
    
    /**
     * Get featured products
     * 
     * @param int $limit Number of products to return
     * @return array Featured products
     */
    public function getFeatured($limit = 6)
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.featured = 1 AND p.status = :status
                ORDER BY p.created_at DESC 
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, [
            'status' => STATUS_ACTIVE,
            'limit' => $limit
        ]);
    }
    
    /**
     * Get low stock products
     * 
     * @param int $threshold Stock threshold
     * @return array Low stock products
     */
    public function getLowStock($threshold = 10)
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.stock_quantity <= :threshold AND p.status = :status
                ORDER BY p.stock_quantity ASC";
        
        return $this->db->fetchAll($sql, [
            'threshold' => $threshold,
            'status' => STATUS_ACTIVE
        ]);
    }
    
    /**
     * Handle product image upload
     * 
     * @param array $imageFile Uploaded file data
     * @return array Upload result
     */
    private function handleImageUpload($imageFile)
    {
        if (!$this->validator->validateFileUpload($imageFile, IMAGE_TYPES)) {
            return [
                'success' => false,
                'message' => 'Invalid image file',
                'errors' => $this->validator->getErrors()
            ];
        }
        
        $uploadDir = PUBLIC_PATH . '/images/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = strtolower(pathinfo($imageFile['name'], PATHINFO_EXTENSION));
        $filename = uniqid('product_') . '.' . $fileExtension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($imageFile['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'filename' => $filename
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to upload image'
            ];
        }
    }
    
    /**
     * Delete product image
     * 
     * @param string $filename Image filename
     */
    private function deleteImage($filename)
    {
        $filepath = PUBLIC_PATH . '/images/products/' . $filename;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
    
    /**
     * Generate unique SKU
     * 
     * @return string Generated SKU
     */
    private function generateSKU()
    {
        return 'PRD-' . strtoupper(uniqid());
    }
    
    /**
     * Validate product data
     * 
     * @param array $data Product data
     * @param bool $isUpdate Whether this is an update operation
     * @return array Validation result
     */
    private function validateProductData($data, $isUpdate = false)
    {
        $errors = [];
        
        if (!$this->validator->validateStringLength($data['name'] ?? '', 1, 255)) {
            $errors[] = 'Product name is required and must be less than 255 characters';
        }
        
        if (!$this->validator->validateStringLength($data['description'] ?? '', 0, 2000)) {
            $errors[] = 'Description must be less than 2000 characters';
        }
        
        if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] < 0) {
            $errors[] = 'Valid price is required';
        }
        
        if (!isset($data['categoryId']) || !is_numeric($data['categoryId'])) {
            $errors[] = 'Valid category is required';
        }
        
        if (isset($data['stock']) && (!is_numeric($data['stock']) || $data['stock'] < 0)) {
            $errors[] = 'Stock quantity must be a non-negative number';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
?>
