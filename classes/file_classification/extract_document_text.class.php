<?PHP

class extract_document_text
{
    private $ocrParam;
    private $common;
    private const CURL_TIMEOUT = 120;
    private $curlOptions = [];

    public function __construct($common, $ocr = false)
    {
        $this->common = $common;
        $this->ocrParam = $ocr ? "?ocr_available=True" : "";
        $this->initDefaultCurlOptions();
    }

    private function initDefaultCurlOptions()
    {
        $this->curlOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => self::CURL_TIMEOUT,
        ];
    }

    public function extract($file)
    {
        // Validate file input
        if (!$this->validateFile($file)) {
            throw new InvalidArgumentException('Invalid file input');
        }

        // Prepare the cURL request
        $curl = curl_init();
        $this->curlOptions[CURLOPT_URL] = $this->common->get_config_value('DOCTOR_API') . '/extract/doc/text/' . $this->ocrParam;
        $this->curlOptions[CURLOPT_POSTFIELDS] = [
            'file' => new CURLFile($file['tmp_name'], strtolower($file['type']), strtolower($file['name']))
        ];
        curl_setopt_array($curl, $this->curlOptions);

        try {
            // Send the request and get the response
            $response = $this->executeCurlRequest($curl);
            return $response['content'];
        } finally {
            curl_close($curl);
        }
    }

    private function executeCurlRequest($curl)
    {
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($response === false) {
            throw new RuntimeException(curl_error($curl), curl_errno($curl));
        }

        if ($httpCode !== 200) {
            throw new RuntimeException("HTTP request failed. Status code: $httpCode");
        }

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("JSON decoding failed: " . json_last_error_msg());
        }

        if (!isset($decodedResponse['content'])) {
            throw new RuntimeException("'content' key not found in the response");
        }

        return $decodedResponse;
    }

    private function validateFile($file)
    {
        return isset($file) &&
            is_array($file) &&
            isset($file['error']) &&
            $file['error'] === UPLOAD_ERR_OK &&
            isset($file['tmp_name']) &&
            isset($file['type']) &&
            isset($file['name']);
    }

    public function setCustomCurlOptions(array $options)
    {
        // Merge custom options with default options
        $this->curlOptions = array_merge($this->curlOptions, $options);
    }
}
