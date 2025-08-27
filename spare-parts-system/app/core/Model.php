<?php
/**
 * Base Model Class
 * 
 * This class provides a base for all models with:
 * - Database abstraction
 * - CRUD operations
 * - Validation
 * - Relationships
 * - Query building
 * - Caching support
 */

namespace App\Core;

use App\Core\Database;

abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $hidden = [];
    protected $casts = [];
    protected $dates = ['created_at', 'updated_at'];
    protected $timestamps = true;
    protected $softDeletes = false;
    protected $perPage = 20;
    
    // Query builder properties
    protected $query = [];
    protected $bindings = [];
    
    // Validation rules
    protected $rules = [];
    protected $messages = [];
    
    // Relationships
    protected $relationships = [];
    
    // Cache settings
    protected $cacheable = false;
    protected $cachePrefix = '';
    protected $cacheTtl = 3600;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = new Database();
        
        if (!$this->table) {
            $this->table = $this->getTableName();
        }
        
        if (!$this->cachePrefix) {
            $this->cachePrefix = $this->table;
        }
        
        $this->resetQuery();
    }

    /**
     * Get table name from class name
     * 
     * @return string Table name
     */
    protected function getTableName()
    {
        $className = (new \ReflectionClass($this))->getShortName();
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $className)) . 's';
    }

    /**
     * Find record by ID
     * 
     * @param int $id Record ID
     * @return array|null Record data
     */
    public function find($id)
    {
        $cacheKey = $this->getCacheKey('find', $id);
        
        if ($this->cacheable && $cached = $this->getFromCache($cacheKey)) {
            return $cached;
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $params = ['id' => $id];
        
        if ($this->softDeletes) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $result = $this->db->selectOne($sql, $params);
        
        if ($result) {
            $result = $this->castAttributes($result);
            
            if ($this->cacheable) {
                $this->putInCache($cacheKey, $result);
            }
        }
        
        return $result;
    }

    /**
     * Find record by field
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @return array|null Record data
     */
    public function findBy($field, $value)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$field} = :value";
        $params = ['value' => $value];
        
        if ($this->softDeletes) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $result = $this->db->selectOne($sql, $params);
        
        if ($result) {
            $result = $this->castAttributes($result);
        }
        
        return $result;
    }

    /**
     * Get all records
     * 
     * @return array Records
     */
    public function all()
    {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($this->softDeletes) {
            $sql .= " WHERE deleted_at IS NULL";
        }
        
        $sql .= " ORDER BY {$this->primaryKey} DESC";
        
        $results = $this->db->select($sql);
        
        return array_map([$this, 'castAttributes'], $results);
    }

    /**
     * Create new record
     * 
     * @param array $data Record data
     * @return int|false Insert ID or false on failure
     */
    public function create(array $data)
    {
        // Validate data
        if (!$this->validate($data)) {
            return false;
        }
        
        // Filter fillable fields
        $data = $this->filterFillable($data);
        
        // Add timestamps
        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        try {
            $id = $this->db->insert($this->table, $data);
            
            // Clear cache
            if ($this->cacheable) {
                $this->clearCache();
            }
            
            return $id;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update record
     * 
     * @param int $id Record ID
     * @param array $data Update data
     * @return bool Success status
     */
    public function update($id, array $data)
    {
        // Validate data
        if (!$this->validate($data, $id)) {
            return false;
        }
        
        // Filter fillable fields
        $data = $this->filterFillable($data);
        
        // Add timestamp
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        try {
            $affected = $this->db->update($this->table, $data, [$this->primaryKey => $id]);
            
            // Clear cache
            if ($this->cacheable) {
                $this->clearCache();
            }
            
            return $affected > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete record
     * 
     * @param int $id Record ID
     * @return bool Success status
     */
    public function delete($id)
    {
        try {
            if ($this->softDeletes) {
                // Soft delete
                $data = ['deleted_at' => date('Y-m-d H:i:s')];
                $affected = $this->db->update($this->table, $data, [$this->primaryKey => $id]);
            } else {
                // Hard delete
                $affected = $this->db->delete($this->table, [$this->primaryKey => $id]);
            }
            
            // Clear cache
            if ($this->cacheable) {
                $this->clearCache();
            }
            
            return $affected > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get paginated results
     * 
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Pagination result
     */
    public function paginate($page = 1, $perPage = null)
    {
        $perPage = $perPage ?: $this->perPage;
        
        $sql = $this->buildSelectQuery();
        
        return $this->db->paginate($sql, $this->bindings, $page, $perPage);
    }

    /**
     * Count records
     * 
     * @return int Record count
     */
    public function count()
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if ($this->softDeletes) {
            $sql .= " WHERE deleted_at IS NULL";
        }
        
        $result = $this->db->selectOne($sql);
        
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Query builder - WHERE clause
     * 
     * @param string $field Field name
     * @param mixed $operator Operator or value
     * @param mixed $value Value (if operator provided)
     * @return self
     */
    public function where($field, $operator = null, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->query['where'][] = [
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'AND'
        ];
        
        return $this;
    }

    /**
     * Query builder - OR WHERE clause
     * 
     * @param string $field Field name
     * @param mixed $operator Operator or value
     * @param mixed $value Value (if operator provided)
     * @return self
     */
    public function orWhere($field, $operator = null, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->query['where'][] = [
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'OR'
        ];
        
        return $this;
    }

    /**
     * Query builder - ORDER BY clause
     * 
     * @param string $field Field name
     * @param string $direction Sort direction
     * @return self
     */
    public function orderBy($field, $direction = 'ASC')
    {
        $this->query['orderBy'][] = [
            'field' => $field,
            'direction' => strtoupper($direction)
        ];
        
        return $this;
    }

    /**
     * Query builder - LIMIT clause
     * 
     * @param int $limit Limit
     * @param int $offset Offset
     * @return self
     */
    public function limit($limit, $offset = 0)
    {
        $this->query['limit'] = $limit;
        $this->query['offset'] = $offset;
        
        return $this;
    }

    /**
     * Execute query and get results
     * 
     * @return array Results
     */
    public function get()
    {
        $sql = $this->buildSelectQuery();
        $results = $this->db->select($sql, $this->bindings);
        
        $this->resetQuery();
        
        return array_map([$this, 'castAttributes'], $results);
    }

    /**
     * Execute query and get first result
     * 
     * @return array|null First result
     */
    public function first()
    {
        $this->limit(1);
        $results = $this->get();
        
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Build SELECT query from query builder
     * 
     * @return string SQL query
     */
    protected function buildSelectQuery()
    {
        $sql = "SELECT * FROM {$this->table}";
        $this->bindings = [];
        
        // WHERE clauses
        if (!empty($this->query['where'])) {
            $whereClauses = [];
            $bindingIndex = 0;
            
            foreach ($this->query['where'] as $i => $where) {
                $bindingKey = 'binding_' . $bindingIndex++;
                $clause = $where['field'] . ' ' . $where['operator'] . ' :' . $bindingKey;
                
                if ($i > 0) {
                    $clause = $where['boolean'] . ' ' . $clause;
                }
                
                $whereClauses[] = $clause;
                $this->bindings[$bindingKey] = $where['value'];
            }
            
            $sql .= ' WHERE ' . implode(' ', $whereClauses);
        }
        
        // Soft deletes
        if ($this->softDeletes) {
            $sql .= empty($this->query['where']) ? ' WHERE' : ' AND';
            $sql .= ' deleted_at IS NULL';
        }
        
        // ORDER BY
        if (!empty($this->query['orderBy'])) {
            $orderClauses = [];
            foreach ($this->query['orderBy'] as $order) {
                $orderClauses[] = $order['field'] . ' ' . $order['direction'];
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderClauses);
        }
        
        // LIMIT
        if (isset($this->query['limit'])) {
            $sql .= ' LIMIT ' . $this->query['limit'];
            if (isset($this->query['offset']) && $this->query['offset'] > 0) {
                $sql .= ' OFFSET ' . $this->query['offset'];
            }
        }
        
        return $sql;
    }

    /**
     * Reset query builder
     */
    protected function resetQuery()
    {
        $this->query = [
            'where' => [],
            'orderBy' => [],
            'limit' => null,
            'offset' => null
        ];
        $this->bindings = [];
    }

    /**
     * Filter fillable fields
     * 
     * @param array $data Input data
     * @return array Filtered data
     */
    protected function filterFillable(array $data)
    {
        if (empty($this->fillable)) {
            // Remove guarded fields
            return array_diff_key($data, array_flip($this->guarded));
        }
        
        // Keep only fillable fields
        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Cast attributes according to $casts property
     * 
     * @param array $attributes Attributes
     * @return array Casted attributes
     */
    protected function castAttributes(array $attributes)
    {
        foreach ($this->casts as $key => $type) {
            if (isset($attributes[$key])) {
                switch ($type) {
                    case 'int':
                    case 'integer':
                        $attributes[$key] = (int)$attributes[$key];
                        break;
                    case 'float':
                    case 'double':
                        $attributes[$key] = (float)$attributes[$key];
                        break;
                    case 'bool':
                    case 'boolean':
                        $attributes[$key] = (bool)$attributes[$key];
                        break;
                    case 'string':
                        $attributes[$key] = (string)$attributes[$key];
                        break;
                    case 'array':
                    case 'json':
                        $attributes[$key] = json_decode($attributes[$key], true);
                        break;
                    case 'date':
                        $attributes[$key] = date('Y-m-d', strtotime($attributes[$key]));
                        break;
                    case 'datetime':
                        $attributes[$key] = date('Y-m-d H:i:s', strtotime($attributes[$key]));
                        break;
                }
            }
        }
        
        // Remove hidden fields
        if (!empty($this->hidden)) {
            $attributes = array_diff_key($attributes, array_flip($this->hidden));
        }
        
        return $attributes;
    }

    /**
     * Validate data
     * 
     * @param array $data Data to validate
     * @param int $id Record ID (for updates)
     * @return bool Validation passed
     */
    protected function validate(array $data, $id = null)
    {
        if (empty($this->rules)) {
            return true;
        }
        
        // Simple validation implementation
        foreach ($this->rules as $field => $rules) {
            $rules = is_string($rules) ? explode('|', $rules) : $rules;
            
            foreach ($rules as $rule) {
                if (!$this->validateRule($field, $data[$field] ?? null, $rule, $id)) {
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
     * Validate single rule
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string $rule Validation rule
     * @param int $id Record ID
     * @return bool Rule passed
     */
    protected function validateRule($field, $value, $rule, $id = null)
    {
        switch ($rule) {
            case 'required':
                return !empty($value);
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'numeric':
                return is_numeric($value);
            case 'integer':
                return filter_var($value, FILTER_VALIDATE_INT) !== false;
            default:
                if (strpos($rule, 'unique:') === 0) {
                    $table = substr($rule, 7);
                    $existing = $this->db->selectOne(
                        "SELECT {$this->primaryKey} FROM {$table} WHERE {$field} = :value" . 
                        ($id ? " AND {$this->primaryKey} != :id" : ""),
                        $id ? ['value' => $value, 'id' => $id] : ['value' => $value]
                    );
                    return !$existing;
                }
                return true;
        }
    }

    /**
     * Get cache key
     * 
     * @param string $method Method name
     * @param mixed $params Parameters
     * @return string Cache key
     */
    protected function getCacheKey($method, ...$params)
    {
        return $this->cachePrefix . ':' . $method . ':' . md5(serialize($params));
    }

    /**
     * Get from cache
     * 
     * @param string $key Cache key
     * @return mixed Cached value or null
     */
    protected function getFromCache($key)
    {
        // Simple file-based cache implementation
        $cacheFile = __DIR__ . '/../../cache/' . md5($key) . '.cache';
        
        if (file_exists($cacheFile)) {
            $data = unserialize(file_get_contents($cacheFile));
            
            if ($data['expires'] > time()) {
                return $data['value'];
            } else {
                unlink($cacheFile);
            }
        }
        
        return null;
    }

    /**
     * Put in cache
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache
     */
    protected function putInCache($key, $value)
    {
        $cacheDir = __DIR__ . '/../../cache/';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $cacheFile = $cacheDir . md5($key) . '.cache';
        $data = [
            'value' => $value,
            'expires' => time() + $this->cacheTtl
        ];
        
        file_put_contents($cacheFile, serialize($data));
    }

    /**
     * Clear cache
     */
    protected function clearCache()
    {
        $cacheDir = __DIR__ . '/../../cache/';
        $pattern = $cacheDir . '*' . $this->cachePrefix . '*.cache';
        
        foreach (glob($pattern) as $file) {
            unlink($file);
        }
    }

    /**
     * Get validation errors
     * 
     * @return array Validation errors
     */
    public function getErrors()
    {
        return $this->errors ?? [];
    }

    /**
     * Magic method to handle dynamic methods
     * 
     * @param string $method Method name
     * @param array $args Arguments
     * @return mixed
     */
    public function __call($method, $args)
    {
        // Handle findByField methods
        if (strpos($method, 'findBy') === 0) {
            $field = strtolower(substr($method, 6));
            return $this->findBy($field, $args[0] ?? null);
        }
        
        throw new \BadMethodCallException("Method {$method} does not exist");
    }
}
