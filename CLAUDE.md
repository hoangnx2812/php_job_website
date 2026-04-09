# PHP Job Website

## Mô tả dự án
Website tìm kiếm việc làm, tham khảo các trang như TopCV, VietnamWorks, ITviec. Giao diện đơn giản, không cần quá cầu kỳ.

## Tech stack
- **Backend**: PHP thuần (server-side), không dùng framework
- **Database**: MySQL (chạy qua Docker Compose)
- **Kiến trúc**: Monolithic, gọn nhẹ
- **Frontend**: Bootstrap 5 (CDN) + custom CSS để tinh chỉnh màu sắc / spacing / card / hover effect cho đẹp. Có thể dùng Bootstrap Icons hoặc Font Awesome CDN cho icon. Không SPA.
- **File upload (CV)**: Lưu vào thư mục local trong project
- **Chỉ chạy local**, không dùng git


## Yêu cầu giao diện                                                                                                                                                                                               
- Layout rộng rãi, có khoảng trắng hợp lý, không bị dí sát.                                                                                                                                                        
- Dùng hệ màu chủ đạo nhất quán (ví dụ xanh dương / xanh ngọc cho nút và điểm nhấn).                                                                                                                               
- Navbar đẹp, có logo/icon, active state rõ ràng.                                                                                                                                                                  
- Trang chủ có hero banner đẹp mắt, ô search nổi bật.                                                                                                                                                              
- Card job / card company có shadow nhẹ, bo góc, hover hiệu ứng nhấc lên.                                                                                                                                          
- Badge trạng thái có màu phân biệt rõ (pending/accepted/rejected).                                                                                                                                                
- Form nhập liệu có label rõ, input lớn, nút CTA nổi bật.                                                                                                                                                          
- Bảng admin đẹp, có striping, hover row.                                                                                                                                                                          
- Responsive tốt trên mobile (dùng grid của Bootstrap).                                                                                                                                                            
- Font chữ dễ đọc (Inter / Be Vietnam Pro / Roboto qua Google Fonts).
## Roles & Permissions
Mỗi user có đúng **1 role** (fix cứng permission, không cần phân quyền phức tạp):

1. **Admin**
   - Quản lý người dùng (CRUD user)
   - Quản lý bài đăng tuyển dụng
   - Quản lý CV
   - Quản lý công ty
2. **Employer (Nhà tuyển dụng)**
   - Đăng / sửa / xoá bài tuyển dụng của mình
   - Xem & duyệt CV ứng tuyển vào bài của mình
   - Các chức năng cơ bản của user thường
3. **User (Ứng viên)**
   - Xem danh sách công ty, danh sách việc làm
   - Tìm kiếm / lọc việc làm
   - Upload CV (file local) và ứng tuyển vào bài đăng

## Authentication
- Có đăng nhập / đăng ký đàng hoàng (session-based)
- Mật khẩu phải hash (password_hash)
- Phân trang theo role sau khi đăng nhập

## Database
- Docker Compose chạy MySQL
- **1 file `script.sql` duy nhất** chứa:
  - Tạo toàn bộ bảng
  - Seed sẵn 3-4 bản ghi fake cho mỗi bảng
- Khi tạo bảng mới phải thêm luôn seed data vào cùng file

## Quy ước code
- Giữ cấu trúc đơn giản, dễ đọc cho người không biết PHP
- Không cần viết test
- Không cần CI/CD, không dùng git
- Comment tiếng Việt ở những chỗ quan trọng để user dễ đọc

## Ghi chú
- User không biết PHP → ưu tiên code dễ hiểu, hạn chế magic
- File upload CV: lưu vào thư mục `uploads/cv/` trong project

## Kế hoạch phát triển tiếp
Xem chi tiết trong [plan.md](./plan.md) — danh sách các tính năng sẽ làm sau
(download CV, profile, phân trang, filter, saved jobs, logo công ty, thống kê admin...).
