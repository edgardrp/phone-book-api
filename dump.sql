CREATE TABLE contacts ( id SERIAL NOT NULL , name VARCHAR(40) NOT NULL , surname VARCHAR(40) NOT NULL , PRIMARY KEY (id)) ENGINE = InnoDB;
CREATE TABLE phones ( id SERIAL NOT NULL , contact_id BIGINT UNSIGNED NOT NULL , phone_number VARCHAR(10) NOT NULL, FOREIGN KEY(contact_id) REFERENCES contacts(id)) ENGINE = InnoDB;
CREATE TABLE emails ( id SERIAL NOT NULL , contact_id BIGINT UNSIGNED NOT NULL , email_address VARCHAR(30) NOT NULL, FOREIGN KEY(contact_id) REFERENCES contacts(id)) ENGINE = InnoDB;