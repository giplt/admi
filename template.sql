CREATE TABLE `Users` (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT
, `ContactID` integer NOT NULL DEFAULT '0'
, `Email` varchar(64) NOT NULL DEFAULT ''
, `Password` varchar(64) NOT NULL DEFAULT ''
, `Status` varchar(64) NOT NULL DEFAULT ''
);
CREATE TABLE `Contacts` (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT
, `Name` varchar(64) NOT NULL DEFAULT ''
);
CREATE TABLE `Projects` (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT
, `AccountID` integer NOT NULL DEFAULT '0'
, `UserIDs` varchar(64) NOT NULL DEFAULT ''
, `Name` varchar(64) NOT NULL DEFAULT ''
);
CREATE TABLE `Accounts` (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT
, `PID` integer NOT NULL DEFAULT '0'
, `Name` varchar(64) NOT NULL DEFAULT ''
);
CREATE TABLE `TransactionTypes` (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT
, `Name` varchar(64) NOT NULL DEFAULT ''
, `Mutations` text
);
CREATE TABLE `Transactions` (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT
, `URL` varchar(63) NOT NULL DEFAULT ''
, `Type` integer NOT NULL DEFAULT '0'
, `TransactionDate` date NOT NULL DEFAULT '0000-00-00'
, `AccountingDate` date NOT NULL DEFAULT '0000-00-00'
, `ContactID` integer NOT NULL DEFAULT '0'
, `ProjectID` integer NOT NULL DEFAULT '0'
, `Reference` varchar(63) NOT NULL DEFAULT ''
);
CREATE TABLE `Mutations` (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT
, `TransactionID` integer NOT NULL DEFAULT '0'
, `AccountID` integer NOT NULL DEFAULT '0'
, `Amount` decimal(10,2) NOT NULL DEFAULT '0.00'
);
/*
CREATE TABLE `ObligationTypes` (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT
, `Name` varchar(64) NOT NULL DEFAULT ''
, `Mutations` text
);
CREATE TABLE `Obligations` (
	ID INT PRIMARY KEY     NOT NULL,
	Type           TEXT    NOT NULL  //    type=in/verkoop, lening, investering, projectverplichting (de typen verplichting hebben een niveau, bon-bank is niveau 0, projectverplichting hoogste niveau)
);
*/
INSERT INTO `Accounts` (ID,PID,Name) VALUES
(1,0,'balansrekeningen'),
(2,0,'resultaatrekeningen'),
(3,1,'bank & kas'),
(4,1,'debiteuren (te ontvangen)'),
(5,1,'crediteuren (te betalen)'),
(6,1,'btw'),
(7,1,'passiva'),
(8,1,'resultaten'),
(9,1,'reserveringen'),
(10,1,'kasverschillen'),
(11,1,'kruisposten'),
(12,2,'kosten'),
(13,2,'opbrengsten'),
(14,3,'kas'),
(15,3,'bank'),
(16,6,'afdragen 0%'),
(17,6,'afdragen 9%'),
(18,6,'afdragen 21%'),
(19,6,'vorderen'),
(20,7,'inventaris'),
(21,7,'afschijvingen'),
(22,9,'projectenfonds'),
(23,12,'huisvesting'),
(24,12,'reiskosten'),
(25,12,'arbeid'),
(26,12,'materialen'),
(27,12,'administratie'),
(28,12,'bankkosten en rente'),
(29,13,'verhuur'),
(30,13,'ontwikkeling'),
(31,13,'reiskosten'),
(32,13,'sejours'),
(33,13,'publiciteit');

INSERT INTO `TransactionTypes` (ID,Name,Mutations) VALUES
(1,'transfer', '3=1'),
(2,'purchase', '5=12+6'),
(3,'sale', '4=13+6');
