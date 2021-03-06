-- FIX FOR BUG #77: PROGRAM NAME LENGTH

-- CREATE TEMP TABLE
CREATE TABLE CTMCMA.PGM_TMP LIKE CTMCMA.PGM;

-- COPY RECORDS FROM PGM TO PGM_TMP
INSERT INTO CTMCMA.PGM_TMP (PGM_ID,APP,PGM_NM)
SELECT PGM_ID,APP,PGM_NM
FROM CTMCMA.PGM;

-- DROP PGM
DROP TABLE CTMCMA.PGM;

-- CREATE TABLE WITH LONGER PGM_NM
CREATE TABLE CTMCMA.PGM
(
 PGM_ID                INTEGER  NOT NULL ,
 APP                   CHAR(2)  ,
 PGM_NM                VARCHAR(80)  
);

LABEL ON TABLE CTMCMA.PGM IS 'Program';
LABEL ON COLUMN CTMCMA.PGM.PGM_ID IS 'Program Identifier';
LABEL ON COLUMN CTMCMA.PGM.PGM_ID TEXT IS 'Program Identifier:';
LABEL ON COLUMN CTMCMA.PGM.APP IS 'Application';
LABEL ON COLUMN CTMCMA.PGM.APP TEXT IS 'Application:';
LABEL ON COLUMN CTMCMA.PGM.PGM_NM IS 'Program Name';
LABEL ON COLUMN CTMCMA.PGM.PGM_NM TEXT IS 'Program Name:';

CREATE UNIQUE INDEX CTMCMA.XPKProgram ON CTMCMA.PGM
(
 PGM_ID                ASC
);

ALTER TABLE CTMCMA.PGM
 ADD PRIMARY KEY (PGM_ID);
 
-- COPY RECORDS FROM PGM_TMP TO PGM
INSERT INTO CTMCMA.PGM (PGM_ID,APP,PGM_NM)
SELECT PGM_ID,APP,PGM_NM
FROM CTMCMA.PGM_TMP;

-- DROP PGM_TMP TABLE
DROP TABLE CTMCMA.PGM_TMP