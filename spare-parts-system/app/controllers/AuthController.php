<?php
/**
 * Authentication Controller
 * 
 * Handles user authentication including login, logout, and password management.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Language;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        // If already authenticated, redirect to dashboard
        if (Auth::check()) {
            $this->redirect('/dashboard');
            return;
        }
        
        $this->setTitle(__('auth.login'));
        $this->layout = 'layouts/auth';
        
        return $this->view('auth/login', [
            'flash_messages' => $this->getFlashMessages()
        ]);
    }
    
    /**
     * Handle login attempt
     */
    public function login()
    {
        // Verify CSRF token
        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/login');
        }
        
        // Validate input
        $errors = $this->validate([
            'username' => 'required',
            'password' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->flash('error', __('auth.login_failed'));
            return $this->redirect('/login');
        }
        
        $username = $this->input('username');
        $password = $this->input('password');
        $remember = $this->input('remember_me') ? true : false;
        
        // Check for lockout
        $remainingLockout = Auth::getRemainingLockoutTime($username);
        if ($remainingLockout > 0) {
            $minutes = ceil($remainingLockout / 60);
            $this->flash('error', __('auth.account_locked') . " ({$minutes} minutes remaining)");
            return $this->redirect('/login');
        }
        
        // Attempt login
        if (Auth::login($username, $password, $remember)) {
            $this->flash('success', __('auth.login_success'));
            
            // Redirect to intended page or dashboard
            $redirectTo = $_SESSION['intended_url'] ?? '/dashboard';
            unset($_SESSION['intended_url']);
            
            return $this->redirect($redirectTo);
        } else {
            $failedAttempts = Auth::getFailedAttempts($username);
            $maxAttempts = 5; // This should come from config
            $remainingAttempts = $maxAttempts - $failedAttempts;
            
            if ($remainingAttempts > 0) {
                $this->flash('error', __('auth.login_failed') . " ({$remainingAttempts} attempts remaining)");
            } else {
                $this->flash('error', __('auth.account_locked'));
            }
            
            return $this->redirect('/login');
        }
    }
    
    /**
     * Handle logout
     */
    public function logout()
    {
        Auth::logout();
        $this->flash('success', __('auth.logout_success'));
        return $this->redirect('/login');
    }
    
    /**
     * Show forgot password form
     */
    public function showForgotPassword()
    {
        $this->setTitle(__('auth.forgot_password'));
        $this->layout = 'layouts/auth';
        
        return $this->view('auth/forgot-password', [
            'flash_messages' => $this->getFlashMessages()
        ]);
    }
    
    /**
     * Handle forgot password request
     */
    public function forgotPassword()
    {
        // Verify CSRF token
        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/forgot-password');
        }
        
        // Validate input
        $errors = $this->validate([
            'email' => 'required|email'
        ]);
        
        if (!empty($errors)) {
            $this->flash('error', 'Please enter a valid email address');
            return $this->redirect('/forgot-password');
        }
        
        $email = $this->input('email');
        
        // TODO: Implement password reset functionality
        // For now, just show a success message
        $this->flash('success', 'If an account with that email exists, we have sent password reset instructions.');
        return $this->redirect('/login');
    }
    
    /**
     * Show change password form
     */
    public function showChangePassword()
    {
        $this->requireAuth();
        
        $this->setTitle(__('auth.change_password'));
        
        return $this->view('auth/change-password', [
            'flash_messages' => $this->getFlashMessages()
        ]);
    }
    
    /**
     * Handle change password request
     */
    public function changePassword()
    {
        $this->requireAuth();
        
        // Verify CSRF token
        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/change-password');
        }
        
        // Validate input
        $errors = $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required|confirmed:new_password'
        ]);
        
        if (!empty($errors)) {
            $this->flash('error', 'Please check your input and try again');
            return $this->redirect('/change-password');
        }
        
        $currentPassword = $this->input('current_password');
        $newPassword = $this->input('new_password');
        
        $user = Auth::user();
        
        // Verify current password
        if (!Auth::verifyPassword($currentPassword, $user['password_hash'])) {
            $this->flash('error', 'Current password is incorrect');
            return $this->redirect('/change-password');
        }
        
        // Update password
        $userModel = new \App\Models\User();
        if ($userModel->updatePassword($user['id'], $newPassword)) {
            $this->flash('success', __('auth.password_changed'));
            return $this->redirect('/dashboard');
        } else {
            $this->flash('error', 'Failed to update password');
            return $this->redirect('/change-password');
        }
    }
    
    /**
     * Check authentication status (AJAX)
     */
    public function checkAuth()
    {
        if ($this->isAjax()) {
            return $this->json([
                'authenticated' => Auth::check(),
                'user' => Auth::user()
            ]);
        }
        
        return $this->redirect('/');
    }
    
    /**
     * Handle session timeout
     */
    public function sessionTimeout()
    {
        Auth::logout();
        
        if ($this->isAjax()) {
            return $this->json([
                'success' => false,
                'message' => __('auth.session_expired'),
                'redirect' => '/login'
            ], 401);
        }
        
        $this->flash('warning', __('auth.session_expired'));
        return $this->redirect('/login');
    }
}
