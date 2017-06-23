#-- CREATE DATABASE `adamford_login`;
#-- CREATE USER 'adamford_user'@'localhost' IDENTIFIED BY 'tXwv}UwZmz*(';
#-- GRANT SELECT, INSERT, UPDATE ON `adamford_login`.* TO 'adamford_user'@'localhost';
#CREATE TABLE `adamford_login`.`members` (
#    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
#    `username` VARCHAR(30) NOT NULL,
#    `email` VARCHAR(50) NOT NULL,
#    `password` CHAR(128) NOT NULL
#) ENGINE = InnoDB;
#CREATE TABLE `adamford_login`.`login_attempts` (
#    `user_id` INT(11) NOT NULL,
#    `time` VARCHAR(30) NOT NULL
#) ENGINE=InnoDB;
INSERT INTO `adamford_login`.`members` VALUES(1, 'Adam Fordyce', 'adam.fordyce@iongeo.com','$2y$10$IrzYJi10j3Jy/K6jzSLQtOLif1wEZqTRQoK3DcS3jdnFEhL4fWM4G');
