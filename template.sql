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
, `TransactionID` integer NOT NULL DEFAULT ''
, `Description` varchar(63) NOT NULL DEFAULT ''
, `FromPaymentEndpointID` integer NOT NULL DEFAULT '0'
, `ToPaymentEndpointID` integer NOT NULL DEFAULT '0'
);

--properties of a memorial				-- Memorial has no status, always done by admin
CREATE TABLE 'Memorial' (
  `ID` integer NOT NULL PRIMARY KEY AUTOINCREMENT 
, `EntryID` integer NOT NULL DEFAULT ''
, `TransactionID` integer NOT NULL DEFAULT ''
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
INSERT INTO `Accounts` (ID, PID, Name) VALUES
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
(11,1,'tussenrekeningen'),
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
(22,11,'ontvangen bedragen'),
(23,11,'betaalde bedragen'),
(24,9,'projectenfonds'),
(25,12,'huisvesting'),
(26,12,'reiskosten'),
(27,12,'arbeid'),
(28,12,'materialen'),
(29,12,'administratie'),
(30,12,'bankkosten en rente'),
(31,13,'verhuur'),
(32,13,'ontwikkeling'),
(33,13,'reiskosten'),
(34,13,'sejours'),
(35,13,'publiciteit');

-- ~ INSERT INTO `TransactionTypes` (ID, Name, MutationFormula) VALUES
-- ~ (1,'transfer', '3=1'),
-- ~ (2,'purchase', '5=12+6'),
-- ~ (3,'sale', '4=13+6');
