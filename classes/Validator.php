<?php
/**
 * Validator Class
 * 
 * This class provides comprehensive input validation methods for the application.
 * It includes validation for various data types and formats with security considerations.
 * 
 * Features:
 * - Email validation
 * - Password strength validation
 * - Username validation
 * - Name validation
 * - Phone number validation
 * - File upload validation
 * - URL validation
 * - Custom validation rules
 */

require_once __DIR__ . '/../config/constants.php';

class Validator
{
    private $errors = [];
    
    /**
     * Validate email address
     * 
     * @param string $email Email to validate
     * @return bool
     */
    public function validateEmail($email)
    {
        if (empty($email)) {
            $this->addError('Email is required');
            return false;
        }
        
        if (strlen($email) > MAX_EMAIL_LENGTH) {
            $this->addError('Email is too long');
            return false;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addError('Invalid email format');
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate password strength
     * 
     * @param string $password Password to validate
     * @return bool
     */
    public function validatePassword($password)
    {
        if (empty($password)) {
            $this->addError('Password is required');
            return false;
        }
        
        if (strlen($password) < MIN_PASSWORD_LENGTH) {
            $this->addError('Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters long');
            return false;
        }
        
        if (strlen($password) > MAX_PASSWORD_LENGTH) {
            $this->addError('Password is too long');
            return false;
        }
        
        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $this->addError('Password must contain at least one uppercase letter');
            return false;
        }
        
        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            $this->addError('Password must contain at least one lowercase letter');
            return false;
        }
        
        // Check for at least one digit
        if (!preg_match('/[0-9]/', $password)) {
            $this->addError('Password must contain at least one number');
            return false;
        }
        
        // Check for at least one special character
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $this->addError('Password must contain at least one special character');
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate username
     * 
     * @param string $username Username to validate
     * @return bool
     */
    public function validateUsername($username)
    {
        if (empty($username)) {
            $this->addError('Username is required');
            return false;
        }
        
        if (strlen($username) < MIN_USERNAME_LENGTH) {
            $this->addError('Username must be at least ' . MIN_USERNAME_LENGTH . ' characters long');
            return false;
        }
        
        if (strlen($username) > MAX_USERNAME_LENGTH) {
            $this->addError('Username is too long');
            return false;
        }
        
        // Username should contain only letters, numbers, and underscores
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $this->addError('Username can only contain letters, numbers, and underscores');
            return false;
        }
        
        // Username should not start with a number
        if (preg_match('/^[0-9]/', $username)) {
            $this->addError('Username cannot start with a number');
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate name (first name, last name)
     * 
     * @param string $name Name to validate
     * @return bool
     */
    public function validateName($name)
    {
        if (empty($name)) {
            $this->addError('Name is required');
            return false;
        }
        
        if (strlen($name) > MAX_NAME_LENGTH) {
            $this->addError('Name is too long');
            return false;
        }
        
        // Name should contain only letters, spaces, and common punctuation
        if (!preg_match('/^[a-zA-Z\s\-\'\.]+$/', $name)) {
            $this->addError('Name contains invalid characters');
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate phone number
     * 
     * @param string $phone Phone number to validate
     * @return bool
     */
    public function validatePhone($phone)
    {
        if (empty($phone)) {
            return true; // Phone is optional
        }
        
        // Remove all non-digit characters for validation
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if phone number has valid length (10-15 digits)
        if (strlen($cleanPhone) < 10 || strlen($cleanPhone) > 15) {
            $this->addError('Phone number must be between 10 and 15 digits');
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate URL
     * 
     * @param string $url URL to validate
     * @return bool
     */
    public function validateUrl($url)
    {
        if (empty($url)) {
            return true; // URL is optional
        }
        
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->addError('Invalid URL format');
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate file upload
     * 
     * @param array $file $_FILES array element
     * @param array $allowedTypes Allowed file extensions
     * @param int $maxSize Maximum file size in bytes
     * @return bool
     */
    public function validateFileUpload($file, $allowedTypes = null, $maxSize = null)
    {
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return true; // No file uploaded (optional)
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->addError('File upload error: ' . $this->getUploadErrorMessage($file['error']));
            return false;
        }
        
        // Check file size
        $maxSize = $maxSize ?? MAX_FILE_SIZE;
        if ($file['size'] > $maxSize) {
            $this->addError('File size exceeds maximum allowed size');
            return false;
        }
        
        // Check file extension
        $allowedTypes = $allowedTypes ?? ALLOWED_EXTENSIONS;
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedTypes)) {
            $this->addError('File type not allowed. Allowed types: ' . implode(', ', $allowedTypes));
            return false;
        }
        
        // Check MIME type for additional security
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, ALLOWED_MIME_TYPES)) {
            $this->addError('Invalid file type');
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate date format
     * 
     * @param string $date Date string
     * @param string $format Expected date format
     * @return bool
     */
    public function validateDate($date, $format = 'Y-m-d')
    {
        if (empty($date)) {
            $this->addError('Date is required');
            return false;
        }
        
        $dateObj = DateTime::createFromFormat($format, $date);
        
        if (!$dateObj || $dateObj->format($format) !== $date) {
            $this->addError('Invalid date format');
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate integer
     * 
     * @param mixed $value Value to validate
     * @param int $min Minimum value
     * @param int $max Maximum value
     * @return bool
     */
    public function validateInteger($value, $min = null, $max = null)
    {
        if (!is_numeric($value) || (int)$value != $value) {
            $this->addError('Value must be an integer');
            return false;
        }
        
        $intValue = (int)$value;
        
        if ($min !== null && $intValue < $min) {
            $this->addError('Value must be at least ' . $min);
            return false;
        }
        
        if ($max !== null && $intValue > $max) {
            $this->addError('Value must be at most ' . $max);
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate string length
     * 
     * @param string $value String to validate
     * @param int $min Minimum length
     * @param int $max Maximum length
     * @return bool
     */
    public function validateStringLength($value, $min = 0, $max = null)
    {
        $length = strlen($value);
        
        if ($length < $min) {
            $this->addError('Value must be at least ' . $min . ' characters long');
            return false;
        }
        
        if ($max !== null && $length > $max) {
            $this->addError('Value must be at most ' . $max . ' characters long');
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate required field
     * 
     * @param mixed $value Value to validate
     * @param string $fieldName Field name for error message
     * @return bool
     */
    public function validateRequired($value, $fieldName = 'Field')
    {
        if (empty($value) && $value !== '0') {
            $this->addError($fieldName . ' is required');
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate array contains only allowed values
     * 
     * @param array $values Values to validate
     * @param array $allowedValues Allowed values
     * @return bool
     */
    public function validateInArray($values, $allowedValues)
    {
        if (!is_array($values)) {
            $values = [$values];
        }
        
        foreach ($values as $value) {
            if (!in_array($value, $allowedValues)) {
                $this->addError('Invalid value: ' . $value);
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Sanitize string input
     * 
     * @param string $input Input string
     * @return string Sanitized string
     */
    public function sanitizeString($input)
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitize email input
     * 
     * @param string $email Email string
     * @return string Sanitized email
     */
    public function sanitizeEmail($email)
    {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Sanitize integer input
     * 
     * @param mixed $input Input value
     * @return int Sanitized integer
     */
    public function sanitizeInteger($input)
    {
        return (int)filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }
    
    /**
     * Add validation error
     * 
     * @param string $error Error message
     */
    private function addError($error)
    {
        $this->errors[] = $error;
    }
    
    /**
     * Get all validation errors
     * 
     * @return array Array of error messages
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * Check if there are any validation errors
     * 
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }
    
    /**
     * Clear all validation errors
     */
    public function clearErrors()
    {
        $this->errors = [];
    }
    
    /**
     * Get upload error message
     * 
     * @param int $error Upload error code
     * @return string Error message
     */
    private function getUploadErrorMessage($error)
    {
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File exceeds upload_max_filesize directive';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File exceeds MAX_FILE_SIZE directive';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
    
    /**
     * Validate CSRF token
     * 
     * @param string $token Token to validate
     * @return bool
     */
    public function validateCSRFToken($token)
    {
        if (!isset($_SESSION[CSRF_TOKEN_NAME]) || empty($token)) {
            $this->addError('Invalid CSRF token');
            return false;
        }
        
        if (!hash_equals($_SESSION[CSRF_TOKEN_NAME], $token)) {
            $this->addError('CSRF token mismatch');
            return false;
        }
        
        return true;
    }
    
    /**
     * Generate CSRF token
     * 
     * @return string CSRF token
     */
    public static function generateCSRFToken()
    {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION[CSRF_TOKEN_NAME];
    }
}
?>
