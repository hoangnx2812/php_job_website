</main>

<footer class="site-footer">
    <div class="container">
        <div class="row g-4 mb-4">
            <!-- Brand -->
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="d-flex align-items-center justify-content-center rounded-2"
                         style="width:36px;height:36px;background:linear-gradient(135deg,#1a56db,#7c3aed)">
                        <i class="bi bi-briefcase-fill text-white" style="font-size:1rem"></i>
                    </div>
                    <span class="text-white fw-700 fs-5">JobVN</span>
                </div>
                <p class="small mb-3" style="line-height:1.7;color:rgba(255,255,255,0.6)">
                    Nền tảng tuyển dụng kết nối ứng viên tài năng với các nhà tuyển dụng hàng đầu Việt Nam.
                </p>
                <div class="d-flex gap-2">
                    <a href="#" class="d-flex align-items-center justify-content-center rounded-2"
                       style="width:34px;height:34px;background:rgba(255,255,255,0.1)">
                        <i class="bi bi-facebook" style="font-size:0.9rem"></i>
                    </a>
                    <a href="#" class="d-flex align-items-center justify-content-center rounded-2"
                       style="width:34px;height:34px;background:rgba(255,255,255,0.1)">
                        <i class="bi bi-linkedin" style="font-size:0.9rem"></i>
                    </a>
                    <a href="#" class="d-flex align-items-center justify-content-center rounded-2"
                       style="width:34px;height:34px;background:rgba(255,255,255,0.1)">
                        <i class="bi bi-twitter-x" style="font-size:0.9rem"></i>
                    </a>
                </div>
            </div>

            <!-- Ứng viên -->
            <div class="col-md-2 col-6">
                <p class="footer-title">Ứng viên</p>
                <ul class="list-unstyled small" style="line-height:2">
                    <li><a href="<?= e(url('jobs')) ?>">Tìm việc làm</a></li>
                    <li><a href="<?= e(url('companies')) ?>">Danh sách công ty</a></li>
                    <li><a href="<?= e(url('register')) ?>">Đăng ký tài khoản</a></li>
                    <li><a href="<?= e(url('user/saved_jobs')) ?>">Việc làm đã lưu</a></li>
                </ul>
            </div>

            <!-- Nhà tuyển dụng -->
            <div class="col-md-2 col-6">
                <p class="footer-title">Nhà tuyển dụng</p>
                <ul class="list-unstyled small" style="line-height:2">
                    <li><a href="<?= e(url('user/become_employer')) ?>">Đăng ký tuyển dụng</a></li>
                    <li><a href="<?= e(url('employer/job_form')) ?>">Đăng bài tuyển dụng</a></li>
                    <li><a href="<?= e(url('employer/applications')) ?>">Quản lý ứng viên</a></li>
                </ul>
            </div>

            <!-- Tìm kiếm nhanh -->
            <div class="col-md-4">
                <p class="footer-title">Tìm kiếm nhanh</p>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php foreach ([
                        'full-time' => 'Toàn thời gian',
                        'part-time' => 'Bán thời gian',
                        'intern'    => 'Thực tập',
                        'contract'  => 'Hợp đồng',
                    ] as $val => $label): ?>
                        <a href="<?= e(url('jobs', ['job_type' => $val])) ?>"
                           class="badge text-decoration-none"
                           style="background:rgba(255,255,255,0.1);color:#cbd5e1;font-size:0.8rem;padding:0.4em 0.75em;border-radius:6px;border:1px solid rgba(255,255,255,0.12)">
                            <?= $label ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="small" style="color:rgba(255,255,255,0.45);line-height:2">
                    <div><i class="bi bi-envelope me-1"></i> contact@jobvn.demo</div>
                    <div><i class="bi bi-telephone me-1"></i> 1900 xxxx</div>
                </div>
            </div>
        </div>

        <hr class="footer-divider" style="margin:0 0 1rem">
        <div class="d-flex flex-wrap justify-content-between align-items-center small" style="color:rgba(255,255,255,0.4)">
            <span>&copy; <?= date('Y') ?> JobVN — Demo PHP Job Website.</span>
            <span>Built with PHP + Bootstrap 5 + MySQL</span>
        </div>
    </div>
</footer>

<!-- Nút Back to Top: xuất hiện khi scroll xuống > 300px -->
<button id="back-to-top" title="Lên đầu trang"
  style="position:fixed;bottom:5rem;right:1.5rem;z-index:998;width:42px;height:42px;
         border-radius:50%;background:var(--bs-primary,#1a56db);color:#fff;border:none;
         box-shadow:0 2px 8px rgba(26,86,219,0.35);opacity:0;transition:opacity 0.25s;
         display:flex;align-items:center;justify-content:center;cursor:pointer;pointer-events:none">
  <i class="bi bi-arrow-up-short" style="font-size:1.3rem"></i>
</button>
<script>
(function() {
    var btn = document.getElementById('back-to-top');
    if (!btn) return;
    // Hiện/ẩn nút theo vị trí scroll
    window.addEventListener('scroll', function() {
        var visible = window.scrollY > 300;
        btn.style.opacity = visible ? '1' : '0';
        btn.style.pointerEvents = visible ? 'auto' : 'none';
    });
    // Scroll mượt lên đầu trang khi click
    btn.onclick = function() { window.scrollTo({ top: 0, behavior: 'smooth' }); };
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- ===== Toast Notification System ===== -->
<!-- Container cố định góc phải dưới màn hình -->
<div id="toast-container" style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;display:flex;flex-direction:column;gap:0.5rem;max-width:320px"></div>

<style>
/* Toast slide-in animation từ phải vào */
@keyframes toastSlideIn {
    from { opacity: 0; transform: translateX(100%); }
    to   { opacity: 1; transform: translateX(0); }
}
@keyframes toastSlideOut {
    from { opacity: 1; transform: translateX(0); }
    to   { opacity: 0; transform: translateX(100%); }
}
.toast-item {
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    font-size: 0.88rem;
    font-weight: 500;
    color: #fff;
    animation: toastSlideIn 0.3s ease forwards;
    min-width: 240px;
    line-height: 1.4;
    position: relative;
}
/* Màu nền theo type */
.toast-item.toast-success { background: #16a34a; }
.toast-item.toast-danger  { background: #dc2626; }
.toast-item.toast-warning { background: #d97706; color: #fff; }
.toast-item.toast-info    { background: #0ea5e9; }
/* Nút đóng X */
.toast-close {
    margin-left: auto;
    background: none;
    border: none;
    color: rgba(255,255,255,0.8);
    cursor: pointer;
    font-size: 1rem;
    padding: 0;
    line-height: 1;
    flex-shrink: 0;
}
.toast-close:hover { color: #fff; }
</style>

<script>
// Hiển thị 1 toast thông báo
// msg: nội dung; type: success|danger|warning|info
function showToast(msg, type) {
    var container = document.getElementById('toast-container');
    var toast = document.createElement('div');
    toast.className = 'toast-item toast-' + (type || 'info');

    // Icon theo type
    var icons = { success: 'bi-check-circle-fill', danger: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
    var iconClass = icons[type] || 'bi-info-circle-fill';

    toast.innerHTML =
        '<i class="bi ' + iconClass + '" style="font-size:1.1rem;flex-shrink:0;margin-top:1px"></i>' +
        '<span style="flex:1">' + msg + '</span>' +
        '<button class="toast-close" onclick="removeToast(this.parentElement)" title="Đóng">&times;</button>';

    container.appendChild(toast);

    // Tự động xóa sau 4 giây
    setTimeout(function() { removeToast(toast); }, 4000);
}

// Xóa toast với animation slide-out
function removeToast(el) {
    if (!el || !el.parentElement) return;
    el.style.animation = 'toastSlideOut 0.3s ease forwards';
    setTimeout(function() { if (el.parentElement) el.parentElement.removeChild(el); }, 280);
}

// Render flash messages từ PHP thành toasts khi trang load xong
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach (flash_get() as $f): ?>
    showToast(<?= json_encode($f['msg']) ?>, <?= json_encode($f['type']) ?>);
    <?php endforeach; ?>
});
</script>
<script>
// Scroll reveal: fade + slide up khi element vào viewport
(function() {
    var els = document.querySelectorAll('.job-card, .company-card, .stat-card, .category-card, .card');
    if (!els.length || !window.IntersectionObserver) return;

    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(e) {
            if (e.isIntersecting) {
                e.target.classList.add('revealed');
                observer.unobserve(e.target);
            }
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });

    els.forEach(function(el, i) {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.45s ease ' + (i % 4 * 0.07) + 's, transform 0.45s ease ' + (i % 4 * 0.07) + 's';
        observer.observe(el);
    });
})();
</script>
</body>
</html>
