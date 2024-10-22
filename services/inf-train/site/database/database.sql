CREATE TABLE `user` (
	`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	
	`first_name` TEXT NOT NULL,
	`last_name` TEXT NOT NULL,
	`password` BLOB NOT NULL,
	`date` DATETIME NOT NULL,
	`storage` TEXT NOT NULL
);


CREATE TABLE `image` (
	`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	
	`storage` TEXT NOT NULL,
	`time_create` DATETIME NOT NULL,
	`public` INTEGER NOT NULL DEFAULT 0,
	`image_w` INTEGER NOT NULL,
	`image_h` INTEGER NOT NULL,
	`file_size` INTEGER NOT NULL,
	`name` TEXT NOT NULL,
	`type` TEXT NOT NULL -- mime-type without 'image/'
);