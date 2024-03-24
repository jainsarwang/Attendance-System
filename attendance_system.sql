CREATE DATABASE `attendance_system`;
use `attendance_system`;

CREATE TABLE `attendance_record` (
	`id` varchar(255) NOT NULL,
	`teaches_id` varchar(255) NOT NULL,
	`class_type` enum('THEORY','PRACTICAL') NOT NULL,
	`time` varchar(255) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `FOREIGN_TEACHES` (`teaches_id`),
	CONSTRAINT `FOREIGN_TEACHES` FOREIGN KEY (`teaches_id`) REFERENCES `teaches` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `attendences` (
	`id` varchar(255) NOT NULL,
	`student_id` varchar(255) NOT NULL,
	`record_id` varchar(255) NOT NULL,
	`status` enum('P','A') NOT NULL,
	PRIMARY KEY (`id`),
	KEY `FOREIGN_STUDENT` (`student_id`),
	KEY `FOREIGN_RECORD` (`record_id`),
	CONSTRAINT `FOREIGN_RECORD` FOREIGN KEY (`record_id`) REFERENCES `attendance_record` (`id`),
	CONSTRAINT `FOREIGN_STUDENT` FOREIGN KEY (`student_id`) REFERENCES `students` (`enrollment_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `classes` (
	`id` varchar(255) NOT NULL,
	`batch` int(11) NOT NULL,
	`department` varchar(255) NOT NULL,
	`semester` int(11) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `students` (
	`enrollment_number` varchar(255) NOT NULL,
	`name` varchar(255) NOT NULL,
	`mobile` bigint(20) NOT NULL,
	`gender` enum('M','F','O') NOT NULL,
	`class_id` varchar(255) NOT NULL,
	PRIMARY KEY (`enrollment_number`),
	KEY `FOREIGN_CLASS` (`class_id`),
	CONSTRAINT `FOREIGN_CLASS` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `subject` (
	`subject_code` varchar(30) NOT NULL,
	`name` varchar(255) NOT NULL,
	PRIMARY KEY (`subject_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `teaches` (
	`id` varchar(255) NOT NULL,
	`class_id` varchar(255) NOT NULL,
	`subject_id` varchar(255) NOT NULL,
	`teacher_id` varchar(255) NOT NULL,
	`class_type` enum('theory','practical') NOT NULL,
	PRIMARY KEY (`id`),
	KEY `FOREIGN_SUBJECT` (`subject_id`),
	KEY `FOREIGN_TEACHER` (`teacher_id`),
	KEY `FOREIGN_CLASS_TEACHES` (`class_id`),
	CONSTRAINT `FOREIGN_CLASS_TEACHES` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
	CONSTRAINT `FOREIGN_SUBJECT` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_code`),
	CONSTRAINT `FOREIGN_TEACHER` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `users` (
	`id` varchar(255) NOT NULL,
	`name` varchar(255) NOT NULL,
	`contact_number` bigint(20) NOT NULL,
	`password` varchar(255) NOT NULL,
	`type` enum('permanent','guest') NOT NULL,
	`position` enum('hod','faculty','staff') NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci