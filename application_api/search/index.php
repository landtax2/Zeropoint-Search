<?PHP
//Gets the common class
ini_set('display_errors', 1);
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/common.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/file_classification/ai_processing.class.php');
//include chat class
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/chat.class.php');

session_start();

//parse the .env file if it exists
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/.env')) {
    $env = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/.env');
} else {
    $env = [];
}
//instantiate the common class
try {
    $common = new common($env);
} catch (Exception $e) {
    echo json_encode(array('success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()));
    exit;
}

//security check - prevents access from non-logged in users
$common->security_check();

//debugging check
($common->get_config_value('DEBUGGING') == '1') ? ini_set('display_errors', 1) : ini_set('log_errors', 0); //turns off error logging if not debugging

// Get JSON payload
$json_payload = file_get_contents('php://input');
$data = json_decode($json_payload, true);


//deal with decodiing issues
if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(array('success' => false, 'message' => 'Invalid JSON payload'));
    exit;
}


//log access to the api
$access = [
    'IP' => $common->get_ip(),
    'User ID' => $_SESSION['user_id'],
    'Data' => $data
];
$common->write_to_log('access', $_SERVER['REQUEST_URI'], $access);

switch ($data['action']) {
    case 'magic_search':
        $ai_processing = new ai_processing($common);
        $result = $ai_processing->magic_search($data['query']);
        $words = explode(',', $result);
        foreach ($words as &$word) {
            $word = trim($word);
            $word = str_replace(' ', ' <-> ', $word);
        }
        $and = implode(' & ', $words);
        $or = implode(' | ', $words);
        if ($data['useFullText'] == 'true') {
            $queryText = "
            SELECT t1.id, t1.name, t1.path, t1.ai_title, t2.full_text as ai_summary, t1.last_found, t1.date_created, t1.date_modified, t1.ai_tags, t1.ai_contact_information,
            ts_rank(to_tsvector('english', t2.full_text), to_tsquery('english', :ai_summary)) AS rank
            FROM network_file t1
            LEFT OUTER JOIN network_file_fulltext t2 ON t1.id = t2.network_file_id
            WHERE 
            t2.full_text @@ to_tsquery('english', :ai_summary)
            AND found_last = 1
            ORDER BY rank DESC
            LIMIT 20";
        } else {
            $queryText = "
        SELECT id, name, path, ai_title, ai_summary, last_found, date_created, date_modified, ai_tags, ai_contact_information,
        ts_rank(to_tsvector('english', ai_summary), to_tsquery('english', :ai_summary)) AS rank
        FROM network_file
        WHERE 
        ai_summary @@ to_tsquery('english', :ai_summary)
        AND found_last = 1
        ORDER BY rank DESC
        LIMIT 20";
        }
        $params[':ai_summary'] = $or;
        $files = $common->query_to_md_array($queryText, $params);
        if (count($files) == 0) {
            $params[':ai_summary'] = $and;
            $files = $common->query_to_md_array($queryText, $params);
        }

        //handle no results or results
        if (count($files) == 0) {
            echo json_encode(array('success' => false, 'message' => 'No results found'));
            exit;
        } else {
            $file_summary = '';
            foreach ($files as $file) {
                $file_summary .= $file['ai_summary'] . "\n\n";
            }
            //Prompt
            $prompt = "Answer the below query using the provided text.  The text is delimited by: ####  The query is: " . $data['query'] . "  The text is: #### " . $file_summary . " ####";
            //Feeds the results to the LLM to answer the original query
            $answer = $ai_processing->answer_query($prompt);
            echo json_encode(array('success' => true, 'result' => $answer, 'files' => $files, 'prompt' => $prompt));
            exit;
        }



        echo json_encode(array('success' => true, 'result' => $files));
        break;
}
