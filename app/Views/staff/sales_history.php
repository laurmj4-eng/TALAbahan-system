<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales History | Staff</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        
        body { 
            margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; 
            background: linear-gradient(120deg, #1e1b4b, #3b0764, #0f172a, #082f49);
            background-size: 300% 300%;
            animation: gradientBg 15s ease infinite;
            color: #ffffff; display: flex; height: 100vh; overflow: hidden; 
        }
        
        @keyframes gradientBg {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            border-radius: 20px;
        }

        .main-content { 
            flex: 1; 
            padding: 40px; 
            overflow-y: auto; 
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        h1 { 
            margin: 0; 
            font-weight: 700; 
            font-size: 2rem; 
            color: #fff; 
        }

        .btn {
            padding: 12px 24px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-back {
            background: rgba(59, 130, 246, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #93c5fd;
        }

        .btn-back:hover {
            background: rgba(59, 130, 246, 0.3);
        }

        /* Table */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        thead {
            background: rgba(255, 255, 255, 0.05);
        }

        th {
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.8);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .txn-code {
            color: #a855f7;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .revenue {
            color: #86efac;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .datetime {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .items {
            color: rgba(255, 255, 255, 0.8);
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255, 255, 255, 0.5);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin: 10px 0;
            color: #86efac;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <?= $this->include('theme/sidebar') ?>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="header">
            <h1>💰 Sales History</h1>
            <a href="<?= site_url('staff/dashboard') ?>" class="btn btn-back">← Back to Dashboard</a>
        </div>

        <!-- STATISTICS -->
        <div class="stats-grid">
            <div class="glass-panel stat-card">
                <div class="stat-label">Total Transactions</div>
                <div class="stat-value" id="totalTxns">0</div>
            </div>
            <div class="glass-panel stat-card">
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value" id="totalRevenue">₱0.00</div>
            </div>
            <div class="glass-panel stat-card">
                <div class="stat-label">Average Sale</div>
                <div class="stat-value" id="avgSale">₱0.00</div>
            </div>
        </div>

        <div class="glass-panel">
            <div class="table-responsive">
                <table id="salesTable">
                    <thead>
                        <tr>
                            <th>Transaction Code</th>
                            <th>Date & Time</th>
                            <th>Items Purchased</th>
                            <th>Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody id="salesBody">
                        <tr><td colspan="4" style="text-align: center; padding: 40px; color: rgba(255,255,255,0.4);">Loading sales history...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        async function loadSalesHistory() {
            try {
                const response = await fetch('<?= site_url('staff/getSalesHistory') ?>');
                const history = await response.json();
                renderSalesHistory(history);
                calculateStats(history);
            } catch (error) {
                console.error('Error loading sales history:', error);
                document.getElementById('salesBody').innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 40px; color: #f87171;">Error loading sales history</td></tr>';
            }
        }

        function renderSalesHistory(history) {
            const tbody = document.getElementById('salesBody');
            
            if (!history || history.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 40px; color: rgba(255,255,255,0.4);">No sales found</td></tr>';
                return;
            }

            tbody.innerHTML = history.map(record => {
                const dateObj = new Date(record.created_at);
                const formattedDate = dateObj.toLocaleDateString() + ' ' + dateObj.toLocaleTimeString();

                return `
                    <tr>
                        <td><strong class="txn-code">${record.transaction_code}</strong></td>
                        <td class="datetime">${formattedDate}</td>
                        <td class="items" title="${record.items_summary || 'N/A'}">${record.items_summary || 'N/A'}</td>
                        <td class="revenue">₱${parseFloat(record.total_amount || 0).toFixed(2)}</td>
                    </tr>
                `;
            }).join('');
        }

        function calculateStats(history) {
            if (!history || history.length === 0) {
                document.getElementById('totalTxns').textContent = '0';
                document.getElementById('totalRevenue').textContent = '₱0.00';
                document.getElementById('avgSale').textContent = '₱0.00';
                return;
            }

            const totalTxns = history.length;
            const totalRevenue = history.reduce((sum, h) => sum + parseFloat(h.total_amount || 0), 0);
            const avgSale = totalTxns > 0 ? totalRevenue / totalTxns : 0;

            document.getElementById('totalTxns').textContent = totalTxns;
            document.getElementById('totalRevenue').textContent = '₱' + totalRevenue.toFixed(2);
            document.getElementById('avgSale').textContent = '₱' + avgSale.toFixed(2);
        }

        // Load sales history when page loads
        document.addEventListener('DOMContentLoaded', loadSalesHistory);

        // Auto-refresh sales history every 60 seconds
        setInterval(loadSalesHistory, 60000);
    </script>

</body>
</html>
