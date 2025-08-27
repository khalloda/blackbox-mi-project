<?php
/**
 * Bilingual Language System with RTL Support
 * 
 * This class provides comprehensive language support with:
 * - English/Arabic language switching
 * - RTL (Right-to-Left) layout support
 * - Pluralization rules
 * - Parameter substitution
 * - Fallback language support
 * - Language detection
 */

namespace App\Core;

class Language
{
    private static $currentLanguage = 'en';
    private static $fallbackLanguage = 'en';
    private static $supportedLanguages = ['en', 'ar'];
    private static $translations = [];
    private static $rtlLanguages = ['ar', 'he', 'fa', 'ur'];
    private static $loaded = [];

    /**
     * Initialize language system
     * 
     * @param string $defaultLanguage Default language code
     */
    public static function init($defaultLanguage = 'en')
    {
        self::$currentLanguage = $defaultLanguage;
        self::detectLanguage();
        self::loadLanguage(self::$currentLanguage);
    }

    /**
     * Set current language
     * 
     * @param string $language Language code
     * @return bool Success status
     */
    public static function setLanguage($language)
    {
        if (!in_array($language, self::$supportedLanguages)) {
            return false;
        }
        
        self::$currentLanguage = $language;
        self::loadLanguage($language);
        
        // Store in session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['language'] = $language;
        
        return true;
    }

    /**
     * Get current language
     * 
     * @return string Current language code
     */
    public static function getCurrentLanguage()
    {
        return self::$currentLanguage;
    }

    /**
     * Get supported languages
     * 
     * @return array Supported language codes
     */
    public static function getSupportedLanguages()
    {
        return self::$supportedLanguages;
    }

    /**
     * Check if current language is RTL
     * 
     * @return bool Is RTL language
     */
    public static function isRTL()
    {
        return in_array(self::$currentLanguage, self::$rtlLanguages);
    }

    /**
     * Check if specific language is RTL
     * 
     * @param string $language Language code
     * @return bool Is RTL language
     */
    public static function isLanguageRTL($language)
    {
        return in_array($language, self::$rtlLanguages);
    }

    /**
     * Get text direction for current language
     * 
     * @return string 'rtl' or 'ltr'
     */
    public static function getDirection()
    {
        return self::isRTL() ? 'rtl' : 'ltr';
    }

    /**
     * Get text alignment for current language
     * 
     * @return string 'right' or 'left'
     */
    public static function getAlignment()
    {
        return self::isRTL() ? 'right' : 'left';
    }

    /**
     * Get opposite alignment for current language
     * 
     * @return string 'left' or 'right'
     */
    public static function getOppositeAlignment()
    {
        return self::isRTL() ? 'left' : 'right';
    }

    /**
     * Translate a key
     * 
     * @param string $key Translation key
     * @param array $params Parameters for substitution
     * @param string $language Optional language override
     * @return string Translated text
     */
    public static function get($key, $params = [], $language = null)
    {
        $language = $language ?: self::$currentLanguage;
        
        // Load language if not loaded
        if (!isset(self::$loaded[$language])) {
            self::loadLanguage($language);
        }
        
        // Get translation
        $translation = self::getTranslation($key, $language);
        
        // If not found, try fallback language
        if ($translation === $key && $language !== self::$fallbackLanguage) {
            if (!isset(self::$loaded[self::$fallbackLanguage])) {
                self::loadLanguage(self::$fallbackLanguage);
            }
            $translation = self::getTranslation($key, self::$fallbackLanguage);
        }
        
        // Substitute parameters
        if (!empty($params)) {
            $translation = self::substituteParameters($translation, $params);
        }
        
        return $translation;
    }

    /**
     * Translate with pluralization
     * 
     * @param string $key Translation key
     * @param int $count Count for pluralization
     * @param array $params Parameters for substitution
     * @param string $language Optional language override
     * @return string Translated text
     */
    public static function choice($key, $count, $params = [], $language = null)
    {
        $language = $language ?: self::$currentLanguage;
        
        // Get plural form
        $pluralKey = self::getPluralKey($key, $count, $language);
        
        // Add count to parameters
        $params['count'] = $count;
        
        return self::get($pluralKey, $params, $language);
    }

    /**
     * Load language file
     * 
     * @param string $language Language code
     */
    private static function loadLanguage($language)
    {
        if (isset(self::$loaded[$language])) {
            return;
        }
        
        $languageFile = __DIR__ . "/../lang/{$language}.php";
        
        if (file_exists($languageFile)) {
            self::$translations[$language] = include $languageFile;
        } else {
            self::$translations[$language] = [];
        }
        
        self::$loaded[$language] = true;
    }

    /**
     * Get translation from loaded translations
     * 
     * @param string $key Translation key
     * @param string $language Language code
     * @return string Translation or key if not found
     */
    private static function getTranslation($key, $language)
    {
        if (!isset(self::$translations[$language])) {
            return $key;
        }
        
        // Support nested keys with dot notation
        $keys = explode('.', $key);
        $translation = self::$translations[$language];
        
        foreach ($keys as $k) {
            if (is_array($translation) && isset($translation[$k])) {
                $translation = $translation[$k];
            } else {
                return $key;
            }
        }
        
        return is_string($translation) ? $translation : $key;
    }

    /**
     * Substitute parameters in translation
     * 
     * @param string $translation Translation text
     * @param array $params Parameters
     * @return string Text with substituted parameters
     */
    private static function substituteParameters($translation, $params)
    {
        foreach ($params as $key => $value) {
            $translation = str_replace([':' . $key, '{' . $key . '}'], $value, $translation);
        }
        
        return $translation;
    }

    /**
     * Get plural key based on count and language rules
     * 
     * @param string $key Base key
     * @param int $count Count
     * @param string $language Language code
     * @return string Plural key
     */
    private static function getPluralKey($key, $count, $language)
    {
        if ($language === 'ar') {
            // Arabic pluralization rules
            if ($count == 0) {
                return $key . '.zero';
            } elseif ($count == 1) {
                return $key . '.one';
            } elseif ($count == 2) {
                return $key . '.two';
            } elseif ($count >= 3 && $count <= 10) {
                return $key . '.few';
            } elseif ($count >= 11 && $count <= 99) {
                return $key . '.many';
            } else {
                return $key . '.other';
            }
        } else {
            // English pluralization rules
            if ($count == 1) {
                return $key . '.one';
            } else {
                return $key . '.other';
            }
        }
    }

    /**
     * Detect language from various sources
     */
    private static function detectLanguage()
    {
        // 1. Check session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['language']) && in_array($_SESSION['language'], self::$supportedLanguages)) {
            self::$currentLanguage = $_SESSION['language'];
            return;
        }
        
        // 2. Check URL parameter
        if (isset($_GET['lang']) && in_array($_GET['lang'], self::$supportedLanguages)) {
            self::$currentLanguage = $_GET['lang'];
            return;
        }
        
        // 3. Check Accept-Language header
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $acceptLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            
            foreach ($acceptLanguages as $lang) {
                $lang = trim(explode(';', $lang)[0]);
                $lang = substr($lang, 0, 2); // Get language code only
                
                if (in_array($lang, self::$supportedLanguages)) {
                    self::$currentLanguage = $lang;
                    return;
                }
            }
        }
        
        // 4. Use default language
        // Already set in init()
    }

    /**
     * Get language name in native script
     * 
     * @param string $language Language code
     * @return string Language name
     */
    public static function getLanguageName($language)
    {
        $names = [
            'en' => 'English',
            'ar' => 'العربية'
        ];
        
        return $names[$language] ?? $language;
    }

    /**
     * Get language switcher HTML
     * 
     * @param string $currentUrl Current URL
     * @return string HTML for language switcher
     */
    public static function getSwitcherHTML($currentUrl = null)
    {
        if (!$currentUrl) {
            $currentUrl = $_SERVER['REQUEST_URI'];
        }
        
        $html = '<div class="language-switcher">';
        
        foreach (self::$supportedLanguages as $lang) {
            $url = self::addLanguageToUrl($currentUrl, $lang);
            $name = self::getLanguageName($lang);
            $active = $lang === self::$currentLanguage ? ' active' : '';
            
            $html .= '<a href="' . htmlspecialchars($url) . '" class="lang-link' . $active . '" data-lang="' . $lang . '">';
            $html .= htmlspecialchars($name);
            $html .= '</a>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Add language parameter to URL
     * 
     * @param string $url URL
     * @param string $language Language code
     * @return string URL with language parameter
     */
    private static function addLanguageToUrl($url, $language)
    {
        $parsedUrl = parse_url($url);
        $query = [];
        
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $query);
        }
        
        $query['lang'] = $language;
        
        $newUrl = $parsedUrl['path'] ?? '/';
        if (!empty($query)) {
            $newUrl .= '?' . http_build_query($query);
        }
        
        return $newUrl;
    }

    /**
     * Format number according to language locale
     * 
     * @param float $number Number to format
     * @param int $decimals Number of decimal places
     * @return string Formatted number
     */
    public static function formatNumber($number, $decimals = 2)
    {
        if (self::$currentLanguage === 'ar') {
            // Arabic number formatting
            return number_format($number, $decimals, '.', ',');
        } else {
            // English number formatting
            return number_format($number, $decimals, '.', ',');
        }
    }

    /**
     * Format currency according to language locale
     * 
     * @param float $amount Amount to format
     * @param string $currency Currency code
     * @return string Formatted currency
     */
    public static function formatCurrency($amount, $currency = 'AED')
    {
        $formattedAmount = self::formatNumber($amount, 2);
        
        if (self::$currentLanguage === 'ar') {
            return $formattedAmount . ' ' . $currency;
        } else {
            return $currency . ' ' . $formattedAmount;
        }
    }

    /**
     * Format date according to language locale
     * 
     * @param string $date Date string
     * @param string $format Date format
     * @return string Formatted date
     */
    public static function formatDate($date, $format = null)
    {
        if (!$format) {
            $format = self::$currentLanguage === 'ar' ? 'd/m/Y' : 'Y-m-d';
        }
        
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        return date($format, $timestamp);
    }

    /**
     * Get CSS class for current language direction
     * 
     * @return string CSS class
     */
    public static function getDirectionClass()
    {
        return self::isRTL() ? 'rtl' : 'ltr';
    }

    /**
     * Get HTML attributes for current language
     * 
     * @return string HTML attributes
     */
    public static function getHtmlAttributes()
    {
        return 'lang="' . self::$currentLanguage . '" dir="' . self::getDirection() . '"';
    }
}

// Helper function for easy translation
if (!function_exists('__')) {
    /**
     * Translate a key (shorthand function)
     * 
     * @param string $key Translation key
     * @param array $params Parameters for substitution
     * @return string Translated text
     */
    function __($key, $params = [])
    {
        return \App\Core\Language::get($key, $params);
    }
}

if (!function_exists('_n')) {
    /**
     * Translate with pluralization (shorthand function)
     * 
     * @param string $key Translation key
     * @param int $count Count for pluralization
     * @param array $params Parameters for substitution
     * @return string Translated text
     */
    function _n($key, $count, $params = [])
    {
        return \App\Core\Language::choice($key, $count, $params);
    }
}
