<div class="card glass-panel" style="padding: 40px; border-radius: 30px;">
    <h2 style="font-size: 2.2rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; margin-bottom: 0;">Financial Ledger 📈</h2>
    <p style="color: rgba(255,255,255,0.6); margin-top: 15px; margin-bottom: 30px;">Real-time transaction logs and revenue records.</p>
    
    <div class="sales-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 15px; flex-wrap: wrap;">
        <input type="text" id="salesSearch" onkeyup="filterSalesTable()" placeholder="Search transactions..." style="padding: 10px 15px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.3); color: white; width: 300px; max-width: 100%;">
        <button onclick="exportSalesToCsv()" class="btn-primary" style="background: #10b981; display: flex; align-items: center; gap: 8px; height: auto; padding: 10px 20px;">
            <i class="fas fa-download"></i> Export CSV
        </button>
    </div>

    <style>
        @media (max-width: 480px) {
            .sales-controls { flex-direction: column; align-items: stretch; }
            #salesSearch { width: 100% !important; }
            .sales-controls .btn-primary { justify-content: center; }
        }
    </style>

    <div class="table-responsive glass-panel" style="padding: 20px; border-radius: 15px;">
        <table class="premium-table" id="sales-table">
            <thead>
                <tr>
                    <th>TRANSACTION CODE</th>
                    <th>DATE & TIME</th>
                    <th>ITEMS PURCHASED</th>
                    <th>TOTAL REVENUE</th>
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
            const response = await fetch('<?= site_url('admin/getHistory') ?>');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            const history = result.data || [];
            
            if (!history || history.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 40px; color:rgba(255,255,255,0.4);">No transactions found in ledger.</td></tr>';
                return;
            }

            tbody.innerHTML = '';
            history.forEach(record => {
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
            console.error('Error loading sales history:', error);
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 40px; color:#f87171;">Unable to fetch financial ledger. Please refresh the page.</td></tr>';
        }
    }
    
    function filterSalesTable() {
        const input = document.getElementById('salesSearch');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('sales-table');
        const tr = table.getElementsByTagName('tr');

        for (let i = 1; i < tr.length; i++) { // Start from 1 to skip header row
            let found = false;
            const td = tr[i].getElementsByTagName('td');
            for (let j = 0; j < td.length; j++) {
                if (td[j].textContent.toLowerCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
            tr[i].style.display = found ? '' : 'none';
        }
    }

    function exportSalesToCsv() {
        let csv = [];
        const rows = document.querySelectorAll('#sales-table tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = [], cols = rows[i].querySelectorAll('td, th');
            for (let j = 0; j < cols.length; j++) {
                let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s)/gm, ' ');
                data = data.replace(/"/g, '""');
                row.push('"' + data + '"');
            }
            csv.push(row.join(','));
        }

        const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
        const downloadLink = document.createElement('a');
        downloadLink.download = 'sales_history.csv';
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = 'none';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }

    document.addEventListener('DOMContentLoaded', loadSalesHistory);
</script>