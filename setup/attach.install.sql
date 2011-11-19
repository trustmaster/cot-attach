-- Main attach table
CREATE TABLE IF NOT EXISTS `cot_attach` (
	att_id INT NOT NULL AUTO_INCREMENT,
	att_user INT NOT NULL,
	att_type CHAR(3) NOT NULL,
	att_parent INT NOT NULL,
	att_item INT NOT NULL,
	att_path VARCHAR(255) NOT NULL,
	att_ext VARCHAR(16) NOT NULL,
	att_img TINYINT NOT NULL DEFAULT 0,
	att_size INT NOT NULL,
	att_title VARCHAR(255) NOT NULL,
	att_count INT NOT NULL DEFAULT 0,
	PRIMARY KEY(att_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;