CREATE TABLE `items` (
	`item_id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT
);

CREATE TABLE `urns` (
	`rel_id`  INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	`urn`     TEXT         NOT NULL,
	`item_id` INT UNSIGNED NOT NULL
);

CREATE TABLE `urn_index` (
	`sha256_hash` BINARY(32) PRIMARY KEY,
	`rel_id`      INT UNSIGNED NOT NULL
)
	ENGINE = MEMORY;

CREATE TABLE `users` (
	`row_id`        INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	`user_id`       VARCHAR(255) NOT NULL,
	`access_token`  VARCHAR(255),
	`refresh_token` VARCHAR(255)
);