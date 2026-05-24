-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 24 mai 2026 à 22:15
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
-- Base de données : `gestion_agents`
--

-- --------------------------------------------------------

--
-- Structure de la table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `participant1` int(11) NOT NULL,
  `participant2` int(11) NOT NULL,
  `last_message` text DEFAULT NULL,
  `last_message_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `conversations`
--

INSERT INTO `conversations` (`id`, `participant1`, `participant2`, `last_message`, `last_message_time`, `created_at`, `updated_at`) VALUES
(1, 5, 2, 'D\'accord Mr', '2026-05-24 11:57:59', '2026-05-02 00:45:22', '2026-05-24 11:57:59'),
(2, 3, 2, 'Non, apparamment je suis hyper occupé avec le business donc je vais faire demain bro', '2026-05-02 01:56:51', '2026-05-02 01:55:15', '2026-05-02 01:56:51'),
(3, 3, 5, 'ttxchvkjihljbv b,jlhix ugugjtrddfdrsdvv', '2026-05-02 14:56:41', '2026-05-02 14:56:41', '2026-05-02 14:56:41');

-- --------------------------------------------------------

--
-- Structure de la table `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `available_quantity` int(11) DEFAULT 1,
  `location` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `equipment`
--

INSERT INTO `equipment` (`id`, `name`, `description`, `quantity`, `available_quantity`, `location`) VALUES
(1, 'Balai', 'Balai standard pour sols durs', 10, 8, 'Local technique Bâtiment A'),
(2, 'Serpillière', 'Serpillière avec manche télescopique', 8, 6, 'Local technique Bâtiment A'),
(3, 'Seau', 'Seau de 10L avec essoreuse', 8, 5, 'Local technique Bâtiment A'),
(4, 'Aspirateur', 'Aspirateur professionnel', 4, 3, 'Réserve centrale'),
(5, 'Chiffons microfibre', 'Lot de 10 chiffons', 20, 18, 'Local technique Bâtiment B'),
(6, 'Produit nettoyant', 'Produit multi-usage 5L', 6, 4, 'Réserve centrale'),
(7, 'Gants de protection', 'Gants en latex (lot 100)', 10, 8, 'Local technique Bâtiment A'),
(8, 'Masque de protection', 'Masque FFP2 (lot 50)', 8, 6, 'Local technique Bâtiment B');

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_id`, `receiver_id`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 5, 2, 'salut , pourquoi tu n\'as pas encore fais le travail que je t\'ai assigné', 1, '2026-05-02 00:45:22'),
(2, 1, 2, 5, 'Désolé Bosss, j\'étais pris par les  CC et autres et je m\'excuse sincerement pour celà', 1, '2026-05-02 01:44:48'),
(3, 2, 3, 2, 'yo bro, tu as fini avec ton taff que le respo t\'a confié ?', 1, '2026-05-02 01:55:15'),
(4, 2, 2, 3, 'Non, apparamment je suis hyper occupé avec le business donc je vais faire demain bro', 1, '2026-05-02 01:56:51'),
(5, 1, 5, 2, 'ghcjjcjhcn,xhcncxj', 1, '2026-05-02 08:40:13'),
(6, 3, 3, 5, 'ttxchvkjihljbv b,jlhix ugugjtrddfdrsdvv', 1, '2026-05-02 14:56:41'),
(7, 1, 5, 2, 'Faite le travail dans le bref delais Mr l\'agent', 1, '2026-05-24 11:57:14'),
(8, 1, 2, 5, 'D\'accord Mr', 1, '2026-05-24 11:57:59');

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 2, 'task_overdue', 'Tâche en retard', 'La tâche « Nettoyage de l\'amphi 750  » est en retard. Veuillez la terminer au plus vite.', '/my_tasks.php', 1, '2026-04-29 09:06:45'),
(2, 5, 'task_overdue', 'Tâche en retard', 'La tâche « Nettoyage de l\'amphi 750  » assignée à agent1 est en retard.', '/my_tasks.php', 1, '2026-04-29 09:06:45'),
(3, 2, 'task_overdue', 'Tâche en retard', 'La tâche « Nettoyage de l\'amphi 750  » est en retard. Veuillez la terminer au plus vite.', '/my_tasks.php', 1, '2026-05-01 23:10:07'),
(4, 5, 'task_overdue', 'Tâche en retard', 'La tâche « Nettoyage de l\'amphi 750  » assignée à agent1 est en retard.', '/my_tasks.php', 1, '2026-05-01 23:10:07'),
(5, 2, 'task_overdue', 'Tâche en retard', 'La tâche « Nettoyage de l\'amphi 750  » est en retard. Veuillez la terminer au plus vite.', '/my_tasks.php', 1, '2026-05-01 23:10:50'),
(6, 5, 'task_overdue', 'Tâche en retard', 'La tâche « Nettoyage de l\'amphi 750  » assignée à agent1 est en retard.', '/my_tasks.php', 1, '2026-05-01 23:10:50'),
(7, 5, 'comment_added', 'Nouveau commentaire', 'Sur la tâche « Nettoyage de l\'amphi 750  » : ka devara a ka guiya a gola', '/tasks.php', 1, '2026-05-01 23:40:19'),
(8, 5, 'comment_added', 'Nouveau commentaire', 'Sur la tâche « Nettoyage de l\'amphi 750  » : yeessss', '/tasks.php', 1, '2026-05-01 23:40:36'),
(9, 2, 'message', 'Nouveau message', 'Vous avez reçu un nouveau message de responsable', 'messages.php', 1, '2026-05-02 00:45:22'),
(10, 5, 'message', 'Nouveau message', 'Vous avez reçu un nouveau message de agent1', 'messages.php', 1, '2026-05-02 01:44:48'),
(11, 2, 'message', 'Nouveau message', 'Vous avez reçu un nouveau message de agent2', 'messages.php', 1, '2026-05-02 01:55:15'),
(12, 3, 'message', 'Nouveau message', 'Vous avez reçu un nouveau message de agent1', 'messages.php', 0, '2026-05-02 01:56:51'),
(13, 2, 'message', 'Nouveau message', 'Vous avez reçu un nouveau message de responsable', 'messages.php', 1, '2026-05-02 08:40:13'),
(14, 3, 'task_assigned', 'Nouvelle tâche', 'Une nouvelle tâche « go cook babana » vous a été assignée (échéance : 03/05/2026).', 'my_tasks.php', 0, '2026-05-02 08:59:50'),
(15, 5, 'task_overdue', 'Problème signalé', 'L\'agent signale un problème sur « go cook babana » : y\'a pas piment', 'tasks.php', 1, '2026-05-02 09:01:59'),
(16, 5, 'status_changed', 'Statut de tâche modifié', 'L\'agent a changé le statut de « go cook babana » en « Terminée ».', 'tasks.php', 1, '2026-05-02 09:03:05'),
(17, 3, 'status_changed', 'Statut modifié', 'Le statut de la tâche « go cook babana » a été changé en « Annulée ».', 'my_tasks.php', 0, '2026-05-02 09:05:00'),
(18, 5, 'message', 'Nouveau message', 'Vous avez reçu un nouveau message de agent2', 'messages.php', 1, '2026-05-02 14:56:41'),
(19, 5, 'report_submitted', 'Nouveau rapport', 'agent1 a soumis un rapport de travail.', 'view_reports.php', 1, '2026-05-21 13:17:44'),
(20, 5, 'report_submitted', 'Nouveau rapport', 'agent1 a soumis un rapport de travail.', 'view_reports.php', 1, '2026-05-21 13:20:12'),
(21, 2, 'report_reviewed', 'Rapport traité', 'Votre rapport « netoyage des salle classe » a été approved.', 'my_reports.php', 1, '2026-05-21 13:21:47'),
(22, 2, 'status_changed', 'Statut modifié', 'Le statut de la tâche « Nettoyage de l\'amphi 750  » a été changé en « Terminée ».', 'my_tasks.php', 1, '2026-05-21 14:44:07'),
(23, 2, 'status_changed', 'Statut modifié', 'Le statut de la tâche « Nettoyage de l\'amphi 750  » a été changé en « En cours ».', 'my_tasks.php', 1, '2026-05-21 14:44:13'),
(24, 2, 'status_changed', 'Statut modifié', 'Le statut de la tâche « Nettoyage de l\'amphi 750  » a été changé en « Terminée ».', 'my_tasks.php', 1, '2026-05-22 09:16:20'),
(25, 2, 'status_changed', 'Statut modifié', 'Le statut de la tâche « Nettoyage de l\'amphi 750  » a été changé en « En cours ».', 'my_tasks.php', 1, '2026-05-22 09:16:24'),
(26, 2, 'message', 'Nouveau message', 'Vous avez reçu un nouveau message de responsable', 'messages.php', 1, '2026-05-24 11:57:14'),
(27, 5, 'message', 'Nouveau message', 'Vous avez reçu un nouveau message de agent1', 'messages.php', 1, '2026-05-24 11:57:59'),
(28, 2, 'status_changed', 'Statut modifié', 'Le statut de la tâche « Nettoyage de l\'amphi 750  » a été changé en « Annulée ».', 'my_tasks.php', 0, '2026-05-24 12:10:03');

-- --------------------------------------------------------

--
-- Structure de la table `performance_reviews`
--

CREATE TABLE `performance_reviews` (
  `id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `review_date` date NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `overall_rating` decimal(2,1) DEFAULT NULL CHECK (`overall_rating` >= 0 and `overall_rating` <= 10),
  `quality_rating` decimal(2,1) DEFAULT NULL CHECK (`quality_rating` >= 0 and `quality_rating` <= 10),
  `punctuality_rating` decimal(2,1) DEFAULT NULL CHECK (`punctuality_rating` >= 0 and `punctuality_rating` <= 10),
  `comments` text DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `report_media`
--

CREATE TABLE `report_media` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` enum('image','video') NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `report_media`
--

INSERT INTO `report_media` (`id`, `report_id`, `file_name`, `file_path`, `file_type`, `file_size`, `created_at`) VALUES
(1, 1, '6a0f05f86793f.jpg', 'uploads/reports/6a0f05f86793f.jpg', 'image', 65282, '2026-05-21 13:17:44'),
(2, 2, '6a0f068c7fe70.mp4', 'uploads/reports/6a0f068c7fe70.mp4', 'video', 14186135, '2026-05-21 13:20:12');

-- --------------------------------------------------------

--
-- Structure de la table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `status` enum('planifie','en_cours','termine','annule') DEFAULT 'planifie',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `schedules`
--

INSERT INTO `schedules` (`id`, `agent_id`, `title`, `description`, `start_datetime`, `end_datetime`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 2, 'Nettoyage du décanat', 'nettoyer proprement', '2026-04-27 16:00:00', '2026-04-27 18:30:00', 'en_cours', 5, '2026-04-27 00:25:33', '2026-04-27 00:33:59'),
(2, 3, 'prefa', 'ghhjhbvfu', '2026-04-27 14:20:00', '2026-04-27 16:20:00', 'planifie', 5, '2026-04-27 08:14:29', '2026-04-27 08:14:29'),
(3, 3, 'nettoyer le decanat', 'toile d\'araignée ', '2026-05-04 19:40:00', '2026-05-02 18:10:00', 'en_cours', 5, '2026-05-02 15:09:22', '2026-05-02 15:10:44');

-- --------------------------------------------------------

--
-- Structure de la table `schedule_types`
--

CREATE TABLE `schedule_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `color` varchar(7) DEFAULT '#667eea',
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `schedule_types`
--

INSERT INTO `schedule_types` (`id`, `name`, `color`, `description`) VALUES
(1, 'Matin', '#4CAF50', 'Travail du matin (08:00 - 12:00)'),
(2, 'Après-midi', '#FF9800', 'Travail de l\'après-midi (13:00 - 17:00)'),
(3, 'Soirée', '#9C27B0', 'Travail du soir (18:00 - 22:00)'),
(4, 'Nettoyage général', '#2196F3', 'Nettoyage complet des locaux'),
(5, 'Maintenance', '#F44336', 'Tâches de maintenance spécifiques');

-- --------------------------------------------------------

--
-- Structure de la table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `equipment_needed` text DEFAULT NULL,
  `priority` enum('basse','moyenne','haute','urgente') DEFAULT 'moyenne',
  `status` enum('a_faire','en_cours','en_retard','terminee','annulee') DEFAULT 'a_faire',
  `assigned_to` int(11) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `due_date` date NOT NULL,
  `completed_date` date DEFAULT NULL,
  `estimated_hours` decimal(5,2) DEFAULT NULL,
  `actual_hours` decimal(5,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `equipment_needed`, `priority`, `status`, `assigned_to`, `assigned_by`, `start_date`, `due_date`, `completed_date`, `estimated_hours`, `actual_hours`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'Nettoyage de l\'amphi 750 ', 'nettoyer les toiles d\'arraignées', 'Array', 'urgente', 'annulee', 2, 5, '2026-04-27', '2026-04-28', NULL, 4.00, 0.00, NULL, '2026-04-27 03:15:02', '2026-05-24 12:10:03'),
(2, 'go cook babana', 'j\'ai faim', NULL, 'urgente', 'annulee', 3, 5, '2026-05-02', '2026-05-03', NULL, 0.50, 0.00, '[SIGNALÉ PAR AGENT - 02/05/2026 11:01] y\'a pas piment\n\nj\'aime le poulet', '2026-05-02 08:59:50', '2026-05-02 09:05:00');

-- --------------------------------------------------------

--
-- Structure de la table `task_comments`
--

CREATE TABLE `task_comments` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `task_comments`
--

INSERT INTO `task_comments` (`id`, `task_id`, `user_id`, `comment`, `created_at`) VALUES
(1, 1, 5, 'merci', '2026-04-27 03:15:33'),
(2, 1, 2, 'JE FERAI PLUTARD BOSS', '2026-05-01 23:08:25'),
(3, 1, 2, 'JE FERAI PLUTARD BOSS', '2026-05-01 23:08:37'),
(4, 1, 2, 'JE FERAI PLUTARD BOSS', '2026-05-01 23:08:48'),
(5, 1, 2, 'yo', '2026-05-01 23:22:04'),
(6, 1, 2, 'yoga', '2026-05-01 23:22:29'),
(7, 1, 2, 'ka devara a ka guiya a gola', '2026-05-01 23:36:53'),
(8, 1, 2, 'ka devara a ka guiya a gola', '2026-05-01 23:40:19'),
(9, 1, 2, 'yeessss', '2026-05-01 23:40:36');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('responsable','agent') DEFAULT 'agent',
  `telephone` varchar(20) DEFAULT NULL,
  `secteur` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_photo` varchar(255) DEFAULT NULL,
  `is_approved` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `telephone`, `secteur`, `created_at`, `updated_at`, `profile_photo`, `is_approved`) VALUES
(2, 'agent1', '$2y$10$.5anjbrLA/.BBVerjuIt9.HZluGBGSY4bD5pz1W1T5qBWSHEy5ZtO', 'agent1@faculte.sciences', 'agent', '5545566', 'Bâtiment A - Niveau 1', '2026-04-26 23:58:38', '2026-05-24 11:48:05', '6a0f0d5bf3568.jpg', 0),
(3, 'agent2', '$2y$10$.5anjbrLA/.BBVerjuIt9.HZluGBGSY4bD5pz1W1T5qBWSHEy5ZtO', 'agent2@faculte.sciences', 'agent', '77 345 67 89', 'Bâtiment B - Laboratoires', '2026-04-26 23:58:38', '2026-04-26 23:58:38', NULL, 0),
(5, 'responsable', '$2y$10$pTLHnqe.X9nUyqkSkLFOouCDw/MNQsseTgZDFxg/p1h37iXiulr76', 'responsable@faculte.sciences', 'responsable', '77 123 45 67', 'Bâtiment Administratif', '2026-04-27 00:02:30', '2026-05-21 13:46:17', '6a0f0ca983174.jpg', 0),
(10, 'admin', '$2y$10$nu9oaWpfFIDw3ARKxYfIxeS10hrxhGEJNoYRaymKJi47wkjwB7XEW', 'hanazi@gmail.com', 'agent', '695246798', 'Bâtiment Administratif', '2026-05-21 11:52:45', '2026-05-21 11:52:45', NULL, 0),
(11, 'hanazi', '$2y$10$XYcbUtBPHMAdaJ6laQZICeU/oxRjaOS4J3E3mscqfg2I9vU1z6iUK', 'hanazi@mail.com', 'responsable', 'rzatyziyy', '', '2026-05-24 13:08:05', '2026-05-24 19:19:40', '6a134f4c66640.jpg', 0),
(12, 'JEAN MARC', '$2y$10$vNJWe./QqNBl3L78XXcuseb5kbn0n4osvWRA1X1D7.tjG7G4I7yne', 'jean@com.com', 'responsable', '467123456', '', '2026-05-24 19:37:53', '2026-05-24 19:37:53', NULL, 0),
(13, 'dovara', '$2y$10$grGJOG/oCck.8OX6iHG9Eu1AZS5.4il.u6KUtpYx0Y48iECzJEKMy', 'dovara@gmail.com', 'responsable', '5545566', '', '2026-05-24 19:42:13', '2026-05-24 19:42:13', NULL, 0),
(14, 'dowi', '$2y$10$Dua2tMlVolG2DHM8HlUXm.tnlOMRb./tEuFxFmwtpj0nNEiKaiCte', 'dowi@gmail.com', 'responsable', '12345678uuzhejz', '', '2026-05-24 20:04:18', '2026-05-24 20:04:18', NULL, 0);

-- --------------------------------------------------------

--
-- Structure de la table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `work_logs`
--

CREATE TABLE `work_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `task_id` int(11) DEFAULT NULL,
  `work_date` date NOT NULL,
  `hours_worked` decimal(4,2) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `work_reports`
--

CREATE TABLE `work_reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `work_date` date NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `hours_spent` decimal(4,2) DEFAULT NULL,
  `status` enum('draft','submitted','approved','rejected') DEFAULT 'submitted',
  `admin_comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `work_reports`
--

INSERT INTO `work_reports` (`id`, `user_id`, `title`, `description`, `work_date`, `location`, `hours_spent`, `status`, `admin_comment`, `created_at`, `updated_at`) VALUES
(1, 2, 'netoyage du toile d\'arraignée', 'ya pas de balai, manque de raclette', '2026-05-21', 'decanat', 2.00, 'submitted', NULL, '2026-05-21 13:17:44', '2026-05-21 13:17:44'),
(2, 2, 'netoyage des salle classe', 'on veut à manger dans chaque lieu de travail', '2026-05-21', 'prefa', 1.00, 'approved', '', '2026-05-21 13:20:12', '2026-05-21 13:21:47');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_conversation` (`participant1`,`participant2`),
  ADD KEY `participant2` (`participant2`),
  ADD KEY `idx_participants` (`participant1`,`participant2`);

--
-- Index pour la table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `idx_conversation` (`conversation_id`),
  ADD KEY `idx_receiver_read` (`receiver_id`,`is_read`),
  ADD KEY `idx_created` (`created_at`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_created` (`created_at`);

--
-- Index pour la table `performance_reviews`
--
ALTER TABLE `performance_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `idx_agent_period` (`agent_id`,`period_start`,`period_end`);

--
-- Index pour la table `report_media`
--
ALTER TABLE `report_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`);

--
-- Index pour la table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_agent_dates` (`agent_id`,`start_datetime`,`end_datetime`),
  ADD KEY `idx_status` (`status`);

--
-- Index pour la table `schedule_types`
--
ALTER TABLE `schedule_types`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `idx_assigned_to` (`assigned_to`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_due_date` (`due_date`);

--
-- Index pour la table `task_comments`
--
ALTER TABLE `task_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_task` (`task_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `work_logs`
--
ALTER TABLE `work_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `idx_user_date` (`user_id`,`work_date`);

--
-- Index pour la table `work_reports`
--
ALTER TABLE `work_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_date` (`user_id`,`work_date`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT pour la table `performance_reviews`
--
ALTER TABLE `performance_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `report_media`
--
ALTER TABLE `report_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `schedule_types`
--
ALTER TABLE `schedule_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `task_comments`
--
ALTER TABLE `task_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `work_logs`
--
ALTER TABLE `work_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `work_reports`
--
ALTER TABLE `work_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`participant1`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_ibfk_2` FOREIGN KEY (`participant2`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `performance_reviews`
--
ALTER TABLE `performance_reviews`
  ADD CONSTRAINT `performance_reviews_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `performance_reviews_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `report_media`
--
ALTER TABLE `report_media`
  ADD CONSTRAINT `report_media_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `work_reports` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedules_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `task_comments`
--
ALTER TABLE `task_comments`
  ADD CONSTRAINT `task_comments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `work_logs`
--
ALTER TABLE `work_logs`
  ADD CONSTRAINT `work_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `work_logs_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `work_reports`
--
ALTER TABLE `work_reports`
  ADD CONSTRAINT `work_reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
