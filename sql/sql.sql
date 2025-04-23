CREATE db IF NOT EXISTS otp;
CREATE table IF NOT EXISTS otp.users (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    names VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

CREATE table IF NOT EXISTS otp.waiting_users (
    waiting_id INT NOT NULL AUTO_INCREMENT,
    names VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email_otp VARCHAR(6) NOT NULL,
    phone_otp VARCHAR(6) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (waiting_id)
);
CREATE table IF NOT EXISTS otp.otp_settings (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email_otp_enabled BOOLEAN DEFAULT TRUE,
    phone_otp_enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES otp.users(id)
);



