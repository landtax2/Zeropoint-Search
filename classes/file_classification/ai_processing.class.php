<?PHP

class ai_processing
{
    private $chat;
    private $common;

    public function __construct($common)
    {
        $this->common = $common;
    }

    private function initializeChat()
    {
        $this->chat = new chat_ollama($this->common);
        $this->chat->seed = 42;
    }


    public function get_pii_prompt($extracted_text)
    {
        if (strlen(trim($this->common->get_config_value('PROMPT_OVERRIDE_PII'))) > 10) {
            $prompt = $this->common->get_config_value('PROMPT_OVERRIDE_PII') . " #### " . $extracted_text . "####";
        } else {
            $prompt  = "You are an AI trained to detect Personally Identifiable Information (PII) in text. ";
            $prompt .= "PII refers only to data that can directly or indirectly identify a specific person. ";
            $prompt .= "Examples include names, social security numbers, personal addresses, phone numbers, medical details, passwords, credit card numbers, and banking information. ";
            $prompt .= "Do not flag technical documentation, system logs, configuration files, device names, or software instructions as PII unless they include real personal identifiers. ";
            $prompt .= "Return only a valid JSON object using the exact format below. ";
            $prompt .= "Do not include explanations, escaped quotes, or any extra output.\n\n";

            $prompt .= "Guidance for each field:\n";
            $prompt .= "1. contains_social_security_number: Flag if the text contains a full or partial social security number (e.g., 123-45-6789).\n";
            $prompt .= "2. contains_phone_number: Flag if the text contains a personal phone number (e.g., mobile, home, or work number).\n";
            $prompt .= "3. contains_street_address: Flag if the text contains a full or partial address (e.g., 123 Main St, City, State, Zip).\n";
            $prompt .= "4. contains_first_and_last_name: Flag if the text contains a full name (e.g., John Doe).\n";
            $prompt .= "5. contains_personal_medical_information: Flag if the text contains medical information (e.g., diagnosis, treatment, prescription details).\n";
            $prompt .= "6. contains_username_and_password: Flag if the text contains any user credentials (e.g., login details, username and password pairs).\n";
            $prompt .= "7. contains_email_address: Flag if the text contains an email address (e.g., user@example.com).\n";
            $prompt .= "8. contains_credit_card: Flag if the text contains a full or partial credit card number (e.g., 4111-1111-1111-1111).\n";
            $prompt .= "9. contains_banking_information: Flag if the text contains any banking information (e.g., account number, routing number, check details).\n";
            $prompt .= "10. severity_of_personal_information: Assign a value from 1 to 10, where 1 means minimal or no PII, and 10 means highly sensitive information (e.g., full identity, medical, or financial info).\n\n";

            $prompt .= "Return the result in the following JSON format:\n";
            $prompt .= "{\n";
            $prompt .= "  \"contains_social_security_number\": \"yes or no\",\n";
            $prompt .= "  \"contains_phone_number\": \"yes or no\",\n";
            $prompt .= "  \"contains_street_address\": \"yes or no\",\n";
            $prompt .= "  \"contains_first_and_last_name\": \"yes or no\",\n";
            $prompt .= "  \"contains_personal_medical_information\": \"yes or no\",\n";
            $prompt .= "  \"contains_username_and_password\": \"yes or no\",\n";
            $prompt .= "  \"contains_email_address\": \"yes or no\",\n";
            $prompt .= "  \"contains_credit_card\": \"yes or no\",\n";
            $prompt .= "  \"contains_banking_information\": \"yes or no\",\n";
            $prompt .= "  \"severity_of_personal_information\": \"1 to 10\"\n";
            $prompt .= "}\n\n";
            $prompt .= "Text to analyze is enclosed below between #### markers:\n";
            $prompt .= "####" . $this->common->substring_words($extracted_text, 2000) . "####";
        }


        return $prompt;
    }

    public function analyzePII($extracted_text)
    {
        $this->initializeChat();

        $prompt = $this->get_pii_prompt($extracted_text);

        try {
            $piiAnalysis = $this->chat->sendRequest($prompt);
        } catch (Exception $e) {
            echo "Error occured with the AI endpoint: $e";
            //Throws an exception
            throw new Exception('An error occurred while analyzing the text for PII.');
        }

        //this should deal with poorly formatted json
        $pii_analysis_r = [
            'contains_social_security_number' => $this->extract_pii($piiAnalysis, 'contains_social_security_number'),
            'contains_phone_number' => $this->extract_pii($piiAnalysis, 'contains_phone_number'),
            'contains_street_address' => $this->extract_pii($piiAnalysis, 'contains_street_address'),
            'contains_first_and_last_name' => $this->extract_pii($piiAnalysis, 'contains_first_and_last_name'),
            'contains_medical_information' => $this->extract_pii($piiAnalysis, 'contains_personal_medical_information'),
            'contains_credentials' => $this->extract_pii($piiAnalysis, 'contains_username_and_password'),
            'contains_email_address' => $this->extract_pii($piiAnalysis, 'contains_email_address'),
            'contains_credit_card' => $this->extract_pii($piiAnalysis, 'contains_credit_card'),
            'contains_bank' => $this->extract_pii($piiAnalysis, 'contains_banking_information'),
            'severity_of_pii' => $this->extract_pii_severity($piiAnalysis, 'severity_of_personal_information')
        ];

        //return $pii_analysis_r;
        //return array('pii_analysis' => json_decode($piiAnalysis, true));
        return array('pii_analysis' => $pii_analysis_r);
    }

    public function extract_pii($pii, $key)
    {
        $pii = explode("\n", $pii);
        foreach ($pii as $p) {
            if (strpos($p, $key) !== false) {
                if (strpos($p, 'yes') !== false) {
                    return 'yes';
                }
            }
        }
        return 'no';
    }

    public function extract_pii_severity($pii, $key)
    {
        $pii = explode("\n", $pii);
        foreach ($pii as $p) {
            if (strpos($p, $key) !== false) {
                //remove all non-numeric characters
                $severity = preg_replace('/[^0-9]/', '', $p);
                return $severity;
            }
        }
        return '0';
    }

    public function get_contact_information_prompt($extracted_text)
    {
        $prompt = "Create a JSON dataset of contact information based on the below text. Respond only with the dataset without explanation. If there is no contact information, respond with an empty value. Only list contacts if there is an associated peace of information, such as a name, phone number, email address, or street address. Provide the results in a valid JSON format. The text is delimieted by #### . The text to analyze is:\n ####" . $extracted_text . '####';
        if (strlen(trim($this->common->get_config_value('PROMPT_OVERRIDE_CONTACT_INFORMATION'))) > 10) {
            $prompt = $this->common->get_config_value('PROMPT_OVERRIDE_CONTACT_INFORMATION') . " #### " . $extracted_text . "####";
        }
        return $prompt;
    }

    public function contact_information($extracted_text)
    {
        $this->initializeChat(0.7);

        $prompt = $this->get_contact_information_prompt($extracted_text);
        try {
            $contact_information = $this->chat->sendRequest($prompt);
            $contact_information = $this->format_contact_information($contact_information);
            return $contact_information;
        } catch (Exception $e) {
            echo "Error occured with the AI endpoint: $e";
            //Throws an exception
            throw new Exception('An error occurred while summarizing the text.');
        }
    }

    public function format_contact_information($contact_information)
    {
        $contact_information = str_replace("Based on the provided text, I've extracted the following contact information:", "", $contact_information);
        $contact_information = str_replace("Here is the contact information I've extracted:", "", $contact_information);
        $contact_information = str_replace("####", "", $contact_information);
        $contact_information = str_replace("Please let me know if you need any further assistance!", "", $contact_information);
        $contact_information = str_replace("Here is the list of contact information extracted from the text:", "", $contact_information);
        $contact_information = str_replace("Here is the list of contact information:", "", $contact_information);
        $contact_information = substr($contact_information, 0, 1000);
        return $contact_information;
    }


    public function get_ai_tags_prompt($extracted_text)
    {

        if (strlen(trim($this->common->get_config_value('PROMPT_OVERRIDE_TAGS'))) > 10) {
            $prompt = $this->common->get_config_value('PROMPT_OVERRIDE_TAGS') . " #### " . $extracted_text . "####";
        } else {
            $prompt = "Analyze the text below and return a concise, comma-separated list of relevant tags. ";
            $prompt .= "Include only the tags—no explanations, formatting, or additional text. ";
            $prompt .= "The text is enclosed between #### markers:\n####" . $extracted_text . "####";
        }
        return $prompt;
    }

    public function ai_tags($extracted_text)
    {
        $this->initializeChat(0.7);

        $prompt = $this->get_ai_tags_prompt($extracted_text);

        try {
            $tags = $this->chat->sendRequest($prompt);
            $tags = $this->format_ai_tags($tags);
            return $tags;
        } catch (Exception $e) {
            echo "Error occured with the AI endpoint: $e";
            //Throws an exception
            throw new Exception('An error occurred while generating tags.');
        }
    }

    public function format_ai_tags($text)
    {
        $text = str_replace("####", "", $text);
        $text = substr($text, 0, 1000);
        return $text;
    }

    public function get_summary_prompt($extracted_text)
    {
        if (strlen(trim($this->common->get_config_value('PROMPT_OVERRIDE_SUMMARY'))) > 10) {
            $prompt = $this->common->get_config_value('PROMPT_OVERRIDE_SUMMARY') . " #### " . $extracted_text . "####";
        } else {
            $summary_length = $this->common->get_config_value('AI_PROCESSING_SUMMARY_LENGTH');
            $prompt  = "Summarize the following text in no more than $summary_length words. ";
            $prompt .= "Focus solely on the **core concepts**, **key purposes**, and **high-level overviews** that are explicitly stated in the text. ";
            $prompt .= "Do not refer to or infer anything about the text’s format, structure, or context, and do not incorporate any external or prior knowledge. ";
            $prompt .= "Exclude all technical details such as file names, paths, configuration settings, specific operational steps, meta-data, revision histories, or any extraneous information. ";
            $prompt .= "Ensure that the summary is cohesive, concise, and entirely based on the provided content. ";
            $prompt .= "If the text is ambiguous or incomplete, summarize only the clear points without making assumptions. ";
            $prompt .= "Respond solely with the summary—do not include any additional commentary or content. ";
            $prompt .= "The text to summarize is enclosed between #### markers:\n#### " . $this->common->substring_words($extracted_text, 1500) . " ####";
        }
        return $prompt;
    }

    public function summarizeText($extracted_text)
    {
        $context_window = $this->common->get_config_value('AI_PROCESSING_CONTEXT_WINDOW');
        $this->initializeChat(0.7);
        $this->chat->contextWindow = $context_window;

        $prompt = $this->get_summary_prompt($extracted_text);

        try {
            $summary = $this->chat->sendRequest($prompt);
            $summary = $this->format_summary($summary);
            return $summary;
        } catch (Exception $e) {
            echo "Error occured with the AI endpoint: $e";
            //Throws an exception
            throw new Exception('An error occurred while summarizing the text.');
        }
    }

    public function format_summary($summary)
    {
        $summary_length = $this->common->get_config_value('AI_PROCESSING_SUMMARY_LENGTH');
        $summary = trim(str_replace("Here is a summary of the text in under $summary_length words:", "", $summary));
        $summary = trim(str_replace("Here is a summary of the provided text:", "", $summary));
        $summary = trim(str_replace("Here is the summary:", "", $summary));
        $summary = trim(str_replace("It appears to be a", "", $summary));
        $summary = trim(str_replace("This appears to be a", "", $summary));
        $summary = trim(str_replace("This document is a", "", $summary));
        $summary = trim(str_replace("The provided text appears to be a", "", $summary));
        $summary = trim(str_replace("The document appears to be a", "", $summary));
        $summary = trim(str_replace("This document appears to be a", "", $summary));
        $summary = trim(str_replace("This text provides a", "", $summary));
        $summary = trim(str_replace("This summary", "", $summary));
        $summary = trim(str_replace("Here is a summary of the provided text in under 500 words:", "", $summary));
        $summary = trim(str_replace("Here is the summary of the provided text:", "", $summary));
        $summary = trim(str_replace("She summarizes the provided text as follows:", "", $summary));
        $summary = trim(str_replace("Here is a summary of the content in under $summary_length words:", "", $summary));
        $summary = trim(str_replace("Summary:", "", $summary));
        $summary = trim(str_replace("The document is a:", "", $summary));
        $summary = trim(str_replace("The document is a", "", $summary));
        $summary = trim(str_replace("####", "", $summary));
        $summary = trim(str_replace("Summary\n\n", "", $summary));
        $summary = trim(str_replace("Here is a summary of the content:", "", $summary));
        $summary = trim(str_replace("Here is a summary of the provided text in less than $summary_length words.", "", $summary));

        $summary = ucfirst($summary);
        $summary = substr($summary, 0, 6000);
        return $summary;
    }

    public function get_title_prompt($text, $file_name = '')
    {
        if (strlen(trim($this->common->get_config_value('PROMPT_OVERRIDE_TITLE'))) > 10) {
            $prompt = $this->common->get_config_value('PROMPT_OVERRIDE_TITLE') . " #### " . $text . "####";
        } else if ($file_name != '') {
            $prompt = "You are an AI that specializes in naming documents. ";
            $prompt .= "Create a clear, concise name for the document using 10 words or fewer. ";
            $prompt .= "Prioritize the file name when determining relevance, but consider the content for context. ";
            $prompt .= "Respond with the name only—no explanations or extra text. ";
            $prompt .= "File name: " . $file_name . ". ";
            $prompt .= "The document content is enclosed below between #### markers:\n####" . $this->common->substring_words($text, 250) . "####";
        } else {
            $prompt = "You are an AI that specializes in naming documents. ";
            $prompt .= "Create a clear, concise name for the document using 10 words or fewer. ";
            $prompt .= "Prioritize the file name when determining relevance, but consider the content for context. ";
            $prompt .= "Respond with the name only—no explanations or extra text. ";
            $prompt .= "The document content is enclosed below between #### markers:\n####" . $this->common->substring_words($text, 250) . "####";
        }

        return $prompt;
    }

    public function titleText($extracted_text, $file_name = '')
    {
        $this->initializeChat(0.7);

        $prompt = $this->get_title_prompt($extracted_text, $file_name);

        try {
            $title = $this->chat->sendRequest($prompt);
            $title = $this->format_title($title);
            return $title;
        } catch (Exception $e) {
            echo "Error occured with the AI endpoint: $e";
            //Throws an exception
            throw new Exception('An error occurred while generating a title.');
        }
    }

    public function format_title($title)
    {
        $title = str_replace('"', '', $title);
        $title = trim(str_replace("It appears to be a", "", $title));
        $title = trim(str_replace("This appears to be a", "", $title));
        $title = trim(str_replace("It appears to be the", "", $title));
        $title = trim(str_replace("It appears that you have provided", "", $title));
        $title = trim(str_replace("Title:", "", $title));
        $title = trim(str_replace("Document Name:", "", $title));
        $title = trim(str_replace("####:", "", $title));

        $title = ucfirst($title);
        $title = substr($title, 0, 400);
        return $title;
    }

    public function get_sensitivity_prompt($extracted_text)
    {
        $prompt = "Evaluate the text below and determine whether the organization must notify an individual of a privacy breach if the document contains, could potentially contain, or is perceived by an individual to contain sensitive information, Personally Identifiable Information (PII), or any data an individual may consider private, even if the data is publicly available. Respond with 'true' if there is any possibility that notification is required, or 'false' if it is definitively not. Only respond with true or false. The text to analyze is:\n " . $extracted_text;
        return $prompt;
    }

    public function determineSensitivity($extracted_text)
    {
        $this->initializeChat(0.5);

        $prompt = $this->get_sensitivity_prompt($extracted_text);

        try {
            $response = $this->chat->sendRequest($prompt);
            $response = strtolower(trim($response));
            $is_sensitive = (strpos($response, 'true') !== false);

            return array('is_sensitive' => $is_sensitive);
        } catch (Exception $e) {
            echo "Error occured with the AI endpoint: $e";
            //Throws an exception
            throw new Exception('An error occurred while analyzing the text for sensitive data.');
        }
    }

    public function magic_search_prompt($extracted_text)
    {
        $prompt = "Return a list of words from the below text that could be used to find a matching document.  Return the list as comma separated words.  Return only a comma separated list without explanation.  Exclude any insignificant words or words that might not be contained in matching document summaries.  Do not include any words that are not in the text.  The text to analyze is delimited by: #### \n #### " . $extracted_text . " ####";
        return $prompt;
    }


    public function magic_search($extracted_text)
    {
        $context_window = $this->common->get_config_value('AI_PROCESSING_CONTEXT_WINDOW');
        $this->initializeChat(0.7);
        $this->chat->contextWindow = $context_window;

        $prompt = $this->magic_search_prompt($extracted_text);

        try {
            $response = $this->chat->sendRequest($prompt);
            $response = str_replace("####", "", $response);
            $response = trim($response);
            return $response;
        } catch (Exception $e) {
            echo "Error occured with the AI endpoint: $e";
            //Throws an exception
            throw new Exception('An error occurred while processing the magic search.');
        }
    }

    public function answer_query($prompt)
    {
        $this->initializeChat(0.1);
        //$this->chat->contextWindow = $this->calculate_context_window($prompt);
        $this->chat->contextWindow = $this->common->get_config_value('AI_PROCESSING_CONTEXT_WINDOW');

        try {
            $answer = $this->chat->sendRequest($prompt);
            return $answer;
        } catch (Exception $e) {
            echo "Error occured with the AI endpoint: $e";
            //Throws an exception
            throw new Exception('An error occurred while answering the query.');
        }
    }

    private function calculate_context_window($prompt)
    {
        // Split text into tokens (words and whitespace)
        $tokens = preg_split('/(\s+|\b)/', $prompt, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $tokenCount = 0;

        foreach ($tokens as $token) {
            if (preg_match('/\s/', $token)) {
                // Count whitespace as one token
                $tokenCount += 1;
            } else if (strlen($token) <= 4) {
                // Short words or symbols count as one token 
                $tokenCount += 1;
            } else {
                // For longer words, assume around 4 characters per token
                $tokenCount += ceil(strlen($token) / 4);
            }
        }

        return $tokenCount + 1000;
    }
}
