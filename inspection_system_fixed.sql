-- Fixed Inspection Officer database generated from the Excel file
-- Officer IDs are generated automatically because the Excel source has no officer_id column.
-- Records imported: 14

CREATE DATABASE IF NOT EXISTS inspection_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inspection_system;

DROP TABLE IF EXISTS officers;

CREATE TABLE officers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    officer_id VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(255) NOT NULL,
    address TEXT,
    nic VARCHAR(30),
    email VARCHAR(150),
    photo VARCHAR(255),
    qr_code VARCHAR(255),
    designation VARCHAR(100) DEFAULT 'Inspection Officer',
    department VARCHAR(150),
    province VARCHAR(100),
    district VARCHAR(100),
    phone VARCHAR(30),
    issue_date DATE NULL,
    expiry_date DATE NULL,
    status ENUM('Active','Inactive','Expired','Suspended') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO officers (officer_id, full_name, address, nic, email, photo, qr_code) VALUES ('INS-0001', 'Ms. A.L.A.P. Umashani', 'B/19/A, Railway Quarters, Ratmalana.', '199881900934', 'probodika.umashani@ucr.ac.lk', 'D:\\PHOTOS\\A L A P Umashani - Probodika Umashani.jpg', 'D:\\PHOTOS\\Ms. A.L.A.P. Umashani QR.jpg');
INSERT INTO officers (officer_id, full_name, address, nic, email, photo, qr_code) VALUES ('INS-0002', 'Mr. C.H. De Saram', '72, De Saram Place, Yakkala Road, Gampaha', '560091907V', 'chrisdesaram@gmail.com', 'D:\\PHOTOS\\Chris de Saram.jpg', 'D:\\PHOTOS\\Mr. C.H. De Saram QR.jpg');
INSERT INTO officers (officer_id, full_name, address, nic, email, photo, qr_code) VALUES ('INS-0003', 'Mr. M.S. Gamunu Srilal', '11/1B,1 st Ln,Raphael Thennakoon Mw,Parakandeniya,Imbulgoda', NULL, 'gamunusrilal@gmail.com', 'D:\\PHOTOS\\Gamunu Portrait AI - Gamunu Srilal.jpg', 'D:\\PHOTOS\\Mr. M.S. Gamunu Srilal QR.jpg');
INSERT INTO officers (officer_id, full_name, address, nic, email, photo, qr_code) VALUES ('INS-0004', 'Ms. Priyanthika Wijenaika', 'No. 134, W. A. silva Mawatha, Wellawatte, Colombo 06.', '556971943v', 'wijenaikepriyanthika15@gmail.com', 'D:\\PHOTOS\\PP Wijenaike.jpg', 'D:\\PHOTOS\\Ms. Priyanthika Wijenaika QR.jpg');
INSERT INTO officers (officer_id, full_name, address, nic, email, photo, qr_code) VALUES ('INS-0005', 'Mr. Nuwan Chamara', 'No 144/A, Maddumage Watta, Gangodawila, Nugegoda.', '802953976V', 'chamichamara1980@gmail.com', 'D:\\PHOTOS\\Nuwan Chamara Senanayake.jpg', NULL);
INSERT INTO officers (officer_id, full_name, address, nic, email, photo, qr_code) VALUES ('INS-0006', 'Ms. Namashiwayam Gishila', 'Peace Haven, Adisham Road, Haputale', '846854533V', 'gishilashivam1984@gmail.com', 'D:\\PHOTOS\\N GISHILA.jpg', 'D:\\PHOTOS\\Ms. Namashiwayam Gishila QR.jpg');
INSERT INTO officers (officer_id, full_name, address, nic, email, photo, qr_code) VALUES ('INS-0007', 'Mr. Gihan Wijesuriya', 'No 11,"Pinibindu Uyana",Nilwakka,Kegalle', '902261230V', 'gihanwijesuriya90@gmail.com', 'D:\\PHOTOS\\W.A.G.Wijesuriya - Gihan.jpg', 'D:\\PHOTOS\\Mr. Gihan Wijesuriya QR.jpg');
INSERT INTO officers (officer_id, full_name, address, nic, email, photo, qr_code) VALUES ('INS-0008', 'Mr. Rohana Bandara', 'Sri Lanka Institute Of Tourism and Hotel Management , Golf Link Road , Bandarawela', '770100801v', 'bandaraw@slithm.edu.lk', NULL, NULL);
INSERT INTO officers (officer_id, full_name, address, nic, email, photo, qr_code) VALUES ('INS-0009', 'Mr. Upul Atapattu', '264/28, Namal Uyana, Thambiligasmulla Road, Kiribathgoda.', '570033948v', 'uattapattu@gmail.com', 'D:\\PHOTOS\\UPUL ATHAPATHTHU .jpg', 'D:\\PHOTOS\\Mr. Upul Atapattu QR.jpg');
INSERT INTO officers (officer_id, full_name, address, nic, email, photo, qr_code) VALUES ('INS-0010', 'Mr. Ravindra Senavirathne', 'No 330 Siyambalagoda Danture', '812730517V', 'ravindras@slithm.edu.lk', NULL, NULL);
INSERT INTO officers (officer_id, full_name, address, nic, email, photo, qr_code) VALUES ('INS-0011', 'Mr. Roshan Fernando', '6/3 , St Rita’s road , Mt Lavinia', '561162433v', 'kfrfdo@gmail.com', 'D:\\PHOTOS\\Roshan Fernando.jpg', 'D:\\PHOTOS\\Mr. Roshan Fernando QR.jpg');
INSERT INTO officers (officer_id, full_name, address, nic, email, photo, qr_code) VALUES ('INS-0012', 'Ms. W. A . I. Madupani Gunasekara', '3 C, 107, NATIONAL HOUSING SCHEME, MATTEGODA', '867830103v', 'indrachapa.tourism@gmail.com', 'D:\\PHOTOS\\W.A.I.M.Gunasekara - Indrachapa Gunasekara.jpg', 'D:\\PHOTOS\\Ms. W. A . I. Madupani Gunasekara QR.jpg');
INSERT INTO officers (officer_id, full_name, address, nic, email, photo, qr_code) VALUES ('INS-0013', 'Mr. Sujith De Silva', 'No.5/1, De Mel Road, Katubedda', '672331250V', 'sujithdesilva29@gmail.com', 'D:\\PHOTOS\\SUJITH MERVIN.jpg', 'D:\\PHOTOS\\Mr. Sujith De Silva QR.jpg');
INSERT INTO officers (officer_id, full_name, address, nic, email, photo, qr_code) VALUES ('INS-0014', 'Ms. Gangani Marasinghe', 'No. 197/5, Dudly Senanayaka Mawatha,Negambo road, Nittambuwa', '937761716V', 'gangani325@gmail.com', 'D:\\PHOTOS\\M.M.G.K Marasinghe - gangani marasinghe.jpg', 'D:\\PHOTOS\\Ms. Gangani Marasinghe QR.jpg');