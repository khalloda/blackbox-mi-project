<?php
/**
 * Base Controller Class
 * 
 * This class provides a base for all controllers with:
 * - View rendering
 * - Request handling
 * - Response formatting
 * - Authentication helpers
 * - Validation helpers
 * - Flash messages
 */

namespace App\Core;

use App\Core\Auth;
use App\Core\Language;
use App\Core\CSRF;
use App\Core\Config;

abstract class Controller
{
    protected $request;
    protected $response;
    protected $viewData = [];
    protected $layout = 'layouts/main';
    protected $middleware = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
        
        // Set default view data
        $this->viewData = [
            'title' => Config::get('app.name', 'Spare Parts Management System'),
            'user' => Auth::user(),
            'language' => Language::getCurrentLanguage(),
            'direction' => Language::getDirection(),
            'csrf_token' => CSRF::getToken(),
        ];
        
        // Apply middleware
        $this->applyMiddleware();
    }

    /**
     * Render view
     * 
     * @param string $view View name
     * @param array $data View data
     * @param string $layout Layout name
     * @return string Rendered HTML
     */
    protected function view($view, $data = [], $layout = null)
    {
        $layout = $layout ?: $this->layout;
        
        // Merge view data
        $data = array_merge($this->viewData, $data);
        
        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include view file
        $viewFile = $this->getViewPath($view);
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new \Exception("View file not found: {$view}");
        }
        
        // Get view content
        $content = ob_get_clean();
        
        // If no layout, return content directly
        if (!$layout) {
            return $content;
        }
        
        // Render with layout
        return $this->renderLayout($layout, $content, $data);
    }

    /**
     * Render layout
     * 
     * @param string $layout Layout name
     * @param string $content Content
     * @param array $data View data
     * @return string Rendered HTML
     */
    protected function renderLayout($layout, $content, $data)
    {
        // Add content to data
        $data['content'] = $content;
        
        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include layout file
        $layoutFile = $this->getViewPath($layout);
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            throw new \Exception("Layout file not found: {$layout}");
        }
        
        return ob_get_clean();
    }

    /**
     * Get view file path
     * 
     * @param string $view View name
     * @return string View file path
     */
    protected function getViewPath($view)
    {
        return __DIR__ . "/../views/{$view}.php";
    }

    /**
     * Return JSON response
     * 
     * @param mixed $data Response data
     * @param int $status HTTP status code
     * @return string JSON response
     */
    protected function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Return success JSON response
     * 
     * @param mixed $data Response data
     * @param string $message Success message
     * @return string JSON response
     */
    protected function success($data = null, $message = 'Success')
    {
        return $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Return error JSON response
     * 
     * @param string $message Error message
     * @param mixed $errors Error details
     * @param int $status HTTP status code
     * @return string JSON response
     */
    protected function error($message = 'Error', $errors = null, $status = 400)
    {
        return $this->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }

    /**
     * Redirect to URL
     * 
     * @param string $url URL to redirect to
     * @param int $status HTTP status code
     */
    protected function redirect($url, $status = 302)
    {
        http_response_code($status);
        header("Location: {$url}");
        exit;
    }

    /**
     * Redirect back to previous page
     * 
     * @param string $default Default URL if no referrer
     */
    protected function back($default = '/')
    {
        $referrer = $_SERVER['HTTP_REFERER'] ?? $default;
        $this->redirect($referrer);
    }

    /**
     * Set flash message
     * 
     * @param string $type Message type (success, error, warning, info)
     * @param string $message Message text
     */
    protected function flash($type, $message)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Get flash messages
     * 
     * @return array Flash messages
     */
    protected function getFlashMessages()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        
        return $messages;
    }

    /**
     * Validate request data
     * 
     * @param array $rules Validation rules
     * @param array $data Data to validate (defaults to POST data)
     * @return array Validation errors
     */
    protected function validate($rules, $data = null)
    {
        $data = $data ?: $_POST;
        $errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $fieldRules = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            $value = $data[$field] ?? null;
            
            foreach ($fieldRules as $rule) {
                $error = $this->validateRule($field, $value, $rule, $data);
                if ($error) {
                    $errors[$field] = $error;
                    break; // Stop at first error for this field
                }
            }
        }
        
        return $errors;
    }

    /**
     * Validate single rule
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string $rule Validation rule
     * @param array $data All data
     * @return string|null Error message or null
     */
    protected function validateRule($field, $value, $rule, $data)
    {
        switch ($rule) {
            case 'required':
                return empty($value) ? "The {$field} field is required" : null;
                
            case 'email':
                return $value && !filter_var($value, FILTER_VALIDATE_EMAIL) 
                    ? "The {$field} must be a valid email address" : null;
                    
            case 'numeric':
                return $value && !is_numeric($value) 
                    ? "The {$field} must be a number" : null;
                    
            case 'integer':
                return $value && !filter_var($value, FILTER_VALIDATE_INT) 
                    ? "The {$field} must be an integer" : null;
                    
            default:
                // Handle parameterized rules
                if (strpos($rule, ':') !== false) {
                    [$ruleName, $parameter] = explode(':', $rule, 2);
                    
                    switch ($ruleName) {
                        case 'min':
                            return $value && strlen($value) < $parameter 
                                ? "The {$field} must be at least {$parameter} characters" : null;
                                
                        case 'max':
                            return $value && strlen($value) > $parameter 
                                ? "The {$field} must not exceed {$parameter} characters" : null;
                                
                        case 'confirmed':
                            $confirmField = $field . '_confirmation';
                            return $value && $value !== ($data[$confirmField] ?? null)
                                ? "The {$field} confirmation does not match" : null;
                    }
                }
                
                return null;
        }
    }

    /**
     * Get request input
     * 
     * @param string $key Input key
     * @param mixed $default Default value
     * @return mixed Input value
     */
    protected function input($key = null, $default = null)
    {
        if ($key === null) {
            return array_merge($_GET, $_POST);
        }
        
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Check if request is AJAX
     * 
     * @return bool Is AJAX request
     */
    protected function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Check if request method is POST
     * 
     * @return bool Is POST request
     */
    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Check if request method is GET
     * 
     * @return bool Is GET request
     */
    protected function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Require authentication
     * 
     * @param string $redirectUrl Redirect URL if not authenticated
     */
    protected function requireAuth($redirectUrl = '/login')
    {
        if (!Auth::check()) {
            if ($this->isAjax()) {
                echo $this->error('Authentication required', null, 401);
                exit;
            }
            
            $this->redirect($redirectUrl);
        }
    }

    /**
     * Require specific role
     * 
     * @param string $role Required role
     * @param string $redirectUrl Redirect URL if unauthorized
     */
    protected function requireRole($role, $redirectUrl = '/unauthorized')
    {
        $this->requireAuth();
        
        if (!Auth::hasRole($role)) {
            if ($this->isAjax()) {
                echo $this->error('Insufficient permissions', null, 403);
                exit;
            }
            
            $this->redirect($redirectUrl);
        }
    }

    /**
     * Require any of the specified roles
     * 
     * @param array $roles Required roles
     * @param string $redirectUrl Redirect URL if unauthorized
     */
    protected function requireAnyRole($roles, $redirectUrl = '/unauthorized')
    {
        $this->requireAuth();
        
        if (!Auth::hasAnyRole($roles)) {
            if ($this->isAjax()) {
                echo $this->error('Insufficient permissions', null, 403);
                exit;
            }
            
            $this->redirect($redirectUrl);
        }
    }

    /**
     * Apply middleware
     */
    protected function applyMiddleware()
    {
        foreach ($this->middleware as $middleware) {
            if (is_callable($middleware)) {
                $result = call_user_func($middleware);
                if ($result !== null) {
                    echo $result;
                    exit;
                }
            }
        }
    }

    /**
     * Set page title
     * 
     * @param string $title Page title
     */
    protected function setTitle($title)
    {
        $this->viewData['title'] = $title;
    }

    /**
     * Add CSS file
     * 
     * @param string $file CSS file path
     */
    protected function addCss($file)
    {
        if (!isset($this->viewData['css'])) {
            $this->viewData['css'] = [];
        }
        
        $this->viewData['css'][] = $file;
    }

    /**
     * Add JavaScript file
     * 
     * @param string $file JavaScript file path
     */
    protected function addJs($file)
    {
        if (!isset($this->viewData['js'])) {
            $this->viewData['js'] = [];
        }
        
        $this->viewData['js'][] = $file;
    }

    /**
     * Set view data
     * 
     * @param string|array $key Data key or array of data
     * @param mixed $value Data value
     */
    protected function setData($key, $value = null)
    {
        if (is_array($key)) {
            $this->viewData = array_merge($this->viewData, $key);
        } else {
            $this->viewData[$key] = $value;
        }
    }

    /**
     * Get uploaded file information
     * 
     * @param string $field File field name
     * @return array|null File information
     */
    protected function getUploadedFile($field)
    {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        return $_FILES[$field];
    }

    /**
     * Handle file upload
     * 
     * @param string $field File field name
     * @param string $destination Destination directory
     * @param array $allowedTypes Allowed file types
     * @param int $maxSize Maximum file size in bytes
     * @return string|false Uploaded file path or false on failure
     */
    protected function handleUpload($field, $destination, $allowedTypes = [], $maxSize = 10485760)
    {
        $file = $this->getUploadedFile($field);
        
        if (!$file) {
            return false;
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            return false;
        }
        
        // Check file type
        if (!empty($allowedTypes)) {
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedTypes)) {
                return false;
            }
        }
        
        // Create destination directory if it doesn't exist
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        // Generate unique filename
        $filename = uniqid() . '_' . $file['name'];
        $filepath = $destination . '/' . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return $filepath;
        }
        
        return false;
    }

    /**
     * Paginate results
     * 
     * @param array $data Data to paginate
     * @param int $perPage Items per page
     * @param int $currentPage Current page
     * @return array Pagination result
     */
    protected function paginate($data, $perPage = 20, $currentPage = 1)
    {
        $total = count($data);
        $totalPages = ceil($total / $perPage);
        $offset = ($currentPage - 1) * $perPage;
        
        $paginatedData = array_slice($data, $offset, $perPage);
        
        return [
            'data' => $paginatedData,
            'pagination' => [
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_previous' => $currentPage > 1,
                'has_next' => $currentPage < $totalPages,
                'previous_page' => $currentPage > 1 ? $currentPage - 1 : null,
                'next_page' => $currentPage < $totalPages ? $currentPage + 1 : null,
            ]
        ];
    }
}

/**
 * Simple Request class
 */
class Request
{
    public function all()
    {
        return array_merge($_GET, $_POST);
    }
    
    public function get($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }
    
    public function post($key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }
    
    public function input($key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
    
    public function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    public function isPost()
    {
        return $this->method() === 'POST';
    }
    
    public function isGet()
    {
        return $this->method() === 'GET';
    }
}

/**
 * Simple Response class
 */
class Response
{
    public function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    
    public function redirect($url, $status = 302)
    {
        http_response_code($status);
        header("Location: {$url}");
        exit;
    }
}
