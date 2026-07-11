# TÀI LIỆU ĐẶC TẢ NGHIỆP VỤ & LOGIC HỆ THỐNG
## Backend hệ thống đặt vé rạp chiếu phim

---

## 1. Tổng quan hệ thống

### 1.1 Vai trò người dùng (Roles)

| Vai trò | Quyền hạn |
|---|---|
| `customer` | Xem phim, xem lịch chiếu, đặt vé online, xem lịch sử vé của mình |
| `staff` | Tất cả quyền của customer + bán vé tại quầy, check-in vé bằng QR |
| `admin` | Tất cả quyền của staff + quản lý phim/phòng/suất chiếu, xem báo cáo doanh thu |

### 1.2 Nguyên tắc thiết kế chung

- **Kiến trúc**: RESTful API, dữ liệu trao đổi dạng JSON.
- **Xác thực**: JWT (JSON Web Token). Token gửi qua header `Authorization: Bearer <token>`.
- **Phân quyền**: Middleware kiểm tra `role` trong token trước khi vào controller (Role-based Access Control).
- **Mã trạng thái HTTP chuẩn hoá**:
  - `200 OK` — thành công, có trả dữ liệu
  - `201 Created` — tạo mới thành công
  - `400 Bad Request` — sai định dạng request
  - `401 Unauthorized` — chưa đăng nhập / token hết hạn
  - `403 Forbidden` — không đủ quyền
  - `404 Not Found` — không tìm thấy tài nguyên
  - `409 Conflict` — xung đột dữ liệu (VD: ghế đã bị người khác giữ)
  - `422 Unprocessable Entity` — dữ liệu hợp lệ về định dạng nhưng vi phạm nghiệp vụ
  - `500 Internal Server Error` — lỗi hệ thống
- **Transaction**: Mọi thao tác ghi ảnh hưởng từ 2 bảng trở lên (đặt vé, thanh toán, tạo suất chiếu) đều phải bọc trong DB transaction để đảm bảo tính toàn vẹn (ACID).

---

## 2. Sơ đồ trạng thái (State Machines)

### 2.1 Trạng thái ghế trong suất chiếu — `showtime_seats.status`

```
available ──(user giữ ghế)──▶ holding ──(thanh toán thành công)──▶ booked
    ▲                            │
    └────(hết hạn giữ ghế /──────┘
          user huỷ chủ động)
```

- `available`: ghế trống, ai cũng có thể giữ.
- `holding`: đang được giữ tạm bởi 1 người dùng, có `hold_expires_at`.
- `booked`: đã thanh toán thành công, không ai được chọn lại.

### 2.2 Trạng thái đơn đặt vé — `bookings.status`

```
pending ──(thanh toán thành công)──▶ paid
   │
   └──(hết hạn / user huỷ)──▶ expired / cancelled
```

- Vé bán tại quầy (`booking_type = counter`) bỏ qua trạng thái `pending`, tạo thẳng `paid`.

### 2.3 Trạng thái suất chiếu — `showtimes.status`

```
scheduled ──(đến giờ chiếu)──▶ ongoing ──(hết giờ chiếu)──▶ ended
                                                    (hoặc) cancelled — admin huỷ suất
```

---

## 3. Chi tiết nghiệp vụ từng module

## MODULE 1 — Quản lý danh mục (Admin)

### 3.1. Quản lý phim

**`POST /api/admin/movies`** — Thêm phim mới
- Request body: `title, description, duration_minutes, genre, age_rating, poster_url, release_date, tag_ids: [1,2,3]`
- Validate: `title` không rỗng, `duration_minutes > 0`, `age_rating` thuộc danh sách cho phép (`P, K, T13, T16, T18`); toàn bộ `tag_ids` phải tồn tại trong bảng `tags` (nếu có id không tồn tại → `422`).
- **Xử lý tag**: mở transaction → insert vào `movies` → nếu có `tag_ids`, bulk insert các dòng tương ứng vào bảng trung gian `movie_tags` (`movie_id`, `tag_id`) → commit.
- Trả về `201 Created` kèm object phim vừa tạo (kèm mảng `tags` đã gắn).
- Lỗi validate → `422` kèm danh sách field lỗi.

**`PUT /api/admin/movies/:id`** — Sửa thông tin phim
- Kiểm tra phim tồn tại (`404` nếu không).
- Không cho sửa nếu phim đang có suất chiếu **trong tương lai** mà thay đổi `duration_minutes` (vì sẽ làm sai lệch giờ kết thúc các suất đã tạo) → cảnh báo `409` hoặc yêu cầu xác nhận.
- Nếu request có `tag_ids`: xử lý kiểu **sync** (đồng bộ toàn bộ danh sách) — xoá hết các dòng cũ của phim trong `movie_tags` rồi insert lại theo danh sách mới, thay vì cộng dồn. Cách này tránh phải so sánh tag nào thêm/bớt ở tầng ứng dụng.

**`DELETE /api/admin/movies/:id`** — Xoá phim
- **Không xoá cứng (hard delete)**. Chuyển `status = 'ended'` (soft delete) để giữ lịch sử vé, doanh thu, báo cáo.
- Nếu phim còn suất chiếu `scheduled` trong tương lai → chặn xoá, trả `409 Conflict`, yêu cầu huỷ suất chiếu trước.
- Các dòng trong `movie_tags` được giữ nguyên (không cascade xoá) vì phim chỉ soft-delete, chỉ khi xoá cứng (hiếm khi xảy ra) mới cascade theo `ON DELETE CASCADE` đã khai báo ở schema.

### 3.1.1. Quản lý tag phim

**`GET /api/admin/tags`** — Danh sách toàn bộ tag
- Trả `200 OK` kèm mảng `{id, name, slug}`.

**`POST /api/admin/tags`** — Tạo tag mới
- Request: `name` (VD: "Hành động"). Backend tự sinh `slug` (không dấu, nối gạch ngang, viết thường) từ `name` nếu client không truyền.
- Validate: `name` và `slug` không được trùng (`UNIQUE` ở DB) → nếu trùng trả `422` ("Tag đã tồn tại").
- Trả `201 Created`.

**`PUT /api/admin/tags/:id`** — Sửa tên tag
- Kiểm tra tồn tại (`404`), kiểm tra trùng tên với tag khác (`422`).

**`DELETE /api/admin/tags/:id`** — Xoá tag
- Xoá cứng được vì tag chỉ là nhãn phân loại, không ảnh hưởng lịch sử vé/doanh thu.
- Nhờ `ON DELETE CASCADE` trên `movie_tags.tag_id`, xoá tag sẽ tự động gỡ tag đó khỏi mọi phim đang gắn, không cần xử lý thủ công.

**Bảng liên quan**: `tags`, `movie_tags`.

### 3.2. Quản lý phòng chiếu & sơ đồ ghế

**`POST /api/admin/rooms`** — Tạo phòng chiếu
- Request: `name, room_type` (2D/3D/IMAX), và sơ đồ ghế: số hàng, số ghế/hàng, vị trí ghế VIP/couple.
- Logic: sau khi tạo `room`, hệ thống tự sinh hàng loạt bản ghi vào bảng `seats` (VD: hàng A-J, mỗi hàng 10 ghế → 100 bản ghi `seats`).
- Trả `201 Created` kèm tổng số ghế đã tạo.

**`PUT /api/admin/rooms/:id/seats/:seat_id`** — Sửa loại ghế (đổi standard → VIP...)
- Chặn sửa nếu ghế này đang thuộc một suất chiếu **chưa diễn ra** đã có người đặt (`409 Conflict`) — tránh thay đổi giá vé của vé đã bán.

### 3.3. Tạo suất chiếu — luồng xử lý chi tiết (quan trọng nhất module này)

**`POST /api/admin/showtimes`**

Request: `movie_id, room_id, start_time, price_standard, price_vip`

**Các bước xử lý (Back-end):**

1. **Tính `end_time`** = `start_time + movie.duration_minutes + thời gian dọn phòng (VD 15 phút đệm)`.
2. **Kiểm tra trùng lịch (overlap check)** — đây là bước dễ sai nhất nếu chỉ dùng UNIQUE constraint thông thường:
   ```sql
   SELECT id FROM showtimes
   WHERE room_id = :room_id
     AND status != 'cancelled'
     AND start_time < :end_time
     AND end_time > :start_time
   FOR UPDATE;
   ```
   - Nếu có bản ghi trả về → phòng đã có suất chiếu trùng khung giờ → trả `422 Unprocessable Entity` kèm thông tin suất chiếu bị trùng.
   - Dùng `FOR UPDATE` để khoá dòng, tránh 2 admin cùng tạo suất trùng giờ trong cùng thời điểm (race condition ở cấp quản trị).
3. **Mở transaction**:
   - Insert 1 bản ghi vào `showtimes` với `status = 'scheduled'`.
   - Query toàn bộ `seats` thuộc `room_id`.
   - Bulk insert vào `showtime_seats`: mỗi ghế trong phòng → 1 bản ghi với `status = 'available'`, `showtime_id` vừa tạo.
   - Commit transaction. Nếu bất kỳ bước nào lỗi → rollback toàn bộ (không tạo suất chiếu "mồ côi" không có ghế).
4. Trả về `201 Created` kèm object suất chiếu.

**Bảng liên quan**: `showtimes`, `seats`, `showtime_seats`.

---

## MODULE 2 — Xem phim & tra cứu (Mọi đối tượng)

**`GET /api/movies?status=showing&tags=hanh-dong,dang-hot`** — Danh sách phim đang chiếu
- Trả `200 OK` kèm mảng phim, có phân trang (`page, limit`), mỗi phim kèm mảng `tags` (dùng Eager Loading `movies.with('tags')` như đã áp dụng cho `showtime_seats`, tránh N+1 query).
- Tham số `tags` (danh sách slug, phân cách bằng dấu phẩy) là **tuỳ chọn**: nếu có, chỉ trả về phim thuộc **ít nhất 1** trong các tag đó (lọc kiểu OR).
  ```sql
  SELECT DISTINCT m.*
  FROM movies m
  JOIN movie_tags mt ON mt.movie_id = m.id
  JOIN tags t ON t.id = mt.tag_id
  WHERE m.status = 'showing' AND t.slug IN ('hanh-dong', 'dang-hot');
  ```

**`GET /api/tags`** — Danh sách tag để hiển thị bộ lọc trên trang chủ/trang danh sách phim
- Trả `200 OK` kèm mảng `{id, name, slug}`, không yêu cầu đăng nhập.

**`GET /api/movies/:id/showtimes?date=YYYY-MM-DD`** — Lịch chiếu theo phim/ngày
- Chỉ trả các suất `status = 'scheduled'` và `start_time` thuộc ngày được chọn.

**`GET /api/showtimes/:id/seats`** — Sơ đồ ghế của 1 suất chiếu
- Trả về danh sách `showtime_seats` kèm thông tin ghế (`row, number, type, status`).
- **Tối ưu bắt buộc — tránh N+1 query**: dùng `Eager Loading` / `JOIN` để lấy `showtime_seats` kèm `seats` trong 1 query duy nhất, thay vì query riêng thông tin ghế cho từng dòng.
  - Laravel: `ShowtimeSeat::with('seat')->where('showtime_id', $id)->get();`
  - Node/Prisma: `include: { seat: true }`
- Ghế đang `holding` nhưng đã hết `hold_expires_at` cần được coi như `available` khi trả về (kể cả cron chưa kịp quét) — xử lý bằng điều kiện tại query hoặc lazy-check khi đọc.

---

## MODULE 3 — Giữ ghế tạm thời (Customer / Staff)

**`POST /api/bookings/hold`**

Request: `showtime_id, seat_ids: [1,2,3]`

**Luồng xử lý chi tiết:**

1. Bắt đầu transaction.
2. Lock các dòng `showtime_seats` tương ứng với `seat_ids` bằng **Pessimistic Locking**:
   ```sql
   SELECT * FROM showtime_seats
   WHERE showtime_id = :showtime_id AND seat_id IN (:seat_ids)
   FOR UPDATE;
   ```
3. Kiểm tra từng ghế:
   - Nếu ghế có `status = 'booked'` → trả `409 Conflict` ("Ghế X đã được đặt").
   - Nếu ghế có `status = 'holding'` và `hold_expires_at > now()` → `409 Conflict` ("Ghế X đang được người khác giữ").
   - Nếu ghế `available`, hoặc `holding` nhưng đã hết hạn → cho phép giữ.
4. Update các dòng hợp lệ: `status = 'holding'`, `held_by_user_id = current_user`, `hold_expires_at = now() + 5 phút`.
5. Tạo bản ghi `bookings` với `status = 'pending'`.
6. Commit transaction.
7. Trả `201 Created` kèm `booking_id`, danh sách ghế đã giữ, và thời điểm hết hạn.

**Cơ chế nhả ghế tự động (chọn 1 trong 2 cách):**

| Cách | Ưu điểm | Nhược điểm |
|---|---|---|
| Cron job quét mỗi phút | Đơn giản, dễ triển khai | Trễ tối đa ~1 phút |
| Redis TTL + keyspace notification | Chính xác theo thời gian thực | Phức tạp hơn, cần thêm Redis |

- Cron job mẫu: `UPDATE showtime_seats SET status='available', held_by_user_id=NULL WHERE status='holding' AND hold_expires_at < NOW();`, đồng thời cập nhật `bookings.status = 'expired'` cho các booking tương ứng chưa thanh toán.

**Bảng liên quan**: `showtime_seats`, `bookings`.

---

## MODULE 4 — Thanh toán & xuất vé (Customer / Staff)

### 4.1. Đặt online

**`POST /api/bookings/:id/checkout`** — Khởi tạo thanh toán
- Kiểm tra `booking.status = 'pending'` và chưa hết hạn giữ ghế.
- Gọi cổng thanh toán giả lập, trả về `payment_url` hoặc `client_secret`.

**`POST /api/payments/callback`** — Webhook nhận kết quả từ cổng thanh toán

**Luồng xử lý (bắt buộc dùng Database Transaction):**

```
BEGIN TRANSACTION
  1. Kiểm tra chữ ký (signature) của callback — chống giả mạo request.
  2. Tìm payment theo transaction_id / idempotency_key.
  3. Nếu payment.status đã là 'success' → trả về 200 OK ngay,
     KHÔNG xử lý lại (đảm bảo Idempotent).
  4. Cập nhật payment.status = 'success', paid_at = now().
  5. Cập nhật booking.status = 'paid'.
  6. Cập nhật toàn bộ showtime_seats liên quan: status = 'booked'.
  7. Sinh mã QR độc nhất (xem mục 4.3) và lưu vào booking.qr_code_data.
COMMIT
```
- Nếu bất kỳ bước 4-7 lỗi → **Rollback toàn bộ**: booking vẫn `pending`, ghế vẫn `holding`, payment vẫn `pending` — không để xảy ra tình trạng "đã trừ tiền nhưng không có vé".
- Trả `200 OK` kèm thông tin vé + QR.

### 4.2. Bán vé tại quầy

**`POST /api/staff/bookings/counter`**

- Request: `showtime_id, seat_ids, payment_method: 'cash'`
- Khác với luồng online: **bỏ qua bước giữ ghế 5 phút**, thực hiện luôn trong 1 transaction:
  1. Lock ghế (`FOR UPDATE`) như module 3, kiểm tra ghế còn trống.
  2. Update ghế → `booked` trực tiếp (không qua `holding`).
  3. Tạo `booking` với `status = 'paid'`, `booking_type = 'counter'`, `staff_id = current_staff_id`.
  4. Tạo `payment` với `payment_method = 'cash'`, `status = 'success'`.
  5. Sinh vé (có thể không cần QR nếu in giấy tại chỗ, hoặc vẫn sinh QR để đối soát).
- Trả `201 Created`, dữ liệu để in vé (tên phim, giờ chiếu, số ghế, mã vé).

### 4.3. Cơ chế Idempotent API — chặn thanh toán trùng

- Client sinh 1 `Idempotency-Key` (UUID) duy nhất mỗi lần bấm nút thanh toán, gửi qua header:
  ```
  Idempotency-Key: 8f14e45f-ceea-467e-91a2-...
  ```
- Backend kiểm tra: nếu `idempotency_key` đã tồn tại trong bảng `payments` → trả về **kết quả của lần xử lý trước đó**, không tạo giao dịch mới, không trừ tiền lần 2.
- Ràng buộc `UNIQUE(idempotency_key)` ở tầng database là lớp bảo vệ cuối cùng nếu tầng ứng dụng có sai sót.

### 4.4. Sinh mã QR

- Nội dung QR nên là 1 chuỗi gồm: `booking_id + booking_code + chữ ký HMAC-SHA256` (dùng secret key phía server), **không** để QR chỉ chứa ID trần — dễ bị đoán/giả mạo.
- Ví dụ payload trước khi encode: `{"booking_id": 123, "code": "BK20260703XYZ", "sig": "a1b2c3..."}`.

**Bảng liên quan**: `bookings`, `booking_seats`, `payments`, `showtime_seats`.

---

## MODULE 5 — Quét QR Check-in (Staff)

**`POST /api/staff/checkin`**

Request: `qr_data` (chuỗi quét được từ máy quét)

**Luồng xử lý:**

1. Giải mã `qr_data`, xác minh chữ ký HMAC — nếu sai → `400 Bad Request` ("Mã vé không hợp lệ").
2. Tìm `booking` theo `booking_id` giải mã được. Nếu không tồn tại → `404 Not Found`.
3. Kiểm tra các điều kiện hợp lệ theo thứ tự:
   - `booking.status != 'paid'` → `422` ("Vé chưa thanh toán hoặc đã huỷ").
   - `booking.is_checked_in == true` → `422` ("Vé đã được check-in trước đó lúc `checked_in_at`") — **chặn quét lại lần 2**.
   - `showtime.status != 'ongoing'` (chưa đến giờ chiếu hoặc đã kết thúc) → `422` ("Chưa đến giờ chiếu" / "Suất chiếu đã kết thúc").
   - Đối chiếu phòng chiếu ghi trên vé với phòng nhân viên đang quét (nếu hệ thống phân quyền theo phòng) → sai phòng trả `422`.
4. Nếu hợp lệ: cập nhật `is_checked_in = true`, `checked_in_at = now()`.
5. Trả `200 OK` kèm thông tin khách (tên phim, số ghế, giờ chiếu) để nhân viên đối chiếu bằng mắt.

**Bảng liên quan**: `bookings`, `showtimes`.

---

## MODULE 6 — Báo cáo & thống kê (Admin)

**`GET /api/admin/reports/revenue?from=&to=&group_by=day|month|movie`**

- Dùng SQL aggregate thay vì vòng lặp ứng dụng để tối ưu hiệu năng:
  ```sql
  SELECT DATE(p.paid_at) AS day, SUM(p.amount) AS revenue, COUNT(*) AS total_orders
  FROM payments p
  WHERE p.status = 'success' AND p.paid_at BETWEEN :from AND :to
  GROUP BY DATE(p.paid_at)
  ORDER BY day;
  ```

**`GET /api/admin/reports/occupancy?showtime_id=`** — Tỷ lệ lấp đầy ghế

  ```sql
  SELECT
    s.id AS showtime_id,
    COUNT(ss.id) AS total_seats,
    SUM(CASE WHEN ss.status = 'booked' THEN 1 ELSE 0 END) AS booked_seats,
    ROUND(SUM(CASE WHEN ss.status = 'booked' THEN 1 ELSE 0 END) * 100.0 / COUNT(ss.id), 2) AS occupancy_rate
  FROM showtimes s
  JOIN showtime_seats ss ON ss.showtime_id = s.id
  WHERE s.id = :showtime_id
  GROUP BY s.id;
  ```

**`GET /api/admin/reports/revenue-by-movie`**

  ```sql
  SELECT m.title, SUM(p.amount) AS revenue, COUNT(DISTINCT b.id) AS total_bookings
  FROM payments p
  JOIN bookings b ON b.id = p.booking_id
  JOIN showtimes st ON st.id = b.showtime_id
  JOIN movies m ON m.id = st.movie_id
  WHERE p.status = 'success'
  GROUP BY m.id
  ORDER BY revenue DESC;
  ```

Tất cả các API báo cáo trả `200 OK` kèm mảng dữ liệu đã tổng hợp sẵn, sẵn sàng để frontend vẽ biểu đồ (Chart.js/Recharts) mà không cần xử lý thêm.

---

## 4. Bảng tổng hợp toàn bộ API Endpoints

| Method | Endpoint | Vai trò | Mô tả |
|---|---|---|---|
| POST | `/api/admin/movies` | admin | Thêm phim (kèm gắn tag) |
| PUT | `/api/admin/movies/:id` | admin | Sửa phim (kèm đồng bộ tag) |
| DELETE | `/api/admin/movies/:id` | admin | Xoá phim (soft delete) |
| GET | `/api/admin/tags` | admin | Danh sách tag |
| POST | `/api/admin/tags` | admin | Tạo tag mới |
| PUT | `/api/admin/tags/:id` | admin | Sửa tag |
| DELETE | `/api/admin/tags/:id` | admin | Xoá tag |
| POST | `/api/admin/rooms` | admin | Tạo phòng + sơ đồ ghế |
| PUT | `/api/admin/rooms/:id/seats/:seat_id` | admin | Sửa loại ghế |
| POST | `/api/admin/showtimes` | admin | Tạo suất chiếu |
| GET | `/api/movies` | public | Danh sách phim (lọc theo tag) |
| GET | `/api/tags` | public | Danh sách tag để lọc |
| GET | `/api/movies/:id/showtimes` | public | Lịch chiếu theo phim |
| GET | `/api/showtimes/:id/seats` | public | Sơ đồ ghế của suất chiếu |
| POST | `/api/bookings/hold` | customer/staff | Giữ ghế tạm thời |
| POST | `/api/bookings/:id/checkout` | customer | Khởi tạo thanh toán online |
| POST | `/api/payments/callback` | webhook | Xử lý kết quả thanh toán |
| POST | `/api/staff/bookings/counter` | staff | Bán vé tại quầy |
| POST | `/api/staff/checkin` | staff | Check-in vé bằng QR |
| GET | `/api/admin/reports/revenue` | admin | Báo cáo doanh thu |
| GET | `/api/admin/reports/occupancy` | admin | Tỷ lệ lấp đầy ghế |
| GET | `/api/admin/reports/revenue-by-movie` | admin | Doanh thu theo phim |

---

## 5. Bảng mã lỗi nghiệp vụ chuẩn hoá

| Mã lỗi (code) | HTTP status | Ý nghĩa |
|---|---|---|
| `SEAT_ALREADY_BOOKED` | 409 | Ghế đã có người đặt |
| `SEAT_ALREADY_HELD` | 409 | Ghế đang được người khác giữ |
| `SHOWTIME_OVERLAP` | 422 | Suất chiếu trùng phòng/giờ |
| `HOLD_EXPIRED` | 422 | Phiên giữ ghế đã hết hạn |
| `PAYMENT_ALREADY_PROCESSED` | 200 | Idempotent — trả kết quả cũ, không xử lý lại |
| `TICKET_ALREADY_CHECKED_IN` | 422 | Vé đã check-in trước đó |
| `INVALID_QR_SIGNATURE` | 400 | Mã QR không hợp lệ / giả mạo |
| `SHOWTIME_NOT_ONGOING` | 422 | Chưa đến giờ chiếu hoặc đã kết thúc |
| `VALIDATION_ERROR` | 422 | Dữ liệu đầu vào vi phạm quy tắc nghiệp vụ |

---

## 6. Bảo mật & Middleware

- **`auth.middleware`**: kiểm tra JWT hợp lệ, gắn `req.user` cho các bước sau.
- **`role.middleware(['admin'])`**: chặn truy cập nếu `req.user.role` không nằm trong danh sách cho phép — dùng cho toàn bộ route `/api/admin/*`.
- **Rate limiting** trên `/api/bookings/hold` và `/api/payments/callback` để chống spam giữ ghế ảo hoặc tấn công callback giả.
- **Webhook signature verification** bắt buộc ở `/api/payments/callback` — không tin tưởng request chỉ vì đến đúng endpoint.

---

## 7. Ghi chú triển khai với Laragon

- Laragon dựng sẵn MySQL + phpMyAdmin/HeidiSQL — import trực tiếp file `schema.sql` bằng 1 trong 2 cách:
  - **phpMyAdmin**: `http://localhost/phpmyadmin` → tạo database `cinema_booking` (nếu file chưa tự tạo) → tab **Import** → chọn `schema.sql`.
  - **HeidiSQL** (đi kèm Laragon, vào bằng nút "Database" trên thanh menu Laragon) → mở kết nối → **File > Load SQL file** → chọn `schema.sql` → chạy (F9).
- Nếu dùng Laravel: sau khi có schema chạy thử bằng tay, nên **generate lại migration** từ schema này (hoặc viết migration tương ứng từng bảng) để version-control được cấu trúc DB, thay vì chỉ giữ 1 file `.sql` tĩnh — migration cũng giúp Antigravity/Cursor hiểu đúng thứ tự phụ thuộc bảng khi sinh code.
- Bảng `movie_tags` là quan hệ N-N chuẩn Laravel (`belongsToMany`) — nếu dùng Eloquent, khai báo quan hệ `Movie::tags()` và `Tag::movies()` để tận dụng `attach()/sync()/detach()` thay vì tự viết SQL insert/delete cho bảng trung gian.

---

*Tài liệu này bám sát và mở rộng chi tiết từ bảng đặc tả nghiệp vụ 6 module gốc, có thể dùng trực tiếp làm phần "Phân tích thiết kế hệ thống" trong báo cáo đồ án.*
