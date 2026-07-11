Đang gặp lỗi 500 Internal Server Error khi gọi API POST /api/bookings/hold để giữ ghế.

THÔNG TIN ĐÃ XÁC NHẬN:
- Request gửi lên (Payload) hợp lệ, không rỗng, không null:
  { "showtime_id": 4, "seat_ids": [157] }
- Response trả về:
  {
    "error_code": "INTERNAL_ERROR",
    "message": "An error occurred while holding seats: SQLSTATE[23000]: Integrity constraint..."
  }
  (thông báo bị cắt ngắn trên trình duyệt, chưa biết đầy đủ ràng buộc nào bị vi phạm)
- Đã tìm trong storage/logs/ nhưng không thấy dòng SQLSTATE[23000] nào khớp.

YÊU CẦU: Tự tìm và sửa dứt điểm lỗi này, làm theo đúng các bước sau, không bỏ qua bước nào:

1. Tìm đúng file log chứa lỗi này:
   - Kiểm tra LOG_CHANNEL trong .env (nếu là "daily" thì log nằm ở
     storage/logs/laravel-{ngày hiện tại}.log, không phải laravel.log).
   - Đọc toàn bộ nội dung file log mới nhất, tìm đoạn chứa "holding seats" hoặc
     "INTERNAL_ERROR" hoặc "SQLSTATE", in ra CHO TÔI xem đầy đủ, không cắt ngắn, bao gồm
     cả stack trace phía dưới (dòng "at ..." chỉ đúng file/dòng code gây lỗi).
   - Nếu file log trống hoặc không ghi được gì, kiểm tra quyền ghi thư mục storage/ và
     bootstrap/cache/, sửa lại nếu cần, rồi tái hiện lỗi 1 lần nữa để log ghi ra được.

2. Sau khi có nguyên nhân cụ thể từ log, đối chiếu với code trong SeatHoldService
   (đã tạo ở bước refactor Service Layer) và migration showtime_seats/bookings trong
   database/migrations/, xác định chính xác dòng code hoặc ràng buộc DB nào gây lỗi.
   Một vài khả năng cần kiểm tra kỹ (không giả định trước, phải xác nhận bằng log thật):
   - Cột NOT NULL nhưng code insert thiếu giá trị (ví dụ: booking_code, total_amount,
     hoặc cột nào đó chưa được set trước khi insert vào bookings).
   - Vi phạm UNIQUE KEY (ví dụ uq_showtime_seat trong showtime_seats, hoặc
     uq_bookings_code nếu logic sinh booking_code bị trùng do không đủ ngẫu nhiên).
   - Vi phạm Foreign Key (ví dụ showtime_id hoặc seat_id gửi lên không khớp với bản ghi
     thực tế đang tồn tại trong bảng showtime_seats do dữ liệu seed bị thiếu hoặc sai).

3. Sửa lại code cho đúng (Service, Model, hoặc migration nếu cần), sau đó tái hiện lại
   đúng request đã lỗi (showtime_id=4, seat_ids=[157]) để xác nhận API trả về 201 Created
   thành công, không còn lỗi 500.

4. Sau khi sửa xong, cải thiện luôn cách xử lý exception trong SeatHoldService: thay vì
   chỉ trả message chung "An error occurred while holding seats: ..." kèm nguyên câu lỗi
   SQL ra ngoài client (rò rỉ thông tin nhạy cảm về cấu trúc DB), hãy:
   - Log đầy đủ chi tiết lỗi thật (Log::error với exception và context) ở phía server.
   - Trả về client 1 thông báo lỗi chung chung, an toàn, ví dụ:
     {"error_code": "INTERNAL_ERROR", "message": "Đã có lỗi xảy ra, vui lòng thử lại."}
   - Đảm bảo cách làm này áp dụng nhất quán cho toàn bộ Service khác (PaymentService,
     CheckinService, ShowtimeService) nếu chúng đang có kiểu lộ message SQL ra ngoài
     tương tự.

Báo lại: (a) nguyên nhân gốc rễ tìm được từ log, (b) dòng code đã sửa, (c) kết quả test
lại request cũ đã thành công.
