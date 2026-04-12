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
DROP TABLE IF EXISTS employer_requests;
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
    bio TEXT NULL,                               -- giới thiệu bản thân
    skills VARCHAR(500) NULL,                    -- kỹ năng (comma-separated)
    experience_years TINYINT NULL,               -- số năm kinh nghiệm
    avatar VARCHAR(255) NULL,                    -- tên file ảnh đại diện (lưu trong uploads/avatars/)
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Mật khẩu gốc: 123456 (prefix PLAIN: để PHP tự hash lại khi login lần đầu)
INSERT INTO users (email, password, full_name, role, phone) VALUES
-- Admin
('admin@example.com',      'PLAIN:123456', 'Quản trị viên',        'admin',    '0900000001'),
-- 9 Employer (mỗi người sở hữu 1 công ty)
('employer1@example.com',  'PLAIN:123456', 'Nguyễn Thanh HR',      'employer', '0900000002'),
('employer2@example.com',  'PLAIN:123456', 'Trần Anh HR',          'employer', '0900000003'),
('employer3@example.com',  'PLAIN:123456', 'Lê Minh HR',           'employer', '0900000006'),
('employer4@example.com',  'PLAIN:123456', 'Phạm Quỳnh HR',        'employer', '0900000007'),
('employer5@example.com',  'PLAIN:123456', 'Hoàng Nam HR',         'employer', '0900000008'),
('employer6@example.com',  'PLAIN:123456', 'Vũ Linh HR',           'employer', '0900000009'),
('employer7@example.com',  'PLAIN:123456', 'Đặng Hùng HR',         'employer', '0900000010'),
('employer8@example.com',  'PLAIN:123456', 'Bùi Tú HR',            'employer', '0900000011'),
('employer9@example.com',  'PLAIN:123456', 'Ngô Châu HR',          'employer', '0900000012'),
-- 20 Ứng viên
('user1@example.com',      'PLAIN:123456', 'Lê Văn An',            'user',     '0900100001'),
('user2@example.com',      'PLAIN:123456', 'Phạm Thị Bích',        'user',     '0900100002'),
('user3@example.com',      'PLAIN:123456', 'Trần Minh Khoa',       'user',     '0900100003'),
('user4@example.com',      'PLAIN:123456', 'Nguyễn Thị Lan',       'user',     '0900100004'),
('user5@example.com',      'PLAIN:123456', 'Võ Đức Thành',         'user',     '0900100005'),
('user6@example.com',      'PLAIN:123456', 'Đinh Thị Hương',       'user',     '0900100006'),
('user7@example.com',      'PLAIN:123456', 'Lý Quốc Bảo',          'user',     '0900100007'),
('user8@example.com',      'PLAIN:123456', 'Phan Thị Tuyết',       'user',     '0900100008'),
('user9@example.com',      'PLAIN:123456', 'Hồ Văn Dũng',          'user',     '0900100009'),
('user10@example.com',     'PLAIN:123456', 'Mai Thị Thu',           'user',     '0900100010'),
('user11@example.com',     'PLAIN:123456', 'Trương Văn Đức',        'user',     '0900100011'),
('user12@example.com',     'PLAIN:123456', 'Ngô Thị Phương',        'user',     '0900100012'),
('user13@example.com',     'PLAIN:123456', 'Vũ Tiến Dũng',          'user',     '0900100013'),
('user14@example.com',     'PLAIN:123456', 'Lưu Thị Ngọc',          'user',     '0900100014'),
('user15@example.com',     'PLAIN:123456', 'Đỗ Xuân Trường',        'user',     '0900100015'),
('user16@example.com',     'PLAIN:123456', 'Bùi Thị Hà',            'user',     '0900100016'),
('user17@example.com',     'PLAIN:123456', 'Đặng Văn Long',         'user',     '0900100017'),
('user18@example.com',     'PLAIN:123456', 'Cao Thị Yến',           'user',     '0900100018'),
('user19@example.com',     'PLAIN:123456', 'Hoàng Minh Tuấn',       'user',     '0900100019'),
('user20@example.com',     'PLAIN:123456', 'Tạ Thị Kim Anh',        'user',     '0900100020');

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

INSERT INTO companies (owner_id, name, description, location, website, logo) VALUES
-- Công ty gốc (3 công ty)
(2, 'FPT Software',       'Công ty phần mềm hàng đầu Việt Nam, chuyên outsource cho thị trường Nhật và Mỹ.',    'Hà Nội',          'https://fptsoftware.com',  'logo_1_1700000000.png'),
(3, 'VNG Corporation',    'Công ty công nghệ với các sản phẩm Zalo, ZaloPay, game online.',                     'TP. Hồ Chí Minh', 'https://vng.com.vn',       NULL),
(4, 'Tiki',               'Sàn thương mại điện tử hàng đầu Việt Nam.',                                          'TP. Hồ Chí Minh', 'https://tiki.vn',          'logo_3_1700000000.png'),
-- Công ty mới (6 công ty nổi tiếng VN)
(5, 'Shopee Vietnam',     'Nền tảng thương mại điện tử hàng đầu Đông Nam Á, vận hành tại Việt Nam.',            'TP. Hồ Chí Minh', 'https://shopee.vn',        'logo_4_1700000000.png'),
(6, 'Grab Vietnam',       'Ứng dụng gọi xe và giao đồ ăn lớn nhất Đông Nam Á.',                                 'TP. Hồ Chí Minh', 'https://grab.com',         'logo_5_1700000000.png'),
(7, 'MoMo',               'Ví điện tử MoMo - nền tảng thanh toán di động phổ biến nhất Việt Nam.',              'TP. Hồ Chí Minh', 'https://momo.vn',          'logo_6_1700000000.png'),
(8, 'VNPT',               'Tập đoàn Bưu chính Viễn thông Việt Nam, cung cấp dịch vụ CNTT và viễn thông.',       'Hà Nội',          'https://vnpt.vn',          'logo_7_1700000000.png'),
(9, 'Viettel',            'Tập đoàn Công nghiệp Viễn thông Quân đội, mạng di động lớn nhất Việt Nam.',          'Hà Nội',          'https://viettel.vn',       'logo_8_1700000000.png'),
(10, 'Sacombank',         'Ngân hàng thương mại cổ phần Sài Gòn Thương Tín, TOP 5 ngân hàng tư nhân VN.',       'TP. Hồ Chí Minh', 'https://sacombank.com.vn', NULL);

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
    tags VARCHAR(300) NULL,                     -- kỹ năng yêu cầu, phân cách bằng dấu phẩy
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Jobs gốc (5 bài)
INSERT INTO jobs (company_id, employer_id, title, description, requirements, location, salary_min, salary_max, job_type, is_hot, views, expired_at, category, tags) VALUES
(1, 2, 'PHP Backend Developer',
 'Phát triển backend cho các dự án thương mại điện tử quy mô lớn.',
 'Thành thạo PHP, MySQL, có kinh nghiệm Laravel là lợi thế.',
 'Hà Nội', 15, 25, 'full-time', 1, 320, DATE_ADD(NOW(), INTERVAL 30 DAY), 'Công nghệ thông tin', 'PHP, MySQL, Laravel, REST API'),

(1, 2, 'Java Fresher',
 'Tham gia phát triển dự án ngân hàng cho khách hàng Nhật.',
 'Tốt nghiệp CNTT, biết Java cơ bản, tiếng Anh đọc hiểu.',
 'Hà Nội', 8, 12, 'full-time', 0, 185, DATE_ADD(NOW(), INTERVAL 14 DAY), 'Công nghệ thông tin', 'Java, Spring Boot, SQL, OOP'),

(2, 3, 'Frontend ReactJS',
 'Xây dựng giao diện sản phẩm Zalo Mini App.',
 '2+ năm kinh nghiệm React, hiểu TypeScript.',
 'TP. Hồ Chí Minh', 20, 35, 'full-time', 1, 512, DATE_ADD(NOW(), INTERVAL 45 DAY), 'Công nghệ thông tin', 'ReactJS, TypeScript, HTML, CSS'),

(3, 4, 'Mobile Developer iOS',
 'Phát triển ứng dụng Tiki trên nền tảng iOS.',
 '2+ năm Swift/SwiftUI, có app trên AppStore là lợi thế.',
 'TP. Hồ Chí Minh', 18, 30, 'full-time', 0, 230, DATE_ADD(NOW(), INTERVAL 20 DAY), 'Công nghệ thông tin', 'Swift, SwiftUI, Xcode, REST API'),

(3, 4, 'Data Analyst Intern',
 'Phân tích dữ liệu bán hàng, lập báo cáo tuần.',
 'Sinh viên năm 3-4 CNTT/Kinh tế, biết SQL và Excel.',
 'TP. Hồ Chí Minh', 4, 7, 'intern', 0, 98, DATE_ADD(NOW(), INTERVAL 7 DAY), 'Công nghệ thông tin', 'SQL, Excel, Power BI, Python');

-- Jobs mới (15 bài đa dạng lĩnh vực, địa điểm, lương)
INSERT INTO jobs (company_id, employer_id, title, description, requirements, location, salary_min, salary_max, job_type, is_hot, views, expired_at, category, tags) VALUES

-- Shopee: IT + Marketing
(4, 5, 'Senior Java Backend Engineer',
 'Xây dựng hệ thống backend phục vụ hàng triệu người dùng trên nền tảng Shopee.',
 '4+ năm Java, Spring Boot, kinh nghiệm microservices, hiểu Kafka/Redis.',
 'TP. Hồ Chí Minh', 35, 60, 'full-time', 1, 890, DATE_ADD(NOW(), INTERVAL 60 DAY), 'Công nghệ thông tin', 'Java, Spring Boot, Kafka, Redis'),

(4, 5, 'Digital Marketing Executive',
 'Lên kế hoạch và triển khai các chiến dịch marketing online cho Shopee tại thị trường Việt Nam.',
 '2+ năm kinh nghiệm digital marketing, thành thạo Facebook Ads, Google Ads, TikTok Ads.',
 'TP. Hồ Chí Minh', 15, 22, 'full-time', 0, 310, DATE_ADD(NOW(), INTERVAL 25 DAY), 'Marketing', 'Facebook Ads, Google Ads, SEO, Content'),

-- Grab: IT + Vận hành
(5, 6, 'Android Developer',
 'Phát triển tính năng mới cho ứng dụng Grab trên nền tảng Android.',
 '3+ năm Kotlin/Java Android, kinh nghiệm làm việc với API REST, hiểu về CI/CD.',
 'TP. Hồ Chí Minh', 25, 45, 'full-time', 1, 620, DATE_ADD(NOW(), INTERVAL 40 DAY), 'Công nghệ thông tin', 'Kotlin, Android, REST API, CI/CD'),

(5, 6, 'Nhân viên Vận hành Đối tác Tài xế',
 'Hỗ trợ, đào tạo và quản lý đối tác tài xế trong khu vực TP.HCM.',
 'Tốt nghiệp đại học, giao tiếp tốt, chịu được áp lực, có kinh nghiệm operations là lợi thế.',
 'TP. Hồ Chí Minh', 10, 15, 'full-time', 0, 145, DATE_ADD(NOW(), INTERVAL 15 DAY), 'Vận hành', NULL),

-- MoMo: IT + Tài chính + Thiết kế
(6, 7, 'UI/UX Designer',
 'Thiết kế giao diện và trải nghiệm người dùng cho ứng dụng ví điện tử MoMo.',
 '3+ năm kinh nghiệm UI/UX, thành thạo Figma, có portfolio thực tế.',
 'TP. Hồ Chí Minh', 20, 35, 'full-time', 1, 480, DATE_ADD(NOW(), INTERVAL 50 DAY), 'Thiết kế', 'Figma, Adobe XD, UI/UX, Prototyping'),

(6, 7, 'Business Analyst - Fintech',
 'Phân tích yêu cầu nghiệp vụ, làm cầu nối giữa business và team IT cho sản phẩm thanh toán.',
 '3+ năm làm BA trong môi trường fintech/ngân hàng, biết SQL, thành thạo viết BRS/SRS.',
 'TP. Hồ Chí Minh', 18, 28, 'full-time', 0, 200, DATE_ADD(NOW(), INTERVAL 20 DAY), 'Tài chính', 'SQL, BRS, SRS, Jira'),

-- VNPT: IT + HR
(7, 8, 'DevOps Engineer',
 'Xây dựng và vận hành hạ tầng cloud cho các sản phẩm VNPT, đảm bảo uptime 99.9%.',
 '3+ năm DevOps, thành thạo Docker/Kubernetes, AWS hoặc GCP, CI/CD pipelines.',
 'Hà Nội', 25, 40, 'full-time', 0, 275, DATE_ADD(NOW(), INTERVAL 30 DAY), 'Công nghệ thông tin', 'Docker, Kubernetes, AWS, CI/CD'),

(7, 8, 'Chuyên viên Tuyển dụng (IT Recruiter)',
 'Tuyển dụng nhân sự kỹ thuật cho các phòng ban CNTT của VNPT trên toàn quốc.',
 '2+ năm kinh nghiệm tuyển dụng IT, hiểu biết về các vị trí kỹ thuật, thành thạo LinkedIn.',
 'Hà Nội', 12, 18, 'full-time', 0, 110, DATE_ADD(NOW(), INTERVAL 10 DAY), 'HR', NULL),

-- Viettel: IT + Bán hàng
(8, 9, 'Data Engineer',
 'Xây dựng và duy trì data pipeline phục vụ phân tích dữ liệu viễn thông quy mô lớn.',
 '3+ năm Data Engineering, thành thạo Spark/Hadoop, Python, SQL, kinh nghiệm BigData.',
 'Hà Nội', 22, 38, 'full-time', 1, 430, DATE_ADD(NOW(), INTERVAL 45 DAY), 'Công nghệ thông tin', 'Python, SQL, Spark, Hadoop'),

(8, 9, 'Nhân viên Kinh doanh B2B',
 'Phát triển khách hàng doanh nghiệp cho dịch vụ viễn thông và giải pháp CNTT của Viettel.',
 'Tốt nghiệp đại học khối kinh tế/kỹ thuật, kỹ năng bán hàng tốt, có xe máy đi lại.',
 'Hà Nội', 8, 20, 'full-time', 0, 155, DATE_ADD(NOW(), INTERVAL 3 DAY), 'Bán hàng', NULL),

-- Sacombank: Tài chính + HR + Marketing
(9, 10, 'Chuyên viên Tín dụng Cá nhân',
 'Tư vấn và xử lý hồ sơ vay vốn cá nhân, thẩm định tín dụng cho khách hàng.',
 'Tốt nghiệp Tài chính/Ngân hàng/Kinh tế, năng động, có khả năng phát triển khách hàng.',
 'TP. Hồ Chí Minh', 10, 18, 'full-time', 0, 190, DATE_ADD(NOW(), INTERVAL 20 DAY), 'Tài chính', NULL),

(9, 10, 'Graphic Designer',
 'Thiết kế ấn phẩm truyền thông, banner quảng cáo và nội dung mạng xã hội cho Sacombank.',
 '2+ năm thiết kế đồ họa, thành thạo Photoshop/Illustrator, có tư duy thẩm mỹ tốt.',
 'TP. Hồ Chí Minh', 12, 18, 'full-time', 0, 80, DATE_ADD(NOW(), INTERVAL 12 DAY), 'Thiết kế', 'Figma, Adobe XD, UI/UX'),

(9, 10, 'HR Intern',
 'Hỗ trợ phòng nhân sự trong công tác tuyển dụng, đào tạo và quản lý hồ sơ nhân viên.',
 'Sinh viên năm cuối Quản trị Nhân lực/Kinh tế, năng động, cẩn thận, biết Excel.',
 'TP. Hồ Chí Minh', 3, 5, 'intern', 0, 62, DATE_ADD(NOW(), INTERVAL 30 DAY), 'HR', NULL),

-- FPT: thêm 1 job part-time
(1, 2, 'Content Marketing Part-time',
 'Viết bài blog, case study và nội dung mạng xã hội cho FPT Software.',
 'Giỏi viết lách tiếng Việt, hiểu về công nghệ thông tin, sáng tạo, có thể làm remote.',
 'Hà Nội', 5, 8, 'part-time', 0, 95, DATE_ADD(NOW(), INTERVAL 25 DAY), 'Marketing', 'Facebook Ads, Google Ads, SEO, Content'),

-- VNG: job đã hết hạn (để test deadline badge)
(2, 3, 'Game Backend Developer',
 'Phát triển server-side cho các sản phẩm game online của VNG.',
 '3+ năm C++/Go, kinh nghiệm game server, hiểu network programming.',
 'TP. Hồ Chí Minh', 28, 45, 'full-time', 0, 340, DATE_SUB(NOW(), INTERVAL 5 DAY), 'Công nghệ thông tin', 'C++, Go, gRPC, WebSocket');

-- ===== Bổ sung thêm jobs đa dạng =====
INSERT INTO jobs (company_id, employer_id, title, description, requirements, location, salary_min, salary_max, job_type, is_hot, views, expired_at, category, tags) VALUES

-- FPT Software: 3 bài thêm
(1, 2, 'Python AI/ML Engineer',
 'Nghiên cứu và xây dựng các mô hình AI/ML cho hệ thống gợi ý sản phẩm và phân tích dữ liệu khách hàng.',
 '3+ năm Python, kinh nghiệm với TensorFlow hoặc PyTorch, hiểu biết về MLOps.',
 'Hà Nội', 25, 45, 'full-time', 1, 670, DATE_ADD(NOW(), INTERVAL 45 DAY), 'Công nghệ thông tin', 'Python, SQL, TensorFlow, Pandas'),

(1, 2, 'QA Engineer (Automation)',
 'Thiết kế và thực thi test automation cho các dự án outsource, đảm bảo chất lượng phần mềm.',
 '2+ năm kinh nghiệm automation testing, thành thạo Selenium/Cypress, hiểu API testing.',
 'Hà Nội', 15, 22, 'full-time', 0, 210, DATE_ADD(NOW(), INTERVAL 30 DAY), 'Công nghệ thông tin', 'Selenium, Cypress, Postman, JIRA'),

(1, 2, 'Scrum Master / Agile Coach',
 'Hỗ trợ và huấn luyện các team phát triển áp dụng Agile/Scrum hiệu quả trong môi trường outsource.',
 'CSM hoặc PSM certificate, 3+ năm làm Scrum Master, kinh nghiệm làm việc với khách hàng Nhật/Mỹ.',
 'Hà Nội', 20, 35, 'contract', 0, 155, DATE_ADD(NOW(), INTERVAL 20 DAY), 'Vận hành', NULL),

-- VNG Corporation: 3 bài thêm
(2, 3, 'Senior Go Developer',
 'Xây dựng microservices hiệu năng cao cho nền tảng Zalo với hàng chục triệu người dùng đồng thời.',
 '4+ năm Go, kinh nghiệm microservices và distributed systems, hiểu gRPC và Kafka.',
 'TP. Hồ Chí Minh', 35, 55, 'full-time', 1, 720, DATE_ADD(NOW(), INTERVAL 60 DAY), 'Công nghệ thông tin', 'Go, gRPC, Kafka, Microservices'),

(2, 3, 'Product Manager - ZaloPay',
 'Định hướng sản phẩm và roadmap cho ZaloPay, phối hợp với các team kỹ thuật và kinh doanh.',
 '4+ năm Product Management, kinh nghiệm fintech/payment, kỹ năng phân tích dữ liệu tốt.',
 'TP. Hồ Chí Minh', 30, 50, 'full-time', 1, 540, DATE_ADD(NOW(), INTERVAL 40 DAY), 'Vận hành', NULL),

(2, 3, 'Community Manager - Game',
 'Quản lý cộng đồng người chơi game của VNG trên các kênh mạng xã hội và forum.',
 '2+ năm community management, đam mê game online, kỹ năng viết content tốt, tiếng Anh khá.',
 'TP. Hồ Chí Minh', 12, 18, 'full-time', 0, 180, DATE_ADD(NOW(), INTERVAL 15 DAY), 'Marketing', 'Facebook Ads, Google Ads, SEO, Content'),

-- Tiki: 3 bài thêm
(3, 4, 'Senior Data Scientist',
 'Xây dựng mô hình dự đoán hành vi mua sắm, hệ thống gợi ý sản phẩm và phát hiện gian lận.',
 '4+ năm Data Science, thành thạo Python/R, kinh nghiệm với mô hình recommendation systems.',
 'TP. Hồ Chí Minh', 35, 55, 'full-time', 1, 610, DATE_ADD(NOW(), INTERVAL 35 DAY), 'Công nghệ thông tin', 'Python, SQL, TensorFlow, Pandas'),

(3, 4, 'Supply Chain Analyst',
 'Phân tích và tối ưu hoá chuỗi cung ứng, theo dõi KPI kho vận và logistics của Tiki.',
 '2+ năm kinh nghiệm supply chain/logistics, thành thạo Excel/SQL, tư duy phân tích tốt.',
 'TP. Hồ Chí Minh', 14, 22, 'full-time', 0, 195, DATE_ADD(NOW(), INTERVAL 25 DAY), 'Vận hành', NULL),

(3, 4, 'Seller Account Manager',
 'Quản lý và phát triển mối quan hệ với các nhà bán hàng trên sàn Tiki, hỗ trợ họ tăng trưởng doanh số.',
 '2+ năm kinh nghiệm account management hoặc business development, kỹ năng đàm phán tốt.',
 'TP. Hồ Chí Minh', 13, 20, 'full-time', 0, 260, DATE_ADD(NOW(), INTERVAL 20 DAY), 'Bán hàng', NULL),

-- Shopee: 3 bài thêm
(4, 5, 'Machine Learning Engineer',
 'Nghiên cứu và triển khai các mô hình ML vào hệ thống gợi ý, tìm kiếm và phát hiện gian lận trên Shopee.',
 '4+ năm Machine Learning, thành thạo Python, kinh nghiệm scale model lên production.',
 'TP. Hồ Chí Minh', 40, 70, 'full-time', 1, 850, DATE_ADD(NOW(), INTERVAL 50 DAY), 'Công nghệ thông tin', 'Python, SQL, TensorFlow, Pandas'),

(4, 5, 'Customer Service Team Lead',
 'Quản lý team CSKH, đảm bảo chất lượng dịch vụ và giải quyết các vấn đề phức tạp của người dùng.',
 '3+ năm customer service, 1+ năm kinh nghiệm team lead, kỹ năng giao tiếp và xử lý tình huống tốt.',
 'TP. Hồ Chí Minh', 18, 28, 'full-time', 0, 315, DATE_ADD(NOW(), INTERVAL 18 DAY), 'Vận hành', NULL),

(4, 5, 'Brand Marketing Manager',
 'Lập kế hoạch và thực thi chiến lược xây dựng thương hiệu Shopee tại thị trường Việt Nam.',
 '5+ năm brand marketing, kinh nghiệm FMCG hoặc e-commerce, có khả năng làm việc độc lập cao.',
 'TP. Hồ Chí Minh', 30, 50, 'full-time', 1, 490, DATE_ADD(NOW(), INTERVAL 40 DAY), 'Marketing', 'Facebook Ads, Google Ads, SEO, Content'),

-- Grab: 3 bài thêm
(5, 6, 'Data Scientist - Pricing',
 'Xây dựng mô hình định giá động cho dịch vụ GrabCar và GrabFood dựa trên dữ liệu thực tế.',
 '3+ năm Data Science, kinh nghiệm với A/B testing, mô hình hóa giá, Python/R thành thạo.',
 'TP. Hồ Chí Minh', 30, 50, 'full-time', 1, 580, DATE_ADD(NOW(), INTERVAL 45 DAY), 'Công nghệ thông tin', 'Python, SQL, TensorFlow, Pandas'),

(5, 6, 'Growth Marketing Executive',
 'Thiết kế và triển khai các chương trình khuyến mãi, referral program để tăng trưởng người dùng mới.',
 '2+ năm growth marketing hoặc digital marketing, hiểu về funnel conversion, analytics.',
 'TP. Hồ Chí Minh', 16, 24, 'full-time', 0, 270, DATE_ADD(NOW(), INTERVAL 22 DAY), 'Marketing', 'Facebook Ads, Google Ads, SEO, Content'),

(5, 6, 'Flutter Developer',
 'Phát triển ứng dụng cross-platform cho Grab bằng Flutter, đảm bảo hiệu năng trên cả iOS và Android.',
 '2+ năm Flutter/Dart, kinh nghiệm với state management (Bloc/Riverpod), tích hợp REST API.',
 'TP. Hồ Chí Minh', 22, 38, 'full-time', 0, 390, DATE_ADD(NOW(), INTERVAL 30 DAY), 'Công nghệ thông tin', 'Flutter, Dart, REST API, Firebase'),

-- MoMo: 2 bài thêm
(6, 7, 'Backend Engineer - Kotlin',
 'Phát triển và tối ưu hóa các microservices xử lý giao dịch thanh toán điện tử với tần suất cao.',
 '3+ năm Kotlin/Java, kinh nghiệm Spring Boot, hiểu biết về payment systems và bảo mật giao dịch.',
 'TP. Hồ Chí Minh', 28, 45, 'full-time', 1, 520, DATE_ADD(NOW(), INTERVAL 35 DAY), 'Công nghệ thông tin', 'Kotlin, Spring Boot, Kafka, Redis'),

(6, 7, 'Content Marketing Specialist',
 'Xây dựng chiến lược và sản xuất nội dung cho các kênh mạng xã hội, blog và email marketing của MoMo.',
 '2+ năm content marketing, khả năng viết lách tốt, hiểu về SEO và social media analytics.',
 'TP. Hồ Chí Minh', 14, 20, 'full-time', 0, 240, DATE_ADD(NOW(), INTERVAL 20 DAY), 'Marketing', 'Facebook Ads, Google Ads, SEO, Content'),

-- VNPT: 2 bài thêm
(7, 8, 'Senior Network Engineer',
 'Thiết kế, triển khai và vận hành hạ tầng mạng backbone quốc gia của VNPT.',
 '5+ năm network engineering, CCNP/CCIE, kinh nghiệm với MPLS, BGP, hệ thống viễn thông lớn.',
 'Hà Nội', 25, 40, 'full-time', 0, 220, DATE_ADD(NOW(), INTERVAL 25 DAY), 'Công nghệ thông tin', 'Docker, Kubernetes, AWS, CI/CD'),

(7, 8, 'Sales Manager - Enterprise',
 'Phát triển và quản lý danh mục khách hàng doanh nghiệp lớn cho giải pháp CNTT và viễn thông VNPT.',
 '5+ năm B2B sales, kinh nghiệm bán giải pháp CNTT/telecom cho doanh nghiệp, có mạng lưới quan hệ tốt.',
 'Hà Nội', 20, 40, 'full-time', 0, 175, DATE_ADD(NOW(), INTERVAL 18 DAY), 'Bán hàng', NULL),

-- Viettel: 2 bài thêm
(8, 9, 'Cybersecurity Engineer',
 'Bảo vệ hạ tầng mạng và hệ thống thông tin của Viettel trước các mối đe dọa an ninh mạng.',
 '3+ năm cybersecurity, kinh nghiệm penetration testing, hiểu về SIEM, SOC operations.',
 'Hà Nội', 25, 40, 'full-time', 1, 440, DATE_ADD(NOW(), INTERVAL 50 DAY), 'Công nghệ thông tin', 'Docker, Kubernetes, AWS, CI/CD'),

(8, 9, 'Technical Writer',
 'Viết tài liệu kỹ thuật, API documentation và hướng dẫn sử dụng cho các sản phẩm phần mềm của Viettel.',
 '2+ năm technical writing, kiến thức về lập trình cơ bản, thành thạo Markdown và Confluence.',
 'Hà Nội', 12, 18, 'part-time', 0, 130, DATE_ADD(NOW(), INTERVAL 20 DAY), 'Khác', NULL),

-- Sacombank: 2 bài thêm
(9, 10, 'Risk Management Analyst',
 'Phân tích và đánh giá rủi ro tín dụng, thị trường và vận hành cho hoạt động ngân hàng.',
 '3+ năm quản lý rủi ro ngân hàng, tốt nghiệp Tài chính/Kinh tế, thành thạo Excel/SAS.',
 'TP. Hồ Chí Minh', 18, 30, 'full-time', 0, 280, DATE_ADD(NOW(), INTERVAL 15 DAY), 'Tài chính', NULL),

(9, 10, 'IT Support Specialist',
 'Hỗ trợ kỹ thuật cho các chi nhánh Sacombank trên toàn quốc, đảm bảo hệ thống hoạt động ổn định.',
 '1+ năm IT support, kiến thức về Windows Server, networking cơ bản, sẵn sàng công tác.',
 'TP. Hồ Chí Minh', 10, 15, 'full-time', 0, 160, DATE_ADD(NOW(), INTERVAL 10 DAY), 'Công nghệ thông tin', NULL),

-- Thêm 1 job intern đa dạng
(5, 6, 'UX Research Intern',
 'Thực hiện nghiên cứu người dùng, phỏng vấn và usability testing cho các tính năng mới của Grab.',
 'Sinh viên năm 3-4 ngành Thiết kế/Tâm lý/Marketing, có kiến thức cơ bản về UX research.',
 'TP. Hồ Chí Minh', 4, 7, 'intern', 0, 85, DATE_ADD(NOW(), INTERVAL 25 DAY), 'Thiết kế', 'Figma, Adobe XD, UI/UX');

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

-- Applications: 40+ đơn đa dạng
INSERT INTO applications (job_id, user_id, cv_file, cover_letter, status) VALUES
-- Đơn vào FPT (jobs 1,2,19,20,21,22)
(1,  11, 'cv_levan.pdf',    'Em rất quan tâm vị trí PHP Backend tại FPT, có 2 năm kinh nghiệm Laravel.',       'pending'),
(1,  13, 'cv_khoa.pdf',     'Tôi đã làm PHP được 3 năm, muốn thử sức tại FPT Software.',                       'rejected'),
(2,  11, 'cv_levan_2.pdf',  'Em là sinh viên mới ra trường, mong được học hỏi môi trường FPT.',                 'accepted'),
(2,  15, 'cv_thanh.pdf',    'Tôi biết Java cơ bản và muốn phát triển sự nghiệp tại đây.',                       'pending'),
(19, 17, 'cv_bao.pdf',      'Tôi có chứng chỉ CSM và 4 năm làm Scrum Master trong môi trường outsource.',      'accepted'),
(20, 14, 'cv_lan.pdf',      'Tôi muốn viết content cho FPT, có kinh nghiệm 2 năm viết blog công nghệ.',         'pending'),

-- Đơn vào VNG (jobs 3,20,23,24,25)
(3,  12, 'cv_phamb.pdf',    'Tôi có 3 năm kinh nghiệm React và muốn thử sức tại VNG.',                          'pending'),
(3,  16, 'cv_huong.pdf',    'Tôi thành thạo React TypeScript, đã làm dự án Zalo API cá nhân.',                  'accepted'),
(23, 13, 'cv_khoa_2.pdf',   'Với 5 năm Go, tôi tự tin đáp ứng yêu cầu của vị trí Senior Go Developer.',        'accepted'),
(24, 18, 'cv_tuyet.pdf',    'Tôi có 4 năm PM fintech, rất quan tâm cơ hội tại ZaloPay.',                        'pending'),
(25, 20, 'cv_thu.pdf',      'Tôi đam mê cộng đồng game và muốn phát triển kênh social cho VNG.',                'pending'),

-- Đơn vào Tiki (jobs 4,5,27,28,29)
(4,  11, 'cv_levan.pdf',    'Tôi muốn ứng tuyển vị trí iOS Developer tại Tiki, có 2 năm Swift.',                'rejected'),
(5,  19, 'cv_dung.pdf',     'Tôi là sinh viên năm 4 muốn thực tập phân tích dữ liệu tại Tiki.',                 'rejected'),
(27, 14, 'cv_lan_2.pdf',    'Tôi có kinh nghiệm 4 năm Data Science, đã xây dựng recommendation system.',        'accepted'),
(28, 17, 'cv_bao_2.pdf',    'Tôi có 3 năm supply chain, muốn ứng tuyển vị trí analyst tại Tiki.',               'pending'),
(29, 16, 'cv_huong_2.pdf',  'Tôi có kỹ năng bán hàng và mong muốn phát triển mảng seller management tại Tiki.','pending'),

-- Đơn vào Shopee (jobs 6,7,30,31,32)
(6,  13, 'cv_khoa.pdf',     'Tôi có 5 năm Java Spring Boot, từng xây dựng hệ thống cho 1 triệu user.',          'accepted'),
(7,  14, 'cv_lan.pdf',      'Tôi rất đam mê digital marketing, đã chạy Facebook Ads hiệu quả 2 năm.',           'pending'),
(30, 15, 'cv_thanh_2.pdf',  'Tôi có nền tảng ML mạnh, thành thạo PyTorch và đã deploy model production.',       'accepted'),
(31, 20, 'cv_thu_2.pdf',    'Tôi có 3 năm CSKH và 1 năm team lead, muốn thử thách tại Shopee.',                 'pending'),
(32, 18, 'cv_tuyet_2.pdf',  'Tôi có kinh nghiệm brand marketing 5 năm trong ngành FMCG.',                       'rejected'),

-- Đơn vào Grab (jobs 8,9,33,34,35)
(8,  15, 'cv_thanh.pdf',    'Với 3 năm Android Kotlin, tôi tự tin có thể đóng góp cho Grab.',                   'accepted'),
(9,  19, 'cv_dung_2.pdf',   'Tôi quan tâm vị trí vận hành đối tác tài xế, có kinh nghiệm operations.',         'pending'),
(33, 21, 'cv_truong.pdf',   'Tôi có 3 năm data science và kinh nghiệm xây dựng pricing model.',                 'accepted'),
(34, 22, 'cv_phuong.pdf',   'Tôi muốn ứng tuyển growth marketing, đã tăng 200% user cho một app bằng referral.','pending'),
(35, 13, 'cv_khoa_3.pdf',   'Tôi có 2 năm Flutter, đã publish 3 app trên Store.',                               'pending'),

-- Đơn vào MoMo (jobs 10,11,36,37)
(10, 16, 'cv_huong.pdf',    'Tôi muốn đóng góp kỹ năng UI/UX cho sản phẩm MoMo.',                              'pending'),
(11, 17, 'cv_bao.pdf',      'Tôi có kinh nghiệm BA fintech 4 năm, thành thạo viết BRS/SRS.',                    'rejected'),
(36, 23, 'cv_dung2.pdf',    'Tôi có 3 năm Kotlin Spring, muốn phát triển hệ thống payment tại MoMo.',           'accepted'),
(37, 24, 'cv_ngoc.pdf',     'Tôi có 2 năm content marketing, muốn xây dựng brand voice cho MoMo.',              'pending'),

-- Đơn vào VNPT (jobs 12,13,38,39)
(12, 18, 'cv_tuyet.pdf',    'Tôi quan tâm vị trí DevOps VNPT, có kinh nghiệm AWS và Kubernetes.',               'pending'),
(13, 20, 'cv_thu.pdf',      'Tôi là HR chuyên IT recruiting, đã tuyển 50+ kỹ sư phần mềm.',                     'accepted'),
(38, 25, 'cv_truong2.pdf',  'Tôi có CCNP và 5 năm network engineering cho hệ thống lớn.',                       'pending'),
(39, 26, 'cv_ha.pdf',       'Tôi có 5 năm B2B sales telecom, muốn thách thức tại VNPT.',                        'pending'),

-- Đơn vào Viettel (jobs 14,15,40,41)
(14, 13, 'cv_khoa_2.pdf',   'Với kinh nghiệm data pipeline, tôi muốn đóng góp tại Viettel.',                    'accepted'),
(15, 19, 'cv_dung.pdf',     'Tôi muốn ứng tuyển kinh doanh B2B tại Viettel.',                                   'pending'),
(40, 27, 'cv_long.pdf',     'Tôi có 3 năm cybersecurity, kinh nghiệm pentest và SOC operations.',               'accepted'),
(41, 28, 'cv_yen.pdf',      'Tôi viết tài liệu kỹ thuật 2 năm, thành thạo Confluence và Markdown.',            'pending'),

-- Đơn vào Sacombank (jobs 16,17,18,42,43)
(16, 21, 'cv_truong_3.pdf', 'Tôi có 3 năm tín dụng cá nhân tại một ngân hàng khác, muốn chuyển sang Sacombank.','accepted'),
(17, 22, 'cv_phuong_2.pdf', 'Tôi có 2 năm thiết kế đồ họa, portfolio đính kèm CV.',                             'pending'),
(18, 24, 'cv_ngoc_2.pdf',   'Tôi là sinh viên năm cuối Quản trị Nhân lực, muốn thực tập tại Sacombank.',        'accepted'),
(42, 29, 'cv_tuan.pdf',     'Tôi có 3 năm risk management ngân hàng, thành thạo phân tích rủi ro tín dụng.',    'pending'),
(43, 11, 'cv_levan_3.pdf',  'Tôi muốn ứng tuyển IT Support tại Sacombank, có 1 năm kinh nghiệm helpdesk.',      'rejected');

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

-- Saved jobs: 30+ lượt lưu đa dạng
INSERT INTO saved_jobs (user_id, job_id) VALUES
(11, 1),  (11, 3),  (11, 6),  (11, 21),
(12, 2),  (12, 3),  (12, 23),
(13, 6),  (13, 8),  (13, 21), (13, 30),
(14, 7),  (14, 10), (14, 27), (14, 32),
(15, 6),  (15, 8),  (15, 30), (15, 35),
(16, 10), (16, 3),  (16, 36),
(17, 11), (17, 24), (17, 33),
(18, 12), (18, 36), (18, 40),
(19, 14), (19, 33), (19, 44),
(20, 13), (20, 37), (20, 25),
(21, 27), (21, 30), (21, 33),
(22, 32), (22, 37),
(23, 36), (23, 40),
(24, 37), (24, 18);

-- ---------------------------------------------------------
-- Bảng employer_requests: yêu cầu trở thành nhà tuyển dụng
-- User gửi yêu cầu kèm thông tin công ty, admin duyệt hoặc từ chối
-- ---------------------------------------------------------
CREATE TABLE employer_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_name VARCHAR(200) NOT NULL,
    company_description TEXT NULL,
    company_location VARCHAR(200) NULL,
    company_website VARCHAR(200) NULL,
    company_logo VARCHAR(255) NULL,              -- tên file logo tải lên khi đăng ký
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    admin_note VARCHAR(500) NULL,                -- lý do từ chối (tuỳ chọn)
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed: vài ứng viên đang xin trở thành employer
INSERT INTO employer_requests (user_id, company_name, company_description, company_location, company_website, status, admin_note) VALUES
(26, 'TechVN Startup',      'Công ty khởi nghiệp lĩnh vực EdTech, phát triển nền tảng học lập trình online.', 'Hà Nội',          'https://techvn.io',  'pending',  NULL),
(27, 'XYZ Logistics',       'Công ty logistics chuyên vận chuyển nội địa và xuyên biên giới.',                 'Đà Nẵng',         NULL,                 'rejected', 'Thông tin công ty chưa đầy đủ, vui lòng cung cấp website.'),
(28, 'Green Energy VN',     'Startup năng lượng tái tạo, phát triển giải pháp điện mặt trời cho hộ gia đình.','TP. Hồ Chí Minh', 'https://greenev.vn', 'pending',  NULL),
(29, 'HealthTech Solutions','Công ty y tế số, xây dựng ứng dụng kết nối bệnh nhân với bác sĩ.',               'TP. Hồ Chí Minh', 'https://healthtech.vn','pending', NULL),
(30, 'Digital Agency ABC',  'Agency truyền thông số, cung cấp dịch vụ SEO, Social Media và Content.',          'Hà Nội',          'https://agencyabc.vn','approved', NULL);

-- ---------------------------------------------------------
-- Bảng notifications: thông báo in-app cho từng user
-- type: 'new_application' | 'status_changed'
-- is_read: 0=chưa đọc, 1=đã đọc
-- link: URL để click vào (nullable)
-- ---------------------------------------------------------
DROP TABLE IF EXISTS notifications;
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(300) NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
