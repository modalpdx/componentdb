CREATE TABLE Measure (
    ID int NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    PRIMARY KEY (ID),
    UNIQUE KEY name (name)
) ENGINE=InnoDB;

CREATE TABLE Package (
    ID int NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    PRIMARY KEY (ID),
    UNIQUE KEY name (name)
) ENGINE=InnoDB;

CREATE TABLE Category (
    ID int NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    PRIMARY KEY (ID),
    UNIQUE KEY name (name)
) ENGINE=InnoDB;

CREATE TABLE Country (
    ID int NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    PRIMARY KEY (ID),
    UNIQUE KEY name (name)
) ENGINE=InnoDB;


CREATE TABLE Supplier (
    ID int NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    address1 varchar(255) DEFAULT NULL,
    address2 varchar(255) DEFAULT NULL,
    address3 varchar(255) DEFAULT NULL,
    locality varchar(255) DEFAULT NULL,
    region varchar(255) DEFAULT NULL,
    postalcode varchar(255) DEFAULT NULL,
    web varchar(255) DEFAULT NULL,
    phone varchar(255) DEFAULT NULL,
    CountryID int,
    PRIMARY KEY (ID),
    CONSTRAINT FOREIGN KEY (CountryID) REFERENCES Country (ID)
      ON DELETE SET NULL
      ON UPDATE CASCADE,
    UNIQUE KEY name (name)
) ENGINE=InnoDB;

CREATE TABLE Vendor (
    ID int NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    address1 varchar(255) DEFAULT NULL,
    address2 varchar(255) DEFAULT NULL,
    address3 varchar(255) DEFAULT NULL,
    locality varchar(255) DEFAULT NULL,
    region varchar(255) DEFAULT NULL,
    postalcode varchar(255) DEFAULT NULL,
    web varchar(255) DEFAULT NULL,
    phone varchar(255) DEFAULT NULL,
    PRIMARY KEY (ID),
    CountryID int,
    CONSTRAINT FOREIGN KEY (CountryID) REFERENCES Country (ID)
      ON DELETE SET NULL
      ON UPDATE CASCADE,
    UNIQUE KEY name (name)
) ENGINE=InnoDB;


CREATE TABLE Component (
    ID int NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    pins int DEFAULT 0,
    partno varchar(255) DEFAULT NULL,
    value varchar(255) DEFAULT NULL,
    quantity int NOT NULL,
    MeasureID int,
    PackageID int,
    VendorID int,
    PRIMARY KEY (ID),
    CONSTRAINT FOREIGN KEY (MeasureID) REFERENCES Measure (ID) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FOREIGN KEY (PackageID) REFERENCES Package (ID) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FOREIGN KEY (VendorID) REFERENCES Vendor (ID) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;


CREATE TABLE Component_Category (
    compID int NOT NULL DEFAULT 0,
    catID int NOT NULL DEFAULT 0,
    PRIMARY KEY (compID, catID),
    FOREIGN KEY (compID) REFERENCES Component (ID),
    FOREIGN KEY (catID) REFERENCES Category (ID)
) ENGINE=InnoDB;

CREATE TABLE Supplier_Component (
    supID int NOT NULL DEFAULT 0,
    compID int NOT NULL DEFAULT 0,
    PRIMARY KEY (supID, compID),
    FOREIGN KEY (supID) REFERENCES Supplier (ID),
    FOREIGN KEY (compID) REFERENCES Component (ID)
) ENGINE=InnoDB;


INSERT INTO Category VALUES (1, 'resistor'), (2, 'capacitor'), (3, 'timer'), (4, 'microcontroller'), (5, 'led'), (6, 'smd'), (7, 'avr');


INSERT INTO Package VALUES (1, 'dip'), (2, 'ssop'), (3, 'qfn'), (4, 'bga'), (5, 'smd');


INSERT INTO Measure VALUES (1, 'ohm'), (2, 'uF'), (3, 'pF'), (4, 'A');


INSERT INTO Country VALUES (1, 'United States'), (2, 'China'), (3, 'India'), (4, 'Thailand');


INSERT INTO Supplier (name, address1, locality, region, postalcode, web, phone, CountryID) 
    VALUES ("Mouser", "1000 North Main Street", "Mainsfield", "Texas", "76063", "http://www.mouser.com", "(800) 346-6873", 
        (SELECT ID from Country WHERE name="United States"));

INSERT INTO Supplier (name, address1, locality, region, postalcode, web, phone, CountryID) 
    VALUES ("Digi-Key Electronics", "701 Brooks Avenue South", "Thief River Falls", "Minnesota", "56701", "http://www.digikey.com", "(800) 344-4539", 
        (SELECT ID from Country WHERE name="United States"));


INSERT INTO Vendor (name, address1, locality, region, postalcode, web, phone, CountryID) 
    VALUES ("Atmel", "1600 Technology Drive", "San Jose", "California", "95110", "http://www.atmel.com", "(408) 436-4270", 
        (SELECT ID from Country WHERE name="United States"));

INSERT INTO Vendor (name, address1, locality, region, postalcode, web, phone, CountryID) 
    VALUES ("Texas Instruments", "12500 TI Boulevard", "Dallas", "Texas", "75243", "http://www.ti.com", "(972) 995-2011", 
        (SELECT ID from Country WHERE name="United States"));

INSERT INTO Vendor (name, address1, locality, region, postalcode, web, phone, CountryID) 
    VALUES ("Kingbright", "225 Brea Canyon Road", "City of Industry", "California", "91789", "http://www.kingbrightusa.com", "(888) 418-2684", 
        (SELECT ID from Country WHERE name="United States"));


INSERT INTO Component (name, pins, partno, quantity, PackageID, VendorID)
    VALUES ("ATtiny85", 8, "ATTINY85-20PU", 10, 
        (SELECT ID FROM Package WHERE name="dip"),
        (SELECT ID FROM Vendor WHERE name="Atmel"));

INSERT INTO Component (name, pins, partno, quantity, PackageID, VendorID)
    VALUES ("MSP430G2553", 16, "MSP430G2553", 1, 
        (SELECT ID FROM Package WHERE name="dip"),
        (SELECT ID FROM Vendor WHERE name="Texas Instruments"));

INSERT INTO Component (name, partno, quantity, PackageID, VendorID)
    VALUES ("Blue Reverse Mount SMD LED", "APTR3216QBC/D", 10, 
        (SELECT ID FROM Package WHERE name="smd"),
        (SELECT ID FROM Vendor WHERE name="Kingbright"));


INSERT INTO Component_Category (compID, catID) VALUES 
    ((SELECT ID FROM Component WHERE name="ATtiny85"), (SELECT ID FROM Category WHERE name="microcontroller")), 
    ((SELECT ID FROM Component WHERE name="ATtiny85"), (SELECT ID FROM Category WHERE name="avr")), 
    ((SELECT ID FROM Component WHERE name="Blue Reverse Mount SMD LED"), (SELECT ID FROM Category WHERE name="led")),
    ((SELECT ID FROM Component WHERE name="Blue Reverse Mount SMD LED"), (SELECT ID FROM Category WHERE name="smd"));

