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
        bottom: calc(20px + env(safe-area-inset-bottom, 0px));
        z-index: 100000;
        border-radius: 30px;
        padding: 10px 15px;
        background: rgba(20, 15, 45, 0.9);
        border: 1px solid rgba(255,255,255,0.15);
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.5);
        display: none;
        transition: transform 0.3s ease, bottom 0.3s ease;
    }

    /* Inner container */
    .customer-bottom-nav-inner {
        display: flex;
        gap: 10px;
        justify-content: space-around;
        align-items: center;
    }

    .cbn-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 8px 5px;
        border-radius: 20px;
        text-decoration: none;
        color: rgba(255,255,255,0.5);
        font-weight: 700;
        font-size: 0.7rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    
    .cbn-item i { 
        font-size: 1.3rem;
        transition: all 0.3s ease;
    }

    .cbn-item.active {
        color: #fff;
        background: rgba(168, 85, 247, 0.15);
    }
    
    .cbn-item.active i {
        color: #a855f7;
        transform: translateY(-2px);
        text-shadow: 0 0 15px rgba(168, 85, 247, 0.5);
    }

    /* Mobile only */
    @media (max-width: 1024px) {
        .customer-bottom-nav { display: block; }
        .mobile-toggle { display: none !important; }
        body.customer-has-bottom-nav .main-content { padding-bottom: 140px !important; }
    }

    @media (max-width: 420px) {
        .customer-bottom-nav {
            left: 15px;
            right: 15px;
            bottom: calc(15px + env(safe-area-inset-bottom, 0px));
        }
        .cbn-item { font-size: 0.65rem; }
        .cbn-item i { font-size: 1.2rem; }
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

