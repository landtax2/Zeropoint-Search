<?PHP

class file_classification_main
{

    private $common;
    private $client_id;
    private $post;

    public function __construct($common, $client_id, $post)
    {
        $this->common = $common;
        $this->client_id = $client_id;
        $this->post = $post;
    }



    public function handle_check_file_id_action()
    {
        $dataFunctions = new DataFunctions($this->common);
        $result = $dataFunctions->check_and_create_network_file($this->client_id, $this->post);
        $this->common->respond_with_json($result);
    }

    private function sanitize_input($input, $fields)
    {
        $sanitized = [];
        foreach ($fields as $field) {
            $sanitized[$field] = $input[$field] ?? '';
        }
        return $sanitized;
    }

    private function convert_and_extract_file_text($common, $files)
    {
        $log = [
            'files' => $files,
            'POST' => $this->post
        ];
        $common->write_to_log('file_classification', 'Converting file to PDF', $log);
        $extractor = new extract_document_text($common, false);
        //converts the file to pdf and extracts the text

        try {
            $convert_to_pdf = new convert_to_pdf($common);
            $file_path = $convert_to_pdf->convert($files['file']['tmp_name']);
            $file_r = [
                'tmp_name' => $file_path,
                'error' => UPLOAD_ERR_OK,
                'type' => 'application/pdf',
                'name' => 'converted_file.pdf'
            ];
            $extracted_text = $extractor->extract($file_r);
        } catch (Exception $e) {
            $common->write_to_log('file_classification', 'Error converting file to PDF', $log);
            die(json_encode(['error' => $e->getMessage()]));
        }

        //removes the temporary file
        unlink($file_path);

        if (strlen($extracted_text) == 0) {
            die(json_encode(['error' => 'Extraction failed or returned no text']));
        } else {
            //returns the extracted text
            return $extracted_text;
        }
    }

    private function extract_file_text($common, $files)
    {
        $log = [
            'files' => $files,
            'POST' => $this->post
        ];
        $common->write_to_log('file_classification', 'Extracting file text', $log);
        $extractor = new extract_document_text($common, false);
        try {
            $extracted_text = $extractor->extract($files['file']);

            if (strlen($extracted_text) < $common->get_config_value('OCR_THRESHOLD')) {
                $extractor = new extract_document_text($common, true);
                $extracted_text = $extractor->extract($files['file']);
            }

            if (strlen($extracted_text) == 0) {
                die(json_encode(['error' => 'Extraction failed or returned no text']));
            } else {
                return $extracted_text;
            }
        } catch (Exception $e) {
            $common->write_to_log('file_classification', 'Error extracting file text', $log);
            die(json_encode(['error' => $e->getMessage()]));
        }
    }

    public function handle_extract_action()
    {
        //Extracts text from the file.  Converts files other than pdf, doc, docx to pdf and then extracts the text
        if (isset($_FILES['file'])) {
            $extension = strtolower($this->post['extension']);
            if ($extension == 'pdf' || $extension == 'doc' || $extension == 'docx') {
                $extracted_text = $this->extract_file_text($this->common, $_FILES);
            } else {
                $extracted_text = $this->convert_and_extract_file_text($this->common, $_FILES);
            }
        } else {
            die(json_encode(['error' => 'No file uploaded']));
        }


        //truncates the text to the max length. helps with performance
        $extracted_text_total_length = strlen($extracted_text);
        $extracted_text = substr($extracted_text, 0, $this->common->get_config_value('AI_PROCESSING_CHAT_MAX_LENGTH'));
        $extracted_text_total_length_after = strlen($extracted_text);

        //Remove the temporary file
        unlink($_FILES['file']['tmp_name']);

        //print_r($_FILES);
        //print_r($this->post);
        //die($extracted_text . 'fin');

        $ssn_hard = PIIRegex::contains_ssn_hard($extracted_text) ? '1' : '0';
        $ssn_soft = PIIRegex::contains_ssn_soft($extracted_text) ? '1' : '0';
        $phone_number = PIIRegex::contains_phone_number($extracted_text) ? '1' : '0';
        $email = PIIRegex::contains_email($extracted_text) ? '1' : '0';
        $password = PIIRegex::contains_password($extracted_text) ? '1' : '0';

        $ai_processing = new ai_processing($this->common);
        $piiAnalysis = $ai_processing->analyzePII($extracted_text);

        $pii = array(
            'contains_social_security_number' => 0,
            'contains_phone_number' => 0,
            'contains_street_address' => 0,
            'contains_first_and_last_name' => 0,
            'contains_medical_information' => 0,
            'contains_email_address' => 0,
            'contains_credit_card' => 0,
            'contains_bank' => 0,
            'contains_credentials' => 0
        );

        if (isset($piiAnalysis['pii_analysis']) && is_array($piiAnalysis['pii_analysis'])) {
            foreach ($pii as $key => &$value) {
                if (isset($piiAnalysis['pii_analysis'][$key])) {
                    $value = strtolower($piiAnalysis['pii_analysis'][$key]);
                    $value = ($value === 'yes' || $value === 'true') ? 1 : 0;
                }
            }
        }

        $severity = isset($piiAnalysis['pii_analysis']['severity_of_pii']) ? $piiAnalysis['pii_analysis']['severity_of_pii'] : '0';

        //AI processing
        $time = time();
        $execution_times = array(
            'summary' => 0,
            'title' => 0,
            'tags' => 0,
            'contact_information' => 0
        );

        //AI processing for summary
        $summary = $ai_processing->summarizeText($extracted_text);
        $execution_times['summary'] = time() - $time;

        //AI processing for title
        $title = $ai_processing->titleText($extracted_text);
        $execution_times['title'] = time() - $time;

        //AI processing for tags
        if ($this->common->get_config_value('FILE_CLASSIFICATION_PROCESS_TAGS') == '1') {
            $tags = $ai_processing->ai_tags($summary);
            $execution_times['tags'] = time() - $time;
        } else {
            $tags = '';
        }

        //AI processing for contact information
        if ($this->common->get_config_value('FILE_CLASSIFICATION_PROCESS_CONTACT_INFORMATION') == '1') {
            $contact_information = $ai_processing->contact_information($extracted_text);
            $execution_times['contact_information'] = time() - $time;
        } else {
            $contact_information = '';
        }



        // Sanitize and assign variables
        $fields = [
            'name',
            'extension',
            'path',
            'hash',
            'date_created',
            'date_modified',
            'folder',
            'cert_signer',
            'cert_date',
            'cert_issued_by',
            'cert_valid',
            'cert_thumb_print',
            'size',
            'internal_name',
            'product_version',
            'file_version',
            'file_id'
        ];

        $sanitized = [];
        foreach ($fields as $field) {
            $sanitized[$field] = isset($this->post[$field]) ? $this->post[$field] : '';
        }

        $last_found = date('m/d/Y H:i:s');

        $dataFunctions = new DataFunctions($this->common);
        $updateData = [
            'sanitized' => $sanitized,
            'client_id' => $this->client_id,
            'last_found' => $last_found,
            'title' => $title,
            'summary' => $summary,
            'tags' => $tags,
            'contact_information' => $contact_information,
            'pii' => $pii,
            'severity' => $severity,
            'ssn_hard' => $ssn_hard,
            'ssn_soft' => $ssn_soft,
            'phone_number' => $phone_number,
            'email' => $email,
            'password' => $password,
        ];

        $dataFunctions->updateNetworkFile($updateData);
        $dataFunctions->create_tag($this->post['file_id'], $tags);

        $analysis_detials = [
            'title' => $title,
            'name' => $this->post['name'],
            'execution_times' => $execution_times,
            'extracted_text_total_length' => $extracted_text_total_length,
            'extracted_text_total_length_after' => $extracted_text_total_length_after,
            'context_window' => $this->common->get_config_value('AI_PROCESSING_CONTEXT_WINDOW'),
            'summary_length' => $this->common->get_config_value('AI_PROCESSING_SUMMARY_LENGTH')
        ];
        $this->common->write_to_log('file_classification_performance', 'Analysis details', $analysis_detials);
        $this->common->respond_with_json($analysis_detials);
    }
}
