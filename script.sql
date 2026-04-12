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
DROP TABLE IF EXISTS saved_jobs;
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
INSERT INTO users (email, password, full_name, role, phone) VALUES
('admin@example.com',      'PLAIN:123456', 'Quản trị viên',        'admin',    '0900000001'),
-- Employer gốc (3 người)
('employer1@example.com',  'PLAIN:123456', 'Nguyễn HR FPT',        'employer', '0900000002'),
('employer2@example.com',  'PLAIN:123456', 'Trần HR VNG',          'employer', '0900000003'),
('employer3@example.com',  'PLAIN:123456', 'Lê HR Tiki',           'employer', '0900000006'),
-- Employer mới: 6 công ty nổi tiếng VN
('employer4@example.com',  'PLAIN:123456', 'Phạm HR Shopee',       'employer', '0900000007'),
('employer5@example.com',  'PLAIN:123456', 'Hoàng HR Grab',        'employer', '0900000008'),
('employer6@example.com',  'PLAIN:123456', 'Vũ HR MoMo',           'employer', '0900000009'),
('employer7@example.com',  'PLAIN:123456', 'Đặng HR VNPT',         'employer', '0900000010'),
('employer8@example.com',  'PLAIN:123456', 'Bùi HR Viettel',       'employer', '0900000011'),
('employer9@example.com',  'PLAIN:123456', 'Ngô HR Sacombank',     'employer', '0900000012'),
-- User ứng viên gốc (2 người)
('user1@example.com',      'PLAIN:123456', 'Lê Văn A',             'user',     '0900000004'),
('user2@example.com',      'PLAIN:123456', 'Phạm Thị B',           'user',     '0900000005'),
-- Ứng viên mới (8 người)
('user3@example.com',      'PLAIN:123456', 'Trần Minh Khoa',       'user',     '0900000013'),
('user4@example.com',      'PLAIN:123456', 'Nguyễn Thị Lan',       'user',     '0900000014'),
('user5@example.com',      'PLAIN:123456', 'Võ Đức Thành',         'user',     '0900000015'),
('user6@example.com',      'PLAIN:123456', 'Đinh Thị Hương',       'user',     '0900000016'),
('user7@example.com',      'PLAIN:123456', 'Lý Quốc Bảo',          'user',     '0900000017'),
('user8@example.com',      'PLAIN:123456', 'Phan Thị Tuyết',       'user',     '0900000018'),
('user9@example.com',      'PLAIN:123456', 'Hồ Văn Dũng',          'user',     '0900000019'),
('user10@example.com',     'PLAIN:123456', 'Mai Thị Thu',           'user',     '0900000020');

-- ---------------------------------------------------------
-- Bảng companies: công ty, mỗi employer gắn với đúng 1 company
-- Ràng buộc UNIQUE(owner_id) đảm bảo quan hệ 1-1 employer↔company
-- ---------------------------------------------------------
CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,                      -- employer sở hữu công ty
    name VARCHAR(200) NOT NULL,
    description TEXT NULL,
    location VARCHAR(200) NULL,
    website VARCHAR(200) NULL,
    logo VARCHAR(255) NULL,                     -- tên file logo lưu trong uploads/logos/
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_company_owner (owner_id),     -- mỗi employer chỉ có 1 công ty
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO companies (owner_id, name, description, location, website) VALUES
-- Công ty gốc (3 công ty)
(2, 'FPT Software',       'Công ty phần mềm hàng đầu Việt Nam, chuyên outsource cho thị trường Nhật và Mỹ.',    'Hà Nội',          'https://fptsoftware.com'),
(3, 'VNG Corporation',    'Công ty công nghệ với các sản phẩm Zalo, ZaloPay, game online.',                     'TP. Hồ Chí Minh', 'https://vng.com.vn'),
(4, 'Tiki',               'Sàn thương mại điện tử hàng đầu Việt Nam.',                                          'TP. Hồ Chí Minh', 'https://tiki.vn'),
-- Công ty mới (6 công ty nổi tiếng VN)
(5, 'Shopee Vietnam',     'Nền tảng thương mại điện tử hàng đầu Đông Nam Á, vận hành tại Việt Nam.',            'TP. Hồ Chí Minh', 'https://shopee.vn'),
(6, 'Grab Vietnam',       'Ứng dụng gọi xe và giao đồ ăn lớn nhất Đông Nam Á.',                                 'TP. Hồ Chí Minh', 'https://grab.com'),
(7, 'MoMo',               'Ví điện tử MoMo - nền tảng thanh toán di động phổ biến nhất Việt Nam.',              'TP. Hồ Chí Minh', 'https://momo.vn'),
(8, 'VNPT',               'Tập đoàn Bưu chính Viễn thông Việt Nam, cung cấp dịch vụ CNTT và viễn thông.',       'Hà Nội',          'https://vnpt.vn'),
(9, 'Viettel',            'Tập đoàn Công nghiệp Viễn thông Quân đội, mạng di động lớn nhất Việt Nam.',          'Hà Nội',          'https://viettel.vn'),
(10, 'Sacombank',         'Ngân hàng thương mại cổ phần Sài Gòn Thương Tín, TOP 5 ngân hàng tư nhân VN.',       'TP. Hồ Chí Minh', 'https://sacombank.com.vn');

-- ---------------------------------------------------------
-- Bảng jobs: bài đăng tuyển dụng
-- salary_min / salary_max: lương dạng số nguyên (đơn vị: triệu VND)
-- category: lĩnh vực công việc để lọc theo ngành nghề
-- ---------------------------------------------------------
CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    employer_id INT NOT NULL,                   -- user role=employer đã đăng bài
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT NULL,
    location VARCHAR(200) NULL,
    salary_min INT NULL,                        -- lương tối thiểu (triệu VND), NULL = thỏa thuận
    salary_max INT NULL,                        -- lương tối đa (triệu VND)
    job_type ENUM('full-time','part-time','intern','contract') NOT NULL DEFAULT 'full-time',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_hot TINYINT(1) NOT NULL DEFAULT 0,       -- job nổi bật, hiển thị HOT badge
    views INT NOT NULL DEFAULT 0,               -- lượt xem
    expired_at DATETIME NULL,                   -- hạn nộp hồ sơ (NULL = không giới hạn)
    category VARCHAR(50) NOT NULL DEFAULT 'Công nghệ thông tin',  -- lĩnh vực: IT, Marketing, v.v.
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Jobs gốc (5 bài)
INSERT INTO jobs (company_id, employer_id, title, description, requirements, location, salary_min, salary_max, job_type, is_hot, views, expired_at, category) VALUES
(1, 2, 'PHP Backend Developer',
 'Phát triển backend cho các dự án thương mại điện tử quy mô lớn.',
 'Thành thạo PHP, MySQL, có kinh nghiệm Laravel là lợi thế.',
 'Hà Nội', 15, 25, 'full-time', 1, 320, DATE_ADD(NOW(), INTERVAL 30 DAY), 'Công nghệ thông tin'),

(1, 2, 'Java Fresher',
 'Tham gia phát triển dự án ngân hàng cho khách hàng Nhật.',
 'Tốt nghiệp CNTT, biết Java cơ bản, tiếng Anh đọc hiểu.',
 'Hà Nội', 8, 12, 'full-time', 0, 185, DATE_ADD(NOW(), INTERVAL 14 DAY), 'Công nghệ thông tin'),

(2, 3, 'Frontend ReactJS',
 'Xây dựng giao diện sản phẩm Zalo Mini App.',
 '2+ năm kinh nghiệm React, hiểu TypeScript.',
 'TP. Hồ Chí Minh', 20, 35, 'full-time', 1, 512, DATE_ADD(NOW(), INTERVAL 45 DAY), 'Công nghệ thông tin'),

(3, 4, 'Mobile Developer iOS',
 'Phát triển ứng dụng Tiki trên nền tảng iOS.',
 '2+ năm Swift/SwiftUI, có app trên AppStore là lợi thế.',
 'TP. Hồ Chí Minh', 18, 30, 'full-time', 0, 230, DATE_ADD(NOW(), INTERVAL 20 DAY), 'Công nghệ thông tin'),

(3, 4, 'Data Analyst Intern',
 'Phân tích dữ liệu bán hàng, lập báo cáo tuần.',
 'Sinh viên năm 3-4 CNTT/Kinh tế, biết SQL và Excel.',
 'TP. Hồ Chí Minh', 4, 7, 'intern', 0, 98, DATE_ADD(NOW(), INTERVAL 7 DAY), 'Công nghệ thông tin');

-- Jobs mới (15 bài đa dạng lĩnh vực, địa điểm, lương)
INSERT INTO jobs (company_id, employer_id, title, description, requirements, location, salary_min, salary_max, job_type, is_hot, views, expired_at, category) VALUES

-- Shopee: IT + Marketing
(4, 5, 'Senior Java Backend Engineer',
 'Xây dựng hệ thống backend phục vụ hàng triệu người dùng trên nền tảng Shopee.',
 '4+ năm Java, Spring Boot, kinh nghiệm microservices, hiểu Kafka/Redis.',
 'TP. Hồ Chí Minh', 35, 60, 'full-time', 1, 890, DATE_ADD(NOW(), INTERVAL 60 DAY), 'Công nghệ thông tin'),

(4, 5, 'Digital Marketing Executive',
 'Lên kế hoạch và triển khai các chiến dịch marketing online cho Shopee tại thị trường Việt Nam.',
 '2+ năm kinh nghiệm digital marketing, thành thạo Facebook Ads, Google Ads, TikTok Ads.',
 'TP. Hồ Chí Minh', 15, 22, 'full-time', 0, 310, DATE_ADD(NOW(), INTERVAL 25 DAY), 'Marketing'),

-- Grab: IT + Vận hành
(5, 6, 'Android Developer',
 'Phát triển tính năng mới cho ứng dụng Grab trên nền tảng Android.',
 '3+ năm Kotlin/Java Android, kinh nghiệm làm việc với API REST, hiểu về CI/CD.',
 'TP. Hồ Chí Minh', 25, 45, 'full-time', 1, 620, DATE_ADD(NOW(), INTERVAL 40 DAY), 'Công nghệ thông tin'),

(5, 6, 'Nhân viên Vận hành Đối tác Tài xế',
 'Hỗ trợ, đào tạo và quản lý đối tác tài xế trong khu vực TP.HCM.',
 'Tốt nghiệp đại học, giao tiếp tốt, chịu được áp lực, có kinh nghiệm operations là lợi thế.',
 'TP. Hồ Chí Minh', 10, 15, 'full-time', 0, 145, DATE_ADD(NOW(), INTERVAL 15 DAY), 'Vận hành'),

-- MoMo: IT + Tài chính + Thiết kế
(6, 7, 'UI/UX Designer',
 'Thiết kế giao diện và trải nghiệm người dùng cho ứng dụng ví điện tử MoMo.',
 '3+ năm kinh nghiệm UI/UX, thành thạo Figma, có portfolio thực tế.',
 'TP. Hồ Chí Minh', 20, 35, 'full-time', 1, 480, DATE_ADD(NOW(), INTERVAL 50 DAY), 'Thiết kế'),

(6, 7, 'Business Analyst - Fintech',
 'Phân tích yêu cầu nghiệp vụ, làm cầu nối giữa business và team IT cho sản phẩm thanh toán.',
 '3+ năm làm BA trong môi trường fintech/ngân hàng, biết SQL, thành thạo viết BRS/SRS.',
 'TP. Hồ Chí Minh', 18, 28, 'full-time', 0, 200, DATE_ADD(NOW(), INTERVAL 20 DAY), 'Tài chính'),

-- VNPT: IT + HR
(7, 8, 'DevOps Engineer',
 'Xây dựng và vận hành hạ tầng cloud cho các sản phẩm VNPT, đảm bảo uptime 99.9%.',
 '3+ năm DevOps, thành thạo Docker/Kubernetes, AWS hoặc GCP, CI/CD pipelines.',
 'Hà Nội', 25, 40, 'full-time', 0, 275, DATE_ADD(NOW(), INTERVAL 30 DAY), 'Công nghệ thông tin'),

(7, 8, 'Chuyên viên Tuyển dụng (IT Recruiter)',
 'Tuyển dụng nhân sự kỹ thuật cho các phòng ban CNTT của VNPT trên toàn quốc.',
 '2+ năm kinh nghiệm tuyển dụng IT, hiểu biết về các vị trí kỹ thuật, thành thạo LinkedIn.',
 'Hà Nội', 12, 18, 'full-time', 0, 110, DATE_ADD(NOW(), INTERVAL 10 DAY), 'HR'),

-- Viettel: IT + Bán hàng
(8, 9, 'Data Engineer',
 'Xây dựng và duy trì data pipeline phục vụ phân tích dữ liệu viễn thông quy mô lớn.',
 '3+ năm Data Engineering, thành thạo Spark/Hadoop, Python, SQL, kinh nghiệm BigData.',
 'Hà Nội', 22, 38, 'full-time', 1, 430, DATE_ADD(NOW(), INTERVAL 45 DAY), 'Công nghệ thông tin'),

(8, 9, 'Nhân viên Kinh doanh B2B',
 'Phát triển khách hàng doanh nghiệp cho dịch vụ viễn thông và giải pháp CNTT của Viettel.',
 'Tốt nghiệp đại học khối kinh tế/kỹ thuật, kỹ năng bán hàng tốt, có xe máy đi lại.',
 'Hà Nội', 8, 20, 'full-time', 0, 155, DATE_ADD(NOW(), INTERVAL 3 DAY), 'Bán hàng'),

-- Sacombank: Tài chính + HR + Marketing
(9, 10, 'Chuyên viên Tín dụng Cá nhân',
 'Tư vấn và xử lý hồ sơ vay vốn cá nhân, thẩm định tín dụng cho khách hàng.',
 'Tốt nghiệp Tài chính/Ngân hàng/Kinh tế, năng động, có khả năng phát triển khách hàng.',
 'TP. Hồ Chí Minh', 10, 18, 'full-time', 0, 190, DATE_ADD(NOW(), INTERVAL 20 DAY), 'Tài chính'),

(9, 10, 'Graphic Designer',
 'Thiết kế ấn phẩm truyền thông, banner quảng cáo và nội dung mạng xã hội cho Sacombank.',
 '2+ năm thiết kế đồ họa, thành thạo Photoshop/Illustrator, có tư duy thẩm mỹ tốt.',
 'TP. Hồ Chí Minh', 12, 18, 'full-time', 0, 80, DATE_ADD(NOW(), INTERVAL 12 DAY), 'Thiết kế'),

(9, 10, 'HR Intern',
 'Hỗ trợ phòng nhân sự trong công tác tuyển dụng, đào tạo và quản lý hồ sơ nhân viên.',
 'Sinh viên năm cuối Quản trị Nhân lực/Kinh tế, năng động, cẩn thận, biết Excel.',
 'TP. Hồ Chí Minh', 3, 5, 'intern', 0, 62, DATE_ADD(NOW(), INTERVAL 30 DAY), 'HR'),

-- FPT: thêm 1 job part-time
(1, 2, 'Content Marketing Part-time',
 'Viết bài blog, case study và nội dung mạng xã hội cho FPT Software.',
 'Giỏi viết lách tiếng Việt, hiểu về công nghệ thông tin, sáng tạo, có thể làm remote.',
 'Hà Nội', 5, 8, 'part-time', 0, 95, DATE_ADD(NOW(), INTERVAL 25 DAY), 'Marketing'),

-- VNG: job đã hết hạn (để test deadline badge)
(2, 3, 'Game Backend Developer',
 'Phát triển server-side cho các sản phẩm game online của VNG.',
 '3+ năm C++/Go, kinh nghiệm game server, hiểu network programming.',
 'TP. Hồ Chí Minh', 28, 45, 'full-time', 0, 340, DATE_SUB(NOW(), INTERVAL 5 DAY), 'Công nghệ thông tin');

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

-- Applications gốc (4 đơn)
INSERT INTO applications (job_id, user_id, cv_file, cover_letter, status) VALUES
(1, 11, 'sample_cv_1.pdf', 'Em rất quan tâm vị trí PHP Backend của quý công ty.',     'pending'),
(2, 11, 'sample_cv_1.pdf', 'Em là sinh viên mới ra trường, mong được học hỏi.',       'accepted'),
(3, 12, 'sample_cv_2.pdf', 'Tôi có 3 năm kinh nghiệm React và muốn thử sức tại VNG.','pending'),
(4, 11, 'sample_cv_1.pdf', 'Tôi muốn ứng tuyển vị trí iOS Developer tại Tiki.',      'rejected');

-- Applications mới (10 đơn đa dạng status)
INSERT INTO applications (job_id, user_id, cv_file, cover_letter, status) VALUES
(6,  13, 'cv_khoa.pdf',    'Tôi có 5 năm kinh nghiệm Java, đã từng làm tại các công ty fintech quy mô lớn.', 'accepted'),
(7,  14, 'cv_lan.pdf',     'Tôi rất đam mê digital marketing và muốn được đóng góp vào sự phát triển của Shopee.', 'pending'),
(8,  15, 'cv_thanh.pdf',   'Với 3 năm kinh nghiệm Android, tôi tự tin có thể đáp ứng yêu cầu vị trí này.', 'accepted'),
(10, 16, 'cv_huong.pdf',   'Tôi muốn đóng góp kỹ năng thiết kế của mình vào sản phẩm MoMo.', 'pending'),
(9,  17, 'cv_bao.pdf',     'Tôi hiểu rõ nghiệp vụ fintech và có kinh nghiệm làm BA 4 năm.', 'rejected'),
(11, 18, 'cv_tuyet.pdf',   'Tôi quan tâm đến vị trí DevOps tại VNPT và có kinh nghiệm với AWS.', 'pending'),
(14, 13, 'cv_khoa_2.pdf',  'Với kinh nghiệm về data pipeline, tôi tin mình phù hợp với vị trí Data Engineer.', 'accepted'),
(17, 14, 'cv_lan_2.pdf',   'Tôi muốn ứng tuyển vị trí Content Marketing tại FPT Software.', 'pending'),
(5,  19, 'cv_dung.pdf',    'Tôi là sinh viên năm 4 muốn thực tập tại Tiki để tích lũy kinh nghiệm.', 'rejected'),
(13, 20, 'cv_thu.pdf',     'Tôi quan tâm đến vị trí tín dụng cá nhân tại Sacombank.', 'accepted');

-- ---------------------------------------------------------
-- Bảng saved_jobs: lưu job yêu thích của user
-- UNIQUE(user_id, job_id) đảm bảo không lưu trùng
-- ---------------------------------------------------------
CREATE TABLE saved_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_saved (user_id, job_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed saved_jobs: gốc + thêm mới (10 lượt lưu)
INSERT INTO saved_jobs (user_id, job_id) VALUES
(11, 1),
(11, 3),
(12, 2),
-- Saved jobs mới
(13, 6),
(13, 8),
(14, 7),
(14, 10),
(15, 6),
(15, 14),
(16, 10),
(17, 11),
(18, 14),
(19, 16),
(20, 13);
