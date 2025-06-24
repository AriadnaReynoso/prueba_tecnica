-- base de datos para almacenar contactos
CREATE DATABASE contact_db;
USE contact_db;

-- Tabla para almacenar información básica de los contactos
CREATE TABLE contacts (
    contact_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL, 
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE 
);

-- Tabla para almacenar números de teléfono asociados a contactos
CREATE TABLE phone_numbers (
    phone_id INT PRIMARY KEY AUTO_INCREMENT, 
    contact_id INT NOT NULL,
    number VARCHAR(20) NOT NULL, 
    FOREIGN KEY (contact_id) REFERENCES contacts(contact_id) ON DELETE CASCADE
);