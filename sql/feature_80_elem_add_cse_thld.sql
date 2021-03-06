-- FEATURE #80 - ADD CASE THRESHOLD FIELD

-- CREATE TEMPORARY TABLE
CREATE TABLE CTMCMA.ELEM_TMP LIKE CTMCMA.ELEM;

-- MOVE RECORDS TO TEMP
INSERT INTO CTMCMA.ELEM_TMP (ELEM_ID,APP,ELEM_TP,ELEM_RT,ELEM_TRGT,ELEM_TRIGR,PCT,PYMT_LMT,SHR,ON_INV_FLG,UNT_DIV,ELEM_DESC,ELEM_NM,ENBL)
SELECT ELEM_ID,APP,ELEM_TP,ELEM_RT,ELEM_TRGT,ELEM_TRIGR,PCT,PYMT_LMT,SHR,ON_INV_FLG,UNT_DIV,ELEM_DESC,ELEM_NM,ENBL
FROM CTMCMA.ELEM;

-- REMOVE TABLE
DROP TABLE CTMCMA.ELEM;

-- CREATE NEW TABLE WITH ADDED FIELD
CREATE TABLE CTMCMA.ELEM
(
 ELEM_ID               INTEGER  NOT NULL ,
 APP                   CHAR(2)  ,
 ELEM_TP               CHAR(2)  ,
 ELEM_RT               DECIMAL(13,2)  ,
 ELEM_TRGT             CHAR(2)  ,
 ELEM_TRIGR            CHAR(2)  ,
 PCT                   DECIMAL(10,2)  ,
 PYMT_LMT              DECIMAL(10,2)  ,
 SHR                   DECIMAL(10,2)  ,
 ON_INV_FLG            CHAR(1)  ,
 UNT_DIV               INTEGER  ,
 ELEM_DESC             VARCHAR(140)  ,
 ELEM_NM               VARCHAR(80)  ,
 ENBL                  CHAR(1)  ,
 CSE_THLD              INTEGER  
);

LABEL ON TABLE CTMCMA.ELEM IS 'Element';
LABEL ON COLUMN CTMCMA.ELEM.ELEM_ID IS 'Element Identifier';
LABEL ON COLUMN CTMCMA.ELEM.ELEM_ID TEXT IS 'Element Identifier:';
LABEL ON COLUMN CTMCMA.ELEM.APP IS 'Application';
LABEL ON COLUMN CTMCMA.ELEM.APP TEXT IS 'Application:';
LABEL ON COLUMN CTMCMA.ELEM.ELEM_TP IS 'Element Type';
LABEL ON COLUMN CTMCMA.ELEM.ELEM_TP TEXT IS 'Element Type:';
LABEL ON COLUMN CTMCMA.ELEM.ELEM_RT IS 'Element Rate';
LABEL ON COLUMN CTMCMA.ELEM.ELEM_RT TEXT IS 'Element Rate:';
LABEL ON COLUMN CTMCMA.ELEM.ELEM_TRGT IS 'Element Target';
LABEL ON COLUMN CTMCMA.ELEM.ELEM_TRGT TEXT IS 'Element Target:';
LABEL ON COLUMN CTMCMA.ELEM.ELEM_TRIGR IS 'Element Trigger';
LABEL ON COLUMN CTMCMA.ELEM.ELEM_TRIGR TEXT IS 'Element Trigger:';
LABEL ON COLUMN CTMCMA.ELEM.PCT IS 'Percent';
LABEL ON COLUMN CTMCMA.ELEM.PCT TEXT IS 'Percent:';
LABEL ON COLUMN CTMCMA.ELEM.PYMT_LMT IS 'Payment Limit';
LABEL ON COLUMN CTMCMA.ELEM.PYMT_LMT TEXT IS 'Payment Limit:';
LABEL ON COLUMN CTMCMA.ELEM.SHR IS 'Share';
LABEL ON COLUMN CTMCMA.ELEM.SHR TEXT IS 'Share:';
LABEL ON COLUMN CTMCMA.ELEM.ON_INV_FLG IS 'On Invoice Flag';
LABEL ON COLUMN CTMCMA.ELEM.ON_INV_FLG TEXT IS 'On Invoice Flag:';
LABEL ON COLUMN CTMCMA.ELEM.UNT_DIV IS 'Unit Division';
LABEL ON COLUMN CTMCMA.ELEM.UNT_DIV TEXT IS 'Unit Division:';
LABEL ON COLUMN CTMCMA.ELEM.ELEM_DESC IS 'Element Description';
LABEL ON COLUMN CTMCMA.ELEM.ELEM_DESC TEXT IS 'Element Description:';
LABEL ON COLUMN CTMCMA.ELEM.ELEM_NM IS 'Element Name';
LABEL ON COLUMN CTMCMA.ELEM.ELEM_NM TEXT IS 'Element Name:';
LABEL ON COLUMN CTMCMA.ELEM.ENBL IS 'Enabled';
LABEL ON COLUMN CTMCMA.ELEM.ENBL TEXT IS 'Enabled:';
LABEL ON COLUMN CTMCMA.ELEM.CSE_THLD IS 'Case Threshold';
LABEL ON COLUMN CTMCMA.ELEM.CSE_THLD TEXT IS 'Case Threshold:';

CREATE UNIQUE INDEX CTMCMA.XPKElement ON CTMCMA.ELEM
(
 ELEM_ID               ASC
);

ALTER TABLE CTMCMA.ELEM
 ADD PRIMARY KEY (ELEM_ID);
 
-- ADD RECORDS BACK TO ELEM TABLE
INSERT INTO CTMCMA.ELEM (ELEM_ID,APP,ELEM_TP,ELEM_RT,ELEM_TRGT,ELEM_TRIGR,PCT,PYMT_LMT,SHR,ON_INV_FLG,UNT_DIV,ELEM_DESC,ELEM_NM,ENBL,CSE_THLD)
SELECT ELEM_ID,APP,ELEM_TP,ELEM_RT,ELEM_TRGT,ELEM_TRIGR,PCT,PYMT_LMT,SHR,ON_INV_FLG,UNT_DIV,ELEM_DESC,ELEM_NM,ENBL,
	0 AS CSE_THLD
FROM CTMCMA.ELEM_TMP;