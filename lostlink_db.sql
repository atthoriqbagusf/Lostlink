-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 16, 2026 at 03:15 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lostlink_db`
--

DELIMITER $$
--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_rasio_penemuan` () RETURNS DECIMAL(5,2) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE total_lap INT;
    DECLARE selesai_lap INT;
    SELECT COUNT(*) INTO total_lap FROM laporan_hilang;
    SELECT COUNT(*) INTO selesai_lap FROM laporan_hilang WHERE status = 'Selesai';
    IF total_lap = 0 THEN RETURN 0.00; END IF;
    RETURN ROUND((selesai_lap / total_lap) * 100, 2);
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_total_laporan_user` (`p_user_id` INT) RETURNS INT(11) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE total_hilang INT DEFAULT 0;
    DECLARE total_temuan INT DEFAULT 0;
    
    -- Hitung laporan kehilangan
    SELECT COUNT(*) INTO total_hilang FROM laporan_hilang WHERE user_id = p_user_id;
    
    -- Hitung laporan temuan
    SELECT COUNT(*) INTO total_temuan FROM laporan_temuan WHERE penemu_id = p_user_id;
    
    RETURN (total_hilang + total_temuan);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `ikon` varchar(50) NOT NULL DEFAULT 'fa-box'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama`, `ikon`) VALUES
(1, 'Elektronik', 'fa-laptop'),
(2, 'Dompet', 'fa-wallet'),
(3, 'Kunci', 'fa-key'),
(4, 'Dokumen', 'fa-file-invoice'),
(5, 'Tas', 'fa-bag-shopping'),
(6, 'Aksesoris', 'fa-ring'),
(7, 'Lainnya', 'fa-box');

-- --------------------------------------------------------

--
-- Table structure for table `klaim_barang`
--

CREATE TABLE `klaim_barang` (
  `id` int(11) NOT NULL,
  `laporan_id` int(11) NOT NULL,
  `claimant_id` int(11) NOT NULL,
  `bukti` text NOT NULL,
  `tanggal_klaim` date NOT NULL,
  `status` enum('Menunggu Verifikasi','Diterima','Ditolak') DEFAULT 'Menunggu Verifikasi',
  `foto_bukti` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `klaim_barang`
--

INSERT INTO `klaim_barang` (`id`, `laporan_id`, `claimant_id`, `bukti`, `tanggal_klaim`, `status`, `foto_bukti`, `created_at`) VALUES
(3, 7, 1, 'NIIK 3313tt4781268', '2026-06-16', 'Diterima', 'assets/uploads/bukti/1781609569_images.jpg', '2026-06-16 11:32:49');

--
-- Triggers `klaim_barang`
--
DELIMITER $$
CREATE TRIGGER `tr_after_insert_klaim` AFTER INSERT ON `klaim_barang` FOR EACH ROW BEGIN
    INSERT INTO log_aktivitas (pesan)
    VALUES (CONCAT('Klaim baru diajukan untuk laporan #', NEW.laporan_id, ' oleh user #', NEW.claimant_id));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `laporan_hilang`
--

CREATE TABLE `laporan_hilang` (
  `id` int(11) NOT NULL,
  `nama_barang` varchar(150) NOT NULL,
  `kategori_id` int(11) NOT NULL,
  `lokasi` varchar(100) NOT NULL,
  `tanggal` date NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `kontak` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('Menunggu Verifikasi','Hilang','Diklaim','Selesai') DEFAULT 'Menunggu Verifikasi',
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `laporan_hilang`
--

INSERT INTO `laporan_hilang` (`id`, `nama_barang`, `kategori_id`, `lokasi`, `tanggal`, `deskripsi`, `kontak`, `user_id`, `status`, `foto`, `created_at`) VALUES
(6, 'HP OPPO A15', 1, 'Masjid Kampus', '2026-06-16', 'HP OPPO A15 dengan stiker UGM di belakang casing hilang sekitar jam 10 di sebelah barat Masjid Kampus', '089623181341', 7, 'Hilang', 'assets/uploads/1781598348_Review-OPPO-A15-Belakang-Lurus-700x525.jpg', '2026-06-16 04:18:46'),
(8, 'Laptop ASUS VivoBook 14', 1, 'Perpustakaan', '2026-06-09', 'Laptop ASUS VivoBook 14 warna silver dengan stiker logo kampus pada bagian belakang layar. Laptop dimasukkan ke dalam tas hitam merek Eiger. Terakhir digunakan sekitar pukul 13.30 WIB di meja baca dekat jendela lantai 2.', '081234567890', 2, 'Hilang', 'assets/uploads/1781599207_asus-vivobook-14-a1407qa-izAnw.jpg', '2026-06-16 08:40:07'),
(9, 'IPHONE 13 PRO', 1, 'Ruang Kuliah', '2026-06-06', 'iPhone 13 Pro Sierra Blue, casing transparan agak menguning.', '08567891234', 3, 'Hilang', 'assets/uploads/1781608840_images (1).jpg', '2026-06-16 11:20:40'),
(10, 'Botol Minum Tumbler', 7, 'Kantin', '2026-06-04', 'Tumbler stainless warna biru tua kapasitas 750 ml. Pada bagian bawah terdapat stiker bertuliskan &quot;Teknik Informatika&quot;. Ditemukan tertinggal di meja kantin setelah jam makan siang.', '089900887766', 4, 'Hilang', NULL, '2026-06-16 11:27:21');

--
-- Triggers `laporan_hilang`
--
DELIMITER $$
CREATE TRIGGER `tr_after_insert_laporan_hilang` AFTER INSERT ON `laporan_hilang` FOR EACH ROW BEGIN
    INSERT INTO log_aktivitas (pesan)
    VALUES (CONCAT('Laporan kehilangan baru: ', NEW.nama_barang, ' oleh user #', NEW.user_id));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_after_update_laporan` AFTER UPDATE ON `laporan_hilang` FOR EACH ROW BEGIN
    IF NEW.status <> OLD.status THEN
        INSERT INTO log_aktivitas (pesan) 
        VALUES (CONCAT('Status laporan hilang ID ', NEW.id, ' berubah menjadi: ', NEW.status));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `laporan_temuan`
--

CREATE TABLE `laporan_temuan` (
  `id` int(11) NOT NULL,
  `nama_barang` varchar(150) NOT NULL,
  `kategori_id` int(11) NOT NULL,
  `lokasi` varchar(100) NOT NULL,
  `tanggal` date NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `kontak` varchar(20) NOT NULL,
  `penemu_id` int(11) NOT NULL,
  `status` enum('Menunggu Verifikasi','Ditemukan','Diklaim','Selesai') DEFAULT 'Menunggu Verifikasi',
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `laporan_temuan`
--

INSERT INTO `laporan_temuan` (`id`, `nama_barang`, `kategori_id`, `lokasi`, `tanggal`, `deskripsi`, `kontak`, `penemu_id`, `status`, `foto`, `created_at`) VALUES
(4, 'Dompet Kulit', 2, 'Masjid Kampus', '2026-06-16', 'Didalamnya ada uang Rp 100.000 dan KTP bernama Jaka Putra', '08631245565', 1, 'Ditemukan', 'assets/uploads/1781579379_download.jpg', '2026-06-16 03:09:39'),
(7, 'KTP (Kartu Tanda Penduduk)', 4, 'Lainnya', '2026-06-16', 'KTP atas nama Muchammad Abduromim. Ditemukan di Gedung DTEDI SV lantai 2. Kemungkinan terjatuh saat berpindah kelas.', '081234567890', 2, 'Selesai', 'assets/uploads/1781599863_images.jpg', '2026-06-16 08:51:03'),
(8, 'Earphone Bluetooth', 1, 'Ruang Kuliah', '2026-06-16', 'Earphone Bluetooth warna putih dengan casing pengisian daya. Tidak terdapat nama pemilik. Ditemukan di bawah kursi baris ketiga setelah perkuliahan selesai sekitar pukul 15.00 WIB.', '08567891234', 3, 'Ditemukan', 'assets/uploads/1781608964_images (2).jpg', '2026-06-16 11:22:44'),
(9, 'Tas Ransel Eiger Hitam', 5, 'Ruang Kuliah', '2026-06-16', 'Tas ransel hitam ditemukan setelah acara seminar berakhir. Di dalam tas terdapat buku catatan, charger laptop, dan kartu identitas mahasiswa yang masih tersimpan dengan baik.', '089900887766', 4, 'Ditemukan', 'assets/uploads/1781609393_images (3).jpg', '2026-06-16 11:29:53');

--
-- Triggers `laporan_temuan`
--
DELIMITER $$
CREATE TRIGGER `tr_after_insert_laporan_temuan` AFTER INSERT ON `laporan_temuan` FOR EACH ROW BEGIN
    INSERT INTO log_aktivitas (pesan)
    VALUES (CONCAT('Laporan temuan baru: ', NEW.nama_barang, ' oleh user #', NEW.penemu_id));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id` int(11) NOT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp(),
  `pesan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `log_aktivitas`
--

INSERT INTO `log_aktivitas` (`id`, `waktu`, `pesan`) VALUES
(1, '2026-06-15 17:06:12', 'User baru terdaftar: Ahmad Junaidi (Admin) (Tipe: admin)'),
(2, '2026-06-15 17:06:12', 'User baru terdaftar: Barkah Sholih (Tipe: mahasiswa)'),
(3, '2026-06-15 17:06:12', 'User baru terdaftar: Siti Rahma (Tipe: mahasiswa)'),
(4, '2026-06-15 17:06:12', 'User baru terdaftar: Rian Hidayat (Tipe: umum)'),
(5, '2026-06-15 17:06:12', 'User baru terdaftar: Linda Permata (Tipe: umum)'),
(6, '2026-06-15 17:06:12', 'Laporan kehilangan baru: Laptop Asus ROG Zephyrus oleh user #2'),
(7, '2026-06-15 17:06:12', 'Laporan kehilangan baru: Kunci Motor Honda Vario oleh user #4'),
(8, '2026-06-15 17:06:12', 'Laporan kehilangan baru: Tas Berisi Berkas Tagihan Paket oleh user #4'),
(9, '2026-06-15 17:06:12', 'Laporan temuan baru: Dompet Eiger Kulit Coklat oleh user #3'),
(10, '2026-06-15 17:06:12', 'Laporan temuan baru: Flashdisk SanDisk 64GB oleh user #2'),
(11, '2026-06-15 17:06:12', 'Laporan temuan baru: iPhone 13 Pro Max oleh user #3'),
(12, '2026-06-15 17:06:12', 'Klaim baru diajukan untuk laporan #1 oleh user #2'),
(13, '2026-06-13 02:30:12', 'Mahasiswa Barkah Sholih membuat laporan kehilangan baru (Laptop Asus)'),
(14, '2026-06-13 02:45:55', 'Mahasiswa Siti Rahma membuat laporan temuan baru (Dompet Eiger)'),
(15, '2026-06-13 03:01:05', 'Pengunjung Umum Rian Hidayat mendaftar akun di sistem'),
(16, '2026-06-13 03:02:10', 'Pengunjung Umum Rian Hidayat membuat laporan kehilangan baru (Tas Berkas Tagihan)'),
(17, '2026-06-15 17:54:12', 'User baru terdaftar: Atthoriq Bagus Fadillah (Tipe: umum)'),
(18, '2026-06-15 17:54:12', 'User baru terdaftar: Atthoriq Bagus Fadillah (Tipe: umum)'),
(19, '2026-06-15 17:54:13', 'User Atthoriq Bagus Fadillah login ke sistem'),
(20, '2026-06-15 17:54:45', 'User Atthoriq Bagus Fadillah logout dari sistem'),
(21, '2026-06-15 17:55:31', 'User Ahmad Junaidi (Admin) login ke sistem'),
(22, '2026-06-15 17:56:37', 'Klaim #1 diproses dengan status: Diterima'),
(23, '2026-06-15 17:58:23', 'User Ahmad Junaidi (Admin) logout dari sistem'),
(24, '2026-06-15 17:59:04', 'User baru terdaftar: Jaka (Tipe: umum)'),
(25, '2026-06-15 17:59:04', 'User baru terdaftar: Jaka (Tipe: umum)'),
(26, '2026-06-15 17:59:04', 'User Jaka login ke sistem'),
(27, '2026-06-15 18:15:06', 'Laporan kehilangan baru: HP OPPO A15 oleh user #7'),
(28, '2026-06-15 18:15:06', 'User #7 membuat laporan kehilangan: HP OPPO A15'),
(29, '2026-06-15 18:30:52', 'Laporan kehilangan baru: HP OPPO A15 oleh user #7'),
(30, '2026-06-15 18:30:52', 'User #7 membuat laporan kehilangan: HP OPPO A15'),
(31, '2026-06-16 02:06:16', 'User Jaka logout dari sistem'),
(32, '2026-06-16 02:07:01', 'User Jaka login ke sistem'),
(33, '2026-06-16 02:07:16', 'User Jaka logout dari sistem'),
(34, '2026-06-16 02:07:28', 'User Jaka login ke sistem'),
(35, '2026-06-16 02:21:45', 'User Jaka logout dari sistem'),
(36, '2026-06-16 02:21:56', 'User Jaka login ke sistem'),
(37, '2026-06-16 02:22:18', 'User Jaka logout dari sistem'),
(38, '2026-06-16 02:24:15', 'User baru terdaftar: Faddly (Tipe: mahasiswa)'),
(39, '2026-06-16 02:24:15', 'User baru terdaftar: Faddly (Tipe: mahasiswa)'),
(40, '2026-06-16 02:24:16', 'User Faddly login ke sistem'),
(41, '2026-06-16 02:26:42', 'User Faddly logout dari sistem'),
(42, '2026-06-16 02:26:52', 'User Ahmad Junaidi (Admin) login ke sistem'),
(43, '2026-06-16 02:27:07', 'Status laporan kehilangan #5 diubah menjadi Hilang'),
(44, '2026-06-16 03:09:39', 'Laporan temuan baru: Dompet Kulit oleh user #1'),
(45, '2026-06-16 03:09:39', 'User #1 membuat laporan temuan: Dompet Kulit'),
(46, '2026-06-16 03:20:50', 'Laporan temuan baru: sfasfs oleh user #1'),
(47, '2026-06-16 03:20:50', 'User #1 membuat laporan temuan: sfasfs'),
(48, '2026-06-16 03:23:19', 'Laporan temuan baru: hp asus oleh user #1'),
(49, '2026-06-16 03:23:19', 'User #1 membuat laporan temuan: hp asus'),
(50, '2026-06-16 03:26:09', 'User Ahmad Junaidi (Admin) logout dari sistem'),
(51, '2026-06-16 03:26:19', 'User Jaka login ke sistem'),
(52, '2026-06-16 04:10:52', 'Klaim baru diajukan untuk laporan #2 oleh user #7'),
(53, '2026-06-16 04:10:52', 'User #7 mengajukan klaim untuk laporan #2'),
(54, '2026-06-16 04:11:15', 'User Jaka logout dari sistem'),
(55, '2026-06-16 04:11:29', 'User Ahmad Junaidi (Admin) login ke sistem'),
(56, '2026-06-16 04:11:43', 'Status laporan kehilangan #4 diubah menjadi Hilang'),
(57, '2026-06-16 04:11:47', 'Status laporan temuan #6 diubah menjadi Ditemukan'),
(58, '2026-06-16 04:11:54', 'Klaim #2 diproses dengan status: Ditolak'),
(59, '2026-06-16 04:12:03', 'User Ahmad Junaidi (Admin) logout dari sistem'),
(60, '2026-06-16 04:12:14', 'User Jaka login ke sistem'),
(61, '2026-06-16 04:13:39', 'Laporan kehilangan #5 dihapus'),
(62, '2026-06-16 04:13:40', 'Laporan kehilangan #4 dihapus'),
(63, '2026-06-16 04:18:46', 'Laporan kehilangan baru: HP OPPO A15 oleh user #7'),
(64, '2026-06-16 04:18:46', 'User #7 membuat laporan kehilangan: HP OPPO A15'),
(65, '2026-06-16 08:25:48', 'Laporan kehilangan #6 diperbarui'),
(66, '2026-06-16 08:26:00', 'User Jaka logout dari sistem'),
(67, '2026-06-16 08:26:47', 'User Ahmad Junaidi (Admin) login ke sistem'),
(68, '2026-06-16 08:27:01', 'Status laporan kehilangan #6 diubah menjadi Hilang'),
(69, '2026-06-16 08:27:06', 'Laporan temuan #5 dihapus'),
(70, '2026-06-16 08:27:11', 'Status laporan temuan #4 diubah menjadi Ditemukan'),
(71, '2026-06-16 08:27:37', 'Laporan temuan #6 dihapus'),
(72, '2026-06-16 08:29:40', 'User Ahmad Junaidi (Admin) logout dari sistem'),
(73, '2026-06-16 08:29:55', 'User Barkah Sholih login ke sistem'),
(74, '2026-06-16 08:30:03', 'Laporan kehilangan #1 dihapus'),
(75, '2026-06-16 08:30:56', 'Laporan temuan #2 diperbarui'),
(76, '2026-06-16 08:31:24', 'Laporan temuan #2 diperbarui'),
(77, '2026-06-16 08:31:52', 'Laporan temuan #2 dihapus'),
(78, '2026-06-16 08:33:55', 'Laporan kehilangan baru: Flashdisk SanDisk 64GB oleh user #2'),
(79, '2026-06-16 08:33:55', 'User #2 membuat laporan kehilangan: Flashdisk SanDisk 64GB'),
(80, '2026-06-16 08:35:02', 'Laporan kehilangan #7 dihapus'),
(81, '2026-06-16 08:40:07', 'Laporan kehilangan baru: Laptop ASUS VivoBook 14 oleh user #2'),
(82, '2026-06-16 08:40:07', 'User #2 membuat laporan kehilangan: Laptop ASUS VivoBook 14'),
(83, '2026-06-16 08:51:03', 'Laporan temuan baru: KTP (Kartu Tanda Penduduk) oleh user #2'),
(84, '2026-06-16 08:51:03', 'User #2 membuat laporan temuan: KTP (Kartu Tanda Penduduk)'),
(85, '2026-06-16 08:51:23', 'User Barkah Sholih logout dari sistem'),
(86, '2026-06-16 11:18:40', 'User Siti Rahma login ke sistem'),
(87, '2026-06-16 11:19:27', 'Laporan temuan #3 dihapus'),
(88, '2026-06-16 11:20:40', 'Laporan kehilangan baru: IPHONE 13 PRO oleh user #3'),
(89, '2026-06-16 11:20:40', 'User #3 membuat laporan kehilangan: IPHONE 13 PRO'),
(90, '2026-06-16 11:20:52', 'Laporan temuan #1 dihapus'),
(91, '2026-06-16 11:22:44', 'Laporan temuan baru: Earphone Bluetooth oleh user #3'),
(92, '2026-06-16 11:22:44', 'User #3 membuat laporan temuan: Earphone Bluetooth'),
(93, '2026-06-16 11:22:52', 'User Siti Rahma logout dari sistem'),
(94, '2026-06-16 11:25:27', 'User Rian Hidayat login ke sistem'),
(95, '2026-06-16 11:25:36', 'Laporan kehilangan #3 dihapus'),
(96, '2026-06-16 11:25:38', 'Laporan kehilangan #2 dihapus'),
(97, '2026-06-16 11:27:21', 'Laporan kehilangan baru: Botol Minum Tumbler oleh user #4'),
(98, '2026-06-16 11:27:21', 'User #4 membuat laporan kehilangan: Botol Minum Tumbler'),
(99, '2026-06-16 11:29:53', 'Laporan temuan baru: Tas Ransel Eiger Hitam oleh user #4'),
(100, '2026-06-16 11:29:53', 'User #4 membuat laporan temuan: Tas Ransel Eiger Hitam'),
(101, '2026-06-16 11:30:00', 'User Rian Hidayat logout dari sistem'),
(102, '2026-06-16 11:30:17', 'User Ahmad Junaidi (Admin) login ke sistem'),
(103, '2026-06-16 11:30:47', 'Status laporan kehilangan #10 diubah menjadi Hilang'),
(104, '2026-06-16 11:30:50', 'Status laporan kehilangan #9 diubah menjadi Hilang'),
(105, '2026-06-16 11:30:53', 'Status laporan temuan #9 diubah menjadi Ditemukan'),
(106, '2026-06-16 11:30:56', 'Status laporan temuan #7 diubah menjadi Ditemukan'),
(107, '2026-06-16 11:30:59', 'Status laporan kehilangan #8 diubah menjadi Hilang'),
(108, '2026-06-16 11:31:02', 'Status laporan temuan #8 diubah menjadi Ditemukan'),
(109, '2026-06-16 11:32:49', 'Klaim baru diajukan untuk laporan #7 oleh user #1'),
(110, '2026-06-16 11:32:49', 'User #1 mengajukan klaim untuk laporan #7'),
(111, '2026-06-16 11:33:09', 'Klaim #3 diproses dengan status: Diterima');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `identitas` varchar(50) NOT NULL COMMENT 'NIM untuk mahasiswa, NIK untuk umum',
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `tipe_user` enum('mahasiswa','umum','admin') DEFAULT 'umum',
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `nama`, `identitas`, `email`, `password`, `phone`, `tipe_user`, `status`, `foto`, `created_at`) VALUES
(1, 'Ahmad Junaidi (Admin)', 'ADM-99801', 'ahmad@admin.kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '08987654321', 'admin', 'aktif', NULL, '2026-06-15 17:06:12'),
(2, 'Barkah Sholih', '2201010145', 'barkah@mhs.kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567890', 'mahasiswa', 'aktif', NULL, '2026-06-15 17:06:12'),
(3, 'Siti Rahma', '2201010199', 'siti@mhs.kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '08567891234', 'mahasiswa', 'aktif', NULL, '2026-06-15 17:06:12'),
(4, 'Rian Hidayat', '3273110512890001', 'rian.hidayat@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '089900887766', 'umum', 'aktif', NULL, '2026-06-15 17:06:12'),
(5, 'Linda Permata', '3273111005920003', 'linda.permata@yahoo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '08122334455', 'umum', 'aktif', NULL, '2026-06-15 17:06:12'),
(6, 'Atthoriq Bagus Fadillah', '4536435', 'atthoriqbagusfadillah@gmail.com', '$2y$10$GKbJoxIHJLU.liFpW7XsFOImJW1syznUFkvtpWSWyklpghGdif4vC', '0895422637486', 'umum', 'aktif', NULL, '2026-06-15 17:54:12'),
(7, 'Jaka', '4723647186', 'jaka@gmail.com', '$2y$10$sp5Jaqhf5O4058hqiuFufu8l.vRog8z3vPtC1EInqtNYb5exS5.fu', '0895422637486', 'umum', 'aktif', NULL, '2026-06-15 17:59:04'),
(8, 'Faddly', '214151234214', 'atthoriqbagusfadilla@gmail.com', '$2y$10$ifxbNgulZeDeN8vEByeQHuTtr4mQIud/.0uGrQ5dL4GDWOWDcyv3i', '0895422637486', 'mahasiswa', 'aktif', NULL, '2026-06-16 02:24:15');

--
-- Triggers `user`
--
DELIMITER $$
CREATE TRIGGER `tr_after_insert_user` AFTER INSERT ON `user` FOR EACH ROW BEGIN
    INSERT INTO log_aktivitas (pesan) 
    VALUES (CONCAT('User baru terdaftar: ', NEW.nama, ' (Tipe: ', NEW.tipe_user, ')'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_detail_klaim`
-- (See below for the actual view)
--
CREATE TABLE `v_detail_klaim` (
`id` int(11)
,`laporan_id` int(11)
,`claimant_id` int(11)
,`bukti` text
,`tanggal_klaim` date
,`status` enum('Menunggu Verifikasi','Diterima','Ditolak')
,`nama_barang` varchar(150)
,`lokasi` varchar(100)
,`penemu_id` int(11)
,`claimant_nama` varchar(150)
,`claimant_phone` varchar(20)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_laporan_aktif`
-- (See below for the actual view)
--
CREATE TABLE `v_laporan_aktif` (
`tipe` varchar(6)
,`id` int(11)
,`nama_barang` varchar(150)
,`lokasi` varchar(100)
,`tanggal` date
,`status` varchar(19)
,`pelapor_id` int(11)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_statistik_kategori`
-- (See below for the actual view)
--
CREATE TABLE `v_statistik_kategori` (
`id` int(11)
,`nama` varchar(100)
,`ikon` varchar(50)
,`total_hilang` bigint(21)
,`total_temuan` bigint(21)
);

-- --------------------------------------------------------

--
-- Structure for view `v_detail_klaim`
--
DROP TABLE IF EXISTS `v_detail_klaim`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_detail_klaim`  AS SELECT `kb`.`id` AS `id`, `kb`.`laporan_id` AS `laporan_id`, `kb`.`claimant_id` AS `claimant_id`, `kb`.`bukti` AS `bukti`, `kb`.`tanggal_klaim` AS `tanggal_klaim`, `kb`.`status` AS `status`, `lt`.`nama_barang` AS `nama_barang`, `lt`.`lokasi` AS `lokasi`, `lt`.`penemu_id` AS `penemu_id`, `u`.`nama` AS `claimant_nama`, `u`.`phone` AS `claimant_phone` FROM ((`klaim_barang` `kb` join `laporan_temuan` `lt` on(`kb`.`laporan_id` = `lt`.`id`)) join `user` `u` on(`kb`.`claimant_id` = `u`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_laporan_aktif`
--
DROP TABLE IF EXISTS `v_laporan_aktif`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_laporan_aktif`  AS SELECT 'hilang' AS `tipe`, `laporan_hilang`.`id` AS `id`, `laporan_hilang`.`nama_barang` AS `nama_barang`, `laporan_hilang`.`lokasi` AS `lokasi`, `laporan_hilang`.`tanggal` AS `tanggal`, `laporan_hilang`.`status` AS `status`, `laporan_hilang`.`user_id` AS `pelapor_id` FROM `laporan_hilang` WHERE `laporan_hilang`.`status` not in ('Selesai','Diklaim')union all select 'temuan' AS `tipe`,`laporan_temuan`.`id` AS `id`,`laporan_temuan`.`nama_barang` AS `nama_barang`,`laporan_temuan`.`lokasi` AS `lokasi`,`laporan_temuan`.`tanggal` AS `tanggal`,`laporan_temuan`.`status` AS `status`,`laporan_temuan`.`penemu_id` AS `pelapor_id` from `laporan_temuan` where `laporan_temuan`.`status` not in ('Selesai','Diklaim')  ;

-- --------------------------------------------------------

--
-- Structure for view `v_statistik_kategori`
--
DROP TABLE IF EXISTS `v_statistik_kategori`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_statistik_kategori`  AS SELECT `k`.`id` AS `id`, `k`.`nama` AS `nama`, `k`.`ikon` AS `ikon`, (select count(0) from `laporan_hilang` where `laporan_hilang`.`kategori_id` = `k`.`id`) AS `total_hilang`, (select count(0) from `laporan_temuan` where `laporan_temuan`.`kategori_id` = `k`.`id`) AS `total_temuan` FROM `kategori` AS `k` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `klaim_barang`
--
ALTER TABLE `klaim_barang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `laporan_id` (`laporan_id`),
  ADD KEY `claimant_id` (`claimant_id`);

--
-- Indexes for table `laporan_hilang`
--
ALTER TABLE `laporan_hilang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategori_id` (`kategori_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `laporan_temuan`
--
ALTER TABLE `laporan_temuan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategori_id` (`kategori_id`),
  ADD KEY `penemu_id` (`penemu_id`);

--
-- Indexes for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identitas` (`identitas`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `klaim_barang`
--
ALTER TABLE `klaim_barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `laporan_hilang`
--
ALTER TABLE `laporan_hilang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `laporan_temuan`
--
ALTER TABLE `laporan_temuan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `klaim_barang`
--
ALTER TABLE `klaim_barang`
  ADD CONSTRAINT `klaim_barang_ibfk_1` FOREIGN KEY (`laporan_id`) REFERENCES `laporan_temuan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `klaim_barang_ibfk_2` FOREIGN KEY (`claimant_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `laporan_hilang`
--
ALTER TABLE `laporan_hilang`
  ADD CONSTRAINT `laporan_hilang_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`),
  ADD CONSTRAINT `laporan_hilang_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `laporan_temuan`
--
ALTER TABLE `laporan_temuan`
  ADD CONSTRAINT `laporan_temuan_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`),
  ADD CONSTRAINT `laporan_temuan_ibfk_2` FOREIGN KEY (`penemu_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
