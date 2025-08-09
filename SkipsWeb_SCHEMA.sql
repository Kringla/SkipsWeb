CREATE DATABASE  IF NOT EXISTS `gerhard_skip` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `gerhard_skip`;
-- MySQL dump 10.13  Distrib 8.0.38, for Win64 (x86_64)
--
-- Host: hostmaster.onnet.no    Database: gerhard_skip
-- ------------------------------------------------------
-- Server version	8.0.39-cll-lve

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `tblFartNavn`
--

DROP TABLE IF EXISTS `tblFartNavn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblFartNavn` (
  `FartNavn_ID` int NOT NULL AUTO_INCREMENT,
  `FartObj_ID` int NOT NULL DEFAULT '1',
  `FartNavn` varchar(100) DEFAULT NULL,
  `FartType_ID` int DEFAULT NULL,
  `PennantTiln` varchar(50) DEFAULT NULL,
  `TidlNavn` varchar(255) DEFAULT NULL,
  `FartNotater` mediumtext,
  KEY `FartNavn_ID` (`FartNavn_ID`),
  KEY `FartObj_ID` (`FartObj_ID`),
  KEY `FartTypeNavn` (`FartType_ID`),
  CONSTRAINT `FartTypeNavn` FOREIGN KEY (`FartType_ID`) REFERENCES `tblzFartType` (`FartType_ID`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `ObjID` FOREIGN KEY (`FartObj_ID`) REFERENCES `tblFartObj` (`FartObj_ID`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=10360 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblFartObj`
--

DROP TABLE IF EXISTS `tblFartObj`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblFartObj` (
  `FartObj_ID` int NOT NULL AUTO_INCREMENT,
  `NavnObj` varchar(100) DEFAULT NULL,
  `FartType_ID` int DEFAULT '1',
  `IMO` int DEFAULT NULL,
  `Kontrahert` varchar(255) DEFAULT NULL,
  `Kjolstrukket` varchar(255) DEFAULT NULL,
  `Sjosatt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `Levert` varchar(255) DEFAULT NULL,
  `Bygget` varchar(255) DEFAULT NULL,
  `LeverID` int DEFAULT '1',
  `SkrogID` int DEFAULT '1',
  `BnrSkrog` varchar(255) DEFAULT NULL,
  `StroketYear` smallint DEFAULT NULL,
  `StroketID` int DEFAULT NULL,
  `ObjNotater` mediumtext,
  `IngenData` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`FartObj_ID`),
  KEY `LeverID` (`LeverID`),
  KEY `SkrogID` (`SkrogID`),
  KEY `FartTypeObj` (`FartType_ID`),
  CONSTRAINT `FartTypeObj` FOREIGN KEY (`FartType_ID`) REFERENCES `tblzFartType` (`FartType_ID`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=9632 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblFartSpes`
--

DROP TABLE IF EXISTS `tblFartSpes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblFartSpes` (
  `FartSpes_ID` int NOT NULL AUTO_INCREMENT,
  `FartObj_ID` int NOT NULL DEFAULT '1',
  `YearSpes` smallint DEFAULT NULL,
  `MndSpes` tinyint DEFAULT NULL,
  `Verft_ID` int DEFAULT NULL,
  `Byggenr` varchar(255) DEFAULT NULL,
  `SkrogID` int DEFAULT NULL,
  `BnrSkrog` varchar(255) DEFAULT NULL,
  `Materiale` varchar(255) DEFAULT NULL,
  `FartMat_ID` int DEFAULT NULL,
  `FartType_ID` int DEFAULT NULL,
  `FartFunk_ID` int DEFAULT NULL,
  `FartSkrog_ID` int DEFAULT NULL,
  `FartDrift_ID` int DEFAULT NULL,
  `FunkDetalj` varchar(255) DEFAULT NULL,
  `TeknDetalj` varchar(255) DEFAULT NULL,
  `FartKlasse_ID` int DEFAULT NULL,
  `Fartklasse` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `Kapasitet` varchar(255) DEFAULT NULL,
  `Rigg` varchar(50) DEFAULT NULL,
  `FartRigg_ID` int DEFAULT NULL,
  `FartMotor_ID` int DEFAULT NULL,
  `MotorDetalj` varchar(50) DEFAULT NULL,
  `MotorEff` varchar(20) DEFAULT NULL,
  `MaxFart` smallint DEFAULT NULL,
  `Lengde` smallint DEFAULT NULL,
  `Bredde` smallint DEFAULT NULL,
  `Dypg` smallint DEFAULT NULL,
  `Tonnasje` varchar(255) DEFAULT NULL,
  `Drektigh` varchar(255) DEFAULT NULL,
  `TonnEnh_ID` int DEFAULT NULL,
  `Objekt` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`FartSpes_ID`),
  KEY `FartObj_ID` (`FartObj_ID`),
  KEY `Verft_ID` (`Verft_ID`),
  KEY `SkrogID` (`SkrogID`),
  KEY `FartTypeSpes` (`FartType_ID`),
  CONSTRAINT `FartTypeSpes` FOREIGN KEY (`FartType_ID`) REFERENCES `tblzFartType` (`FartType_ID`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `SkrogSpes` FOREIGN KEY (`SkrogID`) REFERENCES `tblVerft` (`Verft_ID`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `VerftSpes` FOREIGN KEY (`Verft_ID`) REFERENCES `tblVerft` (`Verft_ID`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=9812 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblFartTid`
--

DROP TABLE IF EXISTS `tblFartTid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblFartTid` (
  `FartTid_ID` int NOT NULL AUTO_INCREMENT,
  `YearTid` smallint DEFAULT NULL,
  `MndTid` tinyint DEFAULT NULL,
  `FartObj_ID` int DEFAULT NULL,
  `FartNavn_ID` int DEFAULT NULL,
  `FartSpes_ID` int DEFAULT NULL,
  `Objekt` tinyint(1) DEFAULT NULL,
  `Rederi` varchar(255) DEFAULT NULL,
  `Nasjon_ID` int DEFAULT NULL,
  `RegHavn` varchar(50) DEFAULT NULL,
  `MMSI` varchar(15) DEFAULT NULL,
  `Kallesignal` varchar(15) DEFAULT NULL,
  `Fiskerinr` varchar(255) DEFAULT NULL,
  `Navning` tinyint(1) DEFAULT NULL,
  `Eierskifte` tinyint(1) DEFAULT NULL,
  `Annet` tinyint(1) DEFAULT NULL,
  `Hendelse` varchar(255) DEFAULT NULL,
  `Historie` mediumtext,
  PRIMARY KEY (`FartTid_ID`),
  KEY `FartObj_ID` (`FartObj_ID`),
  KEY `FartNavn_ID` (`FartNavn_ID`),
  KEY `FartSpes_ID` (`FartSpes_ID`),
  KEY `Nasjon_ID` (`Nasjon_ID`),
  CONSTRAINT `FartIDTid` FOREIGN KEY (`FartNavn_ID`) REFERENCES `tblFartNavn` (`FartNavn_ID`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `NasjonIDTid` FOREIGN KEY (`Nasjon_ID`) REFERENCES `tblzNasjon` (`Nasjon_ID`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `ObjIDTid` FOREIGN KEY (`FartObj_ID`) REFERENCES `tblFartObj` (`FartObj_ID`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `SpesIDTid` FOREIGN KEY (`FartSpes_ID`) REFERENCES `tblFartSpes` (`FartSpes_ID`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=11427 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblVerft`
--

DROP TABLE IF EXISTS `tblVerft`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblVerft` (
  `Verft_ID` int NOT NULL AUTO_INCREMENT,
  `VerftNavn` varchar(255) DEFAULT NULL,
  `Sted` varchar(255) DEFAULT NULL,
  `Nasjon_ID` int DEFAULT NULL,
  `TidlID` int DEFAULT NULL,
  `Etablert` smallint DEFAULT NULL,
  `Nedlagt` smallint DEFAULT NULL,
  `EtterID` int DEFAULT NULL,
  `Merknad` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Verft_ID`) USING BTREE,
  KEY `Nasjon_ID` (`Nasjon_ID`),
  CONSTRAINT `NasjonIDVerft` FOREIGN KEY (`Nasjon_ID`) REFERENCES `tblzNasjon` (`Nasjon_ID`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=1662 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblxFartLink`
--

DROP TABLE IF EXISTS `tblxFartLink`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblxFartLink` (
  `FartLk_ID` int NOT NULL AUTO_INCREMENT,
  `FartID` int NOT NULL DEFAULT '1',
  `LinkType_ID` int DEFAULT NULL,
  `LinkType` varchar(50) DEFAULT NULL,
  `LinkInnh` varchar(50) DEFAULT NULL,
  `Link` mediumtext,
  `SerNo` smallint DEFAULT NULL,
  PRIMARY KEY (`FartLk_ID`) USING BTREE,
  KEY `FartID` (`FartID`)
) ENGINE=InnoDB AUTO_INCREMENT=1831 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblxVerftLink`
--

DROP TABLE IF EXISTS `tblxVerftLink`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblxVerftLink` (
  `VerftLk_ID` int NOT NULL AUTO_INCREMENT,
  `Verft_ID` int NOT NULL DEFAULT '1',
  `LinkType_ID` int DEFAULT NULL,
  `LinkType` varchar(50) DEFAULT NULL,
  `LinkInnh` varchar(50) DEFAULT NULL,
  `Link` mediumtext,
  PRIMARY KEY (`VerftLk_ID`),
  KEY `Verft_ID` (`Verft_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblzFartDrift`
--

DROP TABLE IF EXISTS `tblzFartDrift`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblzFartDrift` (
  `FartDrift_ID` int NOT NULL AUTO_INCREMENT,
  `DriftMiddel` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`FartDrift_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblzFartFunk`
--

DROP TABLE IF EXISTS `tblzFartFunk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblzFartFunk` (
  `FartFunk_ID` int NOT NULL AUTO_INCREMENT,
  `TypeFunksjon` varchar(255) DEFAULT NULL,
  `FunkDet` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`FartFunk_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblzFartKlasse`
--

DROP TABLE IF EXISTS `tblzFartKlasse`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblzFartKlasse` (
  `FartKlasse_ID` int NOT NULL AUTO_INCREMENT,
  `Klasse` tinyint(1) DEFAULT NULL,
  `TypeKlasse` varchar(255) DEFAULT NULL,
  `TypeKlasseNavn` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`FartKlasse_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblzFartMat`
--

DROP TABLE IF EXISTS `tblzFartMat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblzFartMat` (
  `FartMat_ID` int NOT NULL AUTO_INCREMENT,
  `MatFork` varchar(50) DEFAULT NULL,
  `Materiale` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`FartMat_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblzFartMotor`
--

DROP TABLE IF EXISTS `tblzFartMotor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblzFartMotor` (
  `FartMotor_ID` int NOT NULL AUTO_INCREMENT,
  `MotorFork` varchar(10) DEFAULT NULL,
  `MotorDetalj` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`FartMotor_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblzFartRigg`
--

DROP TABLE IF EXISTS `tblzFartRigg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblzFartRigg` (
  `FartRigg_ID` int NOT NULL AUTO_INCREMENT,
  `RiggFork` varchar(3) DEFAULT NULL,
  `RiggDetalj` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`FartRigg_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblzFartSkrog`
--

DROP TABLE IF EXISTS `tblzFartSkrog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblzFartSkrog` (
  `FartSkrog_ID` int NOT NULL AUTO_INCREMENT,
  `TypeSkrog` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`FartSkrog_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblzFartType`
--

DROP TABLE IF EXISTS `tblzFartType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblzFartType` (
  `FartType_ID` int NOT NULL AUTO_INCREMENT,
  `TypeFork` varchar(3) DEFAULT NULL,
  `Type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`FartType_ID`),
  UNIQUE KEY `TypeFork` (`TypeFork`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblzLinkType`
--

DROP TABLE IF EXISTS `tblzLinkType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblzLinkType` (
  `LinkType_ID` int NOT NULL AUTO_INCREMENT,
  `LinkType` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`LinkType_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblzNasjon`
--

DROP TABLE IF EXISTS `tblzNasjon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblzNasjon` (
  `Nasjon_ID` int NOT NULL AUTO_INCREMENT,
  `Nasjon` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Nasjon_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblzStroket`
--

DROP TABLE IF EXISTS `tblzStroket`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblzStroket` (
  `Stroket_ID` int NOT NULL AUTO_INCREMENT,
  `Strok` varchar(255) DEFAULT NULL,
  `StrokDetalj` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Stroket_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblzTonnEnh`
--

DROP TABLE IF EXISTS `tblzTonnEnh`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblzTonnEnh` (
  `TonnEnh_ID` int NOT NULL AUTO_INCREMENT,
  `TonnFork` varchar(5) DEFAULT NULL,
  `TonnDetalj` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`TonnEnh_ID`),
  UNIQUE KEY `TonnFork` (`TonnFork`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblzUser`
--

DROP TABLE IF EXISTS `tblzUser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblzUser` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'USR',
  `isactive` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastused` datetime NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-08-03 10:21:06
