CREATE TABLE `dealer` (
  user_id INT UNSIGNED NOT NULL,
  company_id INT UNSIGNED NOT NULL,
  city_id INT UNSIGNED NOT NULL,
  phone VARCHAR(255) NOT NULL,

  UNIQUE KEY (user_id),
  INDEX (phone),
  FOREIGN KEY (user_id) REFERENCES user (id),
  FOREIGN KEY (company_id) REFERENCES company (id),
  FOREIGN KEY (city_id) REFERENCES city (id)
);
