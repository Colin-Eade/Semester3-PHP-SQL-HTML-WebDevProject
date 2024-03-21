DROP SEQUENCE IF EXISTS file_id_seq CASCADE;
CREATE SEQUENCE file_id_seq START 1000;

-- 1. Creating 'users' table
DROP TABLE IF EXISTS files;
CREATE TABLE files (
                       id INTEGER PRIMARY KEY DEFAULT nextval('file_id_seq'), -- Auto-incrementing primary key
                       directory VARCHAR(255) NOT NULL,
                       original_name VARCHAR(255) NOT NULL,
                       file_name VARCHAR(255) NOT NULL,
                       mime_type VARCHAR(50) NOT NULL,
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       created_by VARCHAR(255) NOT NULL

);