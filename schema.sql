-- ============================================================
-- SCHEMA: Hệ thống đặt vé rạp chiếu phim
-- Dialect: MySQL 8.0+ (InnoDB, hỗ trợ FK + transaction)
-- Ghi chú: nếu dùng PostgreSQL, xem phần "PostgreSQL notes" cuối file
-- ============================================================

CREATE DATABASE IF NOT EXISTS cinema_booking
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE cinema_booking;

-- ------------------------------------------------------------
-- 1. USERS
-- ------------------------------------------------------------
CREATE TABLE users (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name       VARCHAR(150)    NOT NULL,
    email           VARCHAR(150)    NOT NULL,
    phone           VARCHAR(20)     NULL,
    password_hash   VARCHAR(255)    NOT NULL,
    role            ENUM('customer', 'staff', 'admin') NOT NULL DEFAULT 'customer',
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_users_email (email),
    INDEX idx_users_role (role)
) ENGINE = InnoDB;

-- ------------------------------------------------------------
-- 2. MOVIES
-- ------------------------------------------------------------
CREATE TABLE movies (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(255)    NOT NULL,
    description     TEXT            NULL,
    duration_minutes INT UNSIGNED   NOT NULL,
    genre           VARCHAR(100)    NULL,
    age_rating      ENUM('P', 'K', 'T13', 'T16', 'T18') NOT NULL DEFAULT 'P',
    poster_url      VARCHAR(500)    NULL,
    status          ENUM('showing', 'coming_soon', 'ended') NOT NULL DEFAULT 'coming_soon',
    release_date    DATE            NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_movies_status (status),
    INDEX idx_movies_title (title)
) ENGINE = InnoDB;

-- ------------------------------------------------------------
-- 2.1 TAGS (Nhãn/thể loại gắn cho phim — VD: Hành động, Đề cử Oscar, Hoạt hình...)
-- ------------------------------------------------------------
CREATE TABLE tags (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100)    NOT NULL,
    slug            VARCHAR(120)    NOT NULL,   -- dùng cho URL/filter, VD: 'hanh-dong'
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_tags_name (name),
    UNIQUE KEY uq_tags_slug (slug)
) ENGINE = InnoDB;

-- ------------------------------------------------------------
-- 2.2 MOVIE_TAGS (Bảng trung gian N-N giữa movies và tags)
-- ------------------------------------------------------------
CREATE TABLE movie_tags (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    movie_id        BIGINT UNSIGNED NOT NULL,
    tag_id          BIGINT UNSIGNED NOT NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_movie_tags_movie FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    CONSTRAINT fk_movie_tags_tag   FOREIGN KEY (tag_id)   REFERENCES tags(id)   ON DELETE CASCADE,

    -- Chặn gắn trùng 1 tag 2 lần vào cùng 1 phim
    UNIQUE KEY uq_movie_tag (movie_id, tag_id),
    INDEX idx_movie_tags_movie (movie_id),
    INDEX idx_movie_tags_tag (tag_id)
) ENGINE = InnoDB;

-- ------------------------------------------------------------
-- 3. ROOMS (Phòng chiếu)
-- ------------------------------------------------------------
CREATE TABLE rooms (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100)    NOT NULL,
    room_type       ENUM('2D', '3D', 'IMAX') NOT NULL DEFAULT '2D',
    total_seats     INT UNSIGNED    NOT NULL DEFAULT 0,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_rooms_name (name)
) ENGINE = InnoDB;

-- ------------------------------------------------------------
-- 4. SEATS (Sơ đồ ghế cố định của từng phòng)
-- ------------------------------------------------------------
CREATE TABLE seats (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id         BIGINT UNSIGNED NOT NULL,
    seat_row        VARCHAR(5)      NOT NULL,   -- VD: A, B, C...
    seat_number     INT UNSIGNED    NOT NULL,   -- VD: 1, 2, 3...
    seat_type       ENUM('standard', 'vip', 'couple') NOT NULL DEFAULT 'standard',
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_seats_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    UNIQUE KEY uq_seats_room_position (room_id, seat_row, seat_number),
    INDEX idx_seats_room (room_id)
) ENGINE = InnoDB;

-- ------------------------------------------------------------
-- 5. SHOWTIMES (Suất chiếu)
-- ------------------------------------------------------------
CREATE TABLE showtimes (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    movie_id        BIGINT UNSIGNED NOT NULL,
    room_id         BIGINT UNSIGNED NOT NULL,
    start_time      DATETIME        NOT NULL,
    end_time        DATETIME        NOT NULL,
    price_standard  DECIMAL(10, 2)  NOT NULL,
    price_vip       DECIMAL(10, 2)  NOT NULL,
    status          ENUM('scheduled', 'ongoing', 'ended', 'cancelled') NOT NULL DEFAULT 'scheduled',
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_showtimes_movie FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE RESTRICT,
    CONSTRAINT fk_showtimes_room  FOREIGN KEY (room_id)  REFERENCES rooms(id)  ON DELETE RESTRICT,

    -- Không chặn tuyệt đối trùng giờ ở tầng DB (MySQL không có EXCLUDE constraint),
    -- BẮT BUỘC kiểm tra overlap ở tầng ứng dụng (xem tài liệu nghiệp vụ, mục Module 1.3).
    INDEX idx_showtimes_room_time (room_id, start_time, end_time),
    INDEX idx_showtimes_movie (movie_id),
    INDEX idx_showtimes_status (status)
) ENGINE = InnoDB;

-- ------------------------------------------------------------
-- 6. SHOWTIME_SEATS (Trạng thái ghế theo từng suất chiếu)
-- ------------------------------------------------------------
CREATE TABLE showtime_seats (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    showtime_id     BIGINT UNSIGNED NOT NULL,
    seat_id         BIGINT UNSIGNED NOT NULL,
    status          ENUM('available', 'holding', 'booked') NOT NULL DEFAULT 'available',
    held_by_user_id BIGINT UNSIGNED NULL,
    hold_expires_at DATETIME        NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_ss_showtime FOREIGN KEY (showtime_id) REFERENCES showtimes(id) ON DELETE CASCADE,
    CONSTRAINT fk_ss_seat     FOREIGN KEY (seat_id)     REFERENCES seats(id)     ON DELETE CASCADE,
    CONSTRAINT fk_ss_held_by  FOREIGN KEY (held_by_user_id) REFERENCES users(id) ON DELETE SET NULL,

    UNIQUE KEY uq_showtime_seat (showtime_id, seat_id),
    -- Index phục vụ cron job quét ghế giữ quá hạn (Module 3)
    INDEX idx_ss_status_expire (status, hold_expires_at),
    INDEX idx_ss_showtime (showtime_id)
) ENGINE = InnoDB;

-- ------------------------------------------------------------
-- 7. BOOKINGS (Đơn đặt vé)
-- ------------------------------------------------------------
CREATE TABLE bookings (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NULL,        -- NULL nếu khách vãng lai tại quầy
    showtime_id     BIGINT UNSIGNED NOT NULL,
    booking_code    VARCHAR(30)     NOT NULL,     -- VD: BK20260703XYZ
    total_amount    DECIMAL(10, 2)  NOT NULL,
    status          ENUM('pending', 'paid', 'expired', 'cancelled') NOT NULL DEFAULT 'pending',
    booking_type    ENUM('online', 'counter') NOT NULL DEFAULT 'online',
    qr_code_data    TEXT            NULL,
    is_checked_in   BOOLEAN         NOT NULL DEFAULT FALSE,
    checked_in_at   DATETIME        NULL,
    staff_id        BIGINT UNSIGNED NULL,         -- nhân viên tạo đơn tại quầy
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_bookings_user     FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE SET NULL,
    CONSTRAINT fk_bookings_showtime FOREIGN KEY (showtime_id) REFERENCES showtimes(id) ON DELETE RESTRICT,
    CONSTRAINT fk_bookings_staff    FOREIGN KEY (staff_id)    REFERENCES users(id)     ON DELETE SET NULL,

    UNIQUE KEY uq_bookings_code (booking_code),
    INDEX idx_bookings_user (user_id),
    INDEX idx_bookings_showtime (showtime_id),
    INDEX idx_bookings_status (status)
) ENGINE = InnoDB;

-- ------------------------------------------------------------
-- 8. BOOKING_SEATS (Chi tiết ghế trong 1 đơn đặt vé)
-- ------------------------------------------------------------
CREATE TABLE booking_seats (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id          BIGINT UNSIGNED NOT NULL,
    showtime_seat_id    BIGINT UNSIGNED NOT NULL,
    price               DECIMAL(10, 2)  NOT NULL,
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_bs_booking       FOREIGN KEY (booking_id)       REFERENCES bookings(id)       ON DELETE CASCADE,
    CONSTRAINT fk_bs_showtime_seat FOREIGN KEY (showtime_seat_id) REFERENCES showtime_seats(id) ON DELETE RESTRICT,

    -- Đảm bảo 1 ghế/suất chiếu chỉ thuộc về đúng 1 đơn đặt vé
    UNIQUE KEY uq_bs_showtime_seat (showtime_seat_id),
    INDEX idx_bs_booking (booking_id)
) ENGINE = InnoDB;

-- ------------------------------------------------------------
-- 9. PAYMENTS
-- ------------------------------------------------------------
CREATE TABLE payments (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id      BIGINT UNSIGNED NOT NULL,
    amount          DECIMAL(10, 2)  NOT NULL,
    payment_method  ENUM('online_gateway', 'cash') NOT NULL,
    transaction_id  VARCHAR(100)    NULL,          -- mã giao dịch từ cổng thanh toán
    status          ENUM('pending', 'success', 'failed') NOT NULL DEFAULT 'pending',
    idempotency_key VARCHAR(100)    NOT NULL,       -- chống thanh toán trùng (Module 4.3)
    paid_at         DATETIME        NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_payments_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,

    UNIQUE KEY uq_payments_transaction (transaction_id),
    UNIQUE KEY uq_payments_idempotency (idempotency_key),
    INDEX idx_payments_booking (booking_id),
    INDEX idx_payments_status (status)
) ENGINE = InnoDB;

-- ------------------------------------------------------------
-- 10. (Tuỳ chọn) AUDIT LOG — theo dõi thay đổi trạng thái đơn đặt vé
-- Gợi ý thêm nếu giảng viên yêu cầu truy vết lịch sử xử lý
-- ------------------------------------------------------------
CREATE TABLE booking_status_logs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id      BIGINT UNSIGNED NOT NULL,
    old_status      VARCHAR(30)     NULL,
    new_status      VARCHAR(30)     NOT NULL,
    changed_by      BIGINT UNSIGNED NULL,   -- user_id thực hiện thay đổi, NULL nếu do hệ thống (cron)
    note            VARCHAR(255)    NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_bsl_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    CONSTRAINT fk_bsl_user    FOREIGN KEY (changed_by) REFERENCES users(id)    ON DELETE SET NULL,

    INDEX idx_bsl_booking (booking_id)
) ENGINE = InnoDB;

-- ============================================================
-- DỮ LIỆU MẪU (Seed data) — dùng để test nhanh
-- ============================================================

INSERT INTO users (full_name, email, phone, password_hash, role) VALUES
('Admin Hệ Thống', 'admin@cinema.vn', '0900000000', '$2y$10$examplehashadmin', 'admin'),
('Nguyễn Văn Nhân Viên', 'staff@cinema.vn', '0900000001', '$2y$10$examplehashstaff', 'staff'),
('Trần Thị Khách Hàng', 'customer@cinema.vn', '0900000002', '$2y$10$examplehashcustomer', 'customer');

INSERT INTO movies (title, description, duration_minutes, genre, age_rating, status, release_date) VALUES
('Hành Trình Vũ Trụ', 'Phim khoa học viễn tưởng về hành trình khám phá không gian.', 120, 'Khoa học viễn tưởng', 'T13', 'showing', '2026-06-01'),
('Miền Ký Ức', 'Phim tâm lý tình cảm.', 105, 'Tâm lý', 'P', 'showing', '2026-06-15');

INSERT INTO tags (name, slug) VALUES
('Hành động', 'hanh-dong'),
('Khoa học viễn tưởng', 'khoa-hoc-vien-tuong'),
('Tâm lý', 'tam-ly'),
('Đề cử Oscar', 'de-cu-oscar'),
('Hoạt hình', 'hoat-hinh'),
('Đang hot', 'dang-hot');

-- Gắn tag cho phim (movie_id 1 = Hành Trình Vũ Trụ, movie_id 2 = Miền Ký Ức)
INSERT INTO movie_tags (movie_id, tag_id) VALUES
(1, 2),  -- Hành Trình Vũ Trụ -> Khoa học viễn tưởng
(1, 1),  -- Hành Trình Vũ Trụ -> Hành động
(1, 6),  -- Hành Trình Vũ Trụ -> Đang hot
(2, 3),  -- Miền Ký Ức -> Tâm lý
(2, 4);  -- Miền Ký Ức -> Đề cử Oscar

INSERT INTO rooms (name, room_type, total_seats) VALUES
('Phòng 1', '2D', 60),
('Phòng 2', 'IMAX', 80);

-- Sinh ghế mẫu cho Phòng 1: 6 hàng (A-F) x 10 ghế
INSERT INTO seats (room_id, seat_row, seat_number, seat_type)
SELECT 1, r.row_letter, n.num,
       CASE WHEN r.row_letter IN ('E', 'F') THEN 'vip' ELSE 'standard' END
FROM (SELECT 'A' AS row_letter UNION SELECT 'B' UNION SELECT 'C'
      UNION SELECT 'D' UNION SELECT 'E' UNION SELECT 'F') r
CROSS JOIN (SELECT 1 AS num UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
            UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) n;

-- ============================================================
-- POSTGRESQL NOTES (nếu bạn dùng Postgres thay vì MySQL)
-- ============================================================
-- 1. Thay AUTO_INCREMENT -> GENERATED ALWAYS AS IDENTITY hoặc SERIAL
-- 2. Thay ENUM(...) -> tạo CREATE TYPE ... AS ENUM (...) riêng, hoặc dùng VARCHAR + CHECK constraint
-- 3. Thay DATETIME -> TIMESTAMP
-- 4. Thay ON UPDATE CURRENT_TIMESTAMP -> cần trigger riêng để tự cập nhật updated_at
-- 5. Có thể chặn TRÙNG LỊCH CHIẾU ngay ở tầng DB bằng:
--      ALTER TABLE showtimes ADD COLUMN time_range tsrange
--        GENERATED ALWAYS AS (tsrange(start_time, end_time)) STORED;
--      ALTER TABLE showtimes ADD CONSTRAINT no_overlap
--        EXCLUDE USING gist (room_id WITH =, time_range WITH &&)
--        WHERE (status <> 'cancelled');
--    (yêu cầu extension btree_gist: CREATE EXTENSION IF NOT EXISTS btree_gist;)
