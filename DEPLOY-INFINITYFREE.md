# Hướng dẫn deploy lên InfinityFree (branch `uat`)

> Branch này đã được sửa sẵn để chạy trên InfinityFree. Bạn chỉ cần làm theo các bước dưới đây.

---

## 1. Tạo Database trên InfinityFree

1. Đăng nhập [dash.infinityfree.com](https://dash.infinityfree.com) → vào hosting account → **Control Panel**.
2. Mở **MySQL Databases**.
3. Tạo database mới với tên (phần đuôi sau `if0_41829346_`):

   ```
   jobs
   ```

   → DB đầy đủ sẽ là: **`if0_41829346_jobs`** (trùng với `DB_NAME` đã set trong [src/config.php](src/config.php)).

   ⚠️ Nếu bạn muốn dùng tên DB khác, phải sửa lại `DB_NAME` trong `src/config.php` cho khớp.

4. Bấm **Create Database**.

---

## 2. Import dữ liệu (script.sql)

1. Trong Control Panel, mở **phpMyAdmin** (chọn DB vừa tạo).
2. Vào tab **Import** → chọn file [script.sql](script.sql) ở local → bấm **Go**.
3. Đợi import xong, kiểm tra các bảng (`users`, `jobs`, `companies`, `applications`, ...) đã có dữ liệu seed.

---

## 3. Upload code lên hosting

### Cách A — File Manager (đơn giản nhất)
1. Nén toàn bộ project thành 1 file `.zip` (KHÔNG bao gồm `.git/`, `.idea/`, `node_modules/` nếu có).

   Trên Windows PowerShell, từ thư mục project:
   ```powershell
   Compress-Archive -Path index.php, .htaccess, public, src, uploads -DestinationPath deploy.zip -Force
   ```

2. Vào File Manager của InfinityFree → mở thư mục **`htdocs`**.
3. Bấm **Upload & Unzip** → chọn `deploy.zip` → đợi giải nén.

### Cách B — FTP (FileZilla)
1. Lấy thông tin FTP: Control Panel → **FTP Accounts**.
2. Mở FileZilla, kết nối, kéo các thư mục/file sau vào `htdocs/`:
   - `index.php`
   - `.htaccess`
   - `public/`
   - `src/`
   - `uploads/`

### Những file/thư mục KHÔNG cần upload (chỉ phục vụ dev local):
- `.git/`, `.idea/`, `.claude/`
- `docker-compose.yml`, `docker/`
- `CLAUDE.md`, `README.md`, `plan.md`, `presentation.html`
- `script.sql` (đã import qua phpMyAdmin rồi)
- `DEPLOY-INFINITYFREE.md` (file này)

> Nếu lỡ upload mấy file `.md`, `.sql`, `docker-compose.yml` thì cũng không sao —
> root `.htaccess` đã chặn không cho truy cập từ ngoài rồi.

---

## 4. Cấu trúc cuối cùng trên server

Sau khi upload xong, thư mục `htdocs/` của bạn sẽ trông như sau:

```
htdocs/
├── .htaccess              ← rewrite rule + chặn folder nhạy cảm
├── index.php              ← entry point, uỷ quyền cho public/index.php
├── public/
│   ├── .htaccess
│   └── index.php          ← front-controller thật sự
├── src/                   ← code PHP (đã chặn truy cập trực tiếp)
│   ├── config.php         ← đã set sẵn DB credentials InfinityFree
│   ├── db.php
│   ├── auth.php
│   ├── helpers.php
│   ├── layout/
│   └── pages/
└── uploads/
    ├── avatars/
    ├── logos/
    └── cv/                ← có .htaccess chặn truy cập trực tiếp,
                              CV chỉ tải được qua download_cv.php (check quyền)
```

---

## 5. Truy cập website

Mở trình duyệt vào domain của bạn, ví dụ:

```
https://<your-domain>.infinityfreeapp.com/
```

→ Sẽ vào trang chủ. Các URL còn lại đều có dạng `?page=...`:
- `/` — Trang chủ
- `/?page=jobs` — Danh sách việc làm
- `/?page=login` — Đăng nhập
- `/?page=admin/dashboard` — Admin (sau khi login bằng tài khoản admin)

---

## 6. Tài khoản test (lấy từ seed `script.sql`)

Mật khẩu mặc định cho tất cả user trong seed: kiểm tra cuối file [script.sql](script.sql) (hash `password_hash` của một mật khẩu chung). Nếu seed dùng `123456` thì login bằng `123456`.

---

## 7. Khi gặp lỗi

| Triệu chứng | Cách xử lý |
|-------------|-----------|
| `Không kết nối được MySQL` | Kiểm tra DB_NAME đã tạo đúng chưa, host `sql204.infinityfree.com` còn đúng không (InfinityFree thỉnh thoảng đổi). |
| Trang trắng / lỗi 500 | Vào Control Panel → **Error Logs** xem chi tiết. Tạm bật lại `display_errors` trong `src/config.php` để debug. |
| 403 Forbidden khi vào `/uploads/...` | Đúng rồi (chỉ folder `cv/` bị chặn). Logo và avatar vẫn xem được bình thường. |
| Upload CV/logo fail | Check quyền thư mục `uploads/cv/` và `uploads/logos/` phải là `755` hoặc `777`. |
| URL bị 404 sau redirect | Kiểm tra `mod_rewrite` đã bật và file `.htaccess` ở root htdocs/ đã upload thành công. |

---

## 8. Khi đổi credentials hoặc tên DB

Chỉ cần sửa file [src/config.php](src/config.php) (4 dòng `define`) rồi upload đè lên server. Không cần build / migrate gì cả.
