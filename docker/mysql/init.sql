CREATE DATABASE IF NOT EXISTS backend2;

USE backend2;

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

CREATE TABLE IF NOT EXISTS Salon (
    image JSON NOT NULL COMMENT 'URL 배열 (캐러셀)',
    introduction TEXT NOT NULL,
    information JSON NOT NULL COMMENT 'Address, OpeningHour, Holiday, Phone',
    map VARCHAR(255) NOT NULL,
    traffic JSON NOT NULL COMMENT 'Bus, Parking, Directions',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (salon_id)
);

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
    (user_id, experience, good_at, personality, message, created_at)
    VALUES (3, '3', '레이어드컷', '활발하다', '예쁜 공간에서 이미지와 1: 1 맞춤 상담을 통해 진심을 담아 디자인을 선물해드리겠습니다:)'),
    (5, '10', '내추럴 스타일', '조용하다', '최손을 다해서 고객님에 잘 올리는 스타일을 제공합니다.');

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

CREATE TABLE IF NOT EXISTS Service (
    service_id INT AUTO_INCREMENT,
    service_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration_min INT NOT NULL DEFAULT 60, 
    PRIMARY KEY (service_id)
);

INSERT INTO Service (service_name, price, duration_min) VALUES
        ('CUT',40000, 60),
        ('PERM', 80000, 100),
        ('COLOR', 60000, 80)
;


CREATE TABLE IF NOT EXISTS TimeOff (
    to_id INT AUTO_INCREMENT,
    designer_id INT NOT NULL,
    start_at DATE NOT NULL,
    end_at DATE NOT NULL,
    PRIMARY KEY (to_id),
    CONSTRAINT fk_timeoff_designer FOREIGN KEY (designer_id) REFERENCES Users(user_id)
);