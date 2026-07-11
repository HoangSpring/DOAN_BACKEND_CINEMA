# BỘ PROMPT GIAO VIỆC CHO ANTIGRAVITY
## Đồ án Backend Rạp Chiếu Phim — Laravel

**Cách dùng:** Copy từng prompt theo đúng thứ tự, dán vào Antigravity, đợi nó chạy xong và bạn tự test API (Postman) trước khi copy prompt tiếp theo. Không dán nhiều prompt cùng lúc.

Mỗi prompt đều giả định Antigravity đã đọc được 2 file `dac-ta-nghiep-vu-rap-chieu-phim.md` và `schema.sql` trong project (nếu chưa, thêm câu đầu tiên: *"Đọc kỹ 2 file dac-ta-nghiep-vu-rap-chieu-phim.md và schema.sql trong project trước khi làm."*).

---

## PROMPT 0 — Model & Eloquent Relationships

```
Đọc kỹ file dac-ta-nghiep-vu-rap-chieu-phim.md và schema.sql trong project.
Migration đã được tạo xong và chạy thành công. Nhiệm vụ của bạn bây giờ là
hoàn thiện các Eloquent Model tương ứng với TẤT CẢ các bảng trong schema.sql:
users, movies, tags, movie_tags, rooms, seats, showtimes, showtime_seats,
bookings, booking_seats, payments, booking_status_logs.

Yêu cầu cụ thể:
1. Khai báo đúng $fillable hoặc $guarded cho từng model theo đúng cột trong schema.sql.
2. Khai báo $casts cho các cột: datetime -> 'datetime', boolean (is_checked_in) -> 'boolean',
   decimal (amount, price...) -> 'decimal:2'.
3. Khai báo đầy đủ quan hệ Eloquent hai chiều theo đúng foreign key trong schema.sql, ví dụ:
   - Movie belongsToMany Tag (qua bảng movie_tags), và ngược lại Tag belongsToMany Movie.
   - Room hasMany Seat, Room hasMany Showtime.
   - Showtime belongsTo Movie, belongsTo Room, hasMany ShowtimeSeat, hasMany Booking.
   - ShowtimeSeat belongsTo Showtime, belongsTo Seat, belongsTo User (held_by_user_id, tên quan hệ heldBy).
   - Booking belongsTo User, belongsTo Showtime, hasMany BookingSeat, hasOne Payment,
     belongsTo User (staff_id, tên quan hệ staff).
   - BookingSeat belongsTo Booking, belongsTo ShowtimeSeat.
   - Payment belongsTo Booking.
4. KHÔNG được đổi tên bảng hoặc tên cột khác với schema.sql — Model phải khớp 100% với
   schema đã có, không tự ý thêm bảng hoặc cột mới.
5. Sau khi xong, chạy `php artisan tinker` thử load quan hệ Movie::with('tags')->first()
   và Showtime::with('showtimeSeats.seat')->first() để xác nhận không lỗi, rồi báo lại kết quả.
```

---

## PROMPT 1 — Seeder & Factory

```
Viết Database Seeder cho dự án dựa theo đúng dữ liệu mẫu đã có sẵn trong schema.sql
(phần INSERT ở cuối file), nhưng mở rộng thêm để đủ dữ liệu test:

1. UserSeeder: 1 admin, 2 staff, 10 customer (dùng Factory, password đều hash là "password").
2. TagSeeder: dùng đúng 6 tag đã liệt kê trong schema.sql.
3. MovieSeeder: ít nhất 8 phim (2 phim có sẵn trong schema.sql + 6 phim tự sinh bằng Factory
   với dữ liệu tiếng Việt hợp lý), gắn tag ngẫu nhiên 1-3 tag/phim qua quan hệ attach().
4. RoomSeeder + SeatSeeder: 3 phòng, mỗi phòng tự sinh sơ đồ ghế theo đúng logic đã mô tả
   trong dac-ta-nghiep-vu-rap-chieu-phim.md mục 3.1 (không insert tay từng ghế, viết vòng lặp
   sinh ghế theo hàng/số như ví dụ CROSS JOIN trong schema.sql).
5. ShowtimeSeeder: với mỗi phim tạo 2-3 suất chiếu trong 7 ngày tới, đảm bảo KHÔNG trùng
   phòng + giờ (áp dụng đúng luật overlap-check ở mục 3.3 tài liệu nghiệp vụ). Sau khi tạo
   showtime, PHẢI tự động bulk insert showtime_seats cho toàn bộ ghế của phòng đó với
   status = 'available', đúng như logic mô tả trong tài liệu.
6. Đăng ký tất cả seeder vào DatabaseSeeder theo đúng thứ tự phụ thuộc (User -> Tag -> Movie
   -> MovieTag -> Room -> Seat -> Showtime -> ShowtimeSeat).

Sau khi viết xong, chạy `php artisan migrate:fresh --seed` và báo lại số lượng bản ghi
đã tạo ở mỗi bảng (dùng tinker hoặc query count).
```

---

## PROMPT 2 — Auth & Phân quyền

```
Đọc mục "1.1 Vai trò người dùng" và "6. Bảo mật & Middleware" trong file
dac-ta-nghiep-vu-rap-chieu-phim.md. Triển khai xác thực API bằng Laravel Sanctum:

1. Cài đặt và cấu hình Sanctum cho API token (không dùng session, vì đây là API thuần).
2. API endpoints:
   - POST /api/register (chỉ tạo role = customer, không cho tự đăng ký làm admin/staff)
   - POST /api/login -> trả về access token
   - POST /api/logout -> revoke token hiện tại
   - GET /api/me -> trả thông tin user đang đăng nhập
3. Tạo Middleware tên "role" (app/Http/Middleware/CheckRole.php), đăng ký alias 'role' trong
   bootstrap/app.php (Laravel 11) hoặc Kernel.php (Laravel <=10), cho phép dùng dạng
   middleware(['auth:sanctum', 'role:admin']) hoặc role:admin,staff (nhiều role).
4. Áp dụng middleware role cho ĐÚNG các nhóm route sẽ tạo ở các prompt sau:
   - Nhóm route /api/admin/* -> role:admin
   - Nhóm route /api/staff/* -> role:staff,admin (admin cũng được thao tác staff)
   - Route công khai (xem phim, lịch chiếu) -> không cần auth
   - Route đặt vé -> auth:sanctum (mọi role đã đăng nhập)
5. Validate request bằng FormRequest riêng cho register/login, không validate trực tiếp
   trong Controller.
6. Test thử bằng tinker hoặc Postman: login bằng tài khoản admin đã seed, gọi thử 1 route
   giả có middleware role:admin để xác nhận chặn đúng, rồi báo lại kết quả.
```

---
## PROMPT 2.1 — Policy: Phân quyền theo bản ghi (Booking)

Middleware "role" đã tạo ở Prompt 2 chỉ kiểm tra được user có VAI TRÒ gì (customer/staff/
admin), nhưng CHƯA đảm bảo user chỉ thao tác lên đúng dữ liệu của mình. Cụ thể: một
customer đã đăng nhập hiện có thể sửa booking_id trên URL để xem/thanh toán booking của
customer khác (lỗi bảo mật IDOR). Bổ sung Laravel Policy để chặn việc này:

1. Tạo BookingPolicy (php artisan make:policy BookingPolicy --model=Booking), định nghĩa
   các method:
   - view(User $user, Booking $booking): true nếu $user->id === $booking->user_id,
     hoặc $user->role thuộc staff/admin (nhân viên/admin được xem mọi booking).
   - checkout(User $user, Booking $booking): true CHỈ KHI $user->id === $booking->user_id
     (staff/admin KHÔNG được thay khách hàng thanh toán online, họ có luồng riêng ở
     Module 4.2 - bán vé tại quầy).
   - checkin(User $user, Booking $booking): true nếu $user->role thuộc staff/admin.
   Đăng ký Policy đúng theo cơ chế của bản Laravel đang dùng (AuthServiceProvider hoặc
   auto-discovery của Laravel 11).

2. Áp dụng lại vào các API đã có sẵn từ Prompt 5 và Prompt 6 (KHÔNG viết lại toàn bộ
   controller, chỉ bổ sung dòng kiểm tra quyền):
   - Trong route thanh toán (checkout) ở BookingController: gọi
     $this->authorize('checkout', $booking) ngay đầu action, bắt exception
     AuthorizationException và trả về đúng format lỗi JSON đang dùng xuyên suốt project
     ({"error_code": "FORBIDDEN", "message": "..."}), HTTP 403.
   - Trong PaymentService (đã tách ở việc dùng chung nếu có, hoặc trong PaymentController):
     xác nhận booking đang xử lý callback thanh toán thuộc đúng user đã tạo nó, dùng lại
     policy 'checkout' hoặc kiểm tra tương đương.
   - Nếu đã có route xem chi tiết 1 booking/vé (GET /api/bookings/{id} hoặc tương tự):
     áp dụng $this->authorize('view', $booking).
   - Route check-in ở Module 5 giữ nguyên dùng middleware role:staff,admin như cũ (không
     cần authorize('checkin') riêng vì đã đủ chặn bằng role, trừ khi bạn muốn kiểm tra kỹ
     hơn thì áp dụng luôn cho nhất quán).

3. Viết Feature Test mới (tests/Feature/BookingAuthorizationTest.php):
   - Customer A tạo 1 booking (qua hold-seats).
   - Customer B (tài khoản khác, đã đăng nhập) gọi API checkout lên booking của A ->
     PHẢI trả về 403 Forbidden, booking của A KHÔNG được chuyển sang trạng thái paid.
   - Customer A tự checkout booking của chính mình -> vẫn thành công như bình thường
     (đảm bảo không phá vỡ luồng cũ).

Chạy lại TOÀN BỘ test suite đã có từ Prompt 9 (không chỉ test mới) để đảm bảo việc thêm
Policy không làm hỏng các luồng đã chạy đúng trước đó, báo lại kết quả pass/fail.

## PROMPT 3 — Module 1: Quản lý danh mục (Admin)

```
Đọc kỹ mục "MODULE 1 — Quản lý danh mục (Admin)" trong file dac-ta-nghiep-vu-rap-chieu-phim.md
(bao gồm 3.1, 3.1.1, 3.2, 3.3). Triển khai ĐÚNG và ĐỦ các API sau, không thêm bớt phạm vi:

1. CRUD Movie: POST/PUT/DELETE /api/admin/movies — áp dụng đúng logic soft-delete
   (status = 'ended'), đúng logic đồng bộ tag qua sync() khi PUT, đúng validate age_rating.
2. CRUD Tag: GET/POST/PUT/DELETE /api/admin/tags — tự sinh slug từ name nếu không truyền
   (dùng Str::slug()), validate unique name/slug.
3. Room + Seat: POST /api/admin/rooms — nhận số hàng, số ghế/hàng, danh sách vị trí VIP,
   tự động bulk insert vào bảng seats trong 1 transaction.
   PUT /api/admin/rooms/:id/seats/:seat_id — sửa loại ghế, chặn nếu ghế thuộc suất chiếu
   tương lai đã có người đặt (trả 409).
4. Showtime: POST /api/admin/showtimes — PHẢI triển khai ĐÚNG luồng 4 bước trong tài liệu:
   tính end_time, kiểm tra overlap bằng lockForUpdate(), mở DB transaction insert showtime +
   bulk insert showtime_seats, rollback nếu lỗi.

Yêu cầu kỹ thuật bắt buộc:
- Mỗi API dùng FormRequest riêng để validate (không validate trong Controller).
- Dùng Resource class (JsonResource) để format response, không trả raw Eloquent model.
- Trả đúng mã HTTP status như tài liệu quy định (201, 404, 409, 422).
- Toàn bộ thao tác ghi nhiều bảng PHẢI bọc trong DB::transaction().

Sau khi xong, liệt kê lại toàn bộ route đã tạo (php artisan route:list --path=api/admin)
và báo lại.
```

---

## PROMPT 4 — Module 2: Tra cứu (Public)

```
Đọc kỹ mục "MODULE 2 — Xem phim & tra cứu" trong dac-ta-nghiep-vu-rap-chieu-phim.md.
Triển khai các API công khai (không cần đăng nhập):

1. GET /api/movies — hỗ trợ filter ?status=showing và ?tags=slug1,slug2 (lọc kiểu OR
   như SQL mẫu trong tài liệu), có phân trang, dùng Eager Loading with('tags').
2. GET /api/tags — danh sách toàn bộ tag.
3. GET /api/movies/{id}/showtimes?date=YYYY-MM-DD — lọc theo ngày, chỉ trả status = scheduled.
4. GET /api/showtimes/{id}/seats — BẮT BUỘC dùng Eager Loading (with('seat')) để lấy
   showtime_seats kèm thông tin ghế trong 1 query, không được query lặp trong vòng lặp.
   Ghế đang holding nhưng hold_expires_at đã qua phải được coi như available trong response.

Sau khi viết xong, cài Laravel Debugbar (hoặc dùng DB::enableQueryLog()) để tự kiểm tra
API GET /api/showtimes/{id}/seats có bị N+1 query không, báo lại số lượng query thực thi.
```

---

## PROMPT 5 — Module 3: Giữ ghế tạm thời

```
Đọc kỹ mục "MODULE 3 — Giữ ghế tạm thời" trong dac-ta-nghiep-vu-rap-chieu-phim.md.
Đây là module quan trọng nhất về race condition, triển khai CHÍNH XÁC theo đúng
7 bước mô tả trong tài liệu:

1. POST /api/bookings/hold — nhận showtime_id, seat_ids[].
   Trong 1 DB::transaction():
   - Lock các dòng showtime_seats liên quan bằng lockForUpdate().
   - Kiểm tra từng ghế: nếu status = booked -> trả 409 kèm mã lỗi SEAT_ALREADY_BOOKED.
     Nếu status = holding và hold_expires_at chưa qua -> trả 409 SEAT_ALREADY_HELD.
   - Update các ghế hợp lệ: status = holding, held_by_user_id = auth user,
     hold_expires_at = now()->addMinutes(5).
   - Tạo booking status = pending, tính total_amount dựa theo giá ghế
     (price_standard/price_vip từ showtime, theo loại ghế).
2. Viết Job (dùng Laravel Task Scheduling, chạy mỗi phút qua Schedule::command() trong
   routes/console.php hoặc app/Console/Kernel.php) tự động:
   - UPDATE showtime_seats set status=available, held_by_user_id=null nơi status=holding
     và hold_expires_at đã qua.
   - UPDATE bookings tương ứng còn status=pending -> expired.
   Đặt tên command là app:release-expired-seats.

Yêu cầu: dùng mã lỗi (error code) đúng như bảng "5. Bảng mã lỗi nghiệp vụ chuẩn hoá"
trong tài liệu, trả về dạng JSON {"error_code": "...", "message": "..."}.

Sau khi xong, hướng dẫn tôi cách test thủ công: gọi 2 request đồng thời giữ cùng 1 ghế
(ví dụ bằng 2 tab Postman bấm gần như cùng lúc) để xác nhận chỉ 1 request thành công.
```

---

## PROMPT 6 — Module 4: Thanh toán & xuất vé

```
Đọc kỹ mục "MODULE 4 — Thanh toán & xuất vé" trong dac-ta-nghiep-vu-rap-chieu-phim.md,
bao gồm 4.1, 4.2, 4.3, 4.4. Triển khai:

1. POST /api/bookings/{id}/checkout — kiểm tra booking đang pending và chưa hết hạn giữ ghế,
   trả về payload giả lập cổng thanh toán (không cần tích hợp cổng thật, tạo route giả lập
   POST /api/payments/simulate-gateway để test).
2. POST /api/payments/callback — triển khai ĐÚNG 7 bước trong DB::transaction() như tài liệu
   mô tả, đặc biệt:
   - Kiểm tra idempotency_key đã tồn tại trong bảng payments chưa, nếu đã success thì trả
     về kết quả cũ ngay, KHÔNG xử lý lại.
   - Cập nhật payment, booking, showtime_seats (status=booked) trong cùng transaction.
   - Sinh QR: payload gồm booking_id, booking_code, và chữ ký HMAC-SHA256 (dùng 1 secret
     key trong .env tên QR_SECRET_KEY). Lưu vào bookings.qr_code_data. Có thể dùng package
     simplesoftwareio/simple-qrcode để encode nếu cần trả về ảnh QR, nhưng dữ liệu ký số
     phải tự viết bằng hash_hmac('sha256', ...).
3. POST /api/staff/bookings/counter — luồng bán vé tại quầy, bỏ qua bước hold 5 phút,
   tạo thẳng booking status=paid, payment_method=cash, status=success.
4. Middleware/validate bắt buộc header Idempotency-Key cho route checkout và counter,
   nếu thiếu header -> trả 400.

Sau khi xong, viết 1 Feature Test (tests/Feature/PaymentIdempotencyTest.php) gọi API
callback 2 LẦN với cùng idempotency_key, assert rằng booking chỉ được set paid 1 lần và
showtime_seats chỉ update 1 lần (kiểm tra qua updated_at hoặc đếm số bản ghi trong
booking_status_logs nếu bạn có dùng bảng đó).
```

---

## PROMPT 7 — Module 5: Check-in QR

```
Đọc kỹ mục "MODULE 5 — Quét QR Check-in" trong dac-ta-nghiep-vu-rap-chieu-phim.md.
Triển khai POST /api/staff/checkin (chỉ role staff, admin), theo ĐÚNG thứ tự kiểm tra
mô tả trong tài liệu:

1. Giải mã qr_data, xác minh chữ ký HMAC bằng QR_SECRET_KEY (cùng secret đã dùng ở
   Module 4) -> sai chữ ký trả 400, mã lỗi INVALID_QR_SIGNATURE.
2. Tìm booking, không tồn tại -> 404.
3. Kiểm tra lần lượt: booking.status != paid -> 422; is_checked_in == true -> 422 kèm
   error_code TICKET_ALREADY_CHECKED_IN và trả kèm thời điểm checked_in_at cũ;
   showtime.status != ongoing -> 422 error_code SHOWTIME_NOT_ONGOING.
4. Nếu hợp lệ: cập nhật is_checked_in=true, checked_in_at=now(), trả 200 kèm thông tin
   phim/suất chiếu/ghế để nhân viên đối chiếu.

Viết kèm Feature Test: quét 1 vé hợp lệ -> thành công; quét lại lần 2 cùng vé -> phải
trả đúng error_code TICKET_ALREADY_CHECKED_IN, không được cho check-in lại.
```

---

## PROMPT 8 — Module 6: Báo cáo & thống kê

```
Đọc kỹ mục "MODULE 6 — Báo cáo & thống kê" trong dac-ta-nghiep-vu-rap-chieu-phim.md.
Triển khai 3 API (chỉ role admin), dùng query builder hoặc raw SQL với GROUP BY/SUM/COUNT
ĐÚNG như các câu SQL mẫu trong tài liệu, KHÔNG dùng vòng lặp PHP để tính tổng:

1. GET /api/admin/reports/revenue?from=&to=&group_by=day|month
2. GET /api/admin/reports/occupancy?showtime_id=
3. GET /api/admin/reports/revenue-by-movie

Trả dữ liệu dạng mảng JSON sạch, sẵn sàng để frontend vẽ biểu đồ (không lồng nhau phức tạp).
Sau khi xong, gọi thử cả 3 API với dữ liệu đã seed và dán kết quả JSON mẫu để tôi xem qua.
```

---

## PROMPT 9 — Testing tổng thể

```
Viết bộ Feature Test cho Laravel (thư mục tests/Feature) bao phủ các luồng nghiệp vụ
nhạy cảm nhất đã liệt kê trong dac-ta-nghiep-vu-rap-chieu-phim.md:

1. SeatHoldRaceConditionTest — mô phỏng 2 request cùng giữ 1 ghế gần như đồng thời
   (dùng DB transaction test hoặc chạy tuần tự nhưng assert đúng logic lock), chỉ 1
   request thành công, request còn lại nhận đúng error_code SEAT_ALREADY_HELD.
2. PaymentIdempotencyTest — đã viết ở Prompt 6, đảm bảo còn chạy pass.
3. CheckinDuplicateTest — đã viết ở Prompt 7, đảm bảo còn chạy pass.
4. ShowtimeOverlapTest — tạo 2 suất chiếu trùng phòng/giờ, request thứ 2 phải trả 422
   error_code SHOWTIME_OVERLAP.
5. RoleMiddlewareTest — customer gọi route /api/admin/* phải bị 403.

Chạy `php artisan test` và báo lại kết quả toàn bộ test suite (pass/fail).
```

---

## PROMPT 10 — Hoàn thiện tài liệu & bàn giao

```
Dự án đã hoàn thành đầy đủ các module. Bây giờ hãy:

1. Viết file README.md ở gốc project gồm: mô tả dự án, yêu cầu hệ thống (PHP version,
   MySQL), các bước cài đặt (composer install, cp .env.example .env, php artisan key:generate,
   php artisan migrate --seed), cách chạy scheduler cho job nhả ghế (php artisan schedule:work),
   danh sách tài khoản mẫu để test (admin/staff/customer đã seed kèm password).
2. Cài đặt package darkaonline/l5-swagger (hoặc knuckleswtf/scribe), viết annotation
   OpenAPI cho toàn bộ route đã tạo, generate ra trang docs tại /api/documentation.
3. Export 1 file Postman collection (.json) chứa toàn bộ endpoint đã tạo, có sẵn ví dụ
   request body cho từng API, để tôi import vào Postman demo khi bảo vệ đồ án.

Báo lại danh sách file đã tạo/sửa ở bước này.
```

---

## Ghi chú khi dùng bộ prompt Backend (Prompt 0-10)

- Nếu Antigravity báo lỗi ở prompt nào, đừng chuyển sang prompt tiếp theo — sửa xong lỗi đó trước, vì các module sau phụ thuộc trực tiếp vào module trước (đặc biệt Prompt 5 và 6 phụ thuộc chặt vào Prompt 0 và 2).
- Sau mỗi prompt, tự chạy `php artisan route:list` để chắc chắn route được đăng ký đúng và không trùng path.
- Nếu Antigravity tự ý đổi tên bảng/cột khác với `schema.sql` để "cho gọn hơn" — từ chối và yêu cầu làm lại đúng theo schema, vì 2 file tài liệu là nguồn chân lý (source of truth) duy nhất của đồ án.

---
---

# PHẦN 2 — FRONTEND (Blade + Alpine.js, trong chính project Laravel)

**Stack:** Laravel Breeze (bản Blade + Alpine.js, không phải bản React/Vue của Breeze) để có sẵn khung đăng ký/đăng nhập/session, Tailwind CSS (đi kèm Breeze), Alpine.js cho các phần cần tương tác động (chọn ghế, đếm ngược, poll trạng thái, quét QR), Chart.js (qua CDN) cho biểu đồ báo cáo.

**Kiến trúc:** Không tách project riêng như phương án React — toàn bộ nằm trong `resources/views/` của project Laravel đã có. Có 2 hệ route:
- `routes/api.php` — API JSON đã xây ở Phần 1 (giữ nguyên, vẫn dùng cho Postman/Swagger demo hoặc để mở rộng sau này).
- `routes/web.php` — route mới cho giao diện Blade, dùng **session-based authentication** chuẩn của Laravel (không phải bearer token của Sanctum), tách biệt hoàn toàn khỏi `api.php`.

**Quan trọng — tránh trùng lặp logic:** các nghiệp vụ phức tạp (giữ ghế, thanh toán, check-in) đã viết trong API Controller ở Phần 1 KHÔNG được copy-paste sang Web Controller. Phải tách ra Service class dùng chung, tránh 2 nơi xử lý bị lệch nhau.

---

## PROMPT 11 — Refactor Service Layer + Cài Laravel Breeze (Blade + Alpine.js)

```
Đọc lại toàn bộ Controller đã viết ở Prompt 3-8 (Phần Backend). Trước khi làm giao diện,
thực hiện refactor sau:

1. Tạo các Service class trong app/Services chứa TOÀN BỘ logic nghiệp vụ phức tạp đang
   nằm trong API Controller, tối thiểu gồm:
   - SeatHoldService: xử lý logic giữ ghế (lockForUpdate, kiểm tra trạng thái, tạo booking
     pending) đã viết ở Module 3.
   - PaymentService: xử lý logic thanh toán, idempotency check, cập nhật trạng thái,
     sinh QR đã viết ở Module 4.
   - CheckinService: xử lý logic check-in QR đã viết ở Module 5.
   - ShowtimeService: xử lý logic tạo suất chiếu (overlap check + bulk insert
     showtime_seats) đã viết ở Module 1.
2. Sửa lại các API Controller hiện có để gọi qua Service class thay vì xử lý trực tiếp
   trong controller (giữ nguyên hành vi, chỉ tổ chức lại code — chạy lại toàn bộ Feature
   Test đã viết ở Prompt 9, đảm bảo vẫn pass 100% sau khi refactor).

Sau khi refactor xong, cài đặt Laravel Breeze bản Blade:
   composer require laravel/breeze --dev
   php artisan breeze:install blade
   npm install && npm run build
Xác nhận Breeze đã tạo sẵn: trang login/register/logout, middleware auth, layout
resources/views/layouts, và Tailwind + Alpine.js đã hoạt động (Breeze tự cấu hình sẵn,
không cần cài thêm).

Sau khi cài xong, cấu hình 1 bảng màu Tailwind riêng cho thương hiệu rạp phim (tông tối,
điểm nhấn đỏ/vàng gold) trong tailwind.config.js, KHÔNG dùng màu mặc định của Breeze.
Báo lại danh sách file Service đã tạo và xác nhận Breeze chạy được ở localhost.
```

---

## PROMPT 12 — Layout & Route theo vai trò

```
Dựa trên layout Breeze đã cài ở Prompt 11, tạo 3 layout Blade riêng biệt:

1. resources/views/layouts/customer.blade.php — layout cho khách hàng: header (logo, menu,
   nút đăng nhập/avatar), footer, tông màu tối đã cấu hình.
2. resources/views/layouts/admin.blade.php — layout cho admin: sidebar cố định bên trái
   (Dashboard, Quản lý phim, Quản lý tag, Quản lý phòng & ghế, Quản lý suất chiếu, Báo cáo,
   Đăng xuất), tông màu sáng/gọn, khác hẳn layout customer để dễ phân biệt.
3. resources/views/layouts/staff.blade.php — layout cho nhân viên: đơn giản, chữ to, ít
   chi tiết, chỉ 2 menu chính (Bán vé tại quầy, Check-in QR).

Tạo Middleware "role" (nếu Prompt 2 làm cho API rồi thì tạo bản áp dụng được cho route
web, dùng chung logic kiểm tra $request->user()->role), đăng ký route group trong
routes/web.php:
- prefix 'admin', middleware ['auth', 'role:admin'] -> layout admin
- prefix 'staff', middleware ['auth', 'role:staff,admin'] -> layout staff
- route công khai + route cần đăng nhập (đặt vé) dùng layout customer

Sửa trang login của Breeze: sau khi đăng nhập, tự động điều hướng theo role (admin ->
/admin, staff -> /staff, customer -> /).

Chưa cần code nội dung trang, chỉ dựng khung layout + route + guard. Test thử đăng nhập
3 loại tài khoản đã seed, xác nhận điều hướng đúng và bị chặn đúng khi vào nhầm khu vực.
```

---

## PROMPT 13 — Trang khách hàng: Trang chủ, danh sách phim, chi tiết phim

```
Đọc mục "MODULE 2" trong dac-ta-nghiep-vu-rap-chieu-phim.md. Tạo Web Controller
(app/Http/Controllers/Web/MovieController.php, tách namespace riêng khỏi Api Controller)
dùng TRỰC TIẾP Eloquent (Movie::with('tags')...), KHÔNG gọi qua route api.php, để tránh
phức tạp hoá auth giữa 2 hệ route.

1. GET / (trang chủ): banner phim nổi bật (Alpine.js làm carousel đơn giản, x-data quản lý
   slide hiện tại), lưới phim đang chiếu (Blade @foreach, card có poster, badge tag), bộ
   lọc theo tag dùng query string (?tags=slug1,slug2) xử lý ngay trong Controller bằng
   Eloquent whereHas, KHÔNG cần JavaScript cho phần lọc này (submit qua GET form là đủ).
2. GET /movies/{id} (chi tiết phim): mô tả, tag, danh sách lịch chiếu theo ngày — dùng
   Alpine.js x-data để chuyển tab ngày (không cần reload trang), mỗi ngày hiện các suất
   chiếu dạng nút giờ, bấm vào chuyển tới route chọn ghế.
3. Responsive tốt trên mobile, dùng đúng bảng màu tông tối đã cấu hình ở Prompt 11.

Ưu tiên thẩm mỹ: khoảng trắng hợp lý, poster có hover effect (Tailwind transition/scale),
không để giao diện trông sơ sài.
```

---

## PROMPT 14 — Chọn ghế, giữ ghế, đếm ngược (phần tương tác động nhất)

```
Đọc mục "MODULE 3" trong dac-ta-nghiep-vu-rap-chieu-phim.md. Đây là trang cần Alpine.js
nhiều nhất vì phải cập nhật trạng thái ghế gần như real-time mà không load lại trang:

1. Tạo route web GET /showtimes/{id}/seats (middleware auth) — Controller trả về Blade
   kèm dữ liệu ghế ban đầu (render sẵn lần đầu, không cần chờ JS).
2. Tạo thêm 2 route JSON nhẹ dùng riêng cho Alpine.js gọi bằng fetch() (đặt trong
   routes/web.php, cùng middleware auth nên dùng session, KHÔNG dùng route bên api.php để
   tránh xung đột kiểu auth):
   - GET /showtimes/{id}/seats-status (trả JSON trạng thái ghế mới nhất, dùng để poll)
   - POST /showtimes/{id}/hold-seats (gọi qua SeatHoldService đã tạo ở Prompt 11, trả JSON
     kết quả hoặc lỗi 409 kèm error_code)
3. Trong Blade, dùng Alpine.js (x-data ở component sơ đồ ghế):
   - Vẽ sơ đồ ghế theo hàng/cột, 3 trạng thái màu rõ ràng (available/booked/holding),
     ghế VIP có style riêng, có legend chú thích.
   - x-data quản lý danh sách ghế đang chọn, tính tổng tiền hiển thị ở thanh cố định dưới.
   - setInterval gọi fetch('/showtimes/{id}/seats-status') mỗi 5-10 giây để cập nhật ghế
     người khác vừa đặt/giữ, cập nhật lại UI mà không reload trang.
   - Khi bấm "Tiến hành thanh toán": fetch POST tới /hold-seats, nếu lỗi 409 hiện thông
     báo rõ ràng (dùng Alpine x-show cho toast tự ẩn sau vài giây) và refetch lại trạng
     thái ghế; nếu thành công, điều hướng sang trang thanh toán kèm bookingId.
   - Đồng hồ đếm ngược 5 phút hiển thị góc màn hình dùng Alpine setInterval tính từ
     hold_expires_at trả về, hết giờ tự động điều hướng về lại trang chọn ghế kèm cảnh báo.

Giới hạn tối đa 8 ghế/lần chọn ở phía Alpine.js (validate lại lần nữa ở Service phía
backend, không chỉ tin phía client).
```

---

## PROMPT 15 — Thanh toán, vé điện tử, lịch sử vé

```
Đọc mục "MODULE 4" trong dac-ta-nghiep-vu-rap-chieu-phim.md. Tạo Web Controller
BookingController (dùng PaymentService đã tạo ở Prompt 11):

1. GET /checkout/{bookingId}: hiển thị lại thông tin vé đã chọn, đồng hồ đếm ngược kế
   thừa cách làm ở Prompt 14, nút "Xác nhận thanh toán" — dùng Alpine.js sinh
   Idempotency-Key (crypto.randomUUID()) lưu trong x-data, gửi kèm khi fetch POST tới
   route web xử lý thanh toán giả lập, disable nút ngay khi bấm (x-bind:disabled) tránh
   double-click.
2. GET /tickets/{bookingId}: trang vé điện tử sau khi thanh toán thành công — hiển thị mã
   QR (dùng package simplesoftwareio/simple-qrcode để Controller render ảnh QR trực tiếp
   từ Blade bằng {!! QrCode::size(200)->generate($booking->qr_code_data) !!}, không cần
   JS phía client để vẽ QR), trình bày như vé xem phim thật (viền dashed phân đoạn xé vé).
3. GET /my-tickets: danh sách vé đã đặt của user đang đăng nhập (Auth::user()->bookings),
   phân trang, mỗi vé click vào xem lại chi tiết như trang vé điện tử.

Toàn bộ trang có trạng thái loading/error rõ ràng khi cần chờ fetch (Alpine x-show với
biến loading), không để khoảng trắng khi đang xử lý.
```

---

## PROMPT 16 — Admin: CRUD Phim, Tag, Phòng/Ghế, Suất chiếu

```
Đọc mục "MODULE 1" trong dac-ta-nghiep-vu-rap-chieu-phim.md. Trong layout admin đã tạo ở
Prompt 12, xây dựng CRUD bằng Blade + Resource Controller chuẩn Laravel (index/create/
edit/store/update/destroy), KHÔNG cần SPA:

1. /admin/movies: bảng danh sách (Blade table, phân trang Laravel paginate, ô tìm kiếm
   submit GET). Trang tạo/sửa dùng form Blade đầy đủ field theo schema.sql, ô chọn nhiều
   tag dùng <select multiple> (có thể nâng cấp bằng thư viện nhẹ Choices.js qua CDN nếu
   muốn đẹp hơn select mặc định). Nút xoá bọc trong <form> DELETE, dùng Alpine.js
   x-data hiện Dialog xác nhận trước khi submit thật.
2. /admin/tags: CRUD đơn giản dạng bảng + form nhỏ (có thể dùng modal Alpine.js để không
   cần chuyển trang khi thêm/sửa nhanh).
3. /admin/rooms: danh sách phòng, click vào 1 phòng hiện trang riêng vẽ SƠ ĐỒ GHẾ dạng
   lưới bằng Blade @foreach lồng theo hàng/cột (CSS Grid), click từng ghế mở Alpine.js
   popover nhỏ để đổi loại ghế (gọi fetch PUT tới route đổi loại ghế, cập nhật UI ngay
   không cần reload).
4. /admin/showtimes: danh sách suất chiếu (lọc theo phim/phòng/ngày qua query string GET),
   form tạo mới dùng ShowtimeService đã tạo ở Prompt 11. Nếu Service trả lỗi overlap, hiện
   rõ thông báo "Phòng đã có suất chiếu trùng giờ" ngay trong form (dùng session flash
   error của Laravel, hiển thị @error trong Blade).

Toàn bộ form dùng FormRequest để validate, hiển thị lỗi bằng @error() chuẩn Blade dưới
từng field.
```

---

## PROMPT 17 — Admin: Báo cáo & thống kê (biểu đồ)

```
Đọc mục "MODULE 6" trong dac-ta-nghiep-vu-rap-chieu-phim.md. Tạo trang /admin/reports:

1. Bộ lọc khoảng thời gian (from/to) bằng input type="date" chuẩn HTML, submit qua GET,
   Controller dùng lại đúng query SQL đã viết ở Module 6 (SUM/COUNT/GROUP BY).
2. Nhúng Chart.js qua CDN (không cần cài npm riêng), Controller trả dữ liệu đã tổng hợp
   sẵn ra Blade dưới dạng biến PHP, dùng @json() để đẩy vào biến JavaScript, khởi tạo
   biểu đồ bằng 1 đoạn <script> nhỏ ở cuối trang:
   - LineChart hoặc BarChart: doanh thu theo ngày/tháng.
   - BarChart ngang: doanh thu theo từng phim, sắp xếp giảm dần.
3. Bảng tỷ lệ lấp đầy ghế theo từng suất chiếu sắp diễn ra, hiển thị thanh phần trăm bằng
   CSS thuần (div width theo %), không cần thư viện riêng.
4. Thẻ số liệu nhanh ở đầu trang (tổng doanh thu, tổng vé đã bán, tỷ lệ lấp đầy trung
   bình) như đã mô tả ở Prompt 12 (Dashboard tổng quan) — nếu chưa làm ở đó thì làm ở đây.

Toàn bộ số liệu tính bằng SQL, KHÔNG dùng vòng lặp PHP để cộng dồn, đúng nguyên tắc trong
tài liệu nghiệp vụ.
```

---

## PROMPT 18 — Staff: Bán vé tại quầy & Check-in QR

```
Đọc mục "MODULE 4.2" và "MODULE 5" trong dac-ta-nghiep-vu-rap-chieu-phim.md. Trong layout
staff đã tạo ở Prompt 12:

1. /staff/counter: chọn suất chiếu (dropdown lọc theo phim/ngày, submit GET để load lại
   sơ đồ ghế đúng suất), hiện sơ đồ ghế tương tự Prompt 14 (có thể tái sử dụng lại 1 phần
   component Blade nếu hợp lý) nhưng KHÔNG cần giữ ghế 5 phút — chọn xong bấm "Thu tiền &
   xuất vé" gọi thẳng route xử lý bán vé quầy qua Service đã tách ở Prompt 11, sau đó hiện
   nút "In vé" dùng window.print() với CSS @media print riêng cho khổ vé nhỏ (ẩn toàn bộ
   sidebar/header khi in).
2. /staff/checkin: nhúng thư viện html5-qrcode qua CDN để bật camera quét mã trực tiếp
   trên trình duyệt (Alpine.js quản lý trạng thái quét), có ô nhập tay mã vé làm phương án
   dự phòng nếu không có camera. Sau khi quét/nhập, fetch POST tới route check-in (dùng
   CheckinService), hiển thị kết quả TO, RÕ, có màu nền (xanh lá = hợp lệ, đỏ = lỗi kèm
   đúng message theo error_code), tự động reset sau 2 giây để quét vé tiếp theo.

Giao diện /staff cần đơn giản, chữ to, ít bước thao tác — ưu tiên tốc độ hơn thẩm mỹ.
```

---

## PROMPT 19 — Hoàn thiện, Responsive, Polish

```
Rà soát lại toàn bộ giao diện Blade đã xây (Prompt 12-18):

1. Kiểm tra responsive trên 3 breakpoint: mobile (375px), tablet (768px), desktop
   (1280px+) — đặc biệt trang chọn ghế và admin (sidebar phải collapse thành menu
   hamburger trên mobile, dùng Alpine.js x-show để toggle).
2. Thêm trang 404 (resources/views/errors/404.blade.php) và trang lỗi 500 riêng theo tông
   màu app, không dùng trang lỗi mặc định của Laravel.
3. Chuẩn hoá toast/thông báo lỗi dùng 1 component Blade dùng chung (resources/views/
   components/toast.blade.php) xuyên suốt toàn app, không để mỗi trang tự làm 1 kiểu.
4. Kiểm tra định dạng tiền tệ Việt Nam (VD 120.000 ₫) và ngày giờ tiếng Việt nhất quán
   (dùng Carbon::setLocale('vi') hoặc helper riêng), rà lại toàn bộ text không bị lỗi dấu.
5. Chạy lại toàn bộ Feature Test đã viết ở Prompt 9 để đảm bảo phần refactor Service ở
   Prompt 11 không làm hỏng logic cũ.
6. Viết/](cập nhật README.md gốc: hướng dẫn cài đặt đầy đủ 1 lệnh chạy được cả web
   (composer install, npm install && npm run build, php artisan migrate --seed,
   php artisan serve), không cần chạy 2 project riêng như phương án React trước đó.

Báo lại danh sách vấn đề đã phát hiện và sửa ở bước rà soát này.
```

---

## Ghi chú khi dùng bộ prompt Frontend Blade + Alpine.js (Prompt 11-19)

- **Bắt buộc làm Prompt 11 (refactor Service) trước tiên** — nếu bỏ qua bước này, Antigravity rất dễ copy nguyên logic transaction/locking từ API Controller dán sang Web Controller, dẫn đến 2 nơi xử lý cùng 1 nghiệp vụ nhưng dễ lệch nhau khi sau này sửa 1 chỗ quên sửa chỗ kia.
- Route JSON nhẹ tạo riêng cho Alpine.js (Prompt 14) nên đặt trong `routes/web.php`, không dùng lại route trong `routes/api.php` — vì 2 hệ route dùng 2 kiểu auth khác nhau (session vs Sanctum token), trộn lẫn dễ gây lỗi 401 khó hiểu.
- Toàn bộ Phần 1 (API JSON) vẫn giữ nguyên, không cần xoá — vẫn hữu ích để demo qua Postman/Swagger hoặc nếu sau này muốn làm thêm app mobile.
- Sau mỗi prompt, tự bấm thử bằng tay trên trình duyệt (không chỉ đọc code) — đặc biệt Prompt 14, vì đây là phần dễ có lỗi tinh vi nhất (poll ghế, đếm ngược, race condition) mà đọc code khó phát hiện bằng mắt.
