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

    public function analyzePII($extracted_text)
    {
        $this->initializeChat();

        $prompt = "Does the text below contain PII? PII stands for Personally Identifiable Information, which is any information that can be used to identify a person directly or indirectly. The text is delimited by ####. Answer using json following this format:\n
            \{
              \"contains_social_security_number\": \"yes or no\",
              \"contains_phone_number\": \"yes or no\",
              \"contains_street_address\": \"yes or no\",
              \"contains_first_and_last_name\": \"yes or no\",
              \"contains_medical_information\":\"yes or no\",
              \"contains_email_address\":\"yes or no\",
              \"contains_credit_card\":\"yes or no\",
              \"severity_of_pii\":\"1 to 10\"
            \}
            
            Only respond with valid json. Do not escape quotes.

            Text to analyze:#### " . $extracted_text . "####";

        try {
            $piiAnalysis = $this->chat->sendRequest($prompt);
            return array('pii_analysis' => json_decode($piiAnalysis, true));
        } catch (Exception $e) {
            return array();
        }
    }

    public function contact_information($extracted_text)
    {
        $this->initializeChat(0.7);

        $prompt = "Create a JSON dataset of contact information based on the below text. Respond only with the dataset without explanation. If there is no contact information, respond with an empty value. Only list contacts if there is an associated peace of information, such as a name, phone number, email address, or street address. Provide the results in a valid JSON format. The text is delimieted by #### . The text to analyze is:\n ####" . $extracted_text . '####';

        try {
            $contact_information = $this->chat->sendRequest($prompt);
            $contact_information = $this->format_contact_information($contact_information);
            return $contact_information;
        } catch (Exception $e) {
            return "An error occurred while summarizing the text.";
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
    public function ai_tags($extracted_text)
    {
        $this->initializeChat(0.7);

        $prompt = "From the text provided, provide a comma separated list of relevant tags. Provide only the list without explanation.The text is delimieted by #### . The text to analyze is:\n ####" . $extracted_text . '####';

        try {
            $tags = $this->chat->sendRequest($prompt);
            $tags = $this->format_ai_tags($tags);
            return $tags;
        } catch (Exception $e) {
            return "An error occurred while summarizing the text.";
        }
    }

    public function format_ai_tags($text)
    {
        $text = str_replace("####", "", $text);
        $text = substr($text, 0, 1000);
        return $text;
    }


    public function summarizeText($extracted_text)
    {
        $this->initializeChat(0.7);

        $prompt = "Your task is to review the provided text and create a summary of the content in less than 500 words. Respond with just the summary. Summarize the text delimieted by #### The text to analyze is:\n ####" . $extracted_text . '####';

        try {
            $summary = $this->chat->sendRequest($prompt);
            $summary = $this->format_summary($summary);
            return $summary;
        } catch (Exception $e) {
            return "An error occurred while summarizing the text.";
        }
    }

    public function format_summary($summary)
    {
        $summary = trim(str_replace("Here is a summary of the text in under 500 words:", "", $summary));
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
        $summary = trim(str_replace("Here is a summary of the content in under 500 words:", "", $summary));
        $summary = trim(str_replace("Summary:", "", $summary));
        $summary = trim(str_replace("The document is a:", "", $summary));
        $summary = trim(str_replace("The document is a", "", $summary));
        $summary = trim(str_replace("####", "", $summary));
        $summary = trim(str_replace("Summary\n\n", "", $summary));
        $summary = trim(str_replace("Here is a summary of the content:", "", $summary));
        $summary = trim(str_replace("Here is a summary of the provided text in less than 500 words.", "", $summary));

        $summary = ucfirst($summary);
        $summary = substr($summary, 0, 6000);
        return $summary;
    }

    public function titleText($extracted_text)
    {
        $this->initializeChat(0.7);

        $prompt = "You are an AI specialized in generating document names. Your task is to review the provided text and create a clear, concise document name that captures the essence of the content. The text to name is delimieted by ####. The name should be 10 words or less. Only respond with the name. The text to analyze is:\n ####" . $extracted_text . "####";

        try {
            $title = $this->chat->sendRequest($prompt);
            $title = $this->format_title($title);
            return $title;
        } catch (Exception $e) {
            echo $e;
            return json_encode(['error' => 'An error occurred while generating a title.']);
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

    public function determineSensitivity($extracted_text)
    {
        $this->initializeChat(0.5);

        $prompt = "Evaluate the text below and determine whether the organization must notify an individual of a privacy breach if the document contains, could potentially contain, or is perceived by an individual to contain sensitive information, Personally Identifiable Information (PII), or any data an individual may consider private, even if the data is publicly available. Respond with 'true' if there is any possibility that notification is required, or 'false' if it is definitively not. Only respond with true or false. The text to analyze is:\n " . $extracted_text;

        try {
            $response = $this->chat->sendRequest($prompt);
            $response = strtolower(trim($response));
            $is_sensitive = (strpos($response, 'true') !== false);

            return array('is_sensitive' => $is_sensitive);
        } catch (Exception $e) {
            return json_encode(['error' => 'An error occurred while analyzing the text for sensitive data.']);
        }
    }
}
