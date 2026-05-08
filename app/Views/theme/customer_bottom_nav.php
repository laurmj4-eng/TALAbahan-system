<?php
    $role = session()->get('role') ?? '';
    if ($role !== 'customer') return;

    $isShop = url_is('customer/dashboard*');
    $isOrders = url_is('customer/order-center*') || url_is('customer/order-items*');
    $isProfile = url_is('customer/profile*');
?>

<style>
    .customer-bottom-nav {
        position: fixed;
        left: 20px;
        right: 20px;
        bottom: 25px;
        z-index: 100000; /* below chat widget */
        border-radius: 25px;
        padding: 12px 16px;
        background: rgba(20, 15, 45, 0.85);
        border: 1px solid rgba(255,255,255,0.18);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.6);
        display: none;
    }

    /* Inner container: full width on mobile, centered */
    .customer-bottom-nav-inner {
        display: flex;
        gap: 20px;
        justify-content: center;
        align-items: center;
        /* Remove large padding-right to allow buttons to space out */
        padding-right: 0;
    }

    .cbn-item {
        flex: 1;
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px; /* Reduced gap */
        padding: 10px 4px; /* More compact padding */
        border-radius: 18px;
        text-decoration: none;
        color: rgba(255,255,255,0.5); /* Slightly dimmer for better contrast with active */
        font-weight: 700;
        font-size: 0.7rem; /* Smaller font */
        background: transparent;
        border: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
    }
    .cbn-item i { 
        font-size: 1.2rem; /* Slightly smaller icons */
        transition: all 0.3s ease;
    }
    .cbn-item:hover { color: #fff; }
    .cbn-item:hover i { transform: translateY(-2px); color: #a855f7; }
    
    .cbn-item.active {
        color: #fff;
        background: rgba(168, 85, 247, 0.12); /* Purple-ish background for active */
        border: 1px solid rgba(168, 85, 247, 0.2);
    }
    .cbn-item.active i {
        color: #a855f7;
        text-shadow: 0 0 15px rgba(168, 85, 247, 0.6);
    }

    /* Mobile only */
    @media (max-width: 1024px) {
        .customer-bottom-nav { display: block; }
        .mobile-toggle { display: none !important; }
        body.customer-has-bottom-nav .main-content { padding-bottom: 120px !important; }
        
        /* Reposition cart float to be above nav */
        .cart-float {
            bottom: 110px !important;
            right: 25px !important;
        }
        #chat-button-container {
            bottom: 175px !important; /* Stacked above cart icon and bottom nav */
            right: 25px !important;
            display: flex !important;
            left: auto !important;
        }
        #chat-container {
            bottom: 0 !important; /* Mobile full-screen override */
            right: 0 !important;
            width: 100vw !important;
            height: 100% !important;
        }
    }

    @media (max-width: 420px) {
        .customer-bottom-nav {
            left: 15px;
            right: 15px;
            bottom: 20px;
        }
        .customer-bottom-nav-inner { gap: 12px; }
        .cbn-item { padding: 10px 5px; }
    }

    /* Desktop/Laptop: Always Hidden */
    @media (min-width: 1025px) {
        .customer-bottom-nav { 
            display: none !important; 
        }
    }

    /* When a modal is open, hide nav to keep checkout buttons unobstructed */
    body.modal-open .customer-bottom-nav { 
        display: none !important;
    }
</style>

<script>
    (function () {
        try { document.body.classList.add('customer-has-bottom-nav'); } catch (e) {}

        // AJAX Navigation Logic
        function initAjaxNav() {
            document.querySelectorAll('a[href*="/customer/"]').forEach(link => {
                // Skip if it's already handled or has a specific exclusion
                if (link.dataset.ajaxInit || link.getAttribute('href').includes('logout')) return;
                
                link.addEventListener('click', function(e) {
                    const url = this.getAttribute('href');
                    if (!url || url.includes('#') || url.includes('javascript:')) return;

                    e.preventDefault();
                    
                    // Add loading state
                    const content = document.getElementById('main-content');
                    if (!content) return;
                    content.style.opacity = '0.5';
                    
                    fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.text();
                    })
                    .then(html => {
                        const content = document.getElementById('main-content');
                        content.innerHTML = html;
                        content.style.opacity = '1';
                        
                        // Update active state in nav
                        const path = new URL(url, window.location.origin).pathname;
                        document.querySelectorAll('.cbn-item').forEach(item => {
                            const itemPath = new URL(item.href, window.location.origin).pathname;
                            item.classList.toggle('active', path === itemPath);
                        });
                        
                        // Update URL without reload
                        window.history.pushState({ path: url }, '', url);
                        
                        // Re-run any scripts in the new content
                        const scripts = content.querySelectorAll('script');
                        scripts.forEach(oldScript => {
                            const newScript = document.createElement('script');
                            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                            newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                            oldScript.parentNode.replaceChild(newScript, oldScript);
                        });

                        // Re-initialize AJAX links for the new content
                        initAjaxNav();
                        
                        // Scroll to top
                        window.scrollTo(0, 0);
                    })
                    .catch(err => {
                        console.error('AJAX Nav Error:', err);
                        window.location.href = url; // Fallback to normal load
                    });
                });
                
                link.dataset.ajaxInit = 'true';
            });
        }

        initAjaxNav();

        // Handle back/forward buttons
        window.onpopstate = function() {
            window.location.reload();
        };
    })();
</script>

<nav class="customer-bottom-nav" aria-label="Customer navigation">
    <div class="customer-bottom-nav-inner">
        <a class="cbn-item <?= $isShop ? 'active' : '' ?>" href="<?= site_url('customer/dashboard') ?>">
            <i class="fas fa-store"></i> <span>Shop</span>
        </a>
        <a class="cbn-item <?= $isOrders ? 'active' : '' ?>" href="<?= site_url('customer/order-center?tab=all') ?>">
            <i class="fas fa-clipboard-list"></i> <span>Orders</span>
        </a>
        <a class="cbn-item <?= $isProfile ? 'active' : '' ?>" href="<?= site_url('customer/profile') ?>">
            <i class="fas fa-user"></i> <span>Profile</span>
        </a>
    </div>
</nav>

