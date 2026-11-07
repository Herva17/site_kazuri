-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 07 nov. 2026 à 21:22
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `kazur2501119`
--

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
(1, 'Films', 'films', 'Longs et courts métrages', '2026-10-27 18:21:56'),
(2, 'Séries', 'series', 'Épisodes et saisons de séries', '2026-10-27 18:21:56'),
(3, 'Musique', 'musique', 'Clips, concerts et contenus musicaux', '2026-10-27 18:21:56'),
(4, 'Autres', 'autres', 'Autres types de contenus vidéo', '2026-10-27 18:21:56');

-- --------------------------------------------------------

--
-- Structure de la table `contributor_requests`
--

CREATE TABLE `contributor_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `motivation` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `review_comment` text DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `plans`
--

CREATE TABLE `plans` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `label` varchar(100) NOT NULL DEFAULT '',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT '$',
  `price_suffix` varchar(30) NOT NULL DEFAULT '',
  `duration_days` int(10) UNSIGNED NOT NULL,
  `description` text DEFAULT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `badge` varchar(50) DEFAULT NULL,
  `is_popular` tinyint(1) NOT NULL DEFAULT 0,
  `cta_text` varchar(100) NOT NULL DEFAULT '',
  `cta_url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ;

--
-- Déchargement des données de la table `plans`
--

INSERT INTO `plans` (`id`, `name`, `label`, `price`, `currency`, `price_suffix`, `duration_days`, `description`, `features`, `badge`, `is_popular`, `cta_text`, `cta_url`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'free', 'Découverte', 0.00, '$', '/ mois', 36500, 'Accès gratuit limité', '[{\"text\": \"3 vidéos par mois\", \"included\": true}, {\"text\": \"Qualité max 720p\", \"included\": true}, {\"text\": \"Analytics de base\", \"included\": true}, {\"text\": \"Monétisation bloquée\", \"included\": false}]', NULL, 0, 'Commencer gratuitement', 'index.php?p=studio&plan=free', 1, 1, '2026-10-27 18:21:56'),
(2, 'basic', 'Basic', 4.99, '$', '/ mois', 30, 'Abonnement mensuel basique', '[{\"text\": \"10 vidéos par mois\", \"included\": true}, {\"text\": \"Qualité 1080p\", \"included\": true}, {\"text\": \"Analytics standards\", \"included\": true}, {\"text\": \"Monétisation (50 % pour vous)\", \"included\": true}]', NULL, 0, 'Passer Basic', NULL, 2, 1, '2026-10-27 18:21:56'),
(3, 'premium', 'Premium', 9.99, '$', '/ mois', 30, 'Abonnement mensuel premium', '[{\"text\": \"Vidéos illimitées\", \"included\": true}, {\"text\": \"Qualité 4K Ultra HD\", \"included\": true}, {\"text\": \"Analytics avancés\", \"included\": true}, {\"text\": \"Monétisation (70 % pour vous)\", \"included\": true}]', 'Recommandé', 1, 'Passer Premium', NULL, 3, 1, '2026-10-27 18:21:56');

-- --------------------------------------------------------

--
-- Structure de la table `reviewers`
--

CREATE TABLE `reviewers` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `display_name` varchar(120) NOT NULL,
  `email` varchar(180) NOT NULL,
  `role` enum('moderator','editor','admin','super_admin') NOT NULL DEFAULT 'moderator',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `specialty` varchar(80) DEFAULT NULL,
  `max_daily` smallint(5) UNSIGNED NOT NULL DEFAULT 50,
  `last_seen_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'admin', 'Administrateur de la plateforme', '2026-10-27 18:21:56'),
(2, 'contributor', 'Utilisateur autorisé à publier des vidéos', '2026-10-27 18:21:56'),
(3, 'subscriber', 'Utilisateur avec un abonnement actif', '2026-10-27 18:21:56'),
(4, 'visitor', 'Utilisateur non abonné', '2026-10-27 18:21:56'),
(5, 'user', NULL, '2026-10-27 20:07:26'),
(6, 'superadmin', NULL, '2026-11-05 19:34:23');

-- --------------------------------------------------------

--
-- Structure de la table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `channel_id` bigint(20) UNSIGNED NOT NULL,
  `subscriber_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `channel_id`, `subscriber_id`, `created_at`) VALUES
(1, 9, 1, '2026-11-07 21:06:39');

-- --------------------------------------------------------

--
-- Structure de la table `suscriptions`
--

CREATE TABLE `suscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `plan_id` int(10) UNSIGNED NOT NULL,
  `status` enum('pending','active','expired','cancelled') NOT NULL DEFAULT 'pending',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `auto_renew` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `first_name`, `last_name`, `avatar_url`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'herveiragi80@gmail.com', '$2y$10$qc7S8eW/Kky.3KQSAsLWHeARlNJgT8b1tr6NeFTcuVMO2JuM2dAsS', 'Hervé', 'IRAGI', 'assets/img/av_user.jpg', 1, '2026-10-27 20:07:26', '2026-10-27 20:07:26'),
(3, 'asifiwemaombi@gmail.com', '$2y$10$NBYYkx4vP6ofKK7I7x9PcuyebN3jDDJFolJI1myBkWOSXAlTmFvvy', 'ASIFIWE', 'MAOMBI', 'assets/img/av_user.jpg', 1, '2026-10-27 23:59:57', '2026-10-27 23:59:57'),
(4, 'habamungu@gmail.com', '$2y$10$/kpLFzO6Tc0McUAVdo4nnumc3ImJd9hXT58y2G8zaSrgiHYhijzKe', 'HABAMUNGU', 'ELIEZER', 'assets/img/av_user.jpg', 1, '2026-10-28 00:07:16', '2026-10-28 00:07:16'),
(5, 'charlesduciel01@gmail.com', '$2y$10$BA2iesEzMZpYE/s/8A4tuOnQxrM.2Zmhn4h8EBtTsV2noTK42.pe2', 'charles', 'MUNGUIKO', 'assets/img/av_user.jpg', 1, '2026-10-29 09:24:30', '2026-10-29 09:24:30'),
(6, 'admin@kazuri.tv', '$2y$10$P.vObC89BOqKssvaeWsG8eGZoS0BJ7QGnD7QgVwanx9JzLAxa3c0K', 'Super', 'Admin', NULL, 1, '2026-11-05 19:34:23', '2026-11-05 19:34:23'),
(7, 'jeannette@gmail.com', '$2y$10$hZtEoU9OnNiXl8ev.9iBEOOnuZcldrTlNk.aqN8xBCWu1PKh06IM6', 'JEANNETTE', 'UMUTONI', 'assets/img/av_user.jpg', 1, '2026-11-05 22:42:41', '2026-11-05 22:42:41'),
(8, 'isaackatindi@gmail.com', '$2y$10$9RqJiQ/F609vsVtNm7s5xujkbdiagiznLqdeOBMtIPR0QWv1PbWAC', 'Isaac', 'Katindi', 'assets/img/av_user.jpg', 1, '2026-11-07 13:01:00', '2026-11-07 13:01:00'),
(9, 'sergemuhindo@gmail.com', '$2y$10$Y1MmYz0njn6bY5N9b4jeYuUmGErOOFZKNadccQqnVxPBZEoogYvGW', 'Serge', 'MUHINDO', 'assets/img/av_user.jpg', 1, '2026-11-07 13:05:33', '2026-11-07 13:05:33');

-- --------------------------------------------------------

--
-- Structure de la table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`, `assigned_at`) VALUES
(1, 5, '2026-10-27 20:07:26'),
(3, 2, '2026-10-27 23:59:57'),
(4, 2, '2026-10-28 00:07:16'),
(5, 5, '2026-10-29 09:24:30'),
(6, 6, '2026-11-05 19:34:23'),
(7, 2, '2026-11-05 22:42:41'),
(8, 5, '2026-11-07 13:01:01'),
(9, 2, '2026-11-07 13:05:33');

-- --------------------------------------------------------

--
-- Structure de la table `videos`
--

CREATE TABLE `videos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `video_url` varchar(500) NOT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `duration_seconds` int(10) UNSIGNED DEFAULT NULL,
  `views` int(10) UNSIGNED DEFAULT 0,
  `likes` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('draft','pending','approved','rejected','published') NOT NULL DEFAULT 'draft',
  `is_premium` tinyint(1) NOT NULL DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `videos`
--

INSERT INTO `videos` (`id`, `user_id`, `category_id`, `title`, `description`, `video_url`, `thumbnail_url`, `duration_seconds`, `views`, `likes`, `status`, `is_premium`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 'Richesse en enfer', 'Un film plein de leçon de la vie , qui explique que c\'est pas toute richesse qu\'on doit envier', 'uploads/videos/vid_6aece4c30cc0e.mp4', 'uploads/thumbnails/thumb_6aece4c30d899.png', 203, 9, 0, 'published', 0, '2026-11-05 21:42:27', '2026-11-05 21:19:15', '2026-11-07 13:41:38'),
(2, 4, 2, 'Livre de couple', 'Une serie de deborah machine', 'uploads/videos/vid_6aecf33b737e2.mp4', 'uploads/thumbnails/thumb_6aecf33b74016.png', 57, 5, 0, 'published', 0, '2026-11-05 22:24:04', '2026-11-05 22:20:59', '2026-11-07 13:01:57'),
(3, 7, 1, 'Patex', 'Un film de moses kanuma', 'uploads/videos/vid_6aecf8daa30ef.mp4', 'uploads/thumbnails/thumb_6aecf8daa3c16.png', 83, 5, 0, 'published', 0, '2026-11-05 23:13:48', '2026-11-05 22:44:58', '2026-11-06 22:24:32'),
(5, 7, 2, 'Par accident', 'Une serie trés edifiante', 'uploads/videos/vid_6aecfc8b49982.mp4', 'uploads/thumbnails/thumb_6aecfc8b49cd9.png', 126, 10, 0, 'published', 0, '2026-11-05 23:13:40', '2026-11-05 23:00:43', '2026-11-07 20:39:09'),
(6, 9, 2, 'Kop_mago', 'Une serie africaine trés edifiante et remplis des leçons', 'uploads/videos/vid_6aef16ddf23d5.mp4', 'uploads/thumbnails/thumb_6aef16ddf2870.png', 45, 19, 0, 'published', 0, '2026-11-07 13:21:43', '2026-11-07 13:17:33', '2026-11-07 20:46:26'),
(7, 9, 2, 'Kop_mago 2', 'Une serie africaine trés édifiante et remplis des conseils', 'uploads/videos/vid_6aef171fa7bbf.mp4', 'uploads/thumbnails/thumb_6aef171fa7fa7.png', 38, 4, 0, 'published', 0, '2026-11-07 13:21:45', '2026-11-07 13:18:39', '2026-11-07 20:38:54'),
(8, 9, 2, 'Kop_mago 3', 'Une serie africaine trés édifiantes et remplis des conseils', 'uploads/videos/vid_6aef1766b4538.mp4', 'uploads/thumbnails/thumb_6aef1766b492b.png', 61, 2, 1, 'published', 0, '2026-11-07 13:26:24', '2026-11-07 13:19:50', '2026-11-07 21:06:49');

-- --------------------------------------------------------

--
-- Structure de la table `video_likes`
--

CREATE TABLE `video_likes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `video_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `video_likes`
--

INSERT INTO `video_likes` (`id`, `video_id`, `user_id`, `created_at`) VALUES
(1, 8, 1, '2026-11-07 21:06:49');

-- --------------------------------------------------------

--
-- Structure de la table `video_reviews`
--

CREATE TABLE `video_reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `video_id` bigint(20) UNSIGNED NOT NULL,
  `reviewer_id` bigint(20) UNSIGNED NOT NULL,
  `decision` enum('approved','rejected','changes_requested') NOT NULL,
  `comment` text DEFAULT NULL,
  `reviewed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `video_views`
--

CREATE TABLE `video_views` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `video_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `watch_seconds` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_categories_name` (`name`),
  ADD UNIQUE KEY `uq_categories_slug` (`slug`);

--
-- Index pour la table `contributor_requests`
--
ALTER TABLE `contributor_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contributor_requests_user` (`user_id`),
  ADD KEY `idx_contributor_requests_status` (`status`),
  ADD KEY `idx_contributor_requests_reviewer` (`reviewed_by`);

--
-- Index pour la table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_plans_name` (`name`),
  ADD KEY `idx_plans_active_sort` (`is_active`,`sort_order`);

--
-- Index pour la table `reviewers`
--
ALTER TABLE `reviewers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_reviewers_user` (`user_id`),
  ADD UNIQUE KEY `uk_reviewers_email` (`email`),
  ADD KEY `ix_reviewers_status` (`status`),
  ADD KEY `ix_reviewers_role` (`role`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_roles_name` (`name`);

--
-- Index pour la table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_channel_sub` (`channel_id`,`subscriber_id`),
  ADD KEY `ix_channel` (`channel_id`),
  ADD KEY `ix_subscriber` (`subscriber_id`);

--
-- Index pour la table `suscriptions`
--
ALTER TABLE `suscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_suscriptions_user` (`user_id`),
  ADD KEY `idx_suscriptions_status` (`status`),
  ADD KEY `idx_suscriptions_end_date` (`end_date`),
  ADD KEY `fk_suscriptions_plan` (`plan_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- Index pour la table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `idx_user_roles_role` (`role_id`);

--
-- Index pour la table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_videos_user` (`user_id`),
  ADD KEY `idx_videos_category` (`category_id`),
  ADD KEY `idx_videos_status` (`status`),
  ADD KEY `idx_videos_published_at` (`published_at`);

--
-- Index pour la table `video_likes`
--
ALTER TABLE `video_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_video_user` (`video_id`,`user_id`),
  ADD KEY `ix_video` (`video_id`),
  ADD KEY `ix_user` (`user_id`);

--
-- Index pour la table `video_reviews`
--
ALTER TABLE `video_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_video_reviews_video` (`video_id`),
  ADD KEY `idx_video_reviews_reviewer` (`reviewer_id`);

--
-- Index pour la table `video_views`
--
ALTER TABLE `video_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ix_video` (`video_id`),
  ADD KEY `ix_created` (`created_at`),
  ADD KEY `fk_vv_user` (`user_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `contributor_requests`
--
ALTER TABLE `contributor_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reviewers`
--
ALTER TABLE `reviewers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `suscriptions`
--
ALTER TABLE `suscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `video_likes`
--
ALTER TABLE `video_likes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `video_reviews`
--
ALTER TABLE `video_reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `video_views`
--
ALTER TABLE `video_views`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `contributor_requests`
--
ALTER TABLE `contributor_requests`
  ADD CONSTRAINT `fk_contributor_requests_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_contributor_requests_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `fk_sub_channel` FOREIGN KEY (`channel_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sub_subscr` FOREIGN KEY (`subscriber_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `suscriptions`
--
ALTER TABLE `suscriptions`
  ADD CONSTRAINT `fk_suscriptions_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_suscriptions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `videos`
--
ALTER TABLE `videos`
  ADD CONSTRAINT `fk_videos_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_videos_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `video_likes`
--
ALTER TABLE `video_likes`
  ADD CONSTRAINT `fk_vl_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vl_video` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `video_reviews`
--
ALTER TABLE `video_reviews`
  ADD CONSTRAINT `fk_video_reviews_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_video_reviews_video` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `video_views`
--
ALTER TABLE `video_views`
  ADD CONSTRAINT `fk_vv_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_vv_video` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
