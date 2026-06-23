<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Traits\PosOperationsTrait;
use App\Models\ProductModel;
use App\Models\SalesModel;

class PosController extends BaseController
{
    use PosOperationsTrait;

    public function index()
    {
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

    public function getHistory()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate   = $this->request->getGet('end_date');
        $export    = $this->request->getGet('export');

        try {
            $salesModel = new SalesModel();
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
        if (!class_exists('\Dompdf\Dompdf')) {
            echo "<script>window.print();</script>";
            return $this->exportToWord($data);
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
