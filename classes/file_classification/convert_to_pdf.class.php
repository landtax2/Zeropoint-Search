<?php

class convert_to_pdf
{
    private $apiEndpoint;
    private $tempDir;
    private $common;

    public function __construct($common)
    {
        $this->tempDir = sys_get_temp_dir();
        $this->common = $common;
        $this->apiEndpoint = $common->get_config_value('STIRLING_PDF_API');
    }

    public function convert($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: $filePath");
        }

        $curl = curl_init();

        $postFields = [
            'fileInput' => new CURLFile($filePath)
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiEndpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => [
                'Content-Type: multipart/form-data'
            ],
            CURLOPT_VERBOSE => true
        ]);


        $response = curl_exec($curl);

        if ($response === false) {
            throw new Exception('Curl error: ' . curl_error($curl));
        }

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode !== 200) {
            throw new Exception("API request failed with HTTP code $httpCode");
        }

        $tempFileName = $this->generateTempFileName();
        $tempFilePath = $this->tempDir . DIRECTORY_SEPARATOR . $tempFileName;

        if (file_put_contents($tempFilePath, $response) === false) {
            throw new Exception("Failed to save PDF file");
        }

        return $tempFilePath;
    }

    private function generateTempFileName()
    {
        return uniqid('pdf_', true) . '.pdf';
    }
}
