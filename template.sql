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
, `Address` varchar(64) NOT NULL DEFAULT ''
, `Zipcode` varchar(64) NOT NULL DEFAULT ''
, `City` varchar(64) NOT NULL DEFAULT ''
, `Country` varchar(64) NOT NULL DEFAULT ''
, `VATnumber` varchar(64) NOT NULL DEFAULT ''
, `RegistrationNumber` varchar(64) NOT NULL DEFAULT ''
, `Phone` varchar(64) NOT NULL DEFAULT ''
, `Email` varchar(64) NOT NULL DEFAULT ''
, `Member` varchar NOT NULL DEFAULT ''
);
CREATE TABLE `Projects` (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT
, `AccountID` integer NOT NULL DEFAULT '0'
, `UserIDs` varchar(64) NOT NULL DEFAULT ''
, `Status` varchar(64) NOT NULL DEFAULT ''
, `Name` varchar(64) NOT NULL DEFAULT ''
);
CREATE TABLE `Accounts` (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT
, `PID` integer NOT NULL DEFAULT '0'
, `RGS` varchar(32) NOT NULL DEFAULT ''
, `Name` varchar(64) NOT NULL DEFAULT ''
);

--Generic values for all entries
CREATE TABLE `Entries` (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT 
, `TransactionDate` date NOT NULL DEFAULT '0000-00-00' 	-- date of invoice, bank transfer etc
, `AccountingDate` date NOT NULL DEFAULT '0000-00-00' 	-- date of uploading into accounting
, `PeriodFrom` date NOT NULL DEFAULT '0000-00-00'
, `PeriodTo` date NOT NULL DEFAULT '0000-00-00'
, `URL` varchar(63) NOT NULL DEFAULT ''			-- URL or location of the entry , transaction list line x, invoice 201, purchases folder 1
, `Log` text NOT NULL DEFAULT ''
);

--BOOKS: purchases, sales, bank, memorial

--properties of purchases
CREATE TABLE 'Purchases' (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT 
, `EntryID` integer NOT NULL DEFAULT ''
, `Status` integer NOT NULL DEFAULT '0'			-- status in review process
, `Reference` varchar(63) NOT NULL DEFAULT ''		-- reference number or description
, `ContactID` integer NOT NULL DEFAULT '0'		-- ID of contact that submits the reciept, recieves the invoice, or to/from bank transfer
, `SupplierID` integer NOT NULL DEFAULT '0'		-- in case of a declaration, the supplier is different from the contactID
, `ProjectID` integer NOT NULL DEFAULT '0'		-- ID of project
, `Charge` integer Not NULL DEFAULT '0'			-- If the expense/purchase needs to be charged on the invoice for the project 0=no,1=to charge,2=charged
);

--properties of sales
CREATE TABLE 'Sales' (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT 
, `EntryID` integer NOT NULL DEFAULT ''
, `Status` integer NOT NULL DEFAULT '0'			-- status in review process
, `Reference` varchar(63) NOT NULL DEFAULT ''		-- reference number or description
, `ContactID` integer NOT NULL DEFAULT '0'		-- ID of contact that recieves the invoice
, `ProjectID` integer NOT NULL DEFAULT '0'		-- ID of project
);

--properties of a bank transaction
CREATE TABLE 'Bank' (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT 	
, `EntryID` integer NOT NULL DEFAULT ''
, `StatementID` integer NOT NULL DEFAULT ''			-- used to prevent double import of bank statements
, `Description` varchar(63) NOT NULL DEFAULT ''
, `FromPaymentEndpointID` integer NOT NULL DEFAULT '0'
, `ToPaymentEndpointID` integer NOT NULL DEFAULT '0'
);

--properties of a memorial				-- Memorial has no status, always done by admin
CREATE TABLE 'Memorial' (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT 
, `EntryID` integer NOT NULL DEFAULT ''
, `Description` varchar(63) NOT NULL DEFAULT ''		-- Larger description to explain booking
, `ContactID` integer DEFAULT '0'			-- ID of contact that recieves the invoice
, `ProjectID` integer DEFAULT '0'			-- ID of project
);

CREATE TABLE `Transactions` (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT
, `EntryID` integer NOT NULL DEFAULT '0'		--refers to the entry to which the transaction belongs
, `MergeID`integer DEFAULT NULL				    --merging on the level of transactions? 		
);

CREATE TABLE `Mutations` (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT
, `TransactionID` integer NOT NULL DEFAULT '0'		--refers to the transaction to which the mutation belongs
, `AccountID` integer NOT NULL DEFAULT '0'		    --the account to which the mutation should be booked
, `Amount` decimal(10,2) NOT NULL DEFAULT '0.00'	--the amount
);

CREATE TABLE `PaymentProviders` (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT
, `AccountID` integer NOT NULL                  --account ID in accounting structure (PID=3)
, `Name` varchar(63) NOT NULL DEFAULT ''	    --Triodos, PayPal, Mollie, ...
, `Account` varchar(63) NOT NULL DEFAULT ''		--banks own account number identifier (IBAN, email, ...)
, `API` varchar(63) NOT NULL DEFAULT ''			
);

CREATE TABLE `PaymentEndpoint` (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT
, `ContactID` integer NOT NULL DEFAULT '0'		--account 
, `PaymentProviderID` integer NOT NULL DEFAULT '0'	
, `Account` varchar(63) NOT NULL DEFAULT ''		--account number
, `API` varchar(63) NOT NULL DEFAULT ''
);

CREATE TABLE `Log` (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT
, `Timestamp` datetime NOT NULL DEFAULT '0'
, `UserID` integer NOT NULL DEFAULT '0'
, `Message` text NOT NULL DEFAULT ''
);

CREATE TABLE `Merge` (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT
, `MergeDate` date NOT NULL DEFAULT '0'
); 

CREATE TABLE `Rules` (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT
, `Name` varchar(64) NOT NULL DEFAULT ''
, `Json` text NOT NULL DEFAULT ''
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
INSERT INTO `Accounts` (ID, PID, RGS, Name) VALUES
(1,0,'B','balansrekeningen'),

(11,1,'BMva','materiële vaste activa'),
(111,11,'BMvaMei','machines en installaties'),
(1111,111,'BMvaMeiCae','Cumulatieve afschrijvingen en waardeverminderingen'),
(12,1,'BVrd','vooraden'),
(13,1,'BVor','vorderingen'),
(131,13,'BVorDeb','debiteuren (te ontvangen)'),
(132,13,'BVorOva','overlopende activa'),

(14,1,'BLim','liquide middelen'),
(141,14,'BLimKas','Kasmiddelen'),
(142,14,'BLimBan','Tegoeden op bankgirorekeningen'),

(1421,142,'','triodos'),
(1422,142,'','mollie'),
(1423,142,'','paypal'),
(1424,142,'','sumup'),

(15,1,'BEiv','Eigen vermogen'),
(151,15,'BEivSev','Kapitaal stichting, coöperatie en vereniging'),
(152,15,'BEivBef','Bestemmingsfondsen'),
(1521,152,'','Projectenfonds'),
/*(153,41,'BEivKap','Eigen vermogen onderneming natuurlijke personen'),
(1531,413,'BEivKapOnd','Ondernemingsvermogen exclusief fiscale reserves fiscaal'),*/
(154,15,'BEivFir','Fiscale reserves'),
(1541,154,'BEivFirHer','Herinvesteringsreserve fiscaal'),
/*(1542,414,'BEivFirFor','Fiscale oudedagsreserve'),*/

(16,1,'BLas','langlopende schulden'),
(161,16,'BLasAcl','achtergestelde schulden'),
(162,16,'BLasSakCla','schulden aan banken'),  /*geen niveau er onder*/
(163,16,'BLasOvp','Overlopende passiva'),
(1631,163,'BSchOpaNtb','Nog te betalen andere kosten'),

(17,1,'BSch','kortlopende schulden'),
(171,17,'BSchKol','Kortlopende leningen-schulden-verplichtingen'),
(172,17,'BSchSak','schulden aan banken'),
(173,17,'BSchCre','crediteuren (te betalen)'),
(174,17,'BSchBep','Belastingen en premies sociale verzekeringen'),

(1741,174,'BSchBepBtw','Te betalen Omzetbelasting'),
(17411,1741,'BSchBepBtwOla','Omzetbelasting leveringen/diensten belast met hoog tarief'),
(17412,1741,'BSchBepBtwOlv','Omzetbelasting leveringen/diensten belast met laag tarief '),
(17413,1741,'BSchBepBtwOlw','Omzetbelasting leveringen/diensten waarbij de omzetbelasting naar u is verlegd  '),
(17414,1741,'BSchBepBtwOlu','Omzetbelasting leveringen/diensten uit landen binnen EU '),
(17415,1741,'BSchBepBtwVoo','Voorbelasting'),
(17416,1741,'BSchBepBtwAfo','Afgedragen omzetbelasting '),
(17417,1741,'BSchBepBtwOvm','Overige mutaties omzetbelasting'),
(174171,17417,'','Omzetbelasting leveringen/diensten belast met nul tarief'),
(174172,17417,'','Omzetbelasting leveringen/diensten vrijgesteld van btw'),

(1742,174,'BSchBepVpb','Te betalen Vennootschapsbelasting'),
(17421,1742,'BSchBepVpbAav','Aangifte vennootschapsbelasting '),
(17422,1742,'BSchBepVpbTvv','Te verrekenen vennootschapsbelasting'),
(17423,1742,'BSchBepVpbAfv','Afgedragen vennootschapsbelasting'),

(175,17,'BSchOpa','overlopende passiva'),
(1751,173,'BSchOpaNto','Nog te ontvangen facturen'),
(1752,173,'BSchOpaNtb','Nog te betalen andere kosten'),

(18,1,'BVrz','voorzieningen'),
(181,18,'BVrzOvz','overige voorzieningen'),
(1811,181,'BVrzOvzVhe','Voorziening voor herstelkosten'),

(2,0,'W','Winst en verliesrekening'),

(21,2,'WOmz','Netto-omzet'),
(211,21,'WOmzNoo','Overige netto-omzet'),
(212,21,'WOmzGrp','Netto-omzet groepen'),

(22,2,'WRev','Netto resultaat exploitatie van vastgoedportefeuille'),
(221,22,'WRevHuo','Huuropbrengsten'),
(222,22,'WRevOsc','Opbrengsten servicecontracten'),
(223,22,'WRevLsc','Lasten servicecontracten'),
(224,22,'WRevLvb','Lasten verhuur en beheeractiviteiten'),
(225,22,'WRevLoa','Lasten onderhoudsactiviteiten'),

(23,2,'WNoa','Netto resultaat overige activiteiten'),
(231,23,'WNoaOoa','Opbrengsten overige activiteiten'),
(232,23,'WNoaKoa','Kosten overige activiteiten'),

(24,2,'WAkf','algemene beheerskosten'),
(25,2,'WWiv','wijziging voorraden'),
(26,2,'WKpr','kostprijs van de omzet'),
(261,26,'WKprKuw','kosten uitbesteed werk en andere externe kosten'),
(262,26,'WKprKra','kosten van rente en afschrijvingen'),

(27,2,'WOvb','Overige bedrijfsopbrengsten'),

(28,2,'WAfs','Afschrijvingen op immateriële en materiële vaste activa'),
(29,2,'WBed','Overige bedrijfskosten'),
(291,29,'WBedHui','Huisvestingskosten'),
(292,29,'WBedEem','Exploitatie- en machinekosten'),
(293,29,'WBedVkk','Verkoop gerelateerde kosten'),
(294,29,'WBedAut','Autokosten'),
(295,29,'WBedTra','Transportkosten'),
(296,29,'WBedKan','Kantoorkosten'),
(297,29,'WBedOrg','Organisatiekosten'),
(298,29,'WBedAss','Assurantiekosten'),
(299,29,'WBedAea','Accountants- en advieskosten'),
(300,29,'WBedAlk','Andere kosten'),
(31,2,'WFbe','Financiële baten en lasten'),
(32,2,'WBel','Belastingen'),
(33,2,'WLbe','Ledenbetalingen (inclusief reeds betaalde voorschotten)'),
(34,2,'WNer','Nettoresultaat')
/*(35,2,'WMfo','Mutatie fiscale oudedagsreserve'),*/
;


INSERT INTO `PaymentProviders` (ID, AccountID, Name, Account, API) VALUES
(1,36,'Triodos','ABCD1234','{
	"addHeaderRow": "\"datum\",\"eigenRekening\",\"bedrag\",\"creditDebet\",\"wederpartij\",\"rekeningWederpartij\",\"transactiecode\",\"omschrijving\"",
	"fieldSeparator": ",",
	"stringDelimiter": "\"",
	"dateField": "datum",
	"dateFormat": "mm-dd-YYYY",
	"amountField": "bedrag",
	"amountFormat": ",",
	"signField": "creditDebet",
	"signFieldMinusValue": "Debet",
	"descriptionField": "omschrijving",
	"accountField": "wederpartij",
	"paymentEndPointField": "rekeningWederpartij",
	"transactionFeeField": "",
	"transactionID": ""
}'),
(2,37,'Mollie','','{
	"addHeaderRow": "",
	"fieldSeparator": ",",
	"stringDelimiter": "\"",
	"dateField": "Datum",
	"dateFormat": "YYYY-mm-dd H:M:s",
	"amountField": "Bedrag",
	"amountFormat": "",
	"signField": "",
	"signFieldMinusValue": "",
	"descriptionField": "Omschrijving",
	"accountField": "Naam consument",
	"paymentEndPointField": "Rekening consument",
	"transactionFeeField": "",
	"transactionID": "ID"
}'),
(3,38,'PayPal','info@planb.coop','{
	"addHeaderRow": "",
	"fieldSeparator": ",",
	"stringDelimiter": "\"",
	"dateField": "Date",
	"dateFormat": "mm-dd-YYYY",
	"amountField": "Gross",
	"amountFormat": "",
	"signField": "",
	"signFieldMinusValue": "",
	"descriptionField": "Subject",
	"accountField": "Name",
	"paymentEndPointField": "",
	"transactionFeeField": "Fee",
	"transactionID": "Transaction ID"
}'),
(4,39,'Sumup','','{
	"addHeaderRow": "",
	"fieldSeparator": ",",
	"stringDelimiter": "",
	"dateField": "Datum",
	"dateFormat": "YYYY-mm-dd H:M:s",
	"amountField": "Totaal bedrag",
	"amountFormat": "",
	"signField": "",
	"signFieldMinusValue": "",
	"descriptionField": "Beschrijving",
	"accountField": "",
	"paymentEndPointField": "Laatste 4 cijfers",
	"transactionFeeField": "Transactiekosten",
	"transactionID": "Transactie ID"
}');

-- ~ INSERT INTO `TransactionTypes` (ID, Name, MutationFormula) VALUES
-- ~ (1,'transfer', '3=1'),
-- ~ (2,'purchase', '5=12+6'),
-- ~ (3,'sale', '4=13+6');
