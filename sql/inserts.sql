INSERT INTO roles (name) VALUES 
('admin'),
('user');

INSERT INTO users (external_id, name, email, password, role_id)
VALUES (
    '0ca08814c9214b33110fc5e13c60dc7f',
    'Admin',
    'admin@test.com',
    '$2y$10$hujjhVdXj3JnOq0sTQFSaeAXjvR.izHmUqVj6ArkKlfmFAWqglOAm', 
    1
);

CREATE TABLE blocked_extensions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  extension VARCHAR(10) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);