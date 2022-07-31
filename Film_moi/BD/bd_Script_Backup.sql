
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Banco de dados: `bdfilmsvs_luis`
--
CREATE DATABASE IF NOT EXISTS `bdfilmsvs_luis` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `bdfilmsvs_luis`;

-- --------------------------------------------------------

--
-- Estrutura da tabela `film`
--

DROP TABLE IF EXISTS `film`;
CREATE TABLE IF NOT EXISTS `film` (
  `PK_ID_Film` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `categorie` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `realisateur` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(400) COLLATE utf8_unicode_ci NOT NULL,
  `pochette` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`PK_ID_Film`)
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `film`
--

INSERT INTO `film` (`PK_ID_Film`, `titre`, `prix`, `categorie`, `realisateur`, `description`, `pochette`, `url`) VALUES
(137, 'Lion', '10.00', 'Drame', 'OUTLANDER', 'AAAAA', 'd11d2629add30e10beafb40f80e63834348d67d0.jpg', NULL),
(138, 'Dawnfall', '6.00', 'Action', 'OUTLANDER', 'aaaaa', 'c920ed10c2bf8f439982782d6cbdb4cbd387a0fa', ''),
(139, '007 Spectre', '5.00', 'Action', 'OUTLANDER', '007 Spectre', '9f2bb111c2dba511f37f9c91194b9ec501bd22a3.jpg', NULL),
(140, 'Fast Furious 3', '10.00', 'Romance', 'Outlander ', '    \r\n                 ', 'c3642bb51c3f51495af0b4cf8a8148a04a1b7bef', ''),
(142, 'Durkink', '22.00', 'Horreur', 'OUTLANDER', 'Durkink', 'b3fb009f5844ade5626e0c0cd08696a1f848f584.jpg', NULL),
(143, 'Grease', '10.00', 'Comedie', 'OUTLANDER', 'Grease', '3fb304fec66baa682bcda19fb76fe5130f1a9abd.jpg', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `film_location`
--

DROP TABLE IF EXISTS `film_location`;
CREATE TABLE IF NOT EXISTS `film_location` (
  `id_location` int(11) NOT NULL AUTO_INCREMENT,
  `fk_id_film` int(11) NOT NULL,
  `fk_id_membre` int(11) NOT NULL,
  PRIMARY KEY (`id_location`),
  KEY `fk_id_film` (`fk_id_film`),
  KEY `fk_id_membre` (`fk_id_membre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `membre`
--

DROP TABLE IF EXISTS `membre`;
CREATE TABLE IF NOT EXISTS `membre` (
  `PK_ID_Membre` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `prenom` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `profil` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `courriel` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `tel_membre` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `MDP_membre` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`PK_ID_Membre`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `membre`
--

INSERT INTO `membre` (`PK_ID_Membre`, `nom`, `prenom`, `profil`, `courriel`, `tel_membre`, `MDP_membre`) VALUES
(2, 'Boisvert', 'Luk', 'membre', 'membre@gmail.com', '5145460521', '1234'),
(56, 'admin', 'admin', 'admin', 'admin@admin.com', NULL, 'admin'),
(68, 'Janie', 'Racine', 'membre', 'janie@gmail.com', '5145460521', '1234');
COMMIT;
