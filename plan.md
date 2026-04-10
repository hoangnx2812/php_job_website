# Plan phát triển tiếp

Mục tiêu: **đồ án qua môn**, giữ đơn giản, không đi sâu bảo mật.

## Nguyên tắc
- Không phức tạp hoá phần authentication (bỏ CSRF token, rate limit, session regenerate...)
- Không làm email notification
- Không soft delete / audit log / full-text search
- Ưu tiên tính năng thấy được trên UI, dễ demo

---

## ✅ Đã hoàn thành (kể cả nâng cấp UI)

- ✅ Refactor employer ↔ company (1-1), UNIQUE owner_id
- ✅ Download / xem CV (kiểm tra quyền theo role)
- ✅ Trang profile user (sửa thông tin + đổi mật khẩu)
- ✅ Phân trang (pagination) toàn bộ danh sách
- ✅ Lọc job nâng cao (job_type + salary_min/max)
- ✅ Lưu job yêu thích (saved_jobs)
- ✅ Logo công ty (upload + hiển thị)
- ✅ Thống kê admin bằng Chart.js
- ✅ phpMyAdmin trong docker-compose (port 8081)
- ✅ Fix fw-500/fw-600/fw-700 CSS utility classes
- ✅ Trang chi tiết công ty (`company_detail.php`) — xem info + tất cả job của công ty
- ✅ Toggle ẩn/hiện job cho employer và admin (nút mắt bên cạnh nút xoá)
- ✅ Related jobs trên job_detail.php — "Vị trí khác tại công ty này"
- ✅ Footer đẹp hơn (4 cột: brand, ứng viên, nhà tuyển dụng, tìm kiếm nhanh)
- ✅ Home.php: section "Top công ty tuyển dụng" với logo

---

## 🔄 Đang làm

### Luồng đăng ký Employer có Admin duyệt

**Vấn đề với luồng cũ**
- Employer đăng ký xong là có ngay role=employer, không qua kiểm duyệt.
- Admin tạo công ty thủ công trong admin panel — không hợp lý.

**Luồng mới**
1. Trang đăng ký (`register.php`) **chỉ tạo tài khoản role=user** (bỏ option chọn employer).
2. User đã đăng nhập vào trang **"Trở thành nhà tuyển dụng"** (`user/become_employer.php`):
   - Điền thông tin công ty (tên, địa điểm, website, mô tả).
   - Submit → tạo bản ghi `employer_requests` với status=`pending`.
   - Thông báo: "Yêu cầu đã gửi, chờ admin duyệt."
3. Admin vào **`admin/employer_requests.php`**:
   - Xem danh sách yêu cầu (pending / approved / rejected).
   - Nút **Duyệt**: UPDATE users SET role='employer', INSERT INTO companies, UPDATE request status='approved'.
   - Nút **Từ chối**: UPDATE request status='rejected', có thể ghi lý do.
4. Khi được duyệt, user đăng nhập lại sẽ thấy role=employer và có công ty.

**Schema cần thêm**
```sql
CREATE TABLE employer_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_name VARCHAR(200) NOT NULL,
    company_description TEXT NULL,
    company_location VARCHAR(200) NULL,
    company_website VARCHAR(200) NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    admin_note VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Các file cần thay đổi**
- `script.sql`: Thêm bảng `employer_requests` + seed
- `register.php`: Bỏ option chọn role employer, chỉ tạo role=user
- `user/become_employer.php`: Thay vì tạo company ngay → INSERT employer_requests
- Mới: `admin/employer_requests.php`: Danh sách + Duyệt / Từ chối
- `admin/dashboard.php`: Hiển thị số yêu cầu đang chờ duyệt
- `public/index.php`: Thêm route `admin/employer_requests`
- `header.php`: Link "Trở thành NTD" chỉ hiện khi role=user và chưa có request pending

---

## Gói "nếu còn thời gian"

### Rich text editor cho mô tả job
- Dùng TinyMCE CDN (1 dòng script)
- Áp dụng cho textarea `description` và `requirements` trong `employer/job_form.php`
- Khi render ra ở `job_detail.php`: bỏ `nl2br(e(...))`, dùng `e()` hoặc whitelist tag cơ bản
