-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 25 jan. 2022 à 16:18
-- Version du serveur : 10.4.21-MariaDB
-- Version de PHP : 8.0.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `hazel`
--

-- --------------------------------------------------------

--
-- Structure de la table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `clientId` int(11) NOT NULL,
  `photographeId` int(11) NOT NULL,
  `isConfirmedByClient` tinyint(1) NOT NULL DEFAULT 1,
  `isConfirmedByPhotographe` tinyint(1) NOT NULL DEFAULT 0,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `appointments`
--

INSERT INTO `appointments` (`id`, `clientId`, `photographeId`, `isConfirmedByClient`, `isConfirmedByPhotographe`, `date`) VALUES
(21, 38, 39, 1, 0, '2022-02-18 10:55:00'),
(22, 42, 40, 1, 0, '2022-01-31 09:20:00'),
(23, 41, 40, 1, 0, '2022-03-09 10:00:00'),
(24, 38, 40, 1, 0, '2022-03-31 18:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `pictures`
--

CREATE TABLE `pictures` (
  `id` int(11) NOT NULL,
  `src` int(11) NOT NULL,
  `idUser` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `post`
--

CREATE TABLE `post` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `mediaUrl` varchar(255) NOT NULL,
  `mediaType` enum('video','image') NOT NULL,
  `text` text NOT NULL,
  `isComment` tinyint(1) NOT NULL,
  `postId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `birthday` date DEFAULT NULL,
  `isVerified` tinyint(1) NOT NULL DEFAULT 0,
  `placeForAppointments` varchar(300) DEFAULT NULL,
  `role` enum('Photographe','Modele','admin') NOT NULL DEFAULT 'Modele',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `email` varchar(255) NOT NULL,
  `description` varchar(1000) DEFAULT 'Cet utilisateur n''a pas de description',
  `phone` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `name`, `birthday`, `isVerified`, `placeForAppointments`, `role`, `createdAt`, `updatedAt`, `email`, `description`, `phone`) VALUES
(38, 'mboreha', 'd751f76b33d539f5f12db4e3c006e7f05821591487321469f847312936f2dd4a', 'Mboreha ', NULL, 0, NULL, 'admin', '2022-01-24 21:01:54', '2022-01-24 21:01:54', 'mboreha@gmail.com', 'Cet utilisateur n\'a pas de description', NULL),
(39, 'bilal', 'd751f76b33d539f5f12db4e3c006e7f05821591487321469f847312936f2dd4a', 'Bilal Ahamed', '2013-05-14', 0, 'Dans la rue ', 'Photographe', '2022-01-24 21:02:35', '2022-01-24 21:02:35', 'bilal@gmail.com', 'Venez c\'est bilal, ça sera super, je suis un chouette photographe', '0603075657'),
(40, 'naila', 'd751f76b33d539f5f12db4e3c006e7f05821591487321469f847312936f2dd4a', 'Naila Ahamed', NULL, 0, NULL, 'Photographe', '2022-01-24 21:07:10', '2022-01-24 21:07:10', 'naila@gmail.com', 'Cet utilisateur n\'a pas de description', NULL),
(41, 'nadia', 'd751f76b33d539f5f12db4e3c006e7f05821591487321469f847312936f2dd4a', 'Nadia Ahamed', NULL, 0, NULL, 'Modele', '2022-01-25 11:37:53', '2022-01-25 11:37:53', 'nadia@gmail.com', 'Cet utilisateur n\'a pas de description', NULL),
(42, 'belaid', 'd751f76b33d539f5f12db4e3c006e7f05821591487321469f847312936f2dd4a', 'Belaid Ahamed', NULL, 0, NULL, 'Modele', '2022-01-25 11:38:11', '2022-01-25 11:38:11', 'belaid@gmail.com', 'Cet utilisateur n\'a pas de description', NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `fk_freelance_apptm` (`photographeId`),
  ADD KEY `fk_client_apptm` (`clientId`);

--
-- Index pour la table `pictures`
--
ALTER TABLE `pictures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_userId` (`idUser`);

--
-- Index pour la table `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `fk_user` (`userId`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_placeid` (`placeForAppointments`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `pictures`
--
ALTER TABLE `pictures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `post`
--
ALTER TABLE `post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_client_apptm` FOREIGN KEY (`clientId`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `fk_freelance_apptm` FOREIGN KEY (`photographeId`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `pictures`
--
ALTER TABLE `pictures`
  ADD CONSTRAINT `fk_userId` FOREIGN KEY (`idUser`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `post`
--
ALTER TABLE `post`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`userId`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
