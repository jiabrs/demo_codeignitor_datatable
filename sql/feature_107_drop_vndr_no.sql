-- FEATURE #107 - REMOVE VNDR_NO

CREATE TABLE CTMCMA.CNTRCT_TMP LIKE CTMCMA.CNTRCT;

INSERT INTO CTMCMA.CNTRCT_TMP (
	CNTRCT_ID,
 	APP,
 	CSE_TP,
 	STRT_DT,
 	END_DT,
 	CNTRCT_NM   
)	
SELECT CNTRCT_ID,
 	APP,
 	CSE_TP,
 	STRT_DT,
 	END_DT,
 	CNTRCT_NM  
FROM CTMCMA.CNTRCT;

DROP TABLE CTMCMA.CNTRCT;

CREATE TABLE CTMCMA.CNTRCT
(
 CNTRCT_ID             INTEGER  NOT NULL ,
 APP                   CHAR(2)  ,
 CSE_TP                CHAR(2)  ,
 STRT_DT               DATE  ,
 END_DT                DATE  ,
 CNTRCT_NM             VARCHAR(80)  
);

LABEL ON TABLE CTMCMA.CNTRCT IS 'Contract';

LABEL ON COLUMN CTMCMA.CNTRCT.CNTRCT_ID IS 'Contract Identifier';

LABEL ON COLUMN CTMCMA.CNTRCT.CNTRCT_ID TEXT IS 'Contract Identifier:';

LABEL ON COLUMN CTMCMA.CNTRCT.APP IS 'Application';

LABEL ON COLUMN CTMCMA.CNTRCT.APP TEXT IS 'Application:';

LABEL ON COLUMN CTMCMA.CNTRCT.CSE_TP IS 'Case Type';

LABEL ON COLUMN CTMCMA.CNTRCT.CSE_TP TEXT IS 'Case Type:';

LABEL ON COLUMN CTMCMA.CNTRCT.STRT_DT IS 'Start Date';

LABEL ON COLUMN CTMCMA.CNTRCT.STRT_DT TEXT IS 'Start Date:';

LABEL ON COLUMN CTMCMA.CNTRCT.END_DT IS 'End Date';

LABEL ON COLUMN CTMCMA.CNTRCT.END_DT TEXT IS 'End Date:';

LABEL ON COLUMN CTMCMA.CNTRCT.CNTRCT_NM IS 'Contract Name';

LABEL ON COLUMN CTMCMA.CNTRCT.CNTRCT_NM TEXT IS 'Contract Name:';

CREATE UNIQUE INDEX CTMCMA.XPKContract ON CTMCMA.CNTRCT
(
 CNTRCT_ID             ASC
);

ALTER TABLE CTMCMA.CNTRCT
 ADD PRIMARY KEY (CNTRCT_ID);
 
INSERT INTO CTMCMA.CNTRCT (
	CNTRCT_ID,
 	APP,
 	CSE_TP,
 	STRT_DT,
 	END_DT,
 	CNTRCT_NM   
)	
SELECT CNTRCT_ID,
 	APP,
 	CSE_TP,
 	STRT_DT,
 	END_DT,
 	CNTRCT_NM  
FROM CTMCMA.CNTRCT_TMP;

DROP TABLE CTMCMA.CNTRCT_TMP;
