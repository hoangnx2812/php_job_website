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
</body>
</html>
