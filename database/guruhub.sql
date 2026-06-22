-- GuruHub Database Dump
-- Generated: 2026-06-22 06:58:58

SET FOREIGN_KEY_CHECKS=0;

-- --------------------------------------------------------
-- Table structure for `agendas`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `agendas`;
CREATE TABLE `agendas` (
  `id` char(36) NOT NULL,
  `teacher_id` char(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date` datetime NOT NULL,
  `start_time` varchar(255) NOT NULL,
  `end_time` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agendas_teacher_id_foreign` (`teacher_id`),
  CONSTRAINT `agendas_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `agendas`
INSERT INTO `agendas` (`id`, `teacher_id`, `title`, `description`, `date`, `start_time`, `end_time`, `created_at`, `updated_at`) VALUES
  ('019eed7f-8cd0-72d8-a68d-d818dd56fec5', '019eed7f-8caf-7014-9651-502ebc2c9b6a', 'Rapat Koordinasi MGMP', 'Membahas penyusunan TP semester genap tingkat wilayah.', '2026-06-05 00:00:00', '08:00', '10:00', '2026-06-22 04:03:39', '2026-06-22 04:03:39'),
  ('019eed7f-8cd1-7150-90e1-f7f78eee3eed', '019eed7f-8caf-7014-9651-502ebc2c9b6a', 'Koreksi Lembar Kerja Siswa', 'Melakukan koreksi hasil latihan logaritma kelas X.', '2026-06-06 13:00:00', '13:00', '15:00', '2026-06-22 04:03:39', '2026-06-22 04:03:39');

-- --------------------------------------------------------
-- Table structure for `attendance_details`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `attendance_details`;
CREATE TABLE `attendance_details` (
  `id` char(36) NOT NULL,
  `attendance_id` char(36) NOT NULL,
  `student_id` char(36) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'HADIR',
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendance_details_attendance_id_student_id_unique` (`attendance_id`,`student_id`),
  KEY `attendance_details_student_id_foreign` (`student_id`),
  CONSTRAINT `attendance_details_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendances` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_details_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `attendance_details`
-- (No rows)

-- --------------------------------------------------------
-- Table structure for `attendances`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `attendances`;
CREATE TABLE `attendances` (
  `id` char(36) NOT NULL,
  `schedule_id` char(36) NOT NULL,
  `date` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendances_schedule_id_date_unique` (`schedule_id`,`date`),
  CONSTRAINT `attendances_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `attendances`
-- (No rows)

-- --------------------------------------------------------
-- Table structure for `cache`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `cache`
-- (No rows)

-- --------------------------------------------------------
-- Table structure for `cache_locks`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `cache_locks`
-- (No rows)

-- --------------------------------------------------------
-- Table structure for `classes`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `classes`;
CREATE TABLE `classes` (
  `id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `level` varchar(255) NOT NULL,
  `school_year_id` char(36) NOT NULL,
  `semester_id` char(36) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `classes_school_year_id_foreign` (`school_year_id`),
  KEY `classes_semester_id_foreign` (`semester_id`),
  CONSTRAINT `classes_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `classes_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `classes`
INSERT INTO `classes` (`id`, `name`, `level`, `school_year_id`, `semester_id`, `created_at`, `updated_at`) VALUES
  ('019eed7f-8cb5-7122-b616-5fdf5ae8ce20', 'X MIPA 1', 'SMA', '019eed7f-8cb3-73f9-a154-615c870bb09e', '019eed7f-8cb4-700c-ab49-722e1447bdcd', '2026-06-22 04:03:39', '2026-06-22 04:03:39'),
  ('019eed7f-8cb7-73db-9967-a156da20b3a9', 'XI MIPA 2', 'SMA', '019eed7f-8cb3-73f9-a154-615c870bb09e', '019eed7f-8cb4-700c-ab49-722e1447bdcd', '2026-06-22 04:03:39', '2026-06-22 04:03:39');

-- --------------------------------------------------------
-- Table structure for `failed_jobs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `failed_jobs`
-- (No rows)

-- --------------------------------------------------------
-- Table structure for `job_batches`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `job_batches`
-- (No rows)

-- --------------------------------------------------------
-- Table structure for `jobs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `jobs`
-- (No rows)

-- --------------------------------------------------------
-- Table structure for `journals`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `journals`;
CREATE TABLE `journals` (
  `id` char(36) NOT NULL,
  `schedule_id` char(36) NOT NULL,
  `date` datetime NOT NULL,
  `material` text NOT NULL,
  `activity` text NOT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'DRAFT',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `journals_schedule_id_date_unique` (`schedule_id`,`date`),
  CONSTRAINT `journals_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `journals`
-- (No rows)

-- --------------------------------------------------------
-- Table structure for `learning_objectives`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `learning_objectives`;
CREATE TABLE `learning_objectives` (
  `id` char(36) NOT NULL,
  `subject_id` char(36) NOT NULL,
  `class_id` char(36) NOT NULL,
  `teacher_id` char(36) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `learning_objectives_subject_id_class_id_teacher_id_code_unique` (`subject_id`,`class_id`,`teacher_id`,`code`),
  KEY `learning_objectives_class_id_foreign` (`class_id`),
  KEY `learning_objectives_teacher_id_foreign` (`teacher_id`),
  CONSTRAINT `learning_objectives_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `learning_objectives_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `learning_objectives_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `learning_objectives`
INSERT INTO `learning_objectives` (`id`, `subject_id`, `class_id`, `teacher_id`, `code`, `description`, `created_at`, `updated_at`) VALUES
  ('019eed7f-8cc4-737d-a6c6-7d296f87429d', '019eed7f-8cc0-7368-a30e-99c2c7d3048a', '019eed7f-8cb5-7122-b616-5fdf5ae8ce20', '019eed7f-8caf-7014-9651-502ebc2c9b6a', 'TP-01', 'Menjelaskan konsep eksponen dan logaritma serta menyelesaikan masalah terkait.', '2026-06-22 04:03:39', '2026-06-22 04:03:39'),
  ('019eed7f-8cc7-728e-b9d9-83b70dd9b86a', '019eed7f-8cc0-7368-a30e-99c2c7d3048a', '019eed7f-8cb5-7122-b616-5fdf5ae8ce20', '019eed7f-8caf-7014-9651-502ebc2c9b6a', 'TP-02', 'Menganalisis sifat-sifat fungsi kuadrat dan menggambar grafiknya.', '2026-06-22 04:03:39', '2026-06-22 04:03:39');

-- --------------------------------------------------------
-- Table structure for `mentor_students`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `mentor_students`;
CREATE TABLE `mentor_students` (
  `id` char(36) NOT NULL,
  `teacher_id` char(36) NOT NULL,
  `student_id` char(36) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mentor_students_student_id_unique` (`student_id`),
  KEY `mentor_students_teacher_id_foreign` (`teacher_id`),
  CONSTRAINT `mentor_students_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mentor_students_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `mentor_students`
INSERT INTO `mentor_students` (`id`, `teacher_id`, `student_id`, `created_at`, `updated_at`) VALUES
  ('019eed7f-8cd2-7007-8158-187e3ac44a3d', '019eed7f-8caf-7014-9651-502ebc2c9b6a', '019eed7f-8cb8-7185-ba79-1479be612836', '2026-06-22 04:03:39', '2026-06-22 04:03:39');

-- --------------------------------------------------------
-- Table structure for `mentoring_notes`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `mentoring_notes`;
CREATE TABLE `mentoring_notes` (
  `id` char(36) NOT NULL,
  `mentor_student_id` char(36) NOT NULL,
  `category` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `content` text NOT NULL,
  `action_taken` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mentoring_notes_mentor_student_id_foreign` (`mentor_student_id`),
  CONSTRAINT `mentoring_notes_mentor_student_id_foreign` FOREIGN KEY (`mentor_student_id`) REFERENCES `mentor_students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `mentoring_notes`
INSERT INTO `mentoring_notes` (`id`, `mentor_student_id`, `category`, `date`, `content`, `action_taken`, `created_at`, `updated_at`) VALUES
  ('019eed7f-8cd5-711d-80f7-67eeb2c81a56', '019eed7f-8cd2-7007-8158-187e3ac44a3d', 'ACADEMIC', '2026-06-22 04:03:39', 'Ahmad Fauzi menunjukkan peningkatan konsentrasi pada materi matematika, namun perlu bimbingan mandiri untuk latihan logika lanjut.', 'Memberikan latihan soal tambahan terstruktur dan sesi bimbingan 15 menit setelah jam kelas selesai.', '2026-06-22 04:03:39', '2026-06-22 04:03:39');

-- --------------------------------------------------------
-- Table structure for `migrations`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `migrations`
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
  ('1', '0001_01_01_000000_create_users_table', '1'),
  ('2', '0001_01_01_000001_create_cache_table', '1'),
  ('3', '0001_01_01_000002_create_jobs_table', '1'),
  ('4', '2026_06_22_100000_create_guruhub_tables', '1');

-- --------------------------------------------------------
-- Table structure for `notifications`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `type` varchar(255) NOT NULL DEFAULT 'GENERAL',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `notifications`
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `is_read`, `type`, `created_at`, `updated_at`) VALUES
  ('019eed7f-8cd7-7148-9cd3-1286a69e3473', '019eed7f-8cae-7162-8059-c5e1c166ed15', 'Presensi Belum Diisi', 'Jadwal Matematika hari Senin belum diisi presensinya.', '0', 'ATTENDANCE_OVERDUE', '2026-06-22 04:03:39', '2026-06-22 04:03:39'),
  ('019eed7f-8cd8-73d7-8cf4-364f7a816c9a', '019eed7f-8cae-7162-8059-c5e1c166ed15', 'Nilai TP Belum Lengkap', 'Ada 1 murid di kelas X MIPA 1 yang belum memiliki nilai pada TP-02 Matematika.', '0', 'SCORE_INCOMPLETE', '2026-06-22 04:03:39', '2026-06-22 04:03:39');

-- --------------------------------------------------------
-- Table structure for `password_reset_tokens`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `password_reset_tokens`
-- (No rows)

-- --------------------------------------------------------
-- Table structure for `schedules`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `schedules`;
CREATE TABLE `schedules` (
  `id` char(36) NOT NULL,
  `teacher_id` char(36) NOT NULL,
  `class_id` char(36) NOT NULL,
  `subject_id` char(36) NOT NULL,
  `day` varchar(255) NOT NULL,
  `start_time` varchar(255) NOT NULL,
  `end_time` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `schedules_teacher_id_foreign` (`teacher_id`),
  KEY `schedules_class_id_foreign` (`class_id`),
  KEY `schedules_subject_id_foreign` (`subject_id`),
  CONSTRAINT `schedules_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `schedules_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `schedules_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `schedules`
INSERT INTO `schedules` (`id`, `teacher_id`, `class_id`, `subject_id`, `day`, `start_time`, `end_time`, `created_at`, `updated_at`) VALUES
  ('019eed7f-8cc2-732c-a00f-2233ee1dc032', '019eed7f-8caf-7014-9651-502ebc2c9b6a', '019eed7f-8cb5-7122-b616-5fdf5ae8ce20', '019eed7f-8cc0-7368-a30e-99c2c7d3048a', 'MONDAY', '07:30', '09:00', '2026-06-22 04:03:39', '2026-06-22 04:03:39'),
  ('019eed7f-8cc3-71b6-86b6-a17a36fd5718', '019eed7f-8caf-7014-9651-502ebc2c9b6a', '019eed7f-8cb5-7122-b616-5fdf5ae8ce20', '019eed7f-8cc1-712e-bcc6-7c809812f360', 'TUESDAY', '09:00', '10:30', '2026-06-22 04:03:39', '2026-06-22 04:03:39');

-- --------------------------------------------------------
-- Table structure for `school_profiles`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `school_profiles`;
CREATE TABLE `school_profiles` (
  `id` varchar(255) NOT NULL DEFAULT 'singleton',
  `yayasan_name` varchar(255) NOT NULL,
  `school_name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `headmaster` varchar(255) NOT NULL,
  `headmaster_nip` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `school_profiles`
INSERT INTO `school_profiles` (`id`, `yayasan_name`, `school_name`, `address`, `phone`, `email`, `website`, `headmaster`, `headmaster_nip`, `created_at`, `updated_at`) VALUES
  ('singleton', 'YAYASAN PENDIDIKAN GURUHUB INDONESIA', 'SMA GURUHUB UTAMA', 'Jl. Antigravity No. 101, Kota Bandung, Jawa Barat', '(022) 1234567', 'info@guruhub.sch.id', 'www.guruhub.sch.id', 'Dr. H. Mulyadi, M.Pd.', '197208201998031003', '2026-06-22 04:03:39', '2026-06-22 04:03:39');

-- --------------------------------------------------------
-- Table structure for `school_years`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `school_years`;
CREATE TABLE `school_years` (
  `id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_years_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `school_years`
INSERT INTO `school_years` (`id`, `name`, `is_active`, `created_at`, `updated_at`) VALUES
  ('019eed7f-8cb3-73f9-a154-615c870bb09e', '2025/2026', '1', '2026-06-22 04:03:39', '2026-06-22 04:03:39');

-- --------------------------------------------------------
-- Table structure for `scores`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `scores`;
CREATE TABLE `scores` (
  `id` char(36) NOT NULL,
  `student_id` char(36) NOT NULL,
  `learning_objective_id` char(36) NOT NULL,
  `score` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `scores_student_id_learning_objective_id_unique` (`student_id`,`learning_objective_id`),
  KEY `scores_learning_objective_id_foreign` (`learning_objective_id`),
  CONSTRAINT `scores_learning_objective_id_foreign` FOREIGN KEY (`learning_objective_id`) REFERENCES `learning_objectives` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scores_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `scores`
INSERT INTO `scores` (`id`, `student_id`, `learning_objective_id`, `score`, `created_at`, `updated_at`) VALUES
  ('019eed7f-8cc9-71ab-bf33-c7eadb474dbb', '019eed7f-8cb8-7185-ba79-1479be612836', '019eed7f-8cc4-737d-a6c6-7d296f87429d', '85', '2026-06-22 04:03:39', '2026-06-22 04:03:39'),
  ('019eed7f-8cca-7080-9339-bdc3b5d46f07', '019eed7f-8cbb-7393-8916-7ad593013c98', '019eed7f-8cc4-737d-a6c6-7d296f87429d', '90', '2026-06-22 04:03:39', '2026-06-22 04:03:39'),
  ('019eed7f-8ccb-726e-b234-25235db1b106', '019eed7f-8cbc-71c1-b9a4-23b62bb09d58', '019eed7f-8cc4-737d-a6c6-7d296f87429d', '75', '2026-06-22 04:03:39', '2026-06-22 04:03:39'),
  ('019eed7f-8cce-7004-9e88-1d2d55438c6e', '019eed7f-8cb8-7185-ba79-1479be612836', '019eed7f-8cc7-728e-b9d9-83b70dd9b86a', '80', '2026-06-22 04:03:39', '2026-06-22 04:03:39'),
  ('019eed7f-8ccf-709c-aa45-2cab4a5c3a15', '019eed7f-8cbb-7393-8916-7ad593013c98', '019eed7f-8cc7-728e-b9d9-83b70dd9b86a', '95', '2026-06-22 04:03:39', '2026-06-22 04:03:39');

-- --------------------------------------------------------
-- Table structure for `semesters`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `semesters`;
CREATE TABLE `semesters` (
  `id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `semesters`
INSERT INTO `semesters` (`id`, `name`, `is_active`, `created_at`, `updated_at`) VALUES
  ('019eed7f-8cb4-700c-ab49-722e1447bdcd', 'GENAP', '1', '2026-06-22 04:03:39', '2026-06-22 04:03:39');

-- --------------------------------------------------------
-- Table structure for `sessions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `sessions`
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
  ('Lw2mT7N73Wgm13UcgYxOx0bhQDmp8skWTlZSQsdZ', '019eed7f-8cae-7162-8059-c5e1c166ed15', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibXFXVVNHUkdWUjlkS1hGUFhVeHFXenNPZW1uS3M3eUlObmRpRWNIcCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC90ZWFjaGVyL2FjY291bnQiO3M6NToicm91dGUiO3M6MTU6InRlYWNoZXIuYWNjb3VudCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtzOjM2OiIwMTllZWQ3Zi04Y2FlLTcxNjItODA1OS1jNWUxYzE2NmVkMTUiO30=', '1782102122');

-- --------------------------------------------------------
-- Table structure for `students`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `id` char(36) NOT NULL,
  `nis` varchar(255) NOT NULL,
  `nisn` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `gender` varchar(255) NOT NULL,
  `class_id` char(36) NOT NULL,
  `parent_name` varchar(255) NOT NULL,
  `parent_phone` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `students_nis_unique` (`nis`),
  UNIQUE KEY `students_nisn_unique` (`nisn`),
  KEY `students_class_id_foreign` (`class_id`),
  CONSTRAINT `students_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `students`
INSERT INTO `students` (`id`, `nis`, `nisn`, `name`, `gender`, `class_id`, `parent_name`, `parent_phone`, `created_at`, `updated_at`) VALUES
  ('019eed7f-8cb8-7185-ba79-1479be612836', '10001', '0012345678', 'Ahmad Fauzi', 'MALE', '019eed7f-8cb5-7122-b616-5fdf5ae8ce20', 'H. Budi', '081223344556', '2026-06-22 04:03:39', '2026-06-22 04:03:39'),
  ('019eed7f-8cbb-7393-8916-7ad593013c98', '10002', '0012345679', 'Citra Lestari', 'FEMALE', '019eed7f-8cb5-7122-b616-5fdf5ae8ce20', 'Agus Setiawan', '081223344557', '2026-06-22 04:03:39', '2026-06-22 04:03:39'),
  ('019eed7f-8cbc-71c1-b9a4-23b62bb09d58', '10003', '0012345680', 'Dani Wijaya', 'MALE', '019eed7f-8cb5-7122-b616-5fdf5ae8ce20', 'Hendra Gunawan', '081223344558', '2026-06-22 04:03:39', '2026-06-22 04:03:39'),
  ('019eed7f-8cbf-7214-ad0a-50d134f3b889', '10004', '0012345681', 'Eka Putri', 'FEMALE', '019eed7f-8cb7-73db-9967-a156da20b3a9', 'Rudi Hermawan', '081223344559', '2026-06-22 04:03:39', '2026-06-22 04:03:39');

-- --------------------------------------------------------
-- Table structure for `subjects`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `subjects`;
CREATE TABLE `subjects` (
  `id` char(36) NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subjects_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `subjects`
INSERT INTO `subjects` (`id`, `code`, `name`, `created_at`, `updated_at`) VALUES
  ('019eed7f-8cc0-7368-a30e-99c2c7d3048a', 'MAT-SMA', 'Matematika Peminatan', '2026-06-22 04:03:39', '2026-06-22 04:03:39'),
  ('019eed7f-8cc1-712e-bcc6-7c809812f360', 'ING-SMA', 'Bahasa Inggris', '2026-06-22 04:03:39', '2026-06-22 04:03:39');

-- --------------------------------------------------------
-- Table structure for `teachers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `teachers`;
CREATE TABLE `teachers` (
  `id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `nip` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `teachers_user_id_unique` (`user_id`),
  UNIQUE KEY `teachers_nip_unique` (`nip`),
  CONSTRAINT `teachers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `teachers`
INSERT INTO `teachers` (`id`, `user_id`, `nip`, `name`, `phone`, `created_at`, `updated_at`) VALUES
  ('019eed7f-8caf-7014-9651-502ebc2c9b6a', '019eed7f-8cae-7162-8059-c5e1c166ed15', '198804152015031002', 'Budi Santoso, S.Pd.', '081234567890', '2026-06-22 04:03:39', '2026-06-22 04:03:39');

-- --------------------------------------------------------
-- Table structure for `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` char(36) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'TEACHER',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `users`
INSERT INTO `users` (`id`, `email`, `password_hash`, `role`, `created_at`, `updated_at`) VALUES
  ('019eed7f-8ca5-7357-89ce-b4915b27f742', 'admin@guruhub.com', '$2y$12$CnQr7TWb2lVoyKN9xS.I6ekdg6ALpnf.McAbhNhBdRQvjAXFf3Zoi', 'ADMIN', '2026-06-22 04:03:39', '2026-06-22 04:03:39'),
  ('019eed7f-8cae-7162-8059-c5e1c166ed15', 'guru@guruhub.com', '$2y$12$YFNe.iOL3uKAZW3RFE5MfOgls4xTQb8E1iitwoKVyz0tg2UB912EC', 'TEACHER', '2026-06-22 04:03:39', '2026-06-22 04:03:39');

SET FOREIGN_KEY_CHECKS=1;
