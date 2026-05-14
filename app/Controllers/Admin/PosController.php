<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\SalesModel;
use App\Models\OrderModel;

class PosController extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $productModel = new ProductModel();
        $userModel = new \App\Models\UserModel();
        
        $data = [
            'title'     => 'TALAbahan Terminal',
            'username'  => session()->get('username'),
            'products'  => $productModel->findAll(),
            'customers' => $userModel->where('role', 'customer')->findAll(),
        ];

        return inertia('admin/Pos', $data);
    }

    // 1. Get products for the POS screen
    public function getProducts()
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $model = new ProductModel();
        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Products fetched.',
            'data'    => $model->findAll(),
            'token'   => csrf_hash(),
        ]);
    }

    // 2. Process checkout — writes to orders, order_items, AND sales_history
    public function checkout()
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        // Try to get data from POST first (FormData), then fall back to JSON
        $itemsRaw = $this->request->getPost('items');
        if ($itemsRaw) {
            $items = json_decode($itemsRaw, true);
            $customerName = $this->request->getPost('customer_name');
            $customerAlias = $this->request->getPost('customer_alias');
            $userId = $this->request->getPost('user_id');
            $voucherCode = $this->request->getPost('voucher_code');
            $payload = [
                'items' => $items,
                'customer_name' => $customerName,
                'customer_alias' => $customerAlias,
                'user_id' => $userId,
                'voucher_code' => $voucherCode
            ];
        } else {
            $payload = $this->request->getJSON(true);
        }

        if (!$payload || empty($payload['items'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Cart is empty.', 'token' => csrf_hash()])->setStatusCode(400);
        }

        $orderModel = new OrderModel();
        $payload['moved_by'] = (int) (session()->get('user_id') ?? 0);
        $result     = $orderModel->createFromCheckout($payload);
        if (! $result['ok']) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $result['message'],
                'token'   => csrf_hash(),
            ])->setStatusCode(400);
        }

        return $this->response->setJSON([
            'status'  => 'success', 
            'message' => 'Payment of ₱' . number_format((float) $result['total_amount'], 2)
                . ' processed! (TXN: ' . $result['transaction_code'] . ')',
            'data'    => [
                'transaction_code' => $result['transaction_code'],
                'total_amount'     => (float) $result['total_amount'],
                'discount'         => (float) ($result['discount'] ?? 0),
                'customer'         => $result['customer'] ?? 'Walk-in Customer',
                'items'            => $result['items'] ?? [],
                'date'             => $result['date'] ?? date('Y-m-d H:i:s'),
            ],
            'token'   => csrf_hash(),
        ]);
    }

    // 3. Fetch Sales History for the Dashboard
    public function getHistory()
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $startDate = $this->request->getGet('start_date');
        $endDate   = $this->request->getGet('end_date');
        $export    = $this->request->getGet('export');

        try {
            $salesModel = new SalesModel();
            // Join with orders to get customer details
            $query = $salesModel->db->table('sales_history s')
                ->select('s.*, o.customer_name, o.customer_alias, o.user_id')
                ->join('orders o', 'o.transaction_code = s.transaction_code', 'left')
                ->orderBy('s.created_at', 'DESC');

            if ($startDate && $endDate) {
                $query->where('DATE(s.created_at) >=', $startDate)
                      ->where('DATE(s.created_at) <=', $endDate);
            }

            $history = $query->get()->getResultArray();

            if ($export === 'csv') {
                return $this->exportToCSV($history);
            }

            if ($export === 'word') {
                return $this->exportToWord($history);
            }

            if ($export === 'pdf') {
                return $this->exportToPDF($history);
            }
            
            return $this->response->setJSON($history ?? []);
        } catch (\Exception $e) {
            log_message('error', 'getHistory error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to fetch history',
                'token'   => csrf_hash(),
            ])->setStatusCode(500);
        }
    }

    private function exportToCSV(array $data)
    {
        $filename = 'sales_report_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Transaction Code', 'Items Summary', 'Total Amount', 'Created At']);

        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['transaction_code'],
                $row['items_summary'],
                $row['total_amount'],
                $row['created_at']
            ]);
        }

        fclose($output);
        exit;
    }

    private function exportToWord(array $data)
    {
        $filename = 'sales_report_' . date('Ymd_His') . '.doc';
        header('Content-Type: application/vnd.ms-word');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        echo "<html>";
        echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">";
        echo "<body>";
        echo "<h1>Sales Report</h1>";
        echo "<p>Generated on: " . date('Y-m-d H:i:s') . "</p>";
        echo "<table border='1' style='width:100%; border-collapse: collapse;'>";
        echo "<thead>";
        echo "<tr style='background-color: #f2f2f2;'>";
        echo "<th>ID</th>";
        echo "<th>Transaction Code</th>";
        echo "<th>Items Summary</th>";
        echo "<th>Total Amount</th>";
        echo "<th>Created At</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        foreach ($data as $row) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['transaction_code'] . "</td>";
            echo "<td>" . $row['items_summary'] . "</td>";
            echo "<td>₱" . number_format($row['total_amount'], 2) . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
        echo "</body>";
        echo "</html>";
        exit;
    }

    private function exportToPDF(array $data)
    {
        // Check if Dompdf is available
        if (!class_exists('\Dompdf\Dompdf')) {
            // Fallback to a simple HTML print view if dompdf is not loaded
            echo "<script>window.print();</script>";
            return $this->exportToWord($data); // Or just fail gracefully
        }

        $dompdf = new \Dompdf\Dompdf();
        
        $html = "<html><head><style>
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            h1 { text-align: center; color: #333; }
            .footer { text-align: right; font-size: 12px; margin-top: 20px; }
        </style></head><body>";
        $html .= "<h1>Sales Report</h1>";
        $html .= "<p>Generated on: " . date('Y-m-d H:i:s') . "</p>";
        $html .= "<table><thead><tr>
            <th>ID</th>
            <th>Transaction Code</th>
            <th>Items Summary</th>
            <th>Total Amount</th>
            <th>Created At</th>
        </tr></thead><tbody>";

        foreach ($data as $row) {
            $html .= "<tr>
                <td>{$row['id']}</td>
                <td>{$row['transaction_code']}</td>
                <td>{$row['items_summary']}</td>
                <td>₱" . number_format($row['total_amount'], 2) . "</td>
                <td>{$row['created_at']}</td>
            </tr>";
        }

        $html .= "</tbody></table>";
        $html .= "<div class='footer'>TALAbahan System - Financial Ledger</div>";
        $html .= "</body></html>";

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        $filename = 'sales_report_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($filename, ["Attachment" => true]);
        exit;
    }
}