</main>

<footer class="mt-5 pt-5 pb-4" style="background:#1e293b;color:#94a3b8">
    <div class="container">
        <div class="row g-4 mb-4">
            <!-- Brand -->
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2"
                         style="width:32px;height:32px;background:rgba(255,255,255,0.12)">
                        <i class="bi bi-briefcase-fill text-white" style="font-size:0.9rem"></i>
                    </div>
                    <span class="text-white fw-700 fs-5">JobVN</span>
                </div>
                <p class="small mb-3" style="line-height:1.6">
                    Nền tảng tuyển dụng kết nối ứng viên tài năng với các nhà tuyển dụng hàng đầu Việt Nam.
                </p>
                <div class="d-flex gap-2">
                    <a href="#" class="d-flex align-items-center justify-content-center rounded-2 text-white"
                       style="width:32px;height:32px;background:rgba(255,255,255,0.1);text-decoration:none">
                        <i class="bi bi-facebook" style="font-size:0.85rem"></i>
                    </a>
                    <a href="#" class="d-flex align-items-center justify-content-center rounded-2 text-white"
                       style="width:32px;height:32px;background:rgba(255,255,255,0.1);text-decoration:none">
                        <i class="bi bi-linkedin" style="font-size:0.85rem"></i>
                    </a>
                    <a href="#" class="d-flex align-items-center justify-content-center rounded-2 text-white"
                       style="width:32px;height:32px;background:rgba(255,255,255,0.1);text-decoration:none">
                        <i class="bi bi-twitter-x" style="font-size:0.85rem"></i>
                    </a>
                </div>
            </div>

            <!-- Ứng viên -->
            <div class="col-md-2 col-6">
                <h6 class="text-white fw-600 mb-3">Ứng viên</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= e(url('jobs')) ?>" class="text-decoration-none" style="color:#94a3b8">Tìm việc làm</a></li>
                    <li class="mb-2"><a href="<?= e(url('companies')) ?>" class="text-decoration-none" style="color:#94a3b8">Danh sách công ty</a></li>
                    <li class="mb-2"><a href="<?= e(url('register')) ?>" class="text-decoration-none" style="color:#94a3b8">Đăng ký tài khoản</a></li>
                    <li class="mb-2"><a href="<?= e(url('user/saved_jobs')) ?>" class="text-decoration-none" style="color:#94a3b8">Job đã lưu</a></li>
                </ul>
            </div>

            <!-- Nhà tuyển dụng -->
            <div class="col-md-2 col-6">
                <h6 class="text-white fw-600 mb-3">Nhà tuyển dụng</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= e(url('user/become_employer')) ?>" class="text-decoration-none" style="color:#94a3b8">Đăng ký tuyển dụng</a></li>
                    <li class="mb-2"><a href="<?= e(url('employer/job_form')) ?>" class="text-decoration-none" style="color:#94a3b8">Đăng bài tuyển dụng</a></li>
                    <li class="mb-2"><a href="<?= e(url('employer/applications')) ?>" class="text-decoration-none" style="color:#94a3b8">Quản lý ứng viên</a></li>
                </ul>
            </div>

            <!-- Loại công việc nhanh -->
            <div class="col-md-4">
                <h6 class="text-white fw-600 mb-3">Tìm kiếm nhanh</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach (['full-time','part-time','intern','contract'] as $t): ?>
                        <a href="<?= e(url('jobs', ['job_type' => $t])) ?>"
                           class="badge text-decoration-none"
                           style="background:rgba(255,255,255,0.1);color:#cbd5e1;font-size:0.8rem;padding:0.4em 0.75em;border-radius:6px">
                            <?= $t ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="mt-3 small" style="color:#64748b">
                    <i class="bi bi-envelope me-1"></i> contact@jobvn.demo<br>
                    <i class="bi bi-telephone me-1 mt-1"></i> 1900 xxxx
                </div>
            </div>
        </div>

        <hr style="border-color:rgba(255,255,255,0.08);margin:0 0 1rem">
        <div class="d-flex flex-wrap justify-content-between align-items-center small">
            <span>&copy; <?= date('Y') ?> JobVN — Demo PHP Job Website.</span>
            <span>Built with PHP + Bootstrap 5 + MySQL</span>
        </div>
    </div>
</footer>

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
</body>
</html>
