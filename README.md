# PHP Job Website

Web tìm việc làm viết bằng PHP thuần + MySQL, kiến trúc monolithic đơn giản.
Chạy hoàn toàn bằng Docker, **không cần cài PHP / MySQL trên máy**.

## Yêu cầu
- Đã cài Docker và Docker Compose
- Trình duyệt (Chrome/Firefox...)
- Sửa code bằng bất kỳ IDE nào: IntelliJ IDEA, VS Code, PhpStorm... đều được (IDE chỉ để xem/sửa file, việc chạy là do Docker lo)

## Cách chạy

```bash
# từ trong thư mục dự án
docker compose up -d
```

Lần đầu chạy sẽ mất vài phút để kéo image.

Sau khi container khởi động xong, mở trình duyệt:

- App: http://localhost:8080/
- MySQL: localhost:3306 (user=`app`, pass=`app`, db=`job_website`)

Dừng:
```bash
docker compose down
```

Reset sạch DB (xoá volume, chạy lại `script.sql`):
```bash
docker compose down -v
docker compose up -d
```

## Tài khoản demo

Mật khẩu của tất cả tài khoản seed là **`123456`**.

| Email | Role |
|---|---|
| admin@example.com | admin |
| employer1@example.com | employer (FPT Software, FPT Telecom) |
| employer2@example.com | employer (VNG) |
| user1@example.com | user (ứng viên) |
| user2@example.com | user (ứng viên) |

## Cấu trúc thư mục

```
.
├── docker-compose.yml       # MySQL + PHP+Apache
├── script.sql               # Tạo bảng + seed data
├── CLAUDE.md                # Mô tả yêu cầu dự án
├── public/                  # Document root của Apache
│   ├── index.php            # Front controller
│   └── .htaccess
├── src/
│   ├── config.php           # Cấu hình DB, session
│   ├── db.php               # Kết nối PDO
│   ├── auth.php             # Login, role check
│   ├── helpers.php          # Các hàm tiện ích
│   ├── layout/              # header/footer chung
│   └── pages/               # Từng trang
│       ├── home.php, jobs.php, job_detail.php, companies.php
│       ├── login.php, register.php, logout.php
│       ├── user/            # Trang dành cho ứng viên
│       ├── employer/        # Trang dành cho nhà tuyển dụng
│       └── admin/           # Trang dành cho admin
└── uploads/cv/              # File CV upload sẽ lưu ở đây
```

## Luồng sử dụng

1. Mở http://localhost:8080 → vào trang chủ
2. Bấm **Đăng nhập** và dùng 1 tài khoản demo phía trên
   - Admin → chuyển vào trang `admin/dashboard`
   - Employer → chuyển vào trang `employer/dashboard`
   - User → chuyển vào trang `jobs` (danh sách việc làm)
3. Ứng viên:
   - Xem danh sách việc làm, công ty
   - Vào chi tiết job → **Ứng tuyển** → upload file CV (PDF/DOC/DOCX, <=5MB)
   - Xem lại tại **Đơn của tôi**
4. Nhà tuyển dụng:
   - **Đăng bài mới** gắn với 1 công ty mà mình sở hữu
   - Sửa/xoá bài của mình
   - Vào **Đơn ứng tuyển** để Accept/Reject từng đơn
5. Admin:
   - Quản lý user (đổi role, xoá)
   - Quản lý bài đăng, công ty, CV

## Ghi chú kỹ thuật

- **Route**: front controller `public/index.php?page=xxx` → map sang `src/pages/xxx.php`
- **Mật khẩu seed** được lưu dạng `PLAIN:123456`. Khi user seed đăng nhập lần đầu, code sẽ tự thay bằng hash `password_hash()` trong DB.
- **Upload CV** lưu vào `uploads/cv/` (đã được mount vào container tại `/var/www/uploads`)
- **Phân quyền**: đơn giản — 1 user ứng 1 role (`admin`/`employer`/`user`). Mỗi trang gọi `require_role(...)` để chặn.
- **Không dùng composer**, không dùng framework, không dùng build tool. Chỉ PHP thuần + Bootstrap CDN.
