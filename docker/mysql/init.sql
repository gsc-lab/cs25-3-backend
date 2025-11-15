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
('designer1', 1111, '디자이너1', 'designer', 'M', '010-3333-3333', '1995-03-03'),
('designer2', 2222, '디자이너2', 'designer', 'F', '010-5555-5555', '1993-05-05');




CREATE TABLE IF NOT EXISTS Salon (
    image JSON NOT NULL COMMENT 'URL 배열 (캐러셀)',
    introduction TEXT NOT NULL,
    information JSON NOT NULL COMMENT 'Address, OpeningHour, Holiday, Phone',
    map VARCHAR(255) NOT NULL,
    traffic JSON NOT NULL COMMENT 'Bus, Parking, Directions',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO Salon (image, introduction, information, map, traffic)
                     VALUES ('["image1.jpg", "image2"]', 'Make your dreams come true', '["We always keep up with the latest trends and propose styles that will bring out your individuality.","Our experienced stylists will carefully listen to your concerns and wishes. Let/''s work together to create your ideal hairstyle."]',
                            'Degu', '["bus", "train", "car"]');


CREATE TABLE IF NOT EXISTS Service (
    service_id INT AUTO_INCREMENT,
    service_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration_min INT NOT NULL DEFAULT 60,
    PRIMARY KEY (service_id)
);

INSERT INTO Service (service_name, price, duration_min) VALUES
    ('CUT', 10000, 60),
    ('PERM', 60000, 80),
    ('COROL', 50000, 60)
;

CREATE TABLE IF NOT EXISTS HairStyle (
    hair_id INT AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    image VARCHAR(255) NOT NULL,
    description TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (hair_id)
);

CREATE TABLE IF NOT EXISTS Designer (
    designer_id INT AUTO_INCREMENT,
    user_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
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

INSERT INTO Designer 
    (user_id, image, experience, good_at, personality, message)
    VALUES (1, "designer1", 3, '레이어드컷', '활발하다', '예쁜 공간에서 이미지와 1: 1 맞춤 상담을 통해 진심을 담아 디자인을 선물해드리겠습니다:)'),
    (2, "designer2", 10, '내추럴 스타일', '조용하다', '최손을 다해서 고객님에 잘 올리는 스타일을 제공합니다.');



CREATE TABLE IF NOT EXISTS News (
    news_id INT AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    file VARCHAR(255),
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
    service VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
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

