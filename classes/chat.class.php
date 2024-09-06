<?PHP
// Class for handling chat functionality with ollama
class Chat_ollama
{
    // Private properties for storing chat configuration
    private $url;
    private $model;
    private $temperature = 0.7;
    private $maxTokens = -1;
    private $stream = false;
    public float $seed = -1;
    public int $contextWindow = 6000;
    private $common;

    // Constructor to initialize the Chat object
    public function __construct($common)
    {
        //sets url and model from the config
        $this->url = $common->get_config_value('CHAT_API_OLLAMA');
        $this->model = $common->get_config_value('CHAT_API_OLLAMA_MODEL');
    }

    // Method to send a request to the chat API
    public function sendRequest(string $prompt): string
    {
        // Prepare the data for the API request
        $prompt = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $prompt); //removes non-printable characters
        $data = [
            "model" => $this->model,
            "options" => [
                "temperature" => $this->temperature,
                "seed" => $this->seed,
                "num_ctx" => $this->contextWindow,
            ],
            "stream" => $this->stream,
            "prompt" => $prompt,
            "timeout" => 240,
            "system" => 'You are a helpful, smart, and efficient AI assistant. You always fulfill the user\'s requests precisely.',
        ];

        // Encode the data as JSON
        $payload = json_encode($data);
        //print_r($data);
        //echo $payload;

        // Initialize cURL session
        $ch = curl_init($this->url);
        // Set cURL options
        curl_setopt_array($ch, [
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
            CURLOPT_POST => true,
            CURLOPT_VERBOSE => true,
            CURLOPT_STDERR => fopen('php://stderr', 'w'),
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 180,
        ]);

        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            curl_close($ch);
            //die('Curl error: ' . curl_error($ch));
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        // Close cURL session
        curl_close($ch);

        // Decode the JSON response
        $data = json_decode($response, true);

        // Check for JSON decoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON decoding error: ' . json_last_error_msg());
        }

        // Check if the expected data is present in the response
        //print_r($data);
        //die('test');
        if (!isset($data['response'])) {
            print_r($data);
            throw new Exception('Unexpected API response format');
        }

        // Return the content of the chat response
        return $data['response'];
    }
}
