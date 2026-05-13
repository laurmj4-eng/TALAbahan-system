<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Chatbot extends BaseController
{
    /**
     * Handles the AI Chat request using OpenRouter API
     */
    public function process()
    {
        // 1. Base URL Check
        $url = "https://openrouter.ai/api/v1/chat/completions";
        
        // Handling JSON Input from JS Fetch
        $input = $this->request->getJSON(true) ?: $this->request->getPost();

        // 2. Data Fetcher & Role Security
        $statsContext = "";
        $userRole = session()->get('role');

        // ADMIN ROLE: Receives real-time stats and explicit identity injection
        if ($userRole === 'admin') {
            $stats = $this->_getRealTimeStats();
            $statsContext = "\n\nCRITICAL ADMIN CONTEXT (CONFIDENTIAL):"
                          . "\n- Current User: The Admin (MJ)"
                          . "\n- Identity Verified: YES"
                          . "\n- Today's Total Revenue: ₱" . number_format($stats['revenue'], 2)
                          . "\n- Total Orders: " . $stats['orders']
                          . "\n- Top Selling Item: " . $stats['top_item']
                          . "\n- Low Stock Alerts: " . $stats['low_stock']
                          . "\n\nINSTRUCTION FOR MJ ASSISTANT: You are speaking DIRECTLY to the Admin. "
                          . "Do NOT ask for credentials or verify identity; the session is already authenticated. "
                          . "Be direct, professional, and provide business insights using the data above. "
                          . "You are his elite system co-pilot.";
            
            $systemPrompt = "You are Mj, the intelligent assistant for the 'TALAbahan Seafood System'. "
                          . "This system was built by MJ. "
                          . "Your primary role is to help users manage seafood stocks (like mudcrabs, shrimp, and fish) and answer general questions. "
                          . "Be professional, helpful, and use emojis. You speak English and Tagalog. "
                          . "If asked about the system or its creator, always refer to it as the 'TALAbahan Seafood System' and mention it was built by MJ."
                          . $statsContext;
        } 
        // CUSTOMER ROLE: Welcoming persona and product information
        else if ($userRole === 'customer') {
            $products = $this->_getAvailableProducts();
            $productContext = "\n\nAVAILABLE PRODUCTS FOR CUSTOMERS:" . $products;
            
            $systemPrompt = "You are the TALAbahan Customer Assistant, a friendly and welcoming seafood expert. "
                          . "Your goal is to help customers browse our seafood selection, answer questions about our products, and provide a great shopping experience. "
                          . "Be warm, inviting, and helpful. Use emojis like 🦀, 🦐, 🐟. "
                          . "If asked about the system or its creator, mention it's the 'TALAbahan Seafood System' built by MJ. "
                          . "Encourage them to try our fresh seafood!"
                          . $productContext;
        }
        // STAFF/OTHER ROLES: General knowledge only
        else {
            $statsContext = "\n\nNote: You do not have access to financial data or real-time sales stats for this role.";
            $systemPrompt = "You are Mj, the intelligent assistant for the 'TALAbahan Seafood System'. "
                          . "This system was built by MJ. "
                          . "Your primary role is to help users manage seafood stocks (like mudcrabs, shrimp, and fish) and answer general questions. "
                          . "Be professional, helpful, and use emojis. You speak English and Tagalog. "
                          . "If asked about the system or its creator, always refer to it as the 'TALAbahan Seafood System' and mention it was built by MJ."
                          . $statsContext;
        }

        // 4. Model Verification (Common reliable models)
        $model = $input['modelName'] ?? "google/gemini-2.0-flash-001";
        $history = $input['history'] ?? [];
        
        // Inject system prompt if not present or as the first message
        $messages = [["role" => "system", "content" => $systemPrompt]];
        foreach ($history as $msg) {
            $messages[] = ["role" => $msg['role'], "content" => $msg['content']];
        }

        // 3. JSON Handling: Proper encoding of the request body
        $payload = json_encode([
            'model'    => $model,
            'messages' => $messages,
            'stream'   => true, // Enable streaming for the typing effect
        ]);

        $apiKey = env('OPENROUTER_API_KEY');

        // 4. Correct Headers for OpenRouter
        $headers = [
            "Authorization: Bearer " . $apiKey,
            "HTTP-Referer: " . base_url(),
            "X-Title: MJ Chatbot System",
            "Content-Type: application/json",
        ];

        // 5. CURL Debugging with Streaming Support
        if (ob_get_level() > 0) ob_end_clean(); // Clear CI4 output buffer
        
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); 

        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // We use write function
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

            // This function processes each chunk from OpenRouter
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data) {
                // Log raw chunks for debugging
                log_message('error', "[OpenRouter Chunk] " . $data);

                // If the response is a JSON error instead of a stream
                if (strpos($data, '{') === 0) {
                    $decoded = json_decode($data, true);
                    if (isset($decoded['error'])) {
                        $errorMsg = $decoded['error']['message'] ?? 'Unknown API Error';
                        echo "data: " . json_encode(['text' => "❌ API Error: " . $errorMsg]) . "\n\n";
                        return strlen($data);
                    }
                }

                $lines = explode("\n", $data);
                foreach ($lines as $line) {
                    if (strpos($line, 'data: ') === 0) {
                        $content = trim(substr($line, 6));
                        if ($content === '[DONE]') {
                            echo "data: [DONE]\n\n";
                        } else {
                            $decoded = json_decode($content, true);
                            $text = $decoded['choices'][0]['delta']['content'] ?? '';
                            if ($text !== '') {
                                echo "data: " . json_encode(['text' => $text]) . "\n\n";
                            }
                        }
                    }
                }

                // Flush the output to the browser immediately
                if (ob_get_level() > 0) ob_flush();
                flush();
                
                return strlen($data);
            });

            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                log_message('error', "[Chatbot CURL Error] " . $error);
                echo "data: " . json_encode(['text' => "❌ Connection Error: $error"]) . "\n\n";
            }

            curl_close($ch);
            exit; // Exit to prevent CI4 from adding extra output

        } catch (\Exception $e) {
            log_message('error', "[Chatbot Exception] " . $e->getMessage());
            
            echo "data: " . json_encode(['text' => "❌ System Error: " . $e->getMessage()]) . "\n\n";
            exit;
        }
    }

    /**
     * Wipes chat history activity and logs it
     */
    public function deleteHistory()
    {
        // 1. Session & Role Verification
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Unauthorized access.'
            ])->setStatusCode(403);
        }

        try {
            // 2. Activity Logging
            $logModel = new \App\Models\ActivityLogModel();
            $logModel->save([
                'user_id'       => session()->get('user_id'),
                'user_identity' => session()->get('username') ?? 'Admin',
                'role'          => 'admin',
                'event'         => '[Admin] cleared chat history',
                'ip_address'    => $this->request->getIPAddress(),
                'device'        => $this->request->getUserAgent()->getBrowser(),
                'status_code'   => 200
            ]);

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Memory wiped successfully.'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to log activity: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * PRIVATE: Fetches available products for the customer context
     */
    private function _getAvailableProducts()
    {
        try {
            $db = \Config\Database::connect();
            $products = $db->table('products')
                           ->select('name, price, stock, category')
                           ->where('is_available', 1)
                           ->where('stock >', 0)
                           ->get()->getResult();

            if (empty($products)) {
                return "\n- No products currently available.";
            }

            $list = "";
            foreach ($products as $p) {
                $list .= "\n- " . $p->name . " (" . $p->category . "): ₱" . number_format($p->price, 2) . " [Stock: " . $p->stock . "]";
            }
            return $list;
        } catch (\Exception $e) {
            log_message('error', "[Chatbot Products Error] " . $e->getMessage());
            return "\n- Product list unavailable.";
        }
    }

    /**
     * PRIVATE: Fetches real-time system stats for the AI Assistant
     * Only called if user is an Admin
     */
    private function _getRealTimeStats()
    {
        try {
            $db = \Config\Database::connect();
            $today = date('Y-m-d');

            // 1. Total Revenue Today
            $revenue = $db->table('sales_history')
                          ->selectSum('total_amount')
                          ->where('DATE(created_at)', $today)
                          ->get()->getRow()->total_amount ?? 0;

            // 2. Total Orders Today
            $orders = $db->table('sales_history')
                         ->where('DATE(created_at)', $today)
                         ->countAllResults();

            // 3. Top Selling Item Today
            $topItemRow = $db->table('order_items')
                          ->select('order_items.product_name, SUM(order_items.quantity) as qty')
                          ->join('orders', 'orders.id = order_items.order_id')
                          ->where('DATE(orders.created_at)', $today)
                          ->groupBy('order_items.product_name')
                          ->orderBy('qty', 'DESC')
                          ->limit(1)
                          ->get()->getRow();
            $topItem = $topItemRow->product_name ?? 'None yet';

            // 4. Low Stock Alerts (Stock < 10)
            $lowStockCount = $db->table('products')
                                ->where('stock <', 10)
                                ->countAllResults();

            return [
                'revenue'   => (float)$revenue,
                'orders'    => (int)$orders,
                'top_item'  => $topItem,
                'low_stock' => $lowStockCount > 0 ? "$lowStockCount items low" : "All stocks healthy"
            ];
        } catch (\Exception $e) {
            log_message('error', "[Chatbot Stats Error] " . $e->getMessage());
            return [
                'revenue'   => 0,
                'orders'    => 0,
                'top_item'  => 'Stats unavailable',
                'low_stock' => 'Stats unavailable'
            ];
        }
    }
}
