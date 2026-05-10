<?php
/**
 * @var array $user
 * @var array $logs
 */
?>
<?= $this->include('theme/header') ?>
<?= $this->include('theme/sidebar') ?>

<main class="main-content">
    <div class="card glass-panel">
        <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 30px;">
            <div>
                <a href="<?= site_url('admin/activity') ?>" class="btn-back-monitor mb-3">
                    <i class="fas fa-arrow-left"></i> 
                    <span>Back to Monitor</span>
                </a>
                <h2 style="font-size: 2.2rem; margin-top: 10px;">User Timeline: <?= esc($user['username']) ?></h2>
                <p style="color: rgba(255,255,255,0.6);">Detailed activity history for this identity node.</p>
            </div>
            <div class="user-info-card glass-panel" style="padding: 15px 25px; border-radius: 12px; text-align: right;">
                <div style="font-size: 0.8rem; color: rgba(255,255,255,0.4);">Current Status</div>
                <?php 
                    $lastActive = strtotime($user['last_active'] ?? '');
                    $isOnline = (time() - $lastActive) < 300;
                ?>
                <div style="font-weight: bold; color: <?= $isOnline ? '#00ff88' : '#ff4444' ?>;">
                    <?= $isOnline ? 'ONLINE' : 'OFFLINE' ?>
                </div>
                <small style="color: rgba(255,255,255,0.3);">Last seen: <?= $user['last_active'] ?? 'Never' ?></small>
            </div>
        </div>

        <!-- TIMELINE CONTAINER -->
        <div class="timeline-container">
            <?php if(!empty($logs)): ?>
                <?php 
                    $currentDate = ''; 
                    foreach($logs as $log): 
                        $logDate = date('F d, Y', strtotime($log['created_at']));
                ?>
                    <?php if($currentDate !== $logDate): $currentDate = $logDate; ?>
                        <div class="timeline-label">
                            <span><?= $currentDate ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="timeline-item">
                        <div class="timeline-icon">
                            <i class="fas fa-circle"></i>
                        </div>
                        <div class="timeline-content glass-panel">
                            <div class="timeline-header">
                                <span class="timeline-time"><?= date('H:i A', strtotime($log['created_at'])) ?></span>
                                <span class="timeline-event"><?= esc($log['event']) ?></span>
                            </div>
                            <div class="timeline-body">
                                <div class="timeline-detail">
                                    <i class="fas fa-desktop"></i> <?= esc($log['device']) ?>
                                </div>
                                <div class="timeline-detail">
                                    <i class="fas fa-map-marker-alt"></i> <?= esc($log['location']) ?>
                                </div>
                                <div class="timeline-detail">
                                    <i class="fas fa-network-wired"></i> <?= esc($log['ip_address']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 50px; color: rgba(255,255,255,0.3);">
                    No activity recorded for this user.
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<style>
    .btn-back-monitor {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 20px;
        background: rgba(168, 85, 247, 0.1);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(168, 85, 247, 0.2);
        border-radius: 12px;
        color: rgba(255, 255, 255, 0.8) !important;
        text-decoration: none !important;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        margin-bottom: 15px;
    }

    .btn-back-monitor:hover {
        background: rgba(168, 85, 247, 0.2);
        border-color: rgba(168, 85, 247, 0.4);
        color: #fff !important;
        transform: translateX(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    .btn-back-monitor i {
        font-size: 0.8rem;
        transition: transform 0.3s ease;
    }

    .btn-back-monitor:hover i {
        transform: translateX(-3px);
    }

    @media (max-width: 768px) {
        .d-flex.justify-content-between {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 20px;
        }
        .user-info-card {
            width: 100%;
            text-align: left !important;
        }
        .btn-back-monitor {
            width: 100%;
            justify-content: center;
        }
    }

    .timeline-container {
        position: relative;
        padding-left: 30px;
        margin-top: 20px;
    }
    .timeline-container::before {
        content: '';
        position: absolute;
        left: 39px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: rgba(255, 255, 255, 0.1);
    }
    .timeline-label {
        position: relative;
        margin-bottom: 25px;
        margin-left: -10px;
    }
    .timeline-label span {
        background: #7000ff;
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: bold;
        box-shadow: 0 4px 15px rgba(112, 0, 255, 0.3);
    }
    .timeline-item {
        position: relative;
        margin-bottom: 30px;
    }
    .timeline-icon {
        position: absolute;
        left: -3px;
        top: 15px;
        width: 20px;
        height: 20px;
        background: #1a1a2e;
        border: 3px solid #7000ff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
        box-shadow: 0 0 10px rgba(112, 0, 255, 0.5);
    }
    .timeline-icon i { font-size: 6px; color: #7000ff; }
    
    .timeline-content {
        margin-left: 40px;
        padding: 20px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: 0.3s;
    }
    .timeline-content:hover {
        border-color: rgba(112, 0, 255, 0.4);
        transform: translateX(5px);
    }
    .timeline-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        padding-bottom: 10px;
    }
    .timeline-time {
        color: #00d4ff;
        font-weight: bold;
        font-size: 0.9rem;
    }
    .timeline-event {
        font-weight: 500;
        color: white;
    }
    .timeline-body {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }
    .timeline-detail {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.5);
    }
    .timeline-detail i {
        margin-right: 8px;
        width: 15px;
        text-align: center;
    }
</style>

<?= $this->include('theme/footer') ?>
