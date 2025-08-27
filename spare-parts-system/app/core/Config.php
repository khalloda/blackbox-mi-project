<?php
/**
 * Configuration Manager
 * 
 * This class provides easy access to configuration values with:
 * - Dot notation support for nested values
 * - Caching for performance
 * - Environment-specific overrides
 * - Runtime configuration updates
 */

namespace App\Core;

class Config
{
    private static $config = [];
    private static $loaded = [];
    private static $cache = [];

    /**
     * Load configuration from file
     * 
     * @param string $file Configuration file name (without .php extension)
     * @return array Configuration data
     */
    public static function load($file)
    {
        if (isset(self::$loaded[$file])) {
            return self::$config[$file];
        }

        $configPath = __DIR__ . "/../config/{$file}.php";
        
        if (!file_exists($configPath)) {
            throw new \Exception("Configuration file '{$file}' not found");
        }

        $config = require $configPath;
        
        if (!is_array($config)) {
            throw new \Exception("Configuration file '{$file}' must return an array");
        }

        self::$config[$file] = $config;
        self::$loaded[$file] = true;

        return $config;
    }

    /**
     * Get configuration value using dot notation
     * 
     * @param string $key Configuration key (e.g., 'app.name' or 'database.default.host')
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value
     */
    public static function get($key, $default = null)
    {
        // Check cache first
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $keys = explode('.', $key);
        $file = array_shift($keys);

        // Load configuration file if not loaded
        if (!isset(self::$loaded[$file])) {
            try {
                self::load($file);
            } catch (\Exception $e) {
                return $default;
            }
        }

        // Navigate through nested array
        $value = self::$config[$file] ?? null;
        
        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                $value = $default;
                break;
            }
        }

        // Cache the result
        self::$cache[$key] = $value;

        return $value;
    }

    /**
     * Set configuration value using dot notation
     * 
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     */
    public static function set($key, $value)
    {
        $keys = explode('.', $key);
        $file = array_shift($keys);

        // Load configuration file if not loaded
        if (!isset(self::$loaded[$file])) {
            try {
                self::load($file);
            } catch (\Exception $e) {
                self::$config[$file] = [];
                self::$loaded[$file] = true;
            }
        }

        // Navigate and set value
        $config = &self::$config[$file];
        
        foreach ($keys as $k) {
            if (!isset($config[$k]) || !is_array($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }
        
        $config = $value;

        // Clear cache for this key
        unset(self::$cache[$key]);
        
        // Clear related cached keys
        $keyPrefix = $key . '.';
        foreach (array_keys(self::$cache) as $cachedKey) {
            if (strpos($cachedKey, $keyPrefix) === 0) {
                unset(self::$cache[$cachedKey]);
            }
        }
    }

    /**
     * Check if configuration key exists
     * 
     * @param string $key Configuration key
     * @return bool Key exists
     */
    public static function has($key)
    {
        $keys = explode('.', $key);
        $file = array_shift($keys);

        // Load configuration file if not loaded
        if (!isset(self::$loaded[$file])) {
            try {
                self::load($file);
            } catch (\Exception $e) {
                return false;
            }
        }

        // Navigate through nested array
        $value = self::$config[$file] ?? null;
        
        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all configuration for a file
     * 
     * @param string $file Configuration file name
     * @return array Configuration data
     */
    public static function all($file)
    {
        if (!isset(self::$loaded[$file])) {
            self::load($file);
        }

        return self::$config[$file] ?? [];
    }

    /**
     * Merge configuration arrays
     * 
     * @param string $file Configuration file name
     * @param array $config Configuration to merge
     */
    public static function merge($file, array $config)
    {
        if (!isset(self::$loaded[$file])) {
            self::load($file);
        }

        self::$config[$file] = array_merge_recursive(self::$config[$file] ?? [], $config);
        
        // Clear cache
        self::clearCache();
    }

    /**
     * Load environment-specific configuration
     * 
     * @param string $environment Environment name (development, testing, production)
     */
    public static function loadEnvironment($environment)
    {
        $envConfigPath = __DIR__ . "/../config/environments/{$environment}.php";
        
        if (file_exists($envConfigPath)) {
            $envConfig = require $envConfigPath;
            
            if (is_array($envConfig)) {
                foreach ($envConfig as $file => $config) {
                    self::merge($file, $config);
                }
            }
        }
    }

    /**
     * Get database configuration for specific connection
     * 
     * @param string $connection Connection name
     * @return array Database configuration
     */
    public static function database($connection = null)
    {
        $connection = $connection ?: self::get('database.default', 'default');
        return self::get("database.{$connection}", []);
    }

    /**
     * Get application environment
     * 
     * @return string Environment name
     */
    public static function environment()
    {
        return self::get('app.environment', 'production');
    }

    /**
     * Check if application is in debug mode
     * 
     * @return bool Debug mode enabled
     */
    public static function debug()
    {
        return self::get('app.debug', false);
    }

    /**
     * Get application timezone
     * 
     * @return string Timezone
     */
    public static function timezone()
    {
        return self::get('app.timezone', 'UTC');
    }

    /**
     * Get application locale
     * 
     * @return string Locale
     */
    public static function locale()
    {
        return self::get('app.locale', 'en');
    }

    /**
     * Check if feature is enabled
     * 
     * @param string $feature Feature name
     * @return bool Feature enabled
     */
    public static function feature($feature)
    {
        return self::get("app.features.{$feature}", false);
    }

    /**
     * Get business setting
     * 
     * @param string $setting Setting name
     * @param mixed $default Default value
     * @return mixed Setting value
     */
    public static function business($setting, $default = null)
    {
        return self::get("app.business.{$setting}", $default);
    }

    /**
     * Clear configuration cache
     */
    public static function clearCache()
    {
        self::$cache = [];
    }

    /**
     * Reload configuration file
     * 
     * @param string $file Configuration file name
     */
    public static function reload($file)
    {
        unset(self::$config[$file]);
        unset(self::$loaded[$file]);
        
        // Clear related cache entries
        $filePrefix = $file . '.';
        foreach (array_keys(self::$cache) as $key) {
            if (strpos($key, $filePrefix) === 0) {
                unset(self::$cache[$key]);
            }
        }
        
        self::load($file);
    }

    /**
     * Get configuration as JSON
     * 
     * @param string $file Configuration file name
     * @return string JSON representation
     */
    public static function toJson($file)
    {
        return json_encode(self::all($file), JSON_PRETTY_PRINT);
    }

    /**
     * Export configuration to file
     * 
     * @param string $file Configuration file name
     * @param string $exportPath Export file path
     * @return bool Success status
     */
    public static function export($file, $exportPath)
    {
        $config = self::all($file);
        $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
        
        return file_put_contents($exportPath, $content) !== false;
    }

    /**
     * Validate configuration structure
     * 
     * @param string $file Configuration file name
     * @param array $rules Validation rules
     * @return array Validation errors
     */
    public static function validate($file, array $rules)
    {
        $config = self::all($file);
        $errors = [];

        foreach ($rules as $key => $rule) {
            $value = self::getNestedValue($config, $key);
            
            if (isset($rule['required']) && $rule['required'] && $value === null) {
                $errors[] = "Required configuration '{$key}' is missing";
                continue;
            }
            
            if ($value !== null && isset($rule['type'])) {
                $type = gettype($value);
                if ($type !== $rule['type']) {
                    $errors[] = "Configuration '{$key}' must be of type {$rule['type']}, {$type} given";
                }
            }
            
            if ($value !== null && isset($rule['in']) && !in_array($value, $rule['in'])) {
                $errors[] = "Configuration '{$key}' must be one of: " . implode(', ', $rule['in']);
            }
        }

        return $errors;
    }

    /**
     * Get nested value from array using dot notation
     * 
     * @param array $array Array to search
     * @param string $key Dot notation key
     * @return mixed Value or null
     */
    private static function getNestedValue(array $array, $key)
    {
        $keys = explode('.', $key);
        $value = $array;
        
        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return null;
            }
        }
        
        return $value;
    }

    /**
     * Get all loaded configuration files
     * 
     * @return array Loaded files
     */
    public static function getLoadedFiles()
    {
        return array_keys(self::$loaded);
    }

    /**
     * Get cache statistics
     * 
     * @return array Cache statistics
     */
    public static function getCacheStats()
    {
        return [
            'cached_keys' => count(self::$cache),
            'loaded_files' => count(self::$loaded),
            'memory_usage' => memory_get_usage(),
        ];
    }
}
