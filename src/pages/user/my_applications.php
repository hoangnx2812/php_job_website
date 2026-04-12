<?php
// Ứng viên xem danh sách đơn ứng tuyển của mình, có nút tải CV
$u      = require_role('user');
$page   = max(1, (int)($_GET['p'] ?? 1));
$perPage = 10;

$countStmt = db()->prepare('SELECT COUNT(*) FROM applications WHERE user_id = ?');
$countStmt->execute([$u['id']]);
$total = (int)$countStmt->fetchColumn();

$stmt = db()->prepare("
    SELECT a.*, j.title, j.location, c.name AS company_name
    FROM applications a
    JOIN jobs j ON j.id = a.job_id
    JOIN companies c ON c.id = j.company_id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
    LIMIT $perPage OFFSET " . (($page - 1) * $perPage)
);
$stmt->execute([$u['id']]);
$rows = $stmt->fetchAll();

$baseUrl   = BASE_URL . '?page=user/my_applications';
$pageTitle = 'Đơn ứng tuyển của tôi';
require __DIR__ . '/../../layout/header.php';
?>
<!-- CSS cho timeline ứng tuyển -->
<style>
/* Timeline 3 bước nằm ngang: Đã nộp → Đang xét → Kết quả */
.app-timeline {
    display: flex;
    align-items: center;
    gap: 0;
    min-width: 160px;
}
.tl-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
}
/* Vòng tròn step */
.tl-dot {
    width: 24px; height: 24px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.7rem; font-weight: 700;
    background: #e2e8f0; color: #94a3b8;
    border: 2px solid #e2e8f0;
    transition: background 0.2s, color 0.2s;
}
.tl-label {
    font-size: 0.65rem;
    color: #94a3b8;
    font-weight: 500;
    white-space: nowrap;
}
/* Step active (xanh) */
.tl-step.active .tl-dot {
    background: #1a56db; color: #fff; border-color: #1a56db;
}
.tl-step.active .tl-label { color: #1a56db; }
/* Step reject (đỏ) */
.tl-step.reject .tl-dot {
    background: #dc2626; color: #fff; border-color: #dc2626;
}
.tl-step.reject .tl-label { color: #dc2626; }
/* Đường nối giữa các step */
.tl-line {
    flex: 1; height: 2px;
    background: #e2e8f0;
    min-width: 18px;
    transition: background 0.2s;
}
.tl-line.active { background: #1a56db; }
.tl-line.reject { background: #dc2626; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-700 mb-0">
        <i class="bi bi-file-earmark-text me-2 text-primary"></i>Đơn ứng tuyển của tôi
        <span class="badge bg-primary ms-2" style="font-size:0.75rem"><?= $total ?></span>
    </h4>
    <a href="<?= e(url('jobs')) ?>" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-search me-1"></i>Tìm việc thêm
    </a>
</div>

<?php if (!$rows): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>Bạn chưa có đơn ứng tuyển nào.
        <a href="<?= e(url('jobs')) ?>" class="alert-link">Xem danh sách việc làm</a>
    </div>
<?php else: ?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-admin mb-0">
            <thead>
            <tr>
                <th>Vị trí</th>
                <th>Công ty</th>
                <th>Địa điểm</th>
                <th>CV</th>
                <th>Tiến trình</th>
                <th>Ngày gửi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <?php
                $badgeCls    = ['pending'=>'badge-status-pending','accepted'=>'badge-status-accepted','rejected'=>'badge-status-rejected'][$r['status']] ?? 'badge-status-pending';
                $statusLabel = ['pending'=>'Chờ duyệt','accepted'=>'Đã nhận','rejected'=>'Từ chối'][$r['status']] ?? $r['status'];

                // Tính trạng thái từng bước timeline
                // Bước 1 (Đã nộp): luôn active
                // Bước 2 (Đang xem xét): active khi != pending
                // Bước 3 (Kết quả): active=accepted (xanh), fail=rejected (đỏ), inactive=pending
                $step2Active  = in_array($r['status'], ['accepted', 'rejected']);
                $step3Accept  = $r['status'] === 'accepted';
                $step3Reject  = $r['status'] === 'rejected';
                ?>
                <tr>
                    <td>
                        <a href="<?= e(url('job_detail', ['id' => $r['job_id']])) ?>"
                           class="fw-500 text-decoration-none small"><?= e($r['title']) ?></a>
                    </td>
                    <td class="small"><?= e($r['company_name']) ?></td>
                    <td class="small text-muted"><?= e($r['location']) ?></td>
                    <td>
                        <a href="<?= e(url('download_cv', ['id' => $r['id']])) ?>"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download me-1"></i>Tải CV
                        </a>
                    </td>
                    <td>
                        <!-- Badge trạng thái -->
                        <span class="<?= $badgeCls ?> d-block mb-2"><?= $statusLabel ?></span>

                        <!-- Timeline 3 bước: Đã nộp → Đang xem xét → Kết quả -->
                        <div class="app-timeline">
                            <!-- Bước 1: luôn active -->
                            <div class="tl-step active">
                                <div class="tl-dot"><i class="bi bi-check-lg"></i></div>
                                <div class="tl-label">Đã nộp</div>
                            </div>
                            <!-- Đường line nối bước 1 và 2 -->
                            <div class="tl-line <?= $step2Active ? 'active' : '' ?>"></div>
                            <!-- Bước 2: active nếu không còn pending -->
                            <div class="tl-step <?= $step2Active ? 'active' : '' ?>">
                                <div class="tl-dot"><?= $step2Active ? '<i class="bi bi-check-lg"></i>' : '2' ?></div>
                                <div class="tl-label">Đang xét</div>
                            </div>
                            <!-- Đường line nối bước 2 và 3 -->
                            <div class="tl-line <?= ($step3Accept || $step3Reject) ? ($step3Accept ? 'active' : 'reject') : '' ?>"></div>
                            <!-- Bước 3: xanh nếu accepted, đỏ nếu rejected, xám nếu pending -->
                            <div class="tl-step <?= $step3Accept ? 'active' : ($step3Reject ? 'reject' : '') ?>">
                                <div class="tl-dot">
                                    <?php if ($step3Accept): ?><i class="bi bi-check-lg"></i>
                                    <?php elseif ($step3Reject): ?><i class="bi bi-x-lg"></i>
                                    <?php else: ?>3<?php endif; ?>
                                </div>
                                <div class="tl-label">Kết quả</div>
                            </div>
                        </div>
                    </td>
                    <td class="small text-muted"><?= date('d/m/Y', strtotime($r['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">
    <?= render_pagination($total, $perPage, $page, $baseUrl) ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../../layout/footer.php';
