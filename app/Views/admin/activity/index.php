<?php
/**
 * @var array $logs
 * @var \CodeIgniter\Pager\Pager $pager
 */
?>
<?= $this->include('theme/header') ?>
<?= $this->include('theme/sidebar') ?>

<main class="main-content">
    <div class="card glass-panel">
        <div class="d-flex justify-content-between align-items-center" style="border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px;">
            <div>
                <h2 style="font-size: 2.2rem; margin-bottom: 5px;">Activity Monitor</h2>
                <p style="color: rgba(255,255,255,0.6);">Clean, professional tracking of system interactions.</p>
            </div>
        </div>

        <div class="glass-table-container" style="margin-top: 30px;">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th class="d-none d-md-table-cell">IP Address</th>
                        <th class="d-none d-lg-table-cell">Device</th>
                        <th>Status</th>
                        <th>Event</th>
                        <th class="d-none d-sm-table-cell">Location</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($logs)): foreach($logs as $log): ?>
                    <tr>
                        <td data-label="Time">
                            <span style="font-size: 0.9rem; font-weight: 500;">
                                <?= date('H:i:s', strtotime($log['created_at'])) ?>
                            </span><br>
                            <small style="color: rgba(255,255,255,0.4);"><?= date('M d', strtotime($log['created_at'])) ?></small>
                        </td>
                        <td data-label="User">
                            <?php if ($log['user_id']): ?>
                                <a href="<?= site_url('admin/activity/user/'.$log['user_id']) ?>" class="user-link">
                                    <strong><?= esc($log['user_identity']) ?></strong>
                                </a>
                            <?php else: ?>
                                <span style="color: rgba(255,255,255,0.6);">Guest</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="IP Address" class="d-none d-md-table-cell">
                            <code style="color: #a855f7; background: rgba(168, 85, 247, 0.1); padding: 2px 6px; border-radius: 4px; font-size: 0.8rem;">
                                <?= esc($log['ip_address'] ?? '0.0.0.0') ?>
                            </code>
                        </td>
                        <td data-label="Device" class="d-none d-lg-table-cell">
                            <small style="color: rgba(255,255,255,0.6); font-size: 0.75rem;">
                                <i class="fas fa-laptop-code" style="margin-right: 5px;"></i>
                                <?= esc(substr($log['device'] ?? 'Unknown', 0, 20)) ?>...
                            </small>
                        </td>
                        <td data-label="Status">
                            <?php if ($log['user_id']): ?>
                                <?php 
                                    $lastActive = strtotime($log['last_active'] ?? '');
                                    $isOnline = (time() - $lastActive) < 300; // 5 minutes
                                ?>
                                <span class="status-badge <?= $isOnline ? 'online' : 'offline' ?>">
                                    <?= $isOnline ? 'Active' : 'Offline' ?>
                                </span>
                            <?php else: ?>
                                <span class="status-badge offline">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Event">
                            <span class="event-badge"><?= esc($log['event']) ?></span>
                        </td>
                        <td data-label="Location" class="d-none d-sm-table-cell">
                            <small><i class="fas fa-map-marker-alt" style="color: #ff4444; margin-right: 5px;"></i><?= esc($log['location']) ?></small>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="7" style="text-align:center; padding: 50px;">No logs found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pager): ?>
            <div class="pagination-container" style="margin-top: 30px;">
                <?= $pager->links() ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
    .user-link {
        color: #00d4ff;
        text-decoration: none;
        transition: 0.3s;
    }
    .user-link:hover {
        color: #7000ff;
        text-shadow: 0 0 10px rgba(112, 0, 255, 0.5);
    }
    .status-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: bold;
        text-transform: uppercase;
    }
    .status-badge.online {
        background: rgba(0, 255, 136, 0.1);
        color: #00ff88;
        border: 1px solid rgba(0, 255, 136, 0.3);
        box-shadow: 0 0 10px rgba(0, 255, 136, 0.2);
    }
    .status-badge.offline {
        background: rgba(255, 255, 255, 0.05);
        color: rgba(255, 255, 255, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .event-badge {
        background: rgba(112, 0, 255, 0.1);
        color: #b380ff;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        border: 1px solid rgba(112, 0, 255, 0.2);
    }
    .pagination-container ul { display: flex; list-style: none; gap: 8px; justify-content: center; padding: 0; }
    .pagination-container li a { padding: 8px 14px; background: rgba(255, 255, 255, 0.05); border-radius: 4px; color: white; text-decoration: none; }
    .pagination-container li.active a { background: #7000ff; }
</style>

<?= $this->include('theme/footer') ?>
