<?php

namespace App\Services;

use Config\Email;
use CodeIgniter\Email\Email as CIEmail;
use Exception;

class EmailNotificationService
{
    protected $email;
    protected $config;

    public function __construct()
    {
        $this->config = new Email();
        $this->email = \Config\Services::email();
        $this->setupEmailConfig();
    }

    protected function setupEmailConfig(): void
    {
        $this->config->fromEmail = env('email.fromEmail', 'no-reply@talabahan-system.com');
        $this->config->fromName = env('email.fromName', 'TALAbahan System');
        $this->config->protocol = env('email.protocol', 'mail');
        $this->config->SMTPHost = env('email.SMTPHost', '');
        $this->config->SMTPUser = env('email.SMTPUser', '');
        $this->config->SMTPPass = env('email.SMTPPass', '');
        $this->config->SMTPPort = (int)env('email.SMTPPort', 587);
        $this->config->SMTPCrypto = env('email.SMTPCrypto', 'tls');
        $this->config->mailType = 'html';
        $this->config->charset = 'UTF-8';
        $this->config->wordWrap = true;

        $this->email->initialize($this->config);
    }

    public function sendOrderConfirmation(string $toEmail, string $customerName, string $transactionCode, float $totalAmount): bool
    {
        try {
            $this->email->setTo($toEmail);
            $this->email->setSubject('Order Confirmation - ' . $transactionCode);
            
            $message = $this->getOrderConfirmationTemplate($customerName, $transactionCode, $totalAmount);
            $this->email->setMessage($message);

            return $this->email->send();
        } catch (Exception $e) {
            log_message('error', '[EmailNotificationService] Failed to send order confirmation: ' . $e->getMessage());
            return false;
        }
    }

    public function sendOrderStatusUpdate(string $toEmail, string $customerName, string $transactionCode, string $newStatus): bool
    {
        try {
            $this->email->setTo($toEmail);
            $this->email->setSubject('Order Status Update - ' . $transactionCode);
            
            $message = $this->getOrderStatusUpdateTemplate($customerName, $transactionCode, $newStatus);
            $this->email->setMessage($message);

            return $this->email->send();
        } catch (Exception $e) {
            log_message('error', '[EmailNotificationService] Failed to send status update: ' . $e->getMessage());
            return false;
        }
    }

    public function sendLowStockAlert(string $toEmail, array $lowStockProducts): bool
    {
        try {
            $this->email->setTo($toEmail);
            $this->email->setSubject('⚠️ Low Stock Alert');
            
            $message = $this->getLowStockAlertTemplate($lowStockProducts);
            $this->email->setMessage($message);

            return $this->email->send();
        } catch (Exception $e) {
            log_message('error', '[EmailNotificationService] Failed to send low stock alert: ' . $e->getMessage());
            return false;
        }
    }

    protected function getOrderConfirmationTemplate(string $customerName, string $transactionCode, float $totalAmount): string
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4F46E5; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .order-details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Thank You for Your Order!</h1>
                </div>
                <div class="content">
                    <p>Hi ' . htmlspecialchars($customerName) . ',</p>
                    <p>Your order has been placed successfully!</p>
                    
                    <div class="order-details">
                        <p><strong>Order Number:</strong> ' . htmlspecialchars($transactionCode) . '</p>
                        <p><strong>Total Amount:</strong> ₱' . number_format($totalAmount, 2) . '</p>
                    </div>
                    
                    <p>We will notify you when your order is shipped.</p>
                    <p>Best regards,<br>TALAbahan System Team</p>
                </div>
                <div class="footer">
                    <p>This is an automated email. Please do not reply.</p>
                </div>
            </div>
        </body>
        </html>';
    }

    protected function getOrderStatusUpdateTemplate(string $customerName, string $transactionCode, string $newStatus): string
    {
        $statusColors = [
            'Pending'    => '#6B7280',
            'Processing' => '#3B82F6',
            'Shipped'    => '#F59E0B',
            'Completed'  => '#10B981',
            'Cancelled'  => '#EF4444',
            'Refunded'   => '#8B5CF6'
        ];
        
        $color = $statusColors[$newStatus] ?? '#4F46E5';
        $emoji = '';
        $desc  = '';

        switch ($newStatus) {
            case 'Processing': $emoji = '👨‍🍳'; $desc = 'We are now preparing your seafood delight!'; break;
            case 'Shipped':    $emoji = '🚚'; $desc = 'Your order is on the way! Get ready to dive in.'; break;
            case 'Completed':  $emoji = '✅'; $desc = 'Order delivered! We hope you enjoy your meal.'; break;
            case 'Cancelled':  $emoji = '❌'; $desc = 'Your order has been cancelled.'; break;
            default:           $emoji = '🔔'; $desc = 'Your order status has been updated.'; break;
        }

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; border: 1px solid #eee; border-radius: 10px; overflow: hidden; }
                .header { background: #1a1a1a; color: white; padding: 40px 20px; text-align: center; }
                .status-badge { display: inline-block; padding: 8px 20px; border-radius: 20px; color: white; font-weight: bold; text-transform: uppercase; margin-top: 15px; }
                .content { padding: 30px; background: #ffffff; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #999; background: #f9f9f9; }
                .btn { display: inline-block; padding: 12px 25px; background: #1a1a1a; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <div style='font-size: 24px; font-weight: bold; letter-spacing: 2px;'>TALAbahan</div>
                    <div class='status-badge' style='background: {$color};'>{$newStatus} {$emoji}</div>
                </div>
                <div class='content'>
                    <h2>Hi {$customerName},</h2>
                    <p style='font-size: 16px; color: #555;'>{$desc}</p>
                    <div style='background: #f8fafc; border-left: 4px solid #1a1a1a; padding: 20px; margin: 25px 0;'>
                        <div style='font-size: 14px; color: #64748b;'>Transaction Code</div>
                        <div style='font-size: 18px; font-weight: bold; font-family: monospace;'>{$transactionCode}</div>
                    </div>
                    <p>You can track your order progress in your customer dashboard.</p>
                    <a href='https://mjtalabahan.page.gd/customer/dashboard' class='btn'>View Order Details</a>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " TALAbahan Seafood & Grill. All rights reserved.</p>
                    <p>This is an automated message, please do not reply.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    protected function getLowStockAlertTemplate(array $lowStockProducts): string
    {
        $productList = '';
        foreach ($lowStockProducts as $product) {
            $productList .= '<li>' . htmlspecialchars($product['name']) . ' - ' . (float)$product['current_stock'] . ' units remaining</li>';
        }

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #EF4444; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .product-list { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>⚠️ Low Stock Alert</h1>
                </div>
                <div class="content">
                    <p>The following products are running low on stock:</p>
                    
                    <div class="product-list">
                        <ul>' . $productList . '</ul>
                    </div>
                    
                    <p>Please restock soon!</p>
                    <p>Best regards,<br>TALAbahan System Team</p>
                </div>
                <div class="footer">
                    <p>This is an automated email. Please do not reply.</p>
                </div>
            </div>
        </body>
        </html>';
    }
}
