<?php
/**
 * CSRF Protection System
 * 
 * This class provides Cross-Site Request Forgery protection with:
 * - Token generation and validation
 * - Session-based token storage
 * - Automatic token refresh
 * - Form helper methods
 * - AJAX support
 */

namespace App\Core;

class CSRF
{
    private static $tokenName = 'csrf_token';
    private static $sessionKey = 'csrf_tokens';
    private static $maxTokens = 10;
    private static $tokenLength = 32;

    /**
     * Initialize CSRF protection
     */
    public static function init()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION[self::$sessionKey])) {
            $_SESSION[self::$sessionKey] = [];
        }
    }

    /**
     * Generate a new CSRF token
     * 
     * @param string $action Optional action name for token scoping
     * @return string Generated token
     */
    public static function generateToken($action = 'default')
    {
        self::init();
        
        // Generate random token
        $token = bin2hex(random_bytes(self::$tokenLength));
        
        // Store token with timestamp
        $_SESSION[self::$sessionKey][$action] = [
            'token' => $token,
            'timestamp' => time()
        ];
        
        // Clean up old tokens
        self::cleanupTokens();
        
        return $token;
    }

    /**
     * Validate CSRF token
     * 
     * @param string $token Token to validate
     * @param string $action Optional action name for token scoping
     * @return bool Token is valid
     */
    public static function validateToken($token, $action = 'default')
    {
        self::init();
        
        if (!isset($_SESSION[self::$sessionKey][$action])) {
            return false;
        }
        
        $storedData = $_SESSION[self::$sessionKey][$action];
        
        // Check token match
        if (!hash_equals($storedData['token'], $token)) {
            return false;
        }
        
        // Check token age (valid for 1 hour)
        if (time() - $storedData['timestamp'] > 3600) {
            unset($_SESSION[self::$sessionKey][$action]);
            return false;
        }
        
        return true;
    }

    /**
     * Validate token from request
     * 
     * @param string $action Optional action name for token scoping
     * @return bool Token is valid
     */
    public static function validateRequest($action = 'default')
    {
        $token = self::getTokenFromRequest();
        
        if (!$token) {
            return false;
        }
        
        return self::validateToken($token, $action);
    }

    /**
     * Get token from current request
     * 
     * @return string|null Token from request
     */
    public static function getTokenFromRequest()
    {
        // Check POST data
        if (isset($_POST[self::$tokenName])) {
            return $_POST[self::$tokenName];
        }
        
        // Check headers (for AJAX requests)
        $headers = getallheaders();
        if (isset($headers['X-CSRF-Token'])) {
            return $headers['X-CSRF-Token'];
        }
        
        // Check GET parameter (less secure, use with caution)
        if (isset($_GET[self::$tokenName])) {
            return $_GET[self::$tokenName];
        }
        
        return null;
    }

    /**
     * Get current token for an action
     * 
     * @param string $action Optional action name for token scoping
     * @return string|null Current token
     */
    public static function getToken($action = 'default')
    {
        self::init();
        
        if (isset($_SESSION[self::$sessionKey][$action])) {
            $storedData = $_SESSION[self::$sessionKey][$action];
            
            // Check if token is still valid
            if (time() - $storedData['timestamp'] <= 3600) {
                return $storedData['token'];
            } else {
                // Token expired, remove it
                unset($_SESSION[self::$sessionKey][$action]);
            }
        }
        
        // Generate new token if none exists or expired
        return self::generateToken($action);
    }

    /**
     * Generate HTML hidden input field for CSRF token
     * 
     * @param string $action Optional action name for token scoping
     * @return string HTML input field
     */
    public static function field($action = 'default')
    {
        $token = self::getToken($action);
        return '<input type="hidden" name="' . self::$tokenName . '" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Generate meta tag for CSRF token (useful for AJAX)
     * 
     * @param string $action Optional action name for token scoping
     * @return string HTML meta tag
     */
    public static function metaTag($action = 'default')
    {
        $token = self::getToken($action);
        return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
    }

    /**
     * Verify CSRF token and throw exception if invalid
     * 
     * @param string $action Optional action name for token scoping
     * @throws \Exception If token is invalid
     */
    public static function verify($action = 'default')
    {
        if (!self::validateRequest($action)) {
            http_response_code(403);
            throw new \Exception('CSRF token validation failed');
        }
    }

    /**
     * Middleware function for CSRF protection
     * 
     * @param string $action Optional action name for token scoping
     * @return callable Middleware function
     */
    public static function middleware($action = 'default')
    {
        return function() use ($action) {
            // Skip CSRF check for GET requests
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return null;
            }
            
            if (!self::validateRequest($action)) {
                http_response_code(403);
                
                // Return JSON response for AJAX requests
                if (self::isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'CSRF token validation failed']);
                    exit;
                }
                
                // Return HTML response for regular requests
                echo '<!DOCTYPE html>
<html>
<head>
    <title>403 Forbidden</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 100px; }
        .error { color: #d32f2f; }
    </style>
</head>
<body>
    <h1 class="error">403 - Forbidden</h1>
    <p>CSRF token validation failed. Please refresh the page and try again.</p>
    <a href="javascript:history.back()">Go Back</a>
</body>
</html>';
                exit;
            }
            
            return null;
        };
    }

    /**
     * Clean up old tokens
     */
    private static function cleanupTokens()
    {
        if (!isset($_SESSION[self::$sessionKey])) {
            return;
        }
        
        $tokens = $_SESSION[self::$sessionKey];
        $currentTime = time();
        
        // Remove expired tokens
        foreach ($tokens as $action => $data) {
            if ($currentTime - $data['timestamp'] > 3600) {
                unset($_SESSION[self::$sessionKey][$action]);
            }
        }
        
        // Limit number of tokens
        if (count($_SESSION[self::$sessionKey]) > self::$maxTokens) {
            // Sort by timestamp and keep only the newest tokens
            uasort($_SESSION[self::$sessionKey], function($a, $b) {
                return $b['timestamp'] - $a['timestamp'];
            });
            
            $_SESSION[self::$sessionKey] = array_slice($_SESSION[self::$sessionKey], 0, self::$maxTokens, true);
        }
    }

    /**
     * Check if request is AJAX
     * 
     * @return bool Is AJAX request
     */
    private static function isAjaxRequest()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Clear all CSRF tokens
     */
    public static function clearTokens()
    {
        self::init();
        $_SESSION[self::$sessionKey] = [];
    }

    /**
     * Get all active tokens (for debugging)
     * 
     * @return array Active tokens
     */
    public static function getActiveTokens()
    {
        self::init();
        return $_SESSION[self::$sessionKey];
    }

    /**
     * Set token name
     * 
     * @param string $name Token field name
     */
    public static function setTokenName($name)
    {
        self::$tokenName = $name;
    }

    /**
     * Get token name
     * 
     * @return string Token field name
     */
    public static function getTokenName()
    {
        return self::$tokenName;
    }

    /**
     * Set maximum number of tokens to keep
     * 
     * @param int $max Maximum tokens
     */
    public static function setMaxTokens($max)
    {
        self::$maxTokens = max(1, $max);
    }

    /**
     * Set token length
     * 
     * @param int $length Token length in bytes
     */
    public static function setTokenLength($length)
    {
        self::$tokenLength = max(16, $length);
    }

    /**
     * Generate JavaScript code for AJAX CSRF protection
     * 
     * @param string $action Optional action name for token scoping
     * @return string JavaScript code
     */
    public static function ajaxSetup($action = 'default')
    {
        $token = self::getToken($action);
        
        return "
// CSRF Protection for AJAX requests
(function() {
    var token = '" . addslashes($token) . "';
    
    // jQuery setup
    if (typeof $ !== 'undefined') {
        $.ajaxSetup({
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-CSRF-Token', token);
            }
        });
    }
    
    // Vanilla JavaScript fetch wrapper
    var originalFetch = window.fetch;
    window.fetch = function(url, options) {
        options = options || {};
        options.headers = options.headers || {};
        
        if (options.method && options.method.toUpperCase() !== 'GET') {
            options.headers['X-CSRF-Token'] = token;
        }
        
        return originalFetch(url, options);
    };
})();
";
    }
}
