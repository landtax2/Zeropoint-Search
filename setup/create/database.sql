CREATE TABLE public.client (
	id serial4 NOT NULL,
	client_name varchar(50) NOT NULL,
	api_key uuid DEFAULT gen_random_uuid() NULL,
	alert_email varchar(100) NULL,
	days_till_client_inactive int4 NULL,
	CONSTRAINT client_pkey PRIMARY KEY (id)
);

CREATE TABLE public.config (
	id serial4 NOT NULL,
	setting varchar(50) NOT NULL,
	value varchar(1000) NOT NULL,
	editable varchar(1) DEFAULT '0'::character varying NULL,
	description varchar(200) NULL,
	CONSTRAINT config_pkey PRIMARY KEY (id)
);

CREATE TABLE public.network_file (
	id serial4 NOT NULL,
	"name" varchar(200) NULL,
	"extension" varchar(50) NULL,
	"path" varchar(2000) NULL,
	hash varchar(100) NULL,
	date_created timestamp NULL,
	date_modified timestamp NULL,
	file_id varchar(100) NULL,
	folder varchar(50) NULL,
	record_created timestamp DEFAULT CURRENT_TIMESTAMP NULL,
	alert_sent varchar(5) DEFAULT '0'::character varying NULL,
	client_id int4 NULL,
	remediated int2 DEFAULT 0 NULL,
	"comment" varchar(250) NULL,
	cert_signer varchar(250) NULL,
	cert_date timestamp NULL,
	cert_issued_by varchar(250) NULL,
	cert_valid int2 NULL,
	cert_thumb_print varchar(100) NULL,
	"size" int8 NULL,
	internal_name varchar(200) NULL,
	hash_processed int2 DEFAULT 0 NULL,
	product_version varchar(100) NULL,
	file_version varchar(100) NULL,
	last_found timestamp NULL,
	found_last int2 DEFAULT 0 NULL,
	ai_title varchar(500) NULL,
	ai_summary text NULL,
	ai_pii_ssn varchar(10) NULL,
	ai_pii_phone varchar(10) NULL,
	ai_pii_address varchar(10) NULL,
	ai_name varchar(10) NULL,
	ai_medical varchar(10) NULL,
	ai_email varchar(10) NULL,
	ai_severity varchar(10) NULL,
	ai_credit_card varchar(10) NULL,
	ssn_hard varchar(10) NULL,
	ssn_soft varchar(10) NULL,
	phone_number varchar(10) NULL,
	email varchar(10) NULL,
	credit_card varchar(10) NULL,
	ai_sensitive_summary varchar(10) NULL,
	ai_contact_information text NULL,
	ai_tags text NULL,
	"password" varchar(10) NULL,
	CONSTRAINT network_file_pkey PRIMARY KEY (id)
);
CREATE INDEX network_file_ai_contact_information_idx ON public.network_file USING gin (to_tsvector('english'::regconfig, ai_contact_information));
CREATE INDEX network_file_ai_summary_fulltext_idx ON public.network_file USING gin (to_tsvector('english'::regconfig, ai_summary));
CREATE INDEX network_file_ai_tags_idx ON public.network_file USING gin (to_tsvector('english'::regconfig, ai_tags));
CREATE INDEX network_file_ai_title_idx ON public.network_file USING gin (to_tsvector('english'::regconfig, (ai_title)::text));
CREATE INDEX network_file_file_id_idx ON public.network_file USING btree (file_id);


INSERT INTO public.config (setting,value,editable,description) VALUES
	 ('APPLICATION_ABBREVIATION','ZPS','1','Abbreviation of the application name'),
	 ('CHAT_API_SOURCE','OLLAMA','0','The source of the AI API.  Only supports OLLAMA currently'),
	 ('APPLICATION_DESCRIPTION','ZeroPoint Search is a AI enhanced file search engine.','1','Description of the application.'),
	 ('CHAT_API_OLLAMA','','1','Endpoint of the OLLAMA server'),
	 ('DB_VERSION','100','0','Used for database structure updates.'),
	 ('CHAT_API_OLLAMA_MODEL','uncensored','1','The AI model the API calls from OLLAMA'),
	 ('CHAT_MAX_LENGTH','20000','1','This is used for GUI interactive chats.'),
	 ('FILE_CLASSIFICATION_MAX_LENGTH','6000','1','This is used for the file classisification API.'),
	 ('FILE_CLASSIFICATION_PROCESS_CONTACT_INFORMATION','0','1','Determines if the file classification API process the extracted text for contact information.'),
	 ('FILE_CLASSIFICATION_PROCESS_TAGS','0','1','Determines if the file classification API process the extracted text for tags.');
INSERT INTO public.config (setting,value,editable,description) VALUES
	 ('DOCTOR_API','','1','The API endpoint used for text extraction of documents.'),
	 ('OCR_THRESHOLD','50','1','The threshold of characters used to determine if the document needs to have an OCR capture preformed.  Set to 0 to skip OCR.'),
	 ('TIME_ZONE','America/New_York','1','Time zone of the front end.'),
	 ('LOGIN_PASSWORD','notsecure','1','The password used to log into the front end.'),
	 ('APPLICATION_NAME','ZeroPoint Search','1','Name displayed'),
	 ('AI_CHAT_SEED','42','1','Set to -1 if you want inconsistent results.  Otherwise, pick a number.'),
	 ('ALLOWED_IP','65.254.28.131','1','IPs allowed to access the front end.  Note, the API is secured through API Key per client.');

INSERT INTO public.client
(id, client_name, api_key)
VALUES(nextval('client_id_seq'::regclass), 'DEFAULT', gen_random_uuid());