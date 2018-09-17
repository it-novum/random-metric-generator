CREATE TABLE `statusengine_perfdata` (
  `hostname`            VARCHAR(255),
  `service_description` VARCHAR(255),
  `label`               VARCHAR(255),
  `timestamp`           BIGINT(20) NOT NULL,
  `timestamp_unix`      BIGINT(13) NOT NULL,
  `value`               DOUBLE,
  `unit`                VARCHAR(10),
  KEY `metric` (`hostname`, `service_description`, `label`, `timestamp_unix`),
  KEY `timestamp_unix` (`timestamp_unix`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
COLLATE = utf8_general_ci;