DROP TABLE CTMCMA.PRJTD_ACCR;

CREATE TABLE CTMCMA.CNTRCT_ART
(
 CNTRCT_ID             INTEGER  NOT NULL ,
 ELEM_ID               INTEGER  NOT NULL ,
 ART_ID                NUMERIC(9)  NOT NULL 
);

LABEL ON TABLE CTMCMA.CNTRCT_ART IS 'Contract Article';

LABEL ON COLUMN CTMCMA.CNTRCT_ART.CNTRCT_ID IS 'Contract Identifier';

LABEL ON COLUMN CTMCMA.CNTRCT_ART.CNTRCT_ID TEXT IS 'Contract Identifier:';

LABEL ON COLUMN CTMCMA.CNTRCT_ART.ELEM_ID IS 'Element Identifier';

LABEL ON COLUMN CTMCMA.CNTRCT_ART.ELEM_ID TEXT IS 'Element Identifier:';

LABEL ON COLUMN CTMCMA.CNTRCT_ART.ART_ID IS 'Article Identifier';

LABEL ON COLUMN CTMCMA.CNTRCT_ART.ART_ID TEXT IS 'Article Identifier:';

CREATE UNIQUE INDEX CTMCMA.XPKContract_Article ON CTMCMA.CNTRCT_ART
(
 CNTRCT_ID             ASC, 
 ELEM_ID               ASC, 
 ART_ID                ASC
);

ALTER TABLE CTMCMA.CNTRCT_ART
 ADD PRIMARY KEY (CNTRCT_ID, ELEM_ID, ART_ID);

CREATE INDEX CTMCMA.XIF1Contract_Article ON CTMCMA.CNTRCT_ART
(
 CNTRCT_ID             ASC
);

CREATE INDEX CTMCMA.XIF2Contract_Article ON CTMCMA.CNTRCT_ART
(
 ELEM_ID               ASC
);

CREATE TABLE CTMCMA.CNTRCT_OUT
(
 CNTRCT_ID             INTEGER  NOT NULL ,
 ELEM_ID               INTEGER  NOT NULL ,
 OUT_ID                NUMERIC(9)  NOT NULL 
);

LABEL ON TABLE CTMCMA.CNTRCT_OUT IS 'Contract Outlet';

LABEL ON COLUMN CTMCMA.CNTRCT_OUT.CNTRCT_ID IS 'Contract Identifier';

LABEL ON COLUMN CTMCMA.CNTRCT_OUT.CNTRCT_ID TEXT IS 'Contract Identifier:';

LABEL ON COLUMN CTMCMA.CNTRCT_OUT.ELEM_ID IS 'Element Identifier';

LABEL ON COLUMN CTMCMA.CNTRCT_OUT.ELEM_ID TEXT IS 'Element Identifier:';

LABEL ON COLUMN CTMCMA.CNTRCT_OUT.OUT_ID IS 'Outlet Identifier';

LABEL ON COLUMN CTMCMA.CNTRCT_OUT.OUT_ID TEXT IS 'Outlet Identifier:';

CREATE UNIQUE INDEX CTMCMA.XPKContract_Outlet ON CTMCMA.CNTRCT_OUT
(
 CNTRCT_ID             ASC, 
 ELEM_ID               ASC, 
 OUT_ID                ASC
);

ALTER TABLE CTMCMA.CNTRCT_OUT
 ADD PRIMARY KEY (CNTRCT_ID, ELEM_ID, OUT_ID);

CREATE INDEX CTMCMA.XIF1Contract_Outlet ON CTMCMA.CNTRCT_OUT
(
 CNTRCT_ID             ASC
);

CREATE INDEX CTMCMA.XIF2Contract_Outlet ON CTMCMA.CNTRCT_OUT
(
 ELEM_ID               ASC
);

CREATE TABLE CTMCMA.PRJTD_ACCR
(
 CNTRCT_ID             INTEGER  NOT NULL ,
 ELEM_ID               INTEGER  NOT NULL ,
 STRT_DT               DATE  NOT NULL ,
 END_DT                DATE  ,
 ACCR_AMT              DECIMAL(13,4)  
);

LABEL ON TABLE CTMCMA.PRJTD_ACCR IS 'Projected Accrual';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR.CNTRCT_ID IS 'Contract Identifier';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR.CNTRCT_ID TEXT IS 'Contract Identifier:';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR.ELEM_ID IS 'Element Identifier';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR.ELEM_ID TEXT IS 'Element Identifier:';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR.STRT_DT IS 'Start Date';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR.STRT_DT TEXT IS 'Start Date:';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR.END_DT IS 'End Date';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR.END_DT TEXT IS 'End Date:';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR.ACCR_AMT IS 'Accrual Amount';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR.ACCR_AMT TEXT IS 'Accrual Amount:';

CREATE UNIQUE INDEX CTMCMA.XPKProjected_Accrual ON CTMCMA.PRJTD_ACCR
(
 CNTRCT_ID             ASC, 
 ELEM_ID               ASC, 
 STRT_DT               ASC
);

ALTER TABLE CTMCMA.PRJTD_ACCR
 ADD PRIMARY KEY (CNTRCT_ID, ELEM_ID, STRT_DT);

CREATE INDEX CTMCMA.XIF1Projected_Accrual ON CTMCMA.PRJTD_ACCR
(
 CNTRCT_ID             ASC
);

CREATE INDEX CTMCMA.XIF2Projected_Accrual ON CTMCMA.PRJTD_ACCR
(
 ELEM_ID               ASC
);

CREATE TABLE CTMCMA.PRJTD_ACCR_YR
(
 CNTRCT_ID             INTEGER  NOT NULL ,
 ELEM_ID               INTEGER  NOT NULL ,
 ACCR_AMT              DECIMAL(13,4)  ,
 STRT_DT               DATE  NOT NULL ,
 END_DT                DATE  ,
 AFCTD_VOL             DECIMAL(13,4)  ,
 YR                    INTEGER  
);

LABEL ON TABLE CTMCMA.PRJTD_ACCR_YR IS 'Projected Accrual Year';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR_YR.CNTRCT_ID IS 'Contract Identifier';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR_YR.CNTRCT_ID TEXT IS 'Contract Identifier:';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR_YR.ELEM_ID IS 'Element Identifier';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR_YR.ELEM_ID TEXT IS 'Element Identifier:';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR_YR.ACCR_AMT IS 'Accrual Amount';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR_YR.ACCR_AMT TEXT IS 'Accrual Amount:';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR_YR.STRT_DT IS 'Start Date';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR_YR.STRT_DT TEXT IS 'Start Date:';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR_YR.END_DT IS 'End Date';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR_YR.END_DT TEXT IS 'End Date:';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR_YR.AFCTD_VOL IS 'Affected Volume';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR_YR.AFCTD_VOL TEXT IS 'Affected Volume:';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR_YR.YR IS 'Year';

LABEL ON COLUMN CTMCMA.PRJTD_ACCR_YR.YR TEXT IS 'Year:';

CREATE UNIQUE INDEX CTMCMA.XPKProjected_Accrual_Year ON CTMCMA.PRJTD_ACCR_YR
(
 CNTRCT_ID             ASC, 
 ELEM_ID               ASC, 
 STRT_DT               ASC
);

ALTER TABLE CTMCMA.PRJTD_ACCR_YR
 ADD PRIMARY KEY (CNTRCT_ID, ELEM_ID, STRT_DT);

CREATE INDEX CTMCMA.XIF1Projected_Accrual_Year ON CTMCMA.PRJTD_ACCR_YR
(
 CNTRCT_ID             ASC
);

CREATE INDEX CTMCMA.XIF2Projected_Accrual_Year ON CTMCMA.PRJTD_ACCR_YR
(
 ELEM_ID               ASC
);

CREATE VIEW CTMCMA.PRJTD_ACCR_RT (CNTRCT_ID, ELEM_ID, YR, STRT_DT, END_DT, ACCR_RT) AS
SELECT CNTRCT_ID, ELEM_ID, YR, STRT_DT, END_DT, 
	CASE AFCTD_VOL
		WHEN 0 THEN 0
		ELSE DECIMAL(ROUND(DECIMAL(ACCR_AMT, 13, 4) / DECIMAL(AFCTD_VOL, 13, 4), 4), 13, 4)
	END AS ACCR_RT
FROM CTMCMA.PRJTD_ACCR_YR