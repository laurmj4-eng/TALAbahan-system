<style>
    /* IMPROVED SIDEBAR LAYOUT - MODERN DESIGN */
    .sidebar { 
        width: var(--sidebar-width, 260px); 
        min-width: var(--sidebar-width, 260px);
        display: flex; 
        flex-direction: column; 
        z-index: 10; 
        border-right: 1px solid rgba(255,255,255,0.08);
        background: rgba(0, 0, 0, 0.3); 
        backdrop-filter: blur(20px);
        height: 100vh;
    }
    
    .sidebar-header { 
        padding: 28px 20px; 
        text-align: center; 
        border-bottom: 2px solid rgba(168, 85, 247, 0.2);
        background: rgba(168, 85, 247, 0.08);
    }
    .logo-container {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-bottom: 6px;
    }
    .logo-icon {
        font-size: 1.5rem;
        color: #a855f7;
        filter: drop-shadow(0 0 8px rgba(168, 85, 247, 0.5));
    }
    .sidebar-header h2 { 
        margin: 0; 
        font-size: 1.3rem; 
        font-weight: 700; 
        color: #fff; 
        letter-spacing: 0.5px;
    }
    .sidebar-header small {
        display: block;
        color: rgba(168, 85, 247, 0.9);
        font-weight: 600;
        font-size: 0.75rem;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    
    .sidebar-menu { 
        list-style: none !important; 
        padding: 20px 8px; 
        margin: 0; 
        flex: 1; 
        overflow-y: auto; 
        scrollbar-width: thin;
        scrollbar-color: rgba(168, 85, 247, 0.2) transparent;
    }

    .sidebar-section {
        margin-bottom: 16px;
    }

    .sidebar-section-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        margin: 16px 12px;
    }

    .sidebar-section-title {
        padding: 0 16px 12px 16px;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: rgba(168, 85, 247, 0.6);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .sidebar-section-title i {
        font-size: 0.8rem;
    }

    .sidebar-menu li { 
        margin-bottom: 4px !important;
        list-style: none !important;
    }

    .sidebar-menu a { 
        display: flex !important; 
        align-items: center; 
        justify-content: flex-start; 
        padding: 12px 16px; 
        color: rgba(255,255,255,0.7) !important; 
        text-decoration: none !important;
        font-weight: 500; 
        font-size: 0.9rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        border-radius: 12px; 
        cursor: pointer;
        gap: 12px;
        width: 100%;
        position: relative;
        border: 1px solid transparent;
    }

    .sidebar-menu a i { 
        font-size: 1.1rem; 
        width: 24px;
        text-align: center;
        transition: all 0.3s ease;
    }

    .sidebar-menu a:hover { 
        background: rgba(168, 85, 247, 0.1); 
        color: #fff !important;
        border-color: rgba(168, 85, 247, 0.2);
        padding-left: 20px;
    }

    .sidebar-menu a:hover i {
        color: #a855f7;
        transform: scale(1.1);
    }

    .sidebar-menu a.active { 
        background: linear-gradient(135deg, rgba(168, 85, 247, 0.25), rgba(99, 102, 241, 0.15)); 
        color: #fff !important;
        font-weight: 600;
        border-color: rgba(168, 85, 247, 0.3);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .sidebar-menu a.active i {
        color: #c4b5fd;
    }

    .sidebar-menu a.active::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 20px;
        background: #a855f7;
        border-radius: 0 4px 4px 0;
        box-shadow: 0 0 10px #a855f7;
    }

    .sidebar-footer {
        padding: 16px;
        border-top: 1px solid rgba(255,255,255,0.08);
        background: rgba(0, 0, 0, 0.2);
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 12px;
        margin-bottom: 12px;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: rgba(168, 85, 247, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #a855f7;
        border: 1px solid rgba(168, 85, 247, 0.2);
    }

    .user-details {
        flex: 1;
        min-width: 0;
    }

    .user-name {
        font-size: 0.9rem;
        color: #fff;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-role {
        font-size: 0.75rem;
        color: rgba(168, 85, 247, 0.8);
        font-weight: 500;
    }

    .logout-btn { 
        display: flex !important;
        align-items: center;
        justify-content: center;
        gap: 10px;
        color: #fca5a5 !important;
        padding: 12px !important;
        background: rgba(239, 68, 68, 0.1) !important;
        border: 1px solid rgba(239, 68, 68, 0.2) !important;
        border-radius: 12px !important;
        width: 100%;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .logout-btn i {
        font-size: 1rem;
    }

    .logout-btn:hover { 
        background: rgba(239, 68, 68, 0.2) !important; 
        color: #fff !important;
        border-color: rgba(239, 68, 68, 0.4) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
    }
</style>