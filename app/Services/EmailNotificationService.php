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
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #10B981; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .status-badge { display: inline-block; padding: 8px 16px; background: #DBEAFE; color: #1E40AF; border-radius: 4px; font-weight: bold; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Order Status Update</h1>
                </div>
                <div class="content">
                    <p>Hi ' . htmlspecialchars($customerName) . ',</p>
                    <p>Your order status has been updated!</p>
                    
                    <p><strong>Order Number:</strong> ' . htmlspecialchars($transactionCode) . '</p>
                    <p><strong>New Status:</strong> <span class="status-badge">' . htmlspecialchars($newStatus) . '</span></p>
                    
                    <p>Best regards,<br>TALAbahan System Team</p>
                </div>
                <div class="footer">
                    <p>This is an automated email. Please do not reply.</p>
                </div>
            </div>
        </body>
        </html>';
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
