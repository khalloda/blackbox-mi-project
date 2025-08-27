<?php
/**
 * User Model
 * 
 * This model handles user authentication and management with:
 * - User CRUD operations
 * - Password management
 * - Remember token handling
 * - Role-based access control
 * - Login attempt tracking
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Auth;

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'full_name',
        'role',
        'is_active'
    ];
    
    protected $hidden = [
        'password_hash',
        'remember_token'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_login_at' => 'datetime'
    ];
    
    protected $rules = [
        'username' => 'required|unique:users',
        'email' => 'required|email|unique:users',
        'password_hash' => 'required',
        'full_name' => 'required',
        'role' => 'required'
    ];

    /**
     * Find user by username or email
     * 
     * @param string $identifier Username or email
     * @return array|null User data
     */
    public function findByUsernameOrEmail($identifier)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (username = :identifier OR email = :identifier) 
                AND is_active = 1";
        
        return $this->db->selectOne($sql, ['identifier' => $identifier]);
    }

    /**
     * Find user by remember token
     * 
     * @param string $token Hashed remember token
     * @return array|null User data
     */
    public function findByRememberToken($token)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE remember_token = :token 
                AND is_active = 1";
        
        return $this->db->selectOne($sql, ['token' => $token]);
    }

    /**
     * Set remember token for user
     * 
     * @param int $userId User ID
     * @param string $token Hashed remember token
     * @return bool Success status
     */
    public function setRememberToken($userId, $token)
    {
        return $this->update($userId, ['remember_token' => $token]);
    }

    /**
     * Clear remember token for user
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function clearRememberToken($userId)
    {
        return $this->update($userId, ['remember_token' => null]);
    }

    /**
     * Update last login timestamp
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function updateLastLogin($userId)
    {
        return $this->update($userId, ['last_login_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Create new user with hashed password
     * 
     * @param array $data User data
     * @return int|false User ID or false on failure
     */
    public function createUser(array $data)
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password_hash'] = Auth::hashPassword($data['password']);
            unset($data['password']);
        }
        
        // Set default role if not provided
        if (!isset($data['role'])) {
            $data['role'] = 'user';
        }
        
        // Set default active status
        if (!isset($data['is_active'])) {
            $data['is_active'] = true;
        }
        
        return $this->create($data);
    }

    /**
     * Update user password
     * 
     * @param int $userId User ID
     * @param string $newPassword New password
     * @return bool Success status
     */
    public function updatePassword($userId, $newPassword)
    {
        $hashedPassword = Auth::hashPassword($newPassword);
        return $this->update($userId, ['password_hash' => $hashedPassword]);
    }

    /**
     * Check if username exists
     * 
     * @param string $username Username
     * @param int $excludeId User ID to exclude (for updates)
     * @return bool Username exists
     */
    public function usernameExists($username, $excludeId = null)
    {
        $sql = "SELECT id FROM {$this->table} WHERE username = :username";
        $params = ['username' => $username];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        return $this->db->selectOne($sql, $params) !== null;
    }

    /**
     * Check if email exists
     * 
     * @param string $email Email
     * @param int $excludeId User ID to exclude (for updates)
     * @return bool Email exists
     */
    public function emailExists($email, $excludeId = null)
    {
        $sql = "SELECT id FROM {$this->table} WHERE email = :email";
        $params = ['email' => $email];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        return $this->db->selectOne($sql, $params) !== null;
    }

    /**
     * Get users by role
     * 
     * @param string $role User role
     * @return array Users
     */
    public function getUsersByRole($role)
    {
        return $this->where('role', $role)
                   ->where('is_active', true)
                   ->orderBy('full_name')
                   ->get();
    }

    /**
     * Get active users
     * 
     * @return array Active users
     */
    public function getActiveUsers()
    {
        return $this->where('is_active', true)
                   ->orderBy('full_name')
                   ->get();
    }

    /**
     * Deactivate user
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function deactivateUser($userId)
    {
        return $this->update($userId, [
            'is_active' => false,
            'remember_token' => null
        ]);
    }

    /**
     * Activate user
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function activateUser($userId)
    {
        return $this->update($userId, ['is_active' => true]);
    }

    /**
     * Get user statistics
     * 
     * @return array User statistics
     */
    public function getStatistics()
    {
        $stats = [];
        
        // Total users
        $stats['total'] = $this->count();
        
        // Active users
        $stats['active'] = $this->where('is_active', true)->count();
        
        // Users by role
        $roles = ['admin', 'manager', 'user'];
        foreach ($roles as $role) {
            $stats['by_role'][$role] = $this->where('role', $role)
                                           ->where('is_active', true)
                                           ->count();
        }
        
        // Recent registrations (last 30 days)
        $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));
        $stats['recent_registrations'] = $this->where('created_at', '>=', $thirtyDaysAgo)
                                             ->count();
        
        return $stats;
    }

    /**
     * Search users
     * 
     * @param string $query Search query
     * @param int $limit Result limit
     * @return array Search results
     */
    public function search($query, $limit = 20)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (username LIKE :query 
                   OR email LIKE :query 
                   OR full_name LIKE :query)
                AND is_active = 1
                ORDER BY full_name
                LIMIT :limit";
        
        $params = [
            'query' => '%' . $query . '%',
            'limit' => $limit
        ];
        
        return $this->db->select($sql, $params);
    }

    /**
     * Get user profile with additional information
     * 
     * @param int $userId User ID
     * @return array|null User profile
     */
    public function getProfile($userId)
    {
        $user = $this->find($userId);
        
        if (!$user) {
            return null;
        }
        
        // Add additional profile information
        $user['total_quotes'] = $this->getUserQuotesCount($userId);
        $user['total_orders'] = $this->getUserOrdersCount($userId);
        $user['total_invoices'] = $this->getUserInvoicesCount($userId);
        
        return $user;
    }

    /**
     * Get user quotes count
     * 
     * @param int $userId User ID
     * @return int Quotes count
     */
    private function getUserQuotesCount($userId)
    {
        $sql = "SELECT COUNT(*) as count FROM quotes WHERE created_by = :user_id";
        $result = $this->db->selectOne($sql, ['user_id' => $userId]);
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Get user orders count
     * 
     * @param int $userId User ID
     * @return int Orders count
     */
    private function getUserOrdersCount($userId)
    {
        $sql = "SELECT COUNT(*) as count FROM sales_orders WHERE created_by = :user_id";
        $result = $this->db->selectOne($sql, ['user_id' => $userId]);
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Get user invoices count
     * 
     * @param int $userId User ID
     * @return int Invoices count
     */
    private function getUserInvoicesCount($userId)
    {
        $sql = "SELECT COUNT(*) as count FROM invoices WHERE created_by = :user_id";
        $result = $this->db->selectOne($sql, ['user_id' => $userId]);
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Validate user data
     * 
     * @param array $data User data
     * @param int $id User ID (for updates)
     * @return array Validation errors
     */
    public function validateUser(array $data, $id = null)
    {
        $errors = [];
        
        // Username validation
        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        } elseif (strlen($data['username']) < 3) {
            $errors['username'] = 'Username must be at least 3 characters';
        } elseif ($this->usernameExists($data['username'], $id)) {
            $errors['username'] = 'Username already exists';
        }
        
        // Email validation
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } elseif ($this->emailExists($data['email'], $id)) {
            $errors['email'] = 'Email already exists';
        }
        
        // Full name validation
        if (empty($data['full_name'])) {
            $errors['full_name'] = 'Full name is required';
        }
        
        // Role validation
        if (empty($data['role'])) {
            $errors['role'] = 'Role is required';
        } elseif (!in_array($data['role'], ['admin', 'manager', 'user'])) {
            $errors['role'] = 'Invalid role';
        }
        
        // Password validation (for new users or password changes)
        if (isset($data['password'])) {
            if (strlen($data['password']) < 8) {
                $errors['password'] = 'Password must be at least 8 characters';
            }
            
            if (isset($data['password_confirmation']) && 
                $data['password'] !== $data['password_confirmation']) {
                $errors['password_confirmation'] = 'Password confirmation does not match';
            }
        } elseif (!$id) {
            // Password required for new users
            $errors['password'] = 'Password is required';
        }
        
        return $errors;
    }

    /**
     * Get user permissions based on role
     * 
     * @param string $role User role
     * @return array Permissions
     */
    public function getPermissions($role)
    {
        $permissions = [
            'admin' => [
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'clients.view', 'clients.create', 'clients.edit', 'clients.delete',
                'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
                'products.view', 'products.create', 'products.edit', 'products.delete',
                'warehouses.view', 'warehouses.create', 'warehouses.edit', 'warehouses.delete',
                'quotes.view', 'quotes.create', 'quotes.edit', 'quotes.delete', 'quotes.approve',
                'orders.view', 'orders.create', 'orders.edit', 'orders.delete',
                'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete',
                'payments.view', 'payments.create', 'payments.edit', 'payments.delete',
                'stock.view', 'stock.adjust', 'stock.transfer',
                'reports.view', 'reports.export',
                'settings.view', 'settings.edit'
            ],
            'manager' => [
                'clients.view', 'clients.create', 'clients.edit',
                'suppliers.view', 'suppliers.create', 'suppliers.edit',
                'products.view', 'products.create', 'products.edit',
                'warehouses.view', 'warehouses.create', 'warehouses.edit',
                'quotes.view', 'quotes.create', 'quotes.edit', 'quotes.approve',
                'orders.view', 'orders.create', 'orders.edit',
                'invoices.view', 'invoices.create', 'invoices.edit',
                'payments.view', 'payments.create', 'payments.edit',
                'stock.view', 'stock.adjust',
                'reports.view', 'reports.export'
            ],
            'user' => [
                'clients.view',
                'suppliers.view',
                'products.view',
                'warehouses.view',
                'quotes.view', 'quotes.create', 'quotes.edit',
                'orders.view', 'orders.create', 'orders.edit',
                'invoices.view',
                'payments.view',
                'stock.view',
                'reports.view'
            ]
        ];
        
        return $permissions[$role] ?? [];
    }

    /**
     * Check if user has permission
     * 
     * @param int $userId User ID
     * @param string $permission Permission name
     * @return bool Has permission
     */
    public function hasPermission($userId, $permission)
    {
        $user = $this->find($userId);
        
        if (!$user || !$user['is_active']) {
            return false;
        }
        
        $permissions = $this->getPermissions($user['role']);
        
        return in_array($permission, $permissions);
    }
}
