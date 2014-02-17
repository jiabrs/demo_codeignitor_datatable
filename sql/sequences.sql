CREATE SEQUENCE CTMCMAD.SQ_ADT_PK 
	AS INTEGER 
	START WITH 1 
	INCREMENT BY 1 
	MINVALUE 1 
	MAXVALUE 2147483647 
	NO CYCLE CACHE 20 NO ORDER ; 
  
CREATE SEQUENCE CTMCMAD.SQ_CNTRCT_PK 
	AS INTEGER 
	START WITH 1 
	INCREMENT BY 1 
	MINVALUE 1 
	MAXVALUE 2147483647 
	NO CYCLE CACHE 20 NO ORDER ; 
  
CREATE SEQUENCE CTMCMAD.SQ_CRIT_CLCTN_PK 
	AS BIGINT 
	START WITH 1 
	INCREMENT BY 1 
	MINVALUE 1 
	MAXVALUE 9223372036854775807 
	NO CYCLE NO CACHE NO ORDER ; 
  
CREATE SEQUENCE CTMCMAD.SQ_ELEM_PK 
	AS INTEGER 
	START WITH 1 
	INCREMENT BY 1 
	MINVALUE 1 
	MAXVALUE 2147483647 
	NO CYCLE CACHE 20 NO ORDER ; 
  
CREATE SEQUENCE CTMCMAD.SQ_NOTE_PK 
	AS INTEGER 
	START WITH 1 
	INCREMENT BY 1 
	MINVALUE 1 
	MAXVALUE 2147483647 
	NO CYCLE CACHE 20 NO ORDER ; 
  
CREATE SEQUENCE CTMCMAD.SQ_PGM_PK 
	AS BIGINT 
	START WITH 1 
	INCREMENT BY 1 
	MINVALUE 1 
	MAXVALUE 9223372036854775807 
	NO CYCLE CACHE 20 NO ORDER ; 
  
CREATE SEQUENCE CTMCMAD.SQ_USR_PK 
	AS INTEGER 
	START WITH 1 
	INCREMENT BY 1 
	MINVALUE 1 
	MAXVALUE 2147483647 
	NO CYCLE CACHE 20 NO ORDER ;