<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/common.php');
$env = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/.env');
//instantiate the common class

try {
    $common = new common($env);
} catch (Exception $e) {
    echo json_encode(array('success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()));
    exit;
}

//Check to see if the config table exists
$queryText = "SELECT EXISTS (
   SELECT FROM information_schema.tables 
   WHERE  table_schema = 'public'
   AND    table_name   = 'config'
   ) as \"exist\";";

$result = $common->query_to_sd_array($queryText, null);

if ($result['exist'] == 1) {
    die('Database already setup.');
} else {
    echo 'Creating database structures.<br/>';
}

$queryText = "CREATE TABLE public.client (
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
	\"name\" varchar(200) NULL,
	\"extension\" varchar(50) NULL,
	\"path\" varchar(2000) NULL,
	hash varchar(100) NULL,
	date_created timestamp NULL,
	date_modified timestamp NULL,
	file_id varchar(100) NULL,
	folder varchar(50) NULL,
	record_created timestamp DEFAULT CURRENT_TIMESTAMP NULL,
	alert_sent varchar(5) DEFAULT '0'::character varying NULL,
	client_id int4 NULL,
	remediated int2 DEFAULT 0 NULL,
	\"comment\" varchar(250) NULL,
	cert_signer varchar(250) NULL,
	cert_date timestamp NULL,
	cert_issued_by varchar(250) NULL,
	cert_valid int2 NULL,
	cert_thumb_print varchar(100) NULL,
	\"size\" int8 NULL,
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
	\"password\" varchar(10) NULL,
	CONSTRAINT network_file_pkey PRIMARY KEY (id)
);

CREATE INDEX network_file_ai_contact_information_idx ON public.network_file USING gin (to_tsvector('english'::regconfig, ai_contact_information));
CREATE INDEX network_file_ai_summary_fulltext_idx ON public.network_file USING gin (to_tsvector('english'::regconfig, ai_summary));
CREATE INDEX network_file_ai_tags_idx ON public.network_file USING gin (to_tsvector('english'::regconfig, ai_tags));
CREATE INDEX network_file_ai_title_idx ON public.network_file USING gin (to_tsvector('english'::regconfig, (ai_title)::text));
CREATE INDEX network_file_file_id_idx ON public.network_file USING btree (file_id);

";

$result = $common->query_to_sd_array($queryText, null);
echo "Tables created successfully.<br/>";


//Insert data into the config table
$config = [
    [
        "setting" => "APPLICATION_ABBREVIATION",
        "value" => "ZPS",
        "editable" => "1",
        "description" => "Abbreviation of the application name"
    ],
    [
        "setting" => "CHAT_API_SOURCE",
        "value" => "OLLAMA",
        "editable" => "0",
        "description" => "The source of the AI API.  Only supports OLLAMA currently"
    ],
    [
        "setting" => "APPLICATION_DESCRIPTION",
        "value" => "ZeroPoint Search is a AI enhanced file search engine.",
        "editable" => "1",
        "description" => "Description of the application."
    ],
    [
        "setting" => "CHAT_API_OLLAMA",
        "value" => "https:\/\/ai.atomits.com\/api\/generate",
        "editable" => "1",
        "description" => "Endpoint of the OLLAMA server"
    ],
    [
        "setting" => "DB_VERSION",
        "value" => "100",
        "editable" => "0",
        "description" => "Used for database structure updates."
    ],
    [
        "setting" => "CHAT_API_OLLAMA_MODEL",
        "value" => "uncensored",
        "editable" => "1",
        "description" => "The AI model the API calls from OLLAMA"
    ],
    [
        "setting" => "CHAT_MAX_LENGTH",
        "value" => "20000",
        "editable" => "1",
        "description" => "This is used for GUI interactive chats."
    ],
    [
        "setting" => "FILE_CLASSIFICATION_MAX_LENGTH",
        "value" => "6000",
        "editable" => "1",
        "description" => "This is used for the file classisification API."
    ],
    [
        "setting" => "FILE_CLASSIFICATION_PROCESS_CONTACT_INFORMATION",
        "value" => "0",
        "editable" => "1",
        "description" => "Determines if the file classification API process the extracted text for contact information."
    ],
    [
        "setting" => "FILE_CLASSIFICATION_PROCESS_TAGS",
        "value" => "0",
        "editable" => "1",
        "description" => "Determines if the file classification API process the extracted text for tags."
    ],
    [
        "setting" => "DOCTOR_API",
        "value" => "example.com:5050",
        "editable" => "1",
        "description" => "The API endpoint used for text extraction of documents."
    ],
    [
        "setting" => "OCR_THRESHOLD",
        "value" => "50",
        "editable" => "1",
        "description" => "The threshold of characters used to determine if the document needs to have an OCR capture preformed.  Set to 0 to skip OCR."
    ],
    [
        "setting" => "TIME_ZONE",
        "value" => "America\/New_York",
        "editable" => "1",
        "description" => "Time zone of the front end."
    ],
    [
        "setting" => "LOGIN_PASSWORD",
        "value" => "zeropointmodule",
        "editable" => "1",
        "description" => "The password used to log into the front end."
    ],
    [
        "setting" => "APPLICATION_NAME",
        "value" => "ZeroPoint Search",
        "editable" => "1",
        "description" => "Name displayed"
    ],
    [
        "setting" => "AI_CHAT_SEED",
        "value" => "42",
        "editable" => "1",
        "description" => "Set to -1 if you want inconsistent results.  Otherwise, pick a number."
    ],
    [
        "setting" => "ALLOWED_IP",
        "value" => "255.255.255.255",
        "editable" => "1",
        "description" => "IPs allowed to access the front end.  Note, the API is secured through API Key per client."
    ]
];
// Insert config values if they don't exist
foreach ($config_values as $config) {
    $stmt = $common->get_db_connection()->prepare("
        INSERT INTO config (setting, value, editable, description)
        SELECT :setting, :value, :editable, :description
        WHERE NOT EXISTS (
            SELECT 1 FROM config WHERE setting = :setting
        )
    ");

    $stmt->bindParam(':setting', $config['setting']);
    $stmt->bindParam(':value', $config['value']);
    $stmt->bindParam(':editable', $config['editable']);
    $stmt->bindParam(':description', $config['description']);

    $stmt->execute();
}

echo "Config values inserted successfully<br/>.\n";
echo "Setup complete.<br/>";
