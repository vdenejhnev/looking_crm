CREATE TABLE `lead` (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT NOW(),
  updated_at DATETIME NULL,
  status VARCHAR(255) NOT NULL DEFAULT '',
  inn VARCHAR(12) NULL,
  inn_added_at DATETIME NULL,
  company_name VARCHAR(255) NULL,
  name VARCHAR(255) NOT NULL,
  city VARCHAR(255) NULL,
  comment TEXT NULL,

  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES user (id),
  INDEX (inn)
) CHARSET utf8;

CREATE TABLE `lead_phone` (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  lead_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT NOW(),
  updated_at DATETIME NULL,
  value VARCHAR(20) NOT NULL,

  PRIMARY KEY (id),
  UNIQUE KEY (lead_id, value),
  FOREIGN KEY (lead_id) REFERENCES `lead` (id)
) CHARSET utf8;

CREATE TABLE `lead_email` (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  lead_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT NOW(),
  updated_at DATETIME NULL,
  value VARCHAR(255) NOT NULL,

  PRIMARY KEY (id),
  UNIQUE KEY (lead_id, value),
  FOREIGN KEY (lead_id) REFERENCES `lead` (id)
) CHARSET utf8;
