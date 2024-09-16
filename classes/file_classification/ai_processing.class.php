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
        $prompt = "Does the text below contain PII? PII stands for Personally Identifiable Information, which is any information that can be used to identify a person directly or indirectly. The text is delimited by ####. Answer using json following this format:\n
            {
              \"contains_social_security_number\": \"yes or no\",
              \"contains_phone_number\": \"yes or no\",
              \"contains_street_address\": \"yes or no\",
              \"contains_first_and_last_name\": \"yes or no\",
              \"contains_personal_medical_information\":\"yes or no\",
              \"contains_username_and_password\":\"yes or no\",
              \"contains_email_address\":\"yes or no\",
              \"contains_credit_card\":\"yes or no\",
              \"contains_banking_information\":\"yes or no\",
              \"severity_of_personal_information\":\"1 to 10\"
            }
            
            Only respond with valid json. Do not escape quotes.

            Text to analyze:####" . $extracted_text . "####";
        if (strlen(trim($this->common->get_config_value('PROMPT_OVERRIDE_PII'))) > 10) {
            $prompt = $this->common->get_config_value('PROMPT_OVERRIDE_PII') . " #### " . $extracted_text . "####";
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
        $prompt = "From the text provided, provide a comma separated list of relevant tags. Provide only the list without explanation.The text is delimieted by #### . The text to analyze is:\n ####" . $extracted_text . '####';
        if (strlen(trim($this->common->get_config_value('PROMPT_OVERRIDE_TAGS'))) > 10) {
            $prompt = $this->common->get_config_value('PROMPT_OVERRIDE_TAGS') . " #### " . $extracted_text . "####";
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
        $summary_length = $this->common->get_config_value('AI_PROCESSING_SUMMARY_LENGTH');
        $prompt = "Your task is to review the provided text and create a summary of the content in less than $summary_length words. Respond with just the summary without any additional text or introduction. This summary will be used in a search index so include any relevant details that a user might search for. Summarize the text delimited by #### The text to analyze is:\n";
        $prompt .= "#### " . $extracted_text . ' ####';

        if (strlen(trim($this->common->get_config_value('PROMPT_OVERRIDE_SUMMARY'))) > 10) {
            $prompt = $this->common->get_config_value('PROMPT_OVERRIDE_SUMMARY') . " #### " . $extracted_text . "####";
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

    public function get_title_prompt($extracted_text)
    {
        $prompt = "You are an AI specialized in generating document names. Your task is to review the provided text and create a clear, concise document name that captures the essence of the content. The text to name is delimieted by ####. The name should be 10 words or less. Only respond with the name. The text to analyze is:\n ####" . $extracted_text . "####";
        if (strlen(trim($this->common->get_config_value('PROMPT_OVERRIDE_TITLE'))) > 10) {
            $prompt = $this->common->get_config_value('PROMPT_OVERRIDE_TITLE') . " #### " . $extracted_text . "####";
        }
        return $prompt;
    }

    public function titleText($extracted_text)
    {
        $this->initializeChat(0.7);

        $prompt = $this->get_title_prompt($extracted_text);

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
}
