<div class="card glass-panel">
    <h2 style="font-size: 2.2rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px;">Financial Ledger</h2>
    <p style="color: rgba(255,255,255,0.6); margin-top: 15px; margin-bottom: 30px;">Real-time transaction logs and revenue records.</p>
    
    <div class="table-responsive glass-panel">
        <table class="premium-table" id="sales-table">
            <thead>
                <tr>
                    <th>Transaction Code</th>
                    <th>Date & Time</th>
                    <th>Items Purchased</th>
                    <th>Total Revenue</th>
                </tr>
            </thead>
            <tbody id="sales-history-body">
                <tr><td colspan="4" style="text-align:center; padding: 40px; color:rgba(255,255,255,0.4);">Loading financial data...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    async function loadSalesHistory() {
        const tbody = document.getElementById('sales-history-body');
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 40px; color:rgba(255,255,255,0.4);">Loading financial data...</td></tr>';
        
        try {
            const response = await fetch('<?= site_url('api/pos/history') ?>');
            const history = await response.json();
            
            if (history.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 40px; color:rgba(255,255,255,0.4);">No transactions found in ledger.</td></tr>';
                return;
            }

            tbody.innerHTML = '';
            history.forEach(record => {
                // Format the date nicely
                const dateObj = new Date(record.created_at);
                const formattedDate = dateObj.toLocaleDateString() + ' ' + dateObj.toLocaleTimeString();

                tbody.innerHTML += `
                    <tr>
                        <td><strong style="color: #a855f7; letter-spacing: 1px;">${record.transaction_code}</strong></td>
                        <td style="color: rgba(255,255,255,0.7); font-size: 0.9rem;">${formattedDate}</td>
                        <td style="color: #e2e8f0; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${record.items_summary}">
                            ${record.items_summary}
                        </td>
                        <td><strong style="color: #10b981; font-size: 1.1rem;">₱${parseFloat(record.total_amount).toFixed(2)}</strong></td>
                    </tr>
                `;
            });
        } catch (error) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 40px; color:#f87171;">Database connection error. Could not fetch ledger.</td></tr>';
        }
    }
</script>