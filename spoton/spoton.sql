-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2025 at 01:13 AM
-- Wersja serwera: 10.4.28-MariaDB
-- Wersja PHP: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `spoton`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `friends`
--

CREATE TABLE `friends` (
  `user_id` int(11) NOT NULL,
  `friend_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `friends`
--

INSERT INTO `friends` (`user_id`, `friend_id`, `created_at`) VALUES
(11, 13, '2025-03-27 22:21:11'),
(12, 13, '2025-04-09 22:54:41'),
(12, 16, '2025-03-27 21:42:10'),
(13, 11, '2025-03-27 22:21:11'),
(13, 12, '2025-04-09 22:54:41'),
(16, 12, '2025-03-27 21:42:10');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `friend_requests`
--

CREATE TABLE `friend_requests` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `friend_requests`
--

INSERT INTO `friend_requests` (`id`, `sender_id`, `receiver_id`, `status`, `created_at`) VALUES
(26, 13, 11, 'accepted', '2024-11-29 18:27:29'),
(27, 13, 12, 'accepted', '2024-11-29 18:27:34'),
(28, 13, 13, 'accepted', '2024-11-29 18:27:36'),
(29, 12, 8, 'pending', '2024-11-29 18:44:28'),
(32, 16, 15, 'pending', '2025-03-27 21:39:23'),
(33, 16, 12, 'accepted', '2025-03-27 21:39:31'),
(34, 13, 10, 'pending', '2025-03-27 22:13:44'),
(35, 13, 11, 'accepted', '2025-03-27 22:13:47'),
(36, 13, 12, 'accepted', '2025-03-27 22:13:49'),
(37, 22, 12, 'pending', '2025-03-30 10:33:24'),
(38, 12, 16, 'pending', '2025-03-30 21:52:04'),
(39, 12, 18, 'pending', '2025-03-30 21:56:46'),
(40, 12, 13, 'accepted', '2025-03-30 22:09:03'),
(41, 12, 15, 'pending', '2025-04-09 12:44:05'),
(42, 12, 11, 'pending', '2025-04-09 15:56:18');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `locations`
--

CREATE TABLE `locations` (
  `id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `location_name` varchar(255) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `user_id`, `location_name`, `latitude`, `longitude`, `category`, `image_path`, `created_at`) VALUES
(119, 13, 'a', 53.44582110, 14.50668612, '0', '', '2025-04-09 23:08:15'),
(120, 13, 'b', 53.43212470, 14.57361820, '4', '', '2025-04-09 23:08:29'),
(121, 13, 'c', 53.44331842, 14.56742999, '2', '', '2025-04-09 23:08:37'),
(122, 13, 'd', 53.43367919, 14.55550546, '18', '', '2025-04-09 23:08:43'),
(123, 13, 'e', 53.43380525, 14.56357041, '11', '', '2025-04-09 23:08:56');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `miasta`
--

CREATE TABLE `miasta` (
  `id` int(11) NOT NULL,
  `nazwa` varchar(100) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `miasta`
--

INSERT INTO `miasta` (`id`, `nazwa`, `latitude`, `longitude`) VALUES
(1, 'Warszawa', 52.22967600, 21.01222900),
(2, 'Kraków', 50.06465000, 19.94498000),
(3, 'Łódź', 51.75924800, 19.45598300),
(4, 'Wrocław', 51.10788300, 17.03853800),
(5, 'Poznań', 52.40637400, 16.92516800),
(6, 'Gdańsk', 54.35202500, 18.64663800),
(7, 'Szczecin', 53.42854300, 14.55281200),
(8, 'Bydgoszcz', 53.12348200, 18.00843800),
(9, 'Lublin', 51.24645200, 22.56844600),
(10, 'Katowice', 50.26489200, 19.02378100),
(11, 'Białystok', 53.13248800, 23.16884000),
(12, 'Gdynia', 54.51889000, 18.53054000),
(13, 'Częstochowa', 50.81181600, 19.12030900),
(14, 'Radom', 51.40272300, 21.14713300),
(15, 'Sosnowiec', 50.28626400, 19.10407900),
(16, 'Toruń', 53.01379000, 18.59844400),
(17, 'Kielce', 50.86607700, 20.62856800),
(18, 'Gliwice', 50.29449200, 18.67138000),
(19, 'Zabrze', 50.32492800, 18.78571900),
(20, 'Bytom', 50.34838100, 18.91571800),
(21, 'Bielsko-Biała', 49.82237700, 19.05838400),
(22, 'Olsztyn', 53.77842200, 20.48011900),
(23, 'Rzeszów', 50.04118700, 21.99912000),
(24, 'Ruda Śląska', 50.25582800, 18.85557100),
(25, 'Rybnik', 50.09708800, 18.54179700),
(26, 'Tychy', 50.12397300, 18.99743800),
(27, 'Dąbrowa Górnicza', 50.31809600, 19.20455600),
(28, 'Opole', 50.67510600, 17.92129800),
(29, 'Elbląg', 54.15224100, 19.40449000),
(30, 'Płock', 52.54634400, 19.70653600),
(31, 'Wałbrzych', 50.77140700, 16.28432100),
(32, 'Gorzów Wielkopolski', 52.73678800, 15.22878100),
(33, 'Włocławek', 52.64803800, 19.06779700),
(34, 'Zielona Góra', 51.93562100, 15.50618600),
(35, 'Tarnów', 50.01249300, 20.98801400),
(36, 'Chorzów', 50.30581900, 18.97420000),
(37, 'Kalisz', 51.76721800, 18.08549100),
(38, 'Koszalin', 54.19438400, 16.17222300),
(39, 'Legnica', 51.20700600, 16.15538000),
(40, 'Grudziądz', 53.48496400, 18.75362200),
(41, 'Jaworzno', 50.20555800, 19.27409600),
(42, 'Słupsk', 54.46405100, 17.02866200),
(43, 'Jastrzębie-Zdrój', 49.95027000, 18.57498700),
(44, 'Nowy Sącz', 49.61732400, 20.71580800),
(45, 'Jelenia Góra', 50.90487800, 15.71994600),
(46, 'Siedlce', 52.16744200, 22.29099800),
(47, 'Mysłowice', 50.20829400, 19.16667400),
(48, 'Konin', 52.22309800, 18.25183900),
(49, 'Piotrków Trybunalski', 51.40538600, 19.70382600),
(50, 'Inowrocław', 52.79811600, 18.26364700),
(51, 'Lubin', 51.40065200, 16.20198600),
(52, 'Ostrowiec Świętokrzyski', 50.92987800, 21.38508200),
(53, 'Gniezno', 52.53409300, 17.58255700),
(54, 'Suwałki', 54.09948100, 22.93332200),
(55, 'Ostrołęka', 53.08419800, 21.55927500),
(56, 'Stargard', 53.33683900, 15.03264500),
(57, 'Pabianice', 51.66462700, 19.35472700),
(58, 'Łomża', 53.17993800, 22.05904900),
(59, 'Leszno', 51.84153700, 16.57420800),
(60, 'Żory', 50.04503900, 18.70034000),
(61, 'Tomaszów Mazowiecki', 51.53172100, 20.00874800),
(62, 'Przemyśl', 49.78345800, 22.76968900),
(63, 'Stalowa Wola', 50.57107200, 22.05396000),
(64, 'Kędzierzyn-Koźle', 50.35004800, 18.22601500),
(65, 'Łowicz', 52.10592500, 19.94117700),
(66, 'Olkusz', 50.28163200, 19.56219100),
(67, 'Skarżysko-Kamienna', 51.11534500, 20.86748900),
(68, 'Pruszków', 52.17008300, 20.80881100),
(69, 'Świdnica', 50.84367600, 16.48977100),
(70, 'Biała Podlaska', 52.03241000, 23.11688100),
(71, 'Ciechanów', 52.88115000, 20.61473100),
(72, 'Grodzisk Mazowiecki', 52.10377000, 20.62611000),
(73, 'Brodnica', 53.25942800, 19.39953000),
(74, 'Kołobrzeg', 54.17631800, 15.57695000),
(75, 'Wągrowiec', 52.80849600, 17.19473100),
(76, 'Świecie', 53.41028700, 18.43769100),
(77, 'Zgierz', 51.85539800, 19.40937300),
(78, 'Jarosław', 50.01683600, 22.67809500),
(79, 'Bartoszyce', 54.25136100, 20.80867800),
(80, 'Piła', 53.15162800, 16.73848200),
(81, 'Biskupiec', 53.85716800, 20.94994800),
(82, 'Malbork', 54.03625400, 19.03788100),
(83, 'Nowogard', 53.67464200, 15.11657200),
(84, 'Nakło nad Notecią', 53.14175100, 17.60128600),
(85, 'Chełmno', 53.34766100, 18.42525300),
(86, 'Polkowice', 51.50352200, 16.07131100),
(87, 'Radomsko', 51.06804900, 19.44424500),
(88, 'Świnoujście', 53.91020900, 14.24725500),
(89, 'Ząbki', 52.27449200, 21.10428200),
(90, 'Żary', 51.64205700, 15.14053300),
(91, 'Pszczyna', 49.98042300, 18.94748000);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(10) NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(1000) NOT NULL,
  `currency` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `currency`) VALUES
(8, 'Nikodem', 'nikodem2287@gmail.com', 'd87d24c791fa7b14fc03a0f9c842a5d6', 0),
(9, 'Michalsmierdzipotem', 'huj@gmail.com', 'd87d24c791fa7b14fc03a0f9c842a5d6', 0),
(10, 'Nkdm', 'nkdm@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', 0),
(11, 'Nk1', 'nk1@gmail.com', '28a8437b1ab785fecd0270ca8da6c98c', 0),
(12, 'Nk', 'nk@gmail.com', '7d3bbe1a34b64921e0902868320a7ca4', 0),
(13, 'Nk2', 'nk2@gmail.com', '56b6cd74067895a84e31ca4b410a11b6', 0),
(14, '111', '111@gam.ass', '698d51a19d8a121ce581499d7b701668', 0),
(15, 'test5', 'test5@gmail.com', '098f6bcd4621d373cade4e832627b4f6', 0),
(16, 'dd', 'dd@gmail.com', '1aabac6d068eef6a7bad3fdf50a05cc8', 0),
(17, 'aaa', 'aaa@gmail.com', '47bce5c74f589f4867dbd57e9ca9f808', 0),
(18, 'helo', 'test1@gmail.com', 'c4ca4238a0b923820dcc509a6f75849b', 0),
(19, 'k_u_r_w_a', 'm@g.com', '4eea1e5de59fbc61cb3ab480dbbf6a5f', 0),
(20, 'Michal', 'mic@g', '4eea1e5de59fbc61cb3ab480dbbf6a5f', 0),
(21, 'Ukr', 'bartekswiridow@gmail.com', 'fd8f05a4f187b3a96b56f56a7bea45e1', 0),
(22, 'SYKA', 'SYLKAS75@WP.PL', '1713d839c84afc021eff2485a8652500', 0),
(23, 'Kot', 'kot@gmail.com', '91162629d258a876ee994e9233b2ad87', 0),
(24, '64654', '34543@rrew.pl', 'dd95b1ca8dca61e3ab7ca3f18bbdef78', 0);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `user_cities`
--

CREATE TABLE `user_cities` (
  `user_id` int(10) NOT NULL,
  `city_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_cities`
--

INSERT INTO `user_cities` (`user_id`, `city_id`) VALUES
(24, 1),
(21, 7),
(23, 7),
(18, 8),
(19, 12),
(20, 17),
(22, 18),
(17, 20);

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `friends`
--
ALTER TABLE `friends`
  ADD PRIMARY KEY (`user_id`,`friend_id`),
  ADD KEY `friend_id` (`friend_id`);

--
-- Indeksy dla tabeli `friend_requests`
--
ALTER TABLE `friend_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indeksy dla tabeli `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `miasta`
--
ALTER TABLE `miasta`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `user_cities`
--
ALTER TABLE `user_cities`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `city_id` (`city_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `friend_requests`
--
ALTER TABLE `friend_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `miasta`
--
ALTER TABLE `miasta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `user_cities`
--
ALTER TABLE `user_cities`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `friends`
--
ALTER TABLE `friends`
  ADD CONSTRAINT `friends_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `friends_ibfk_2` FOREIGN KEY (`friend_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `friend_requests`
--
ALTER TABLE `friend_requests`
  ADD CONSTRAINT `friend_requests_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `friend_requests_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_cities`
--
ALTER TABLE `user_cities`
  ADD CONSTRAINT `user_cities_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `miasta` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
