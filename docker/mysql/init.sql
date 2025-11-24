-- 문자셋/콜레이션 먼저 고정
SET NAMES utf8mb4;
SET SESSION collation_connection = 'utf8mb4_0900_ai_ci';

CREATE DATABASE IF NOT EXISTS backend
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_0900_ai_ci;

USE backend;

-- 테스트 코드
CREATE TABLE IF NOT EXISTS student (
    std_id CHAR(7) PRIMARY KEY,                       -- 학번 (문자형)
    email VARCHAR(100) NOT NULL UNIQUE,               -- 이메일(ID)
    password VARCHAR(255) NOT NULL,                   -- 비밀번호 (해싱 저장)
    name VARCHAR(50) NOT NULL,                        -- 이름
    birth DATE NOT NULL,                              -- 생년월일
    gender ENUM('M', 'F') NOT NULL,                   -- 성별(M: 남성, F: 여성)
    admission_year YEAR NOT NULL,                     -- 입학년도 (예: 2023)
    current_year TINYINT UNSIGNED NOT NULL,           -- 현재 학년 (1~4)
    status ENUM('재학', '휴학', '졸업', '제적', '자퇴') NOT NULL DEFAULT '재학',  -- 학적 상태
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

-- mock 데이터 삽입
INSERT INTO student (std_id, email, password, name, birth, gender, admission_year, current_year, status) VALUES
(2023001,'kim@example.com',SHA2('password1',256),'배찬승','2003-05-14','M',2023,2,'재학'),
(2023002,'lee@example.com',SHA2('password2',256),'김영욱','2004-01-22','F',2023,2,'재학'),
(2023003,'park@example.com',SHA2('password3',256),'이재현','2002-11-03','F',2022,3,'휴학');


-- 본 코드
CREATE TABLE IF NOT EXISTS Users (
    user_id INT AUTO_INCREMENT,
    account VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    role ENUM ('client', 'designer', 'manager') NOT NULL DEFAULT 'client',
    gender VARCHAR(100) NOT NULL,
    phone VARCHAR(30),
    birth DATE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id)
);

-- 💡 FK 충족을 위해 Users 샘플(특정 ID 3,5) 삽입
INSERT INTO Users (account, password, user_name, role, gender, phone, birth)
VALUES
('designer1',SHA2('1111',256), '디자이너1', 'designer', 'M', '010-3333-3333', '1995-03-03'),
('designer2',SHA2('2222',256), '디자이너2', 'designer', 'F', '010-5555-5555', '1993-05-05');




CREATE TABLE IF NOT EXISTS Salon (
    image VARCHAR(255) NOT NULL COMMENT 'URL 배열 (캐러셀)',
    image_key VARCHAR(255) NOT NULL,
    introduction TEXT NOT NULL,
    information JSON NOT NULL COMMENT 'Address, OpeningHour, Holiday, Phone',
    map VARCHAR(255) NOT NULL,
    traffic JSON NOT NULL COMMENT 'Bus, Parking, Directions',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
);

-- 살롱 데이터
INSERT INTO Salon (image, image_key, introduction, information, map, traffic) VALUES (
    "https://pub-08298820ca884cc49d536c1b0ce8b7c4.r2.dev/salon/1.jpg",
    "salon/1.jpg",
    "저희 살롱은 고객 개개인의 스타일을 존중하며 맞춤형 서비스를 제공합니다.",
    JSON_OBJECT(
        "address", "대구광역시 북구 복현로 35",
        "opening_hour", "10:00 - 19:00",
        "holiday", "일요일",
        "phone", "010-4819-7975"
    ),
    "https://pub-08298820ca884cc49d536c1b0ce8b7c4.r2.dev/salon/1.png",
    JSON_OBJECT(
        "bus", "706, 719, 730, 북구2",
        "parking", "영진전문대 정문 주차장 이용 가능 (방문객 30분 무료)",
        "directions", "대구 1호선 칠곡경대병원역 3번 출구 기준 도보 10분"
    )
);

CREATE TABLE IF NOT EXISTS Service (
    service_id INT AUTO_INCREMENT,
    service_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration_min INT NOT NULL ,
    PRIMARY KEY (service_id)
);

INSERT INTO Service (service_name, price, duration_min) VALUES
    -- 기본 커트
    ('MEN CUT', 12000, 50),
    ('WOMEN CUT', 15000, 60),
    ('DRY CUT', 15000, 60),
    ('KIDS CUT', 8000, 40),

    -- 펌 (PERM)
    ('BASIC PERM', 50000, 90),
    ('DIGITAL PERM', 80000, 120),
    ('SETTING PERM', 90000, 120),
    ('VOLUME PERM', 70000, 100),
    ('DOWN PERM', 30000, 40),

    -- 염색 (COLOR)
    ('COLOR BASIC', 50000, 90),
    ('COLOR FULL', 70000, 100),
    ('BLEACHING', 90000, 120),
    ('RETOUCH COLOR', 40000, 70),
    ('GRAY COVER COLOR', 50000, 80),

    -- 클리닉 (CARE / CLINIC)
    ('KERATIN TREATMENT', 60000, 60),
    ('PROTEIN CARE', 40000, 50),
    ('MOISTURE CARE', 35000, 45),
    ('SCALP CARE', 30000, 40),

    -- 스타일링
    ('BLOW DRY', 15000, 30),
    ('IRON STYLING', 20000, 40),
    ('UP STYLE', 30000, 60)
;

CREATE TABLE IF NOT EXISTS HairStyle (
    hair_id INT AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    image VARCHAR(255) NOT NULL,
    image_key VARCHAR(255) NOT NULL,
    description TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (hair_id)
);

CREATE TABLE IF NOT EXISTS Designer (
    designer_id INT AUTO_INCREMENT,
    user_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    image_key VARCHAR(255) NOT NULL,
    experience INT NOT NULL,
    good_at VARCHAR(255) NOT NULL,
    personality VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (designer_id),
    CONSTRAINT uq_designer_user UNIQUE (user_id),
    CONSTRAINT fk_designer_user FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS News (
    news_id INT AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    file VARCHAR(255),
    file_key VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (news_id)
);

INSERT INTO News (title, content) 
        VALUES ('haechan', 'hi'),
    ('mark','hello'),
    ('jisung', 'hi'),
    ('haechan', 'hello'),
    ('mark', 'hi'),
    ('jisung', 'hello'),
    ('haechan', 'hi');

CREATE TABLE IF NOT EXISTS Reservation (
    reservation_id INT AUTO_INCREMENT,
    client_id INT NOT NULL,
    designer_id INT NOT NULL,
    requirement TEXT,
    day DATE NOT NULL,
    start_at TIME NOT NULL,
    end_at TIME ,
    status ENUM('pending', 'confirmed', 'checked_in', 'completed', 'cancelled', 'no_show') NOT NULL DEFAULT 'pending',
    cancelled_at DATETIME,
    cancel_reason TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (reservation_id),
    CONSTRAINT FK_reservation_client 
        FOREIGN KEY (client_id) REFERENCES Users(user_id)
        ON DELETE CASCADE,
    CONSTRAINT FK_reservation_designer 
        FOREIGN KEY (designer_id) REFERENCES Users(user_id)  
);

-- 예약 내목
CREATE TABLE IF NOT EXISTS ReservationService (
    reservation_id INT NOT NULL,  
    service_id     INT NOT NULL,
    qty            INT NOT NULL DEFAULT 1,
    unit_price     DECIMAL(10,2) NOT NULL,
    PRIMARY KEY(reservation_id, service_id),
    CONSTRAINT FK_ReservationService_Reservation 
    FOREIGN KEY (reservation_id) REFERENCES Reservation(reservation_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES Service(service_id)
        ON UPDATE CASCADE ON DELETE RESTRICT    
);

INSERT INTO Service (service_name, price, duration_min) VALUES
        ('CUT',40000, 60),
        ('PERM', 80000, 100),
        ('COLOR', 60000, 80)
;


CREATE TABLE IF NOT EXISTS TimeOff (
    to_id INT AUTO_INCREMENT,
    user_id INT NOT NULL,
    start_at DATE NOT NULL,
    end_at DATE NOT NULL,
    PRIMARY KEY (to_id),
    CONSTRAINT fk_timeoff_designer FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

-- event scheduler ON--
SET GLOBAL event_scheduler = ON;

-- 과거의 스케즐 삭제 --
CREATE EVENT IF NOT EXISTS delete_old_timeoff
ON SCHEDULE EVERY 1 DAY
DO
  DELETE FROM TimeOff
  WHERE end_at < CURDATE();

