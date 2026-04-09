# Plan phát triển tiếp

Danh sách các tính năng sẽ làm sau để hoàn thiện dự án.
Mục tiêu: **đồ án qua môn**, giữ đơn giản, không đi sâu bảo mật.

## Nguyên tắc
- Không phức tạp hoá phần authentication (bỏ CSRF token, rate limit, session regenerate...)
- Không làm email notification
- Không soft delete / audit log / full-text search
- Ưu tiên tính năng thấy được trên UI, dễ demo

---

## Gói "nên làm" (ưu tiên cao)

### 0. [LÀM ĐẦU TIÊN] Refactor luồng Employer ↔ Company (1 employer = 1 company)

**Vấn đề hiện tại**
- Đăng ký chỉ có 3 field (email/tên/mật khẩu) + chọn role.
- Khi employer đăng bài có dropdown "Chọn công ty" — không hợp lý vì:
  - 1 employer thực tế chỉ đại diện cho 1 công ty duy nhất
  - Công ty hiện do admin tạo thủ công, employer tự nhiên không có công ty nào → không đăng bài được
- Seed data đang có trường hợp 1 employer sở hữu nhiều công ty (employer1 có cả FPT Software + FPT Telecom) — cần sửa.

**Quy tắc mới**
- **1 user role=employer ⟷ đúng 1 company** (quan hệ 1-1)
- User thường (role=user) muốn trở thành employer thì phải "đăng ký làm nhà tuyển dụng" và **bắt buộc nhập thông tin công ty** luôn trong form đó.
- Khi đăng bài, **không còn dropdown chọn công ty** — tự động gắn với công ty của chính employer đó.

**Việc cần làm**

1. **Schema (`script.sql`)**
   - Thêm `UNIQUE KEY` trên `companies.owner_id` để ép 1-1
   - Sửa seed: mỗi employer chỉ sở hữu 1 công ty (xoá "FPT Telecom" hoặc gắn cho employer khác)
   - Cân nhắc: có thể thêm cột `users.company_id` để truy cập nhanh, nhưng không bắt buộc — có thể join qua `companies.owner_id`

2. **Trang đăng ký (`src/pages/register.php`)**
   - Khi user chọn role `employer`, form JavaScript show thêm block "Thông tin công ty":
     - Tên công ty (bắt buộc)
     - Địa điểm
     - Website
     - Mô tả
   - Khi submit với role=employer: tạo user + tạo company trong **1 transaction**, set `companies.owner_id = user.id`
   - Khi submit với role=user: như hiện tại

3. **Trang "Trở thành nhà tuyển dụng" (tuỳ chọn, cho user đã đăng ký sẵn)**
   - Tạo `src/pages/user/become_employer.php`
   - Chỉ user role=user mới vào được
   - Form nhập thông tin công ty → tạo company + update `users.role = 'employer'`
   - Link nút "Trở thành nhà tuyển dụng" ở navbar hoặc profile

4. **Form đăng bài (`src/pages/employer/job_form.php`)**
   - **Bỏ dropdown chọn công ty**
   - Tự lấy `company_id` bằng query: `SELECT id FROM companies WHERE owner_id = ?`
   - Nếu không có (edge case) → redirect sang trang tạo company hoặc báo lỗi

5. **Trang quản lý công ty của employer (`src/pages/employer/company.php`)**
   - Employer xem và sửa thông tin công ty của chính mình (tên, địa điểm, website, mô tả, logo sau này)
   - Link "Công ty của tôi" trong navbar/dashboard

6. **Admin (`src/pages/admin/companies.php`)**
   - Khi tạo company mới, chọn owner từ danh sách employer **chưa có company**
   - Không cho 1 employer có 2 company (enforce bằng UNIQUE + check trong form)

7. **Reset DB** sau khi đổi schema:
   ```bash
   docker compose down -v && docker compose up -d
   ```

---

### 1. Download / xem CV
- Thêm route `download_cv.php` (hoặc `?page=download_cv&id=...`)
- Kiểm tra quyền:
  - `admin` → tải mọi CV
  - `employer` → chỉ tải CV của đơn ứng tuyển vào job của mình
  - `user` → chỉ tải CV của chính mình
- Thêm nút "Tải CV" ở:
  - `src/pages/employer/applications.php`
  - `src/pages/admin/applications.php`
  - `src/pages/user/my_applications.php`
- File đọc từ `UPLOAD_DIR`, dùng `readfile()` + header `Content-Disposition: attachment`

### 2. Trang profile user
- Tạo `src/pages/user/profile.php` (và cả cho employer/admin nếu muốn)
- Cho phép sửa: `full_name`, `email`, `phone`, đổi mật khẩu (nhập mật khẩu cũ)
- Link "Hồ sơ cá nhân" trong navbar khi đã đăng nhập

### 3. Phân trang (pagination)
- Áp dụng cho các trang danh sách:
  - `src/pages/jobs.php`
  - `src/pages/companies.php`
  - `src/pages/admin/users.php`, `admin/jobs.php`, `admin/applications.php`
  - `src/pages/employer/jobs.php`, `employer/applications.php`
- Cách đơn giản: `?p=2`, `LIMIT 10 OFFSET (p-1)*10`
- Hiển thị thanh số trang cơ bản bằng Bootstrap `.pagination`

### 4. Lọc job nâng cao
- Mở rộng form search ở `src/pages/jobs.php`:
  - Thêm select `job_type` (full-time/part-time/intern/contract)
  - Thêm 2 input mức lương min/max (hoặc select khoảng lương cố định)
- Đổi cột `jobs.salary` từ `VARCHAR` sang 2 cột `salary_min INT`, `salary_max INT` để lọc số được.
  - Cập nhật lại `script.sql` (tạo bảng + seed)
  - Cập nhật `job_form.php`, `job_detail.php`, `jobs.php`, `home.php` để dùng 2 cột mới
  - Format hiển thị: `{min}-{max} triệu`

### 5. Lưu job yêu thích
- Tạo bảng mới trong `script.sql`:
  ```sql
  CREATE TABLE saved_jobs (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT NOT NULL,
      job_id INT NOT NULL,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      UNIQUE KEY uniq_user_job (user_id, job_id),
      FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
      FOREIGN KEY (job_id)  REFERENCES jobs(id)  ON DELETE CASCADE
  );
  ```
- Thêm vài bản ghi seed
- Nút ❤ "Lưu" ở `job_detail.php` và card `jobs.php` (chỉ hiện khi đã login, role=user)
- Trang `src/pages/user/saved_jobs.php` — danh sách job đã lưu
- Link "Job đã lưu" trong navbar khi role=user

### 6. Logo công ty
- Thêm cột `logo` VARCHAR(255) vào bảng `companies` (trong `script.sql`)
- Thư mục upload mới: `uploads/logos/`, mount vào docker-compose
- Admin form `admin/companies.php` cho upload logo
- Employer có thể sửa logo công ty của mình (tạo `employer/company_form.php`)
- Hiển thị `<img>` logo trong: `companies.php`, `job_detail.php`, card jobs

---

## Gói "nếu còn thời gian"

### 7. Rich text editor cho mô tả job
- Dùng TinyMCE CDN (1 dòng script)
- Áp dụng cho textarea `description` và `requirements` trong `employer/job_form.php`
- Khi render ra ở `job_detail.php`: bỏ `nl2br(e(...))`, dùng `e()` cẩn thận hoặc whitelist tag cơ bản

### 8. Thống kê admin bằng Chart.js
- Thêm Chart.js CDN vào `admin/dashboard.php`
- Vẽ:
  - Biểu đồ cột: số job tạo mới theo ngày (7/30 ngày gần nhất)
  - Biểu đồ tròn: tỉ lệ đơn theo trạng thái (pending/accepted/rejected)
  - Biểu đồ cột: số user theo role
- Query MySQL bằng `GROUP BY DATE(created_at)`, encode JSON, truyền cho JS

### 9. phpMyAdmin trong docker-compose
- Thêm service `phpmyadmin/phpmyadmin` vào `docker-compose.yml`
- Expose port 8081
- Env: `PMA_HOST=mysql`, `PMA_USER=root`, `PMA_PASSWORD=root`
- Ghi chú: chỉ để hỗ trợ dev xem DB, không phải tính năng của web

---

## Thứ tự gợi ý khi làm
0. **[BẮT BUỘC LÀM TRƯỚC]** Refactor employer ↔ company (1-1), ép nhập thông tin công ty khi đăng ký làm nhà tuyển dụng
1. Download CV (quan trọng, chức năng upload chưa trọn)
2. Profile user
3. Phân trang
4. Lọc job nâng cao (cần đổi schema `salary` → reset DB)
5. Lưu job yêu thích
6. Logo công ty
7. (Tuỳ chọn) Thống kê admin bằng Chart.js
8. (Tuỳ chọn) Rich text editor
9. (Tuỳ chọn) phpMyAdmin

Mỗi khi đổi schema nhớ reset DB: `docker compose down -v && docker compose up -d`.
