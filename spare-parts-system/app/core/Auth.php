<?php
/**
 * Authentication System with Secure Session Management
 * 
 * This class provides secure authentication with:
 * - Password hashing using PHP's password_hash()
 * - Session management with security features
 * - Login attempt limiting
 * - Remember me functionality
 * - Role-based access control
 */

namespace App\Core;

use App\Models\User;

class Auth
{
    private static $user = null;
    private static $sessionStarted = false;
    private static $maxLoginAttempts = 5;
    private static $lockoutTime = 900; // 15 minutes

    /**
     * Start secure session
     */
    public static function startSession()
    {
        if (!self::$sessionStarted) {
            // Configure session security
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', 'Strict');
            
            // Set session name
            session_name('SPMS_SESSION');
            
            // Start session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            self::$sessionStarted = true;
            
            // Regenerate session ID periodically
            self::regenerateSessionId();
            
            // Load user from session
            self::loadUserFromSession();
        }
    }

    /**
     * Attempt to log in a user
     * 
     * @param string $username Username or email
     * @param string $password Password
     * @param bool $remember Remember me option
     * @return bool Login success
     */
    public static function login($username, $password, $remember = false)
    {
        self::startSession();
        
        // Check for too many login attempts
        if (self::isLockedOut($username)) {
            return false;
        }
        
        // Find user by username or email
        $userModel = new User();
        $user = $userModel->findByUsernameOrEmail($username);
        
        if (!$user || !$user['is_active']) {
            self::recordFailedAttempt($username);
            return false;
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            self::recordFailedAttempt($username);
            return false;
        }
        
        // Clear failed attempts
        self::clearFailedAttempts($username);
        
        // Set user session
        self::setUserSession($user, $remember);
        
        // Update last login
        $userModel->updateLastLogin($user['id']);
        
        return true;
    }

    /**
     * Log out the current user
     */
    public static function logout()
    {
        self::startSession();
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
            
            // Remove remember token from database
            if (self::$user) {
                $userModel = new User();
                $userModel->clearRememberToken(self::$user['id']);
            }
        }
        
        // Clear session
        $_SESSION = [];
        session_destroy();
        
        // Clear user
        self::$user = null;
        self::$sessionStarted = false;
    }

    /**
     * Check if user is authenticated
     * 
     * @return bool Authentication status
     */
    public static function check()
    {
        self::startSession();
        return self::$user !== null;
    }

    /**
     * Get current authenticated user
     * 
     * @return array|null User data or null
     */
    public static function user()
    {
        self::startSession();
        return self::$user;
    }

    /**
     * Get current user ID
     * 
     * @return int|null User ID or null
     */
    public static function id()
    {
        $user = self::user();
        return $user ? $user['id'] : null;
    }

    /**
     * Check if user has specific role
     * 
     * @param string $role Role to check
     * @return bool Has role
     */
    public static function hasRole($role)
    {
        $user = self::user();
        return $user && $user['role'] === $role;
    }

    /**
     * Check if user has any of the specified roles
     * 
     * @param array $roles Roles to check
     * @return bool Has any role
     */
    public static function hasAnyRole($roles)
    {
        $user = self::user();
        return $user && in_array($user['role'], $roles);
    }

    /**
     * Require authentication
     * 
     * @param string $redirectUrl Redirect URL if not authenticated
     */
    public static function requireAuth($redirectUrl = '/login')
    {
        if (!self::check()) {
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    /**
     * Require specific role
     * 
     * @param string $role Required role
     * @param string $redirectUrl Redirect URL if unauthorized
     */
    public static function requireRole($role, $redirectUrl = '/unauthorized')
    {
        self::requireAuth();
        
        if (!self::hasRole($role)) {
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    /**
     * Require any of the specified roles
     * 
     * @param array $roles Required roles
     * @param string $redirectUrl Redirect URL if unauthorized
     */
    public static function requireAnyRole($roles, $redirectUrl = '/unauthorized')
    {
        self::requireAuth();
        
        if (!self::hasAnyRole($roles)) {
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    /**
     * Hash a password
     * 
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify a password
     * 
     * @param string $password Plain text password
     * @param string $hash Hashed password
     * @return bool Password matches
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Set user session
     * 
     * @param array $user User data
     * @param bool $remember Remember me option
     */
    private static function setUserSession($user, $remember = false)
    {
        // Store user in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_data'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'role' => $user['role']
        ];
        $_SESSION['login_time'] = time();
        
        // Set current user
        self::$user = $_SESSION['user_data'];
        
        // Handle remember me
        if ($remember) {
            self::setRememberToken($user['id']);
        }
    }

    /**
     * Load user from session
     */
    private static function loadUserFromSession()
    {
        // Check session timeout
        if (isset($_SESSION['login_time'])) {
            $sessionTimeout = Config::get('session_timeout', 3600);
            if (time() - $_SESSION['login_time'] > $sessionTimeout) {
                self::logout();
                return;
            }
        }
        
        // Load user from session
        if (isset($_SESSION['user_data'])) {
            self::$user = $_SESSION['user_data'];
            return;
        }
        
        // Try remember me token
        if (isset($_COOKIE['remember_token'])) {
            self::loginFromRememberToken($_COOKIE['remember_token']);
        }
    }

    /**
     * Set remember me token
     * 
     * @param int $userId User ID
     */
    private static function setRememberToken($userId)
    {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        
        // Store hashed token in database
        $userModel = new User();
        $userModel->setRememberToken($userId, $hashedToken);
        
        // Set cookie (expires in 30 days)
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', isset($_SERVER['HTTPS']), true);
    }

    /**
     * Login from remember token
     * 
     * @param string $token Remember token
     */
    private static function loginFromRememberToken($token)
    {
        $hashedToken = hash('sha256', $token);
        
        $userModel = new User();
        $user = $userModel->findByRememberToken($hashedToken);
        
        if ($user && $user['is_active']) {
            self::setUserSession($user);
            
            // Regenerate remember token for security
            self::setRememberToken($user['id']);
        } else {
            // Invalid token, clear cookie
            setcookie('remember_token', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
        }
    }

    /**
     * Regenerate session ID periodically
     */
    private static function regenerateSessionId()
    {
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }

    /**
     * Check if IP is locked out
     * 
     * @param string $username Username
     * @return bool Is locked out
     */
    private static function isLockedOut($username)
    {
        $key = 'login_attempts_' . md5($username . $_SERVER['REMOTE_ADDR']);
        
        if (isset($_SESSION[$key])) {
            $attempts = $_SESSION[$key];
            
            if ($attempts['count'] >= self::$maxLoginAttempts) {
                if (time() - $attempts['last_attempt'] < self::$lockoutTime) {
                    return true;
                } else {
                    // Lockout expired, clear attempts
                    unset($_SESSION[$key]);
                }
            }
        }
        
        return false;
    }

    /**
     * Record failed login attempt
     * 
     * @param string $username Username
     */
    private static function recordFailedAttempt($username)
    {
        $key = 'login_attempts_' . md5($username . $_SERVER['REMOTE_ADDR']);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'last_attempt' => 0];
        }
        
        $_SESSION[$key]['count']++;
        $_SESSION[$key]['last_attempt'] = time();
    }

    /**
     * Clear failed login attempts
     * 
     * @param string $username Username
     */
    private static function clearFailedAttempts($username)
    {
        $key = 'login_attempts_' . md5($username . $_SERVER['REMOTE_ADDR']);
        unset($_SESSION[$key]);
    }

    /**
     * Get remaining lockout time
     * 
     * @param string $username Username
     * @return int Remaining seconds
     */
    public static function getRemainingLockoutTime($username)
    {
        $key = 'login_attempts_' . md5($username . $_SERVER['REMOTE_ADDR']);
        
        if (isset($_SESSION[$key])) {
            $attempts = $_SESSION[$key];
            
            if ($attempts['count'] >= self::$maxLoginAttempts) {
                $remaining = self::$lockoutTime - (time() - $attempts['last_attempt']);
                return max(0, $remaining);
            }
        }
        
        return 0;
    }

    /**
     * Get failed login attempts count
     * 
     * @param string $username Username
     * @return int Attempts count
     */
    public static function getFailedAttempts($username)
    {
        $key = 'login_attempts_' . md5($username . $_SERVER['REMOTE_ADDR']);
        
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key]['count'];
        }
        
        return 0;
    }
}
