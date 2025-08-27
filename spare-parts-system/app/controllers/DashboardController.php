<?php
/**
 * Dashboard Controller
 * 
 * Handles the main dashboard with system overview, statistics, and quick actions.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Show dashboard
     */
    public function index()
    {
        $this->requireAuth();
        
        $this->setTitle(__('dashboard.title'));
        
        // Get dashboard statistics
        $stats = $this->getDashboardStats();
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities();
        
        // Get low stock alerts
        $lowStockItems = $this->getLowStockItems();
        
        // Get pending items
        $pendingItems = $this->getPendingItems();
        
        return $this->view('dashboard/index', [
            'stats' => $stats,
            'recent_activities' => $recentActivities,
            'low_stock_items' => $lowStockItems,
            'pending_items' => $pendingItems,
            'flash_messages' => $this->getFlashMessages()
        ]);
    }
    
    /**
     * Get dashboard statistics
     * 
     * @return array Dashboard statistics
     */
    private function getDashboardStats()
    {
        $stats = [];
        $db = new Database();
        
        try {
            // Total clients
            $clientsResult = $db->selectOne("SELECT COUNT(*) as count FROM clients WHERE is_active = 1");
            $stats['total_clients'] = $clientsResult ? (int)$clientsResult['count'] : 0;
            
            // Total products
            $productsResult = $db->selectOne("SELECT COUNT(*) as count FROM products WHERE is_active = 1");
            $stats['total_products'] = $productsResult ? (int)$productsResult['count'] : 0;
            
            // Total stock value
            $stockValueResult = $db->selectOne("
                SELECT SUM(p.cost_price * s.quantity) as total_value 
                FROM products p 
                JOIN stock s ON p.id = s.product_id 
                WHERE p.is_active = 1
            ");
            $stats['total_stock_value'] = $stockValueResult ? (float)$stockValueResult['total_value'] : 0;
            
            // Pending orders
            $pendingOrdersResult = $db->selectOne("
                SELECT COUNT(*) as count 
                FROM sales_orders 
                WHERE status IN ('open', 'shipped')
            ");
            $stats['pending_orders'] = $pendingOrdersResult ? (int)$pendingOrdersResult['count'] : 0;
            
            // Low stock items
            $lowStockResult = $db->selectOne("
                SELECT COUNT(DISTINCT p.id) as count 
                FROM products p 
                LEFT JOIN stock s ON p.id = s.product_id 
                WHERE p.is_active = 1 
                AND (s.quantity IS NULL OR s.quantity <= p.min_stock_level)
            ");
            $stats['low_stock_items'] = $lowStockResult ? (int)$lowStockResult['count'] : 0;
            
            // Monthly sales (current month)
            $currentMonth = date('Y-m');
            $monthlySalesResult = $db->selectOne("
                SELECT SUM(total_amount) as total 
                FROM invoices 
                WHERE DATE_FORMAT(invoice_date, '%Y-%m') = :month 
                AND status != 'cancelled'
            ", ['month' => $currentMonth]);
            $stats['monthly_sales'] = $monthlySalesResult ? (float)$monthlySalesResult['total'] : 0;
            
            // Outstanding invoices
            $outstandingResult = $db->selectOne("
                SELECT SUM(balance_amount) as total 
                FROM invoices 
                WHERE status IN ('sent', 'overdue') 
                AND balance_amount > 0
            ");
            $stats['outstanding_amount'] = $outstandingResult ? (float)$outstandingResult['total'] : 0;
            
            // This month's quotes
            $monthlyQuotesResult = $db->selectOne("
                SELECT COUNT(*) as count 
                FROM quotes 
                WHERE DATE_FORMAT(quote_date, '%Y-%m') = :month
            ", ['month' => $currentMonth]);
            $stats['monthly_quotes'] = $monthlyQuotesResult ? (int)$monthlyQuotesResult['count'] : 0;
            
        } catch (\Exception $e) {
            // Log error and return empty stats
            error_log("Dashboard stats error: " . $e->getMessage());
            $stats = [
                'total_clients' => 0,
                'total_products' => 0,
                'total_stock_value' => 0,
                'pending_orders' => 0,
                'low_stock_items' => 0,
                'monthly_sales' => 0,
                'outstanding_amount' => 0,
                'monthly_quotes' => 0
            ];
        }
        
        return $stats;
    }
    
    /**
     * Get recent activities
     * 
     * @return array Recent activities
     */
    private function getRecentActivities()
    {
        $activities = [];
        $db = new Database();
        
        try {
            // Recent quotes
            $recentQuotes = $db->select("
                SELECT q.id, q.quote_number, q.quote_date, q.total_amount, q.status,
                       c.company_name, c.contact_person, c.first_name, c.last_name,
                       u.full_name as created_by_name
                FROM quotes q
                LEFT JOIN clients c ON q.client_id = c.id
                LEFT JOIN users u ON q.created_by = u.id
                ORDER BY q.created_at DESC
                LIMIT 5
            ");
            
            foreach ($recentQuotes as $quote) {
                $clientName = $quote['company_name'] ?: ($quote['first_name'] . ' ' . $quote['last_name']);
                $activities[] = [
                    'type' => 'quote',
                    'icon' => 'fas fa-file-alt',
                    'title' => "Quote #{$quote['quote_number']}",
                    'description' => "Created for {$clientName}",
                    'amount' => $quote['total_amount'],
                    'status' => $quote['status'],
                    'date' => $quote['quote_date'],
                    'user' => $quote['created_by_name'],
                    'url' => "/quotes/{$quote['id']}"
                ];
            }
            
            // Recent orders
            $recentOrders = $db->select("
                SELECT so.id, so.order_number, so.order_date, so.total_amount, so.status,
                       c.company_name, c.contact_person, c.first_name, c.last_name,
                       u.full_name as created_by_name
                FROM sales_orders so
                LEFT JOIN clients c ON so.client_id = c.id
                LEFT JOIN users u ON so.created_by = u.id
                ORDER BY so.created_at DESC
                LIMIT 5
            ");
            
            foreach ($recentOrders as $order) {
                $clientName = $order['company_name'] ?: ($order['first_name'] . ' ' . $order['last_name']);
                $activities[] = [
                    'type' => 'order',
                    'icon' => 'fas fa-shopping-cart',
                    'title' => "Order #{$order['order_number']}",
                    'description' => "Created for {$clientName}",
                    'amount' => $order['total_amount'],
                    'status' => $order['status'],
                    'date' => $order['order_date'],
                    'user' => $order['created_by_name'],
                    'url' => "/sales-orders/{$order['id']}"
                ];
            }
            
            // Recent invoices
            $recentInvoices = $db->select("
                SELECT i.id, i.invoice_number, i.invoice_date, i.total_amount, i.status,
                       c.company_name, c.contact_person, c.first_name, c.last_name,
                       u.full_name as created_by_name
                FROM invoices i
                LEFT JOIN clients c ON i.client_id = c.id
                LEFT JOIN users u ON i.created_by = u.id
                ORDER BY i.created_at DESC
                LIMIT 5
            ");
            
            foreach ($recentInvoices as $invoice) {
                $clientName = $invoice['company_name'] ?: ($invoice['first_name'] . ' ' . $invoice['last_name']);
                $activities[] = [
                    'type' => 'invoice',
                    'icon' => 'fas fa-file-invoice',
                    'title' => "Invoice #{$invoice['invoice_number']}",
                    'description' => "Created for {$clientName}",
                    'amount' => $invoice['total_amount'],
                    'status' => $invoice['status'],
                    'date' => $invoice['invoice_date'],
                    'user' => $invoice['created_by_name'],
                    'url' => "/invoices/{$invoice['id']}"
                ];
            }
            
            // Sort activities by date
            usort($activities, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            
            // Limit to 10 most recent
            $activities = array_slice($activities, 0, 10);
            
        } catch (\Exception $e) {
            error_log("Recent activities error: " . $e->getMessage());
        }
        
        return $activities;
    }
    
    /**
     * Get low stock items
     * 
     * @return array Low stock items
     */
    private function getLowStockItems()
    {
        $lowStockItems = [];
        $db = new Database();
        
        try {
            $lowStockItems = $db->select("
                SELECT p.id, p.code, p.name_en, p.name_ar, p.min_stock_level,
                       COALESCE(SUM(s.quantity), 0) as current_stock,
                       c.name_en as classification_name
                FROM products p
                LEFT JOIN stock s ON p.id = s.product_id
                LEFT JOIN classifications c ON p.classification_id = c.id
                WHERE p.is_active = 1
                GROUP BY p.id
                HAVING current_stock <= p.min_stock_level
                ORDER BY (current_stock / NULLIF(p.min_stock_level, 0)) ASC
                LIMIT 10
            ");
            
        } catch (\Exception $e) {
            error_log("Low stock items error: " . $e->getMessage());
        }
        
        return $lowStockItems;
    }
    
    /**
     * Get pending items that need attention
     * 
     * @return array Pending items
     */
    private function getPendingItems()
    {
        $pendingItems = [];
        $db = new Database();
        
        try {
            // Pending quotes (sent but not approved/rejected)
            $pendingQuotes = $db->select("
                SELECT 'quote' as type, id, quote_number as number, quote_date as date,
                       total_amount, 'Pending Approval' as description
                FROM quotes 
                WHERE status = 'sent'
                ORDER BY quote_date ASC
                LIMIT 5
            ");
            
            // Overdue invoices
            $overdueInvoices = $db->select("
                SELECT 'invoice' as type, id, invoice_number as number, due_date as date,
                       balance_amount as total_amount, 'Overdue Payment' as description
                FROM invoices 
                WHERE status = 'overdue' OR (status = 'sent' AND due_date < CURDATE())
                ORDER BY due_date ASC
                LIMIT 5
            ");
            
            // Orders ready to ship
            $readyToShip = $db->select("
                SELECT 'order' as type, id, order_number as number, order_date as date,
                       total_amount, 'Ready to Ship' as description
                FROM sales_orders 
                WHERE status = 'open'
                ORDER BY order_date ASC
                LIMIT 5
            ");
            
            $pendingItems = array_merge($pendingQuotes, $overdueInvoices, $readyToShip);
            
            // Sort by date
            usort($pendingItems, function($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });
            
            // Limit to 10 items
            $pendingItems = array_slice($pendingItems, 0, 10);
            
        } catch (\Exception $e) {
            error_log("Pending items error: " . $e->getMessage());
        }
        
        return $pendingItems;
    }
    
    /**
     * Get dashboard data for AJAX requests
     */
    public function getData()
    {
        $this->requireAuth();
        
        if (!$this->isAjax()) {
            return $this->redirect('/dashboard');
        }
        
        $type = $this->input('type', 'stats');
        
        switch ($type) {
            case 'stats':
                $data = $this->getDashboardStats();
                break;
                
            case 'activities':
                $data = $this->getRecentActivities();
                break;
                
            case 'low_stock':
                $data = $this->getLowStockItems();
                break;
                
            case 'pending':
                $data = $this->getPendingItems();
                break;
                
            default:
                return $this->error('Invalid data type');
        }
        
        return $this->success($data);
    }
    
    /**
     * Get sales chart data
     */
    public function getSalesChart()
    {
        $this->requireAuth();
        
        if (!$this->isAjax()) {
            return $this->redirect('/dashboard');
        }
        
        $db = new Database();
        
        try {
            // Get last 12 months sales data
            $salesData = $db->select("
                SELECT DATE_FORMAT(invoice_date, '%Y-%m') as month,
                       SUM(total_amount) as total_sales,
                       COUNT(*) as invoice_count
                FROM invoices 
                WHERE invoice_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                AND status != 'cancelled'
                GROUP BY DATE_FORMAT(invoice_date, '%Y-%m')
                ORDER BY month ASC
            ");
            
            // Format data for chart
            $chartData = [
                'labels' => [],
                'sales' => [],
                'invoices' => []
            ];
            
            foreach ($salesData as $data) {
                $chartData['labels'][] = date('M Y', strtotime($data['month'] . '-01'));
                $chartData['sales'][] = (float)$data['total_sales'];
                $chartData['invoices'][] = (int)$data['invoice_count'];
            }
            
            return $this->success($chartData);
            
        } catch (\Exception $e) {
            return $this->error('Failed to load chart data');
        }
    }
}
