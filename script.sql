-- =========================================================
-- script.sql - Tạo toàn bộ bảng và seed data fake
-- File này được Docker MySQL tự chạy lần đầu khi khởi tạo DB
-- =========================================================

-- Ép toàn bộ session dùng utf8mb4 để text tiếng Việt không bị lỗi font
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
SET CHARACTER SET utf8mb4;

ALTER DATABASE job_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE job_website;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS companies;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------
-- Bảng users: lưu tất cả người dùng, phân biệt qua cột role
-- role: admin | employer | user
-- ---------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,             -- hash bằng password_hash()
    full_name VARCHAR(150) NOT NULL,
    role ENUM('admin','employer','user') NOT NULL DEFAULT 'user',
    phone VARCHAR(30) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Mật khẩu gốc của toàn bộ user seed: 123456
-- Dùng prefix PLAIN: để code PHP nhận biết và tự hash lại khi login lần đầu.
-- (Cách này giúp seed SQL không cần hard-code bcrypt hash.)
INSERT INTO users (email, password, full_name, role, phone) VALUES
('admin@example.com',    'PLAIN:123456', 'Quản trị viên',  'admin',    '0900000001'),
('employer1@example.com','PLAIN:123456', 'Nguyễn HR FPT',  'employer', '0900000002'),
('employer2@example.com','PLAIN:123456', 'Trần HR VNG',    'employer', '0900000003'),
('user1@example.com',    'PLAIN:123456', 'Lê Văn A',       'user',     '0900000004'),
('user2@example.com',    'PLAIN:123456', 'Phạm Thị B',     'user',     '0900000005');

-- ---------------------------------------------------------
-- Bảng companies: công ty, mỗi employer gắn với 1 company
-- ---------------------------------------------------------
CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,                      -- employer sở hữu công ty
    name VARCHAR(200) NOT NULL,
    description TEXT NULL,
    location VARCHAR(200) NULL,
    website VARCHAR(200) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO companies (owner_id, name, description, location, website) VALUES
(2, 'FPT Software',   'Công ty phần mềm hàng đầu Việt Nam, chuyên outsource cho thị trường Nhật và Mỹ.', 'Hà Nội',       'https://fptsoftware.com'),
(3, 'VNG Corporation','Công ty công nghệ với các sản phẩm Zalo, ZaloPay, game online.',                 'TP. Hồ Chí Minh','https://vng.com.vn'),
(2, 'FPT Telecom',    'Nhà cung cấp dịch vụ Internet và truyền hình.',                                  'Hà Nội',       'https://fpt.vn');

-- ---------------------------------------------------------
-- Bảng jobs: bài đăng tuyển dụng
-- ---------------------------------------------------------
CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    employer_id INT NOT NULL,                   -- user role=employer đã đăng bài
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT NULL,
    location VARCHAR(200) NULL,
    salary VARCHAR(100) NULL,                   -- để dạng text cho linh hoạt: "15-25 triệu"
    job_type ENUM('full-time','part-time','intern','contract') NOT NULL DEFAULT 'full-time',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO jobs (company_id, employer_id, title, description, requirements, location, salary, job_type) VALUES
(1, 2, 'PHP Backend Developer',   'Phát triển backend cho các dự án thương mại điện tử.',   'Thành thạo PHP, MySQL, có kinh nghiệm Laravel là lợi thế.', 'Hà Nội',          '15-25 triệu', 'full-time'),
(1, 2, 'Java Fresher',             'Tham gia phát triển dự án ngân hàng cho khách hàng Nhật.','Tốt nghiệp CNTT, biết Java cơ bản, tiếng Anh đọc hiểu.',    'Hà Nội',          '8-12 triệu',  'full-time'),
(2, 3, 'Frontend ReactJS',         'Xây dựng giao diện sản phẩm Zalo Mini App.',              '2+ năm kinh nghiệm React, hiểu TypeScript.',                'TP. Hồ Chí Minh', '20-35 triệu', 'full-time'),
(3, 2, 'Nhân viên hỗ trợ kỹ thuật','Hỗ trợ khách hàng sử dụng dịch vụ Internet.',             'Giao tiếp tốt, chịu được áp lực.',                          'Hà Nội',          '8-10 triệu',  'part-time');

-- ---------------------------------------------------------
-- Bảng applications: đơn ứng tuyển của user vào job
-- ---------------------------------------------------------
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    user_id INT NOT NULL,
    cv_file VARCHAR(255) NOT NULL,              -- tên file lưu trong uploads/cv/
    cover_letter TEXT NULL,
    status ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO applications (job_id, user_id, cv_file, cover_letter, status) VALUES
(1, 4, 'sample_cv_1.pdf', 'Em rất quan tâm vị trí PHP Backend của quý công ty.', 'pending'),
(2, 4, 'sample_cv_1.pdf', 'Em là sinh viên mới ra trường, mong được học hỏi.',   'accepted'),
(3, 5, 'sample_cv_2.pdf', 'Tôi có 3 năm kinh nghiệm React và muốn thử sức tại VNG.','pending');
