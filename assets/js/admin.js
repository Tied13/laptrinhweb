document.addEventListener('DOMContentLoaded', function () {
    setActiveMenu();
    setupMobileSidebar();
    setupDeleteConfirm();
    setupAutoHideAlerts();
    setupOrderStatusBadge();
    setupCKEditor();
    setupRevenueChart();
    setupProductForm();
});

function setActiveMenu() {
    const currentPath = window.location.pathname;
    const links = document.querySelectorAll('.admin-menu a');

    links.forEach(function (link) {
        const href = link.getAttribute('href');
        if (!href || href.startsWith('../')) {
            return;
        }
        const linkPage = href.split('/').pop();
        if (currentPath.endsWith('/admin/' + linkPage)) {
            link.classList.add('active');
        }
    });
}

function setupMobileSidebar() {
    const sidebar = document.querySelector('.admin-sidebar');
    if (!sidebar) return;

    const toggleBtn = document.createElement('button');
    toggleBtn.className = 'admin-sidebar-toggle';
    toggleBtn.innerHTML = '<i class="bi bi-list"></i>';
    toggleBtn.setAttribute('type', 'button');
    toggleBtn.setAttribute('aria-label', 'Mở/đóng menu');
    document.body.appendChild(toggleBtn);

    const overlay = document.createElement('div');
    overlay.className = 'admin-sidebar-overlay';
    document.body.appendChild(overlay);

    function openSidebar() {
        sidebar.classList.add('admin-sidebar-open');
        overlay.classList.add('show');
    }

    function closeSidebar() {
        sidebar.classList.remove('admin-sidebar-open');
        overlay.classList.remove('show');
    }

    toggleBtn.addEventListener('click', function () {
        if (sidebar.classList.contains('admin-sidebar-open')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    overlay.addEventListener('click', closeSidebar);

    sidebar.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', closeSidebar);
    });
}

function setupDeleteConfirm() {
    document.addEventListener('click', function (e) {
        const target = e.target.closest('a');
        if (!target) return;

        let message = null;

        if (target.classList.contains('btn-delete-confirm')) {
            message = 'Bạn có chắc chắn muốn xóa mục này không?';
        } else if (target.hasAttribute('data-confirm')) {
            message = target.getAttribute('data-confirm');
        } else if (target.classList.contains('btn-toggle-confirm') && target.hasAttribute('data-message')) {
            message = target.getAttribute('data-message');
        }

        if (message !== null) {
            const ok = window.confirm(message);
            if (!ok) {
                e.preventDefault();
            }
        }
    });
}

function setupAutoHideAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-8px)';
            setTimeout(function () {
                alert.remove();
            }, 400);
        }, 4000);
    });
}

function setupOrderStatusBadge() {
    const statusMap = {
        '0': { label: 'Chờ xử lý', className: 'badge-pending' },
        '1': { label: 'Đang giao', className: 'badge-shipping' },
        '2': { label: 'Hoàn thành', className: 'badge-done' },
        '3': { label: 'Đã hủy', className: 'badge-cancel' }
    };

    document.querySelectorAll('.status-select').forEach(function (select) {
        select.addEventListener('change', function () {
            const targetId = select.getAttribute('data-badge-target');
            const badge = targetId ? document.getElementById(targetId) : null;
            const info = statusMap[select.value];

            if (badge && info) {
                badge.className = 'badge ' + info.className;
                badge.textContent = info.label;
            }
        });
    });
}

function setupCKEditor() {
    const editorEl = document.getElementById('editor');
    if (editorEl && typeof ClassicEditor !== 'undefined') {
        ClassicEditor
            .create(editorEl)
            .catch(function (error) {
                console.error('Lỗi khởi tạo CKEditor:', error);
            });
    }
}

function setupRevenueChart() {
    const chartElement = document.getElementById('revenueChart');

    if (!chartElement || typeof Chart === 'undefined') return;

    const weeklyRevenue = window.weeklyRevenue || [];

    const labels = weeklyRevenue.map(item => item.date || '');
    const revenueData = weeklyRevenue.map(item => Number(item.revenue || 0));

    new Chart(chartElement, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Doanh thu',
                data: revenueData,
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function setupProductForm() {
    const addBtn = document.getElementById('btn-add-product');
    const closeBtn = document.getElementById('btn-close-product');
    const cancelBtn = document.getElementById('btn-cancel-product');
    const productForm = document.getElementById('product-form');

    if (!addBtn || !productForm) return;

    addBtn.addEventListener('click', function () {
        productForm.classList.add('show');

        productForm.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    });

    function closeProductForm() {
        productForm.classList.remove('show');
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeProductForm);
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeProductForm);
    }
}