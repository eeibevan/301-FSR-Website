CREATE TABLE `users2ElectricBoogaloo` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `username` VARCHAR(200) UNIQUE NOT NULL,
  `password` VARBINARY(255) NOT NULL,
  `salt` VARBINARY(255) NOT NULL,
  `is_active` TINYINT NOT NULL DEFAULT 1,
  `role` VARCHAR(100) NOT NULL DEFAULT 'faculty'
);

CREATE TABLE `request` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `created` DATETIME NOT NULL,
  `class` VARCHAR(255) NOT NULL,
  `drives` INT NOT NULL DEFAULT 0,
  `operating_system` VARCHAR(255) NOT NULL,
  `other` TEXT NOT NULL,
  `status` VARCHAR(200) NOT NULL DEFAULT 'Open',
  INDEX(`user_id`),
  INDEX (`status`),
  FOREIGN KEY (`user_id`) REFERENCES `users2ElectricBoogaloo`(`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
);

-- Insert Fsr User
-- Be Sure To Change The Password
SET @SALT = SUBSTRING(MD5(RAND()), -10);
INSERT INTO `users2ElectricBoogaloo` (username, password, salt, is_active, role)
VALUES ('fsradmin@localhost', SHA2(concat(@SALT, 'password'), 512), @SALT, 1, 'fsr');

