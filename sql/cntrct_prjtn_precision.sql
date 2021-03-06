
DROP TABLE CTMCMA.CNTRCT_PRJTN;

CREATE TABLE CTMCMA.CNTRCT_PRJTN
(
 STLMNT_DT             DATE  NOT NULL ,
 SLS_CTR_CD            CHAR(2)  NOT NULL ,
 CNTRCT_ID             INTEGER  NOT NULL ,
 ELEM_ID               INTEGER  NOT NULL ,
 PRJTD_CSE_VOL         DECIMAL(13,4)  ,
 LST_UPDT_TM           TIMESTAMP  ,
 USR_ID                INTEGER  NOT NULL 
);

LABEL ON TABLE CTMCMA.CNTRCT_PRJTN IS 'Contract Projection';

LABEL ON COLUMN CTMCMA.CNTRCT_PRJTN.STLMNT_DT IS 'Settlement Date';

LABEL ON COLUMN CTMCMA.CNTRCT_PRJTN.STLMNT_DT TEXT IS 'Settlement Date:';

LABEL ON COLUMN CTMCMA.CNTRCT_PRJTN.SLS_CTR_CD IS 'Sales Center code';

LABEL ON COLUMN CTMCMA.CNTRCT_PRJTN.SLS_CTR_CD TEXT IS 'Sales Center code:';

LABEL ON COLUMN CTMCMA.CNTRCT_PRJTN.CNTRCT_ID IS 'Contract Identifier';

LABEL ON COLUMN CTMCMA.CNTRCT_PRJTN.CNTRCT_ID TEXT IS 'Contract Identifier:';

LABEL ON COLUMN CTMCMA.CNTRCT_PRJTN.ELEM_ID IS 'Element Identifier';

LABEL ON COLUMN CTMCMA.CNTRCT_PRJTN.ELEM_ID TEXT IS 'Element Identifier:';

LABEL ON COLUMN CTMCMA.CNTRCT_PRJTN.PRJTD_CSE_VOL IS 'Projected Case Volume';

LABEL ON COLUMN CTMCMA.CNTRCT_PRJTN.PRJTD_CSE_VOL TEXT IS 'Projected Case Volume:';

LABEL ON COLUMN CTMCMA.CNTRCT_PRJTN.LST_UPDT_TM IS 'Last Update Time';

LABEL ON COLUMN CTMCMA.CNTRCT_PRJTN.LST_UPDT_TM TEXT IS 'Last Update Time:';

LABEL ON COLUMN CTMCMA.CNTRCT_PRJTN.USR_ID IS 'User Identifier';

LABEL ON COLUMN CTMCMA.CNTRCT_PRJTN.USR_ID TEXT IS 'User Identifier:';

CREATE UNIQUE INDEX CTMCMA.XPKContract_Projection ON CTMCMA.CNTRCT_PRJTN
(
 STLMNT_DT             ASC, 
 SLS_CTR_CD            ASC, 
 CNTRCT_ID             ASC, 
 ELEM_ID               ASC
);

ALTER TABLE CTMCMA.CNTRCT_PRJTN
 ADD PRIMARY KEY (STLMNT_DT, SLS_CTR_CD, CNTRCT_ID, ELEM_ID);

CREATE INDEX CTMCMA.XIF1Contract_Projection ON CTMCMA.CNTRCT_PRJTN
(
 USR_ID                ASC
);

CREATE INDEX CTMCMA.XIF2Contract_Projection ON CTMCMA.CNTRCT_PRJTN
(
 CNTRCT_ID             ASC
);

CREATE INDEX CTMCMA.XIF3Contract_Projection ON CTMCMA.CNTRCT_PRJTN
(
 ELEM_ID               ASC
)
