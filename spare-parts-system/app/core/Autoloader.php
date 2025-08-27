<?php
/**
 * PSR-4 Autoloader with Case-Insensitive Fallback
 * 
 * This autoloader implements PSR-4 standard with additional case-insensitive
 * fallback for better compatibility. It automatically loads classes based on
 * their namespace and directory structure.
 */

namespace App\Core;

class Autoloader
{
    private static $namespaces = [];
    private static $registered = false;

    /**
     * Register the autoloader
     */
    public static function register()
    {
        if (!self::$registered) {
            spl_autoload_register([__CLASS__, 'loadClass']);
            self::$registered = true;
            
            // Register default namespaces
            self::addNamespace('App\\', dirname(__DIR__) . '/');
        }
    }

    /**
     * Add a namespace mapping
     * 
     * @param string $namespace The namespace prefix
     * @param string $baseDir The base directory for the namespace
     */
    public static function addNamespace($namespace, $baseDir)
    {
        // Normalize namespace
        $namespace = trim($namespace, '\\') . '\\';
        
        // Normalize base directory
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        
        // Initialize namespace array if needed
        if (!isset(self::$namespaces[$namespace])) {
            self::$namespaces[$namespace] = [];
        }
        
        // Add base directory to namespace
        array_push(self::$namespaces[$namespace], $baseDir);
    }

    /**
     * Load a class file
     * 
     * @param string $className The fully qualified class name
     * @return bool True if the file was loaded, false otherwise
     */
    public static function loadClass($className)
    {
        // Work through registered namespaces
        foreach (self::$namespaces as $namespace => $baseDirs) {
            // Check if class uses this namespace
            if (strpos($className, $namespace) === 0) {
                // Get relative class name
                $relativeClass = substr($className, strlen($namespace));
                
                // Try each base directory
                foreach ($baseDirs as $baseDir) {
                    $file = self::getClassFile($baseDir, $relativeClass);
                    if ($file && self::requireFile($file)) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }

    /**
     * Get the file path for a class
     * 
     * @param string $baseDir Base directory
     * @param string $relativeClass Relative class name
     * @return string|false File path or false if not found
     */
    private static function getClassFile($baseDir, $relativeClass)
    {
        // Convert namespace separators to directory separators
        $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
        
        // Try exact case first
        if (file_exists($file)) {
            return $file;
        }
        
        // Try case-insensitive fallback
        return self::findCaseInsensitiveFile($baseDir, $relativeClass);
    }

    /**
     * Find file with case-insensitive matching
     * 
     * @param string $baseDir Base directory
     * @param string $relativeClass Relative class name
     * @return string|false File path or false if not found
     */
    private static function findCaseInsensitiveFile($baseDir, $relativeClass)
    {
        $parts = explode('\\', $relativeClass);
        $currentDir = $baseDir;
        
        foreach ($parts as $part) {
            if (!is_dir($currentDir)) {
                return false;
            }
            
            $found = false;
            $items = scandir($currentDir);
            
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                
                // For the last part, check for .php file
                if ($part === end($parts)) {
                    $itemWithoutExt = pathinfo($item, PATHINFO_FILENAME);
                    if (strcasecmp($itemWithoutExt, $part) === 0 && 
                        pathinfo($item, PATHINFO_EXTENSION) === 'php') {
                        return $currentDir . DIRECTORY_SEPARATOR . $item;
                    }
                } else {
                    // For directories, check case-insensitive match
                    if (is_dir($currentDir . DIRECTORY_SEPARATOR . $item) && 
                        strcasecmp($item, $part) === 0) {
                        $currentDir .= DIRECTORY_SEPARATOR . $item;
                        $found = true;
                        break;
                    }
                }
            }
            
            if (!$found && $part !== end($parts)) {
                return false;
            }
        }
        
        return false;
    }

    /**
     * Require a file if it exists
     * 
     * @param string $file File path
     * @return bool True if file was required, false otherwise
     */
    private static function requireFile($file)
    {
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }

    /**
     * Get all registered namespaces
     * 
     * @return array Array of registered namespaces
     */
    public static function getNamespaces()
    {
        return self::$namespaces;
    }

    /**
     * Clear all registered namespaces
     */
    public static function clearNamespaces()
    {
        self::$namespaces = [];
    }
}
