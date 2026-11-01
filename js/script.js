/**
 * DriveSphere - Main JavaScript Controller
 * Handles global UI interactions, modal dropdowns, animations, dynamic filtering,
 * authentication validation, navigation highlights, and page-specific handlers.
 */

document.addEventListener('DOMContentLoaded', () => {

    /* ==========================================================================
    1. GLOBAL SCROLL ANIMATIONS & HEADER EFFECTS
    ========================================================================== */
    const header = document.querySelector('.main-header');
    
    // Add scroll-shadow class to header when scrolled past threshold
    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    // Elements to reveal with stagger on scroll (exclude primary vehicle cards so they are immediately visible)
    const animatedSelectors = [
        '.section-title', '.section-subtitle', '.section-header',
        '.stat-card', '.a-stat-card', '.value-box', 
        '.split-row', '.pillar-card', '.eco-card', '.support-card', 
        '.action-card', '.info-card', '.timeline-row', '.t-feature-card',
        '.p-item', '.p-grid-item', '.policy-intro-banner'
    ];

    const elementsToReveal = document.querySelectorAll(animatedSelectors.join(', '));
    elementsToReveal.forEach(el => {
        const rect = el.getBoundingClientRect();
        // Only hide elements that are genuinely below the initial viewport
        if (rect.top >= window.innerHeight - 30) {
            el.classList.add('reveal-up');
        }
    });

    const revealObserver = new IntersectionObserver((entries, observer) => {
        const intersectingEntries = entries.filter(entry => entry.isIntersecting);
        intersectingEntries.forEach((entry, index) => {
            setTimeout(() => {
                entry.target.classList.add('is-visible');
            }, index * 80); 
            observer.unobserve(entry.target);
        });
    }, { 
        root: null, 
        rootMargin: '50px 0px', 
        threshold: 0.05 
    });

    elementsToReveal.forEach(el => {
        if (el.classList.contains('reveal-up')) {
            revealObserver.observe(el);
        }
    });


    /* ==========================================================================
    2. INTERACTIVE ACCOUNT DROPDOWN MODAL HANDLER (HEADER)
    ========================================================================== */
    const accountTrigger = document.getElementById('accountTrigger');
    const accountDropdown = document.getElementById('accountDropdown');
    const closeDropdown = document.getElementById('closeDropdown');

    if (accountTrigger && accountDropdown) {
        
        // Toggle Account Dropdown Modal Open / Closed
        const toggleDropdown = (show) => {
            const shouldShow = show !== undefined ? show : !accountDropdown.classList.contains('show');
            
            if (shouldShow) {
                accountDropdown.classList.add('show');
                accountTrigger.classList.add('active');
                accountTrigger.setAttribute('aria-expanded', 'true');
            } else {
                accountDropdown.classList.remove('show');
                accountTrigger.classList.remove('active');
                accountTrigger.setAttribute('aria-expanded', 'false');
            }
        };

        // Trigger Click Listener
        accountTrigger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation(); 
            toggleDropdown();
        });

        // Close Button Listener (Inside Modal if present)
        if (closeDropdown) {
            closeDropdown.addEventListener('click', (e) => {
                e.preventDefault();
                toggleDropdown(false);
            });
        }

        // Close on Click Outside
        document.addEventListener('click', (e) => {
            if (!accountTrigger.contains(e.target) && !accountDropdown.contains(e.target)) {
                if (accountDropdown.classList.contains('show')) {
                    toggleDropdown(false);
                }
            }
        });

        // Close on Keyboard Press (Escape Key)
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && accountDropdown.classList.contains('show')) {
                toggleDropdown(false);
            }
        });

        // Prevent Click Inside Dropdown from Closing Menu
        accountDropdown.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }


    /* ==========================================================================
    3. HOME PAGE LOGIC
    ========================================================================== */
    const copyBtn = document.getElementById('copyBtn');
    const promoText = document.getElementById('promoText');

    // Promo Code Clipboard Copy Button
    if (copyBtn && promoText) {
        copyBtn.addEventListener('click', () => {
            const codeToCopy = promoText.innerText.trim();
            navigator.clipboard.writeText(codeToCopy).then(() => {
                const originalText = copyBtn.innerText;
                copyBtn.innerText = 'COPIED';
                copyBtn.style.backgroundColor = 'var(--light-blue)';
                copyBtn.style.color = 'var(--white)';
                
                setTimeout(() => {
                    copyBtn.innerText = originalText;
                    copyBtn.style.backgroundColor = '';
                    copyBtn.style.color = '';
                }, 2000);
            }).catch(err => console.error('Failed to copy promo code: ', err));
        });
    }



    /* ==========================================================================
    4. RENTAL & INVENTORY PAGE LOGIC
    ========================================================================== */

    // Dynamic Price Range Slider
    const slider = document.querySelector('.slider');
    if (slider) {
        slider.addEventListener('input', (e) => {
            const value = e.target.value;
            const formattedValue = new Intl.NumberFormat('en-PH').format(value);
            const labelSpan = e.target.previousElementSibling?.querySelector('span');
            if (labelSpan) {
                labelSpan.innerText = `Up to ₱${formattedValue} / day`;
            }
        });
    }

    // Category Filter Pills Active State Switcher
    const filterPills = document.querySelectorAll('.filter-pills .pill');
    if (filterPills.length > 0) {
        filterPills.forEach(pill => {
            pill.addEventListener('click', () => {
                filterPills.forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
            });
        });
    }

    // Grid / List View Mode Toggle Buttons
    const viewBtns = document.querySelectorAll('.view-btn');
    if (viewBtns.length > 0) {
        viewBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                viewBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });
    }


    /* ==========================================================================
    5. HELP & FAQ ACCORDION LOGIC
    ========================================================================== */
    function initFaqAccordions() {
        const faqWrapper = document.querySelector('.faq-accordion-wrapper');
        const faqItems = document.querySelectorAll('.faq-accordion-wrapper .accordion-item');
        if (!faqWrapper || faqItems.length === 0) return;

        // Toggle a single FAQ item (expand or collapse)
        function toggleAccordionItem(item, forceOpen) {
            if (!item) return;
            const isCurrentlyActive = item.classList.contains('active');
            const willBeActive = (typeof forceOpen === 'boolean') ? forceOpen : !isCurrentlyActive;

            if (willBeActive) {
                item.classList.add('active', 'open');
            } else {
                item.classList.remove('active', 'open');
            }

            // Synchronize toggle icon immediately (+ <-> −)
            const icon = item.querySelector('.toggle-icon i');
            if (icon) {
                if (willBeActive) {
                    icon.classList.remove('fa-plus');
                    icon.classList.add('fa-minus');
                } else {
                    icon.classList.remove('fa-minus');
                    icon.classList.add('fa-plus');
                }
            }

            // Synchronize ARIA state for accessibility
            const header = item.querySelector('.accordion-header');
            const btn = item.querySelector('.toggle-icon');
            const expandedVal = willBeActive ? 'true' : 'false';
            if (header) header.setAttribute('aria-expanded', expandedVal);
            if (btn) btn.setAttribute('aria-expanded', expandedVal);
        }

        // Initialize each FAQ item with proper accessible attributes and events
        faqItems.forEach((item, index) => {
            const header = item.querySelector('.accordion-header');
            const btn = item.querySelector('.toggle-icon');
            const body = item.querySelector('.accordion-body');
            const bodyId = body ? (body.id || `faq-ans-${String(index + 1).padStart(2, '0')}`) : `faq-ans-${index + 1}`;

            if (body && !body.id) {
                body.id = bodyId;
            }

            const isActive = item.classList.contains('active');
            if (header) {
                header.setAttribute('role', 'button');
                header.setAttribute('tabindex', '0');
                header.setAttribute('aria-expanded', isActive ? 'true' : 'false');
                header.setAttribute('aria-controls', bodyId);

                // Click handler (covers entire header, question text, plus/minus button)
                header.addEventListener('click', (e) => {
                    e.preventDefault();
                    toggleAccordionItem(item);
                });

                // Keyboard activation (Enter / Space)
                header.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        toggleAccordionItem(item);
                    }
                });
            }

            if (btn) {
                btn.setAttribute('tabindex', '-1');
                btn.setAttribute('aria-hidden', 'true');
                btn.setAttribute('aria-expanded', isActive ? 'true' : 'false');
                btn.setAttribute('aria-controls', bodyId);
            }
        });

        // Global fallback toggle function in case invoked by legacy callers
        window.toggleFaq = function (target) {
            if (!target) return;
            const item = target.closest ? target.closest('.accordion-item') : null;
            if (item) {
                toggleAccordionItem(item);
            }
        };

        // FAQ Category Filter Pills
        const faqPills = document.querySelectorAll('.faq-categories .faq-pill');
        if (faqPills.length > 0) {
            faqPills.forEach(pill => {
                pill.addEventListener('click', (e) => {
                    e.preventDefault();
                    faqPills.forEach(p => p.classList.remove('active'));
                    pill.classList.add('active');
                    const cat = pill.getAttribute('data-category');

                    faqItems.forEach(item => {
                        const itemCat = item.getAttribute('data-category');
                        if (cat === 'all' || itemCat === cat) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });
        }

        // FAQ Live Keyword Search
        const helpSearchForm = document.getElementById('helpSearchForm');
        const searchInput = document.getElementById('faqSearchInput') || (helpSearchForm ? helpSearchForm.querySelector('input') : null);

        const runFaqSearch = (keyword) => {
            const q = (keyword || '').toLowerCase().trim();
            faqItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (!q || text.includes(q)) {
                    item.style.display = '';
                    if (q) {
                        toggleAccordionItem(item, true);
                    }
                } else {
                    item.style.display = 'none';
                }
            });
        };
        window.filterFaqs = runFaqSearch;

        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                runFaqSearch(e.target.value);
            });
        }

        if (helpSearchForm) {
            helpSearchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                if (searchInput) {
                    runFaqSearch(searchInput.value);
                }
                faqWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        }
    }

    initFaqAccordions();


    /* ==========================================================================
    6. UNIVERSAL AUTHENTICATION & FORM VALIDATION
    ========================================================================== */

    // A. Password Eye Icon Toggle
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('toggle-pw')) {
            const passwordInput = e.target.previousElementSibling;
            
            if (passwordInput && passwordInput.tagName === 'INPUT') {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    e.target.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    e.target.classList.replace('fa-eye-slash', 'fa-eye');
                }
            }
        }
    });

    // B. Customer Registration Password Match Check
    const registerForm = document.getElementById('registerForm'); 
    const regPw = document.getElementById('regPassword');
    const regConfirmPw = document.getElementById('regConfirmPassword');

    if (registerForm && regPw && regConfirmPw) {
        registerForm.addEventListener('submit', (e) => {
            if (regPw.value !== regConfirmPw.value) {
                e.preventDefault(); 
                alert('Passwords do not match. Please check your spelling and try again.');
                regConfirmPw.value = '';
                regConfirmPw.focus();
            }
        });
    }


    // D. Universal "Show Password" Checkbox Toggle
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('universal-show-pw')) {
            const passFields = [
                document.getElementById('regPassword'),
                document.getElementById('regConfirmPassword'),
                document.getElementById('passwordInput'),
                document.getElementById('adminPasswordInput'),
                document.getElementById('adminRegPassword'),
                document.getElementById('adminRegConfirmPassword')
            ];

            const newType = e.target.checked ? 'text' : 'password';

            passFields.forEach(field => {
                if (field) field.type = newType;
            });
        }
    });



}); // End of DOMContentLoaded
