<?php
/**
 * Database Abstraction Layer with PDO
 * 
 * This class provides a secure database abstraction layer using PDO with:
 * - Prepared statements for security
 * - Connection pooling
 * - Query logging
 * - Transaction support
 * - Multiple database support
 */

namespace App\Core;

use PDO;
use PDOException;
use Exception;

class Database
{
    private static $connections = [];
    private static $defaultConnection = 'default';
    private static $queryLog = [];
    private static $logQueries = false;

    private $pdo;
    private $connectionName;
    private $transactionLevel = 0;

    /**
     * Constructor
     * 
     * @param string $connectionName Connection name
     */
    public function __construct($connectionName = null)
    {
        $this->connectionName = $connectionName ?: self::$defaultConnection;
        $this->connect();
    }

    /**
     * Set database configuration
     * 
     * @param array $config Database configuration
     * @param string $connectionName Connection name
     */
    public static function setConfig($config, $connectionName = 'default')
    {
        self::$connections[$connectionName] = $config;
        
        if ($connectionName === 'default') {
            self::$defaultConnection = $connectionName;
        }
    }

    /**
     * Enable or disable query logging
     * 
     * @param bool $enable Enable logging
     */
    public static function enableQueryLog($enable = true)
    {
        self::$logQueries = $enable;
    }

    /**
     * Get query log
     * 
     * @return array Query log
     */
    public static function getQueryLog()
    {
        return self::$queryLog;
    }

    /**
     * Clear query log
     */
    public static function clearQueryLog()
    {
        self::$queryLog = [];
    }

    /**
     * Connect to database
     * 
     * @throws Exception If connection fails
     */
    private function connect()
    {
        if (!isset(self::$connections[$this->connectionName])) {
            throw new Exception("Database connection '{$this->connectionName}' not configured");
        }

        $config = self::$connections[$this->connectionName];
        
        $dsn = $this->buildDsn($config);
        $options = $this->getDefaultOptions();

        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Build DSN string
     * 
     * @param array $config Database configuration
     * @return string DSN string
     */
    private function buildDsn($config)
    {
        $driver = $config['driver'] ?? 'mysql';
        
        switch ($driver) {
            case 'mysql':
                return sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    $config['host'] ?? 'localhost',
                    $config['port'] ?? 3306,
                    $config['database'],
                    $config['charset'] ?? 'utf8mb4'
                );
                
            case 'pgsql':
                return sprintf(
                    'pgsql:host=%s;port=%s;dbname=%s',
                    $config['host'] ?? 'localhost',
                    $config['port'] ?? 5432,
                    $config['database']
                );
                
            case 'sqlite':
                return 'sqlite:' . $config['database'];
                
            default:
                throw new Exception("Unsupported database driver: {$driver}");
        }
    }

    /**
     * Get default PDO options
     * 
     * @return array PDO options
     */
    private function getDefaultOptions()
    {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_FOUND_ROWS => true,
        ];
    }

    /**
     * Execute a SELECT query
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array Query results
     */
    public function select($sql, $params = [])
    {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Execute a SELECT query and return first row
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array|null First row or null
     */
    public function selectOne($sql, $params = [])
    {
        $stmt = $this->execute($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Execute an INSERT query
     * 
     * @param string $table Table name
     * @param array $data Data to insert
     * @return int Last insert ID
     */
    public function insert($table, $data)
    {
        $columns = array_keys($data);
        $placeholders = array_map(function($col) { return ':' . $col; }, $columns);
        
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        $this->execute($sql, $data);
        return $this->pdo->lastInsertId();
    }

    /**
     * Execute an UPDATE query
     * 
     * @param string $table Table name
     * @param array $data Data to update
     * @param array $where WHERE conditions
     * @return int Number of affected rows
     */
    public function update($table, $data, $where)
    {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = $column . ' = :' . $column;
        }
        
        $whereParts = [];
        $whereParams = [];
        foreach ($where as $column => $value) {
            $whereKey = 'where_' . $column;
            $whereParts[] = $column . ' = :' . $whereKey;
            $whereParams[$whereKey] = $value;
        }
        
        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $table,
            implode(', ', $setParts),
            implode(' AND ', $whereParts)
        );
        
        $params = array_merge($data, $whereParams);
        $stmt = $this->execute($sql, $params);
        
        return $stmt->rowCount();
    }

    /**
     * Execute a DELETE query
     * 
     * @param string $table Table name
     * @param array $where WHERE conditions
     * @return int Number of affected rows
     */
    public function delete($table, $where)
    {
        $whereParts = [];
        foreach (array_keys($where) as $column) {
            $whereParts[] = $column . ' = :' . $column;
        }
        
        $sql = sprintf(
            'DELETE FROM %s WHERE %s',
            $table,
            implode(' AND ', $whereParts)
        );
        
        $stmt = $this->execute($sql, $where);
        return $stmt->rowCount();
    }

    /**
     * Execute a raw SQL query
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return PDOStatement Executed statement
     */
    public function execute($sql, $params = [])
    {
        $startTime = microtime(true);
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            if (self::$logQueries) {
                $this->logQuery($sql, $params, microtime(true) - $startTime);
            }
            
            return $stmt;
            
        } catch (PDOException $e) {
            if (self::$logQueries) {
                $this->logQuery($sql, $params, microtime(true) - $startTime, $e->getMessage());
            }
            
            throw new Exception("Database query failed: " . $e->getMessage());
        }
    }

    /**
     * Begin a transaction
     * 
     * @return bool Success status
     */
    public function beginTransaction()
    {
        if ($this->transactionLevel === 0) {
            $result = $this->pdo->beginTransaction();
        } else {
            $result = $this->pdo->exec("SAVEPOINT sp" . $this->transactionLevel);
        }
        
        $this->transactionLevel++;
        return $result;
    }

    /**
     * Commit a transaction
     * 
     * @return bool Success status
     */
    public function commit()
    {
        $this->transactionLevel--;
        
        if ($this->transactionLevel === 0) {
            return $this->pdo->commit();
        } else {
            return $this->pdo->exec("RELEASE SAVEPOINT sp" . $this->transactionLevel);
        }
    }

    /**
     * Rollback a transaction
     * 
     * @return bool Success status
     */
    public function rollback()
    {
        $this->transactionLevel--;
        
        if ($this->transactionLevel === 0) {
            return $this->pdo->rollback();
        } else {
            return $this->pdo->exec("ROLLBACK TO SAVEPOINT sp" . $this->transactionLevel);
        }
    }

    /**
     * Check if in transaction
     * 
     * @return bool Transaction status
     */
    public function inTransaction()
    {
        return $this->transactionLevel > 0;
    }

    /**
     * Get the PDO instance
     * 
     * @return PDO PDO instance
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Log a query
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @param float $time Execution time
     * @param string $error Error message if any
     */
    private function logQuery($sql, $params, $time, $error = null)
    {
        self::$queryLog[] = [
            'sql' => $sql,
            'params' => $params,
            'time' => $time,
            'error' => $error,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get table schema information
     * 
     * @param string $table Table name
     * @return array Table schema
     */
    public function getTableSchema($table)
    {
        $sql = "DESCRIBE {$table}";
        return $this->select($sql);
    }

    /**
     * Check if table exists
     * 
     * @param string $table Table name
     * @return bool Table exists
     */
    public function tableExists($table)
    {
        $sql = "SHOW TABLES LIKE :table";
        $result = $this->selectOne($sql, ['table' => $table]);
        return !empty($result);
    }

    /**
     * Get next sequence value
     * 
     * @param string $sequenceName Sequence name
     * @return int Next sequence value
     */
    public function getNextSequence($sequenceName)
    {
        $this->beginTransaction();
        
        try {
            // Get current sequence
            $sequence = $this->selectOne(
                'SELECT * FROM sequences WHERE sequence_name = :name FOR UPDATE',
                ['name' => $sequenceName]
            );
            
            if (!$sequence) {
                throw new Exception("Sequence '{$sequenceName}' not found");
            }
            
            // Increment sequence
            $nextValue = $sequence['current_value'] + 1;
            $this->update('sequences', 
                ['current_value' => $nextValue], 
                ['sequence_name' => $sequenceName]
            );
            
            $this->commit();
            
            // Format the sequence value
            $prefix = $sequence['prefix'] ?? '';
            $suffix = $sequence['suffix'] ?? '';
            $padding = $sequence['padding'] ?? 0;
            
            if ($padding > 0) {
                $formattedValue = $prefix . str_pad($nextValue, $padding, '0', STR_PAD_LEFT) . $suffix;
            } else {
                $formattedValue = $prefix . $nextValue . $suffix;
            }
            
            return $formattedValue;
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Paginate query results
     * 
     * @param string $sql Base SQL query
     * @param array $params Query parameters
     * @param int $page Page number (1-based)
     * @param int $perPage Items per page
     * @return array Pagination result
     */
    public function paginate($sql, $params = [], $page = 1, $perPage = 20)
    {
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as count_query";
        $totalResult = $this->selectOne($countSql, $params);
        $total = $totalResult['total'];
        
        // Calculate pagination
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        // Get paginated results
        $paginatedSql = $sql . " LIMIT {$perPage} OFFSET {$offset}";
        $data = $this->select($paginatedSql, $params);
        
        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_previous' => $page > 1,
                'has_next' => $page < $totalPages
            ]
        ];
    }
}
