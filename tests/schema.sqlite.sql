-- DROP TABLE IF EXISTS user_password_reset;
CREATE TABLE IF NOT EXISTS user_password_reset
(
    request_key VARCHAR(32) NOT NULL,
    user_id INT(11) NOT NULL,
    request_time DATETIME NOT NULL,
    PRIMARY KEY(request_key),
    UNIQUE(user_id)
);

DROP TABLE user_password_reset;

