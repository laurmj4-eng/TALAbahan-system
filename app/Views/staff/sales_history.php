<?= $this->include('theme/header') ?>
<?= $this->include('theme/sidebar') ?>

    <style>
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        h1 { 
            margin: 0; 
            font-weight: 700; 
            font-size: 2.2rem; 
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

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

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
                            <th>Items</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody id="salesBody">
                        <!-- Loading state -->
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 100px;">
                                <i class="fas fa-circle-notch fa-spin" style="font-size: 2rem; color: #a855f7;"></i>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        async function loadSalesHistory() {
            const tableBody = document.getElementById('salesBody');
            
            try {
                const response = await fetch('<?= site_url('staff/getSalesHistory') ?>');
                const result = await response.json();
                
                if (result.status === 'success') {
                    const sales = result.data || [];
                    
                    // Calculate stats
                    const totalTxns = sales.length;
                    const totalRevenue = sales.reduce((sum, s) => sum + parseFloat(s.total_amount || 0), 0);
                    const avgSale = totalTxns > 0 ? totalRevenue / totalTxns : 0;
                    
                    // Update stats
                    document.getElementById('totalTxns').innerText = totalTxns;
                    document.getElementById('totalRevenue').innerText = '₱' + parseFloat(totalRevenue).toLocaleString(undefined, {minimumFractionDigits: 2});
                    document.getElementById('avgSale').innerText = '₱' + parseFloat(avgSale).toLocaleString(undefined, {minimumFractionDigits: 2});
                    
                    // Render table
                    if (sales.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="4" class="empty-state">No sales history found.</td></tr>';
                    } else {
                        tableBody.innerHTML = '';
                        sales.forEach(sale => {
                            const row = `
                                <tr>
                                    <td class="txn-code">${sale.transaction_code}</td>
                                    <td class="datetime">${new Date(sale.created_at).toLocaleString()}</td>
                                    <td class="items" title="${sale.items}">${sale.items}</td>
                                    <td class="revenue">₱${parseFloat(sale.total_amount).toFixed(2)}</td>
                                </tr>
                            `;
                            tableBody.innerHTML += row;
                        });
                    }
                } else {
                    tableBody.innerHTML = '<tr><td colspan="4" class="empty-state">Failed to load sales history: ' + (result.message || 'Unknown error') + '</td></tr>';
                }
            } catch (error) {
                console.error('Error fetching sales history:', error);
                tableBody.innerHTML = '<tr><td colspan="4" class="empty-state">Failed to load sales history.</td></tr>';
            }
        }

        window.onload = loadSalesHistory;
    </script>

<?= $this->include('theme/footer') ?>
