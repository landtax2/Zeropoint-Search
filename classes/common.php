<?PHP
//this is a class that will contain the common functions that are used throughout the project, such as database connections, session management, and other common functions.
class common
{

    private $db_connection;
    public $env;
    public $db_version = '112';
    public $boolean = array('0' => 'False', '1' => 'True');

    public function __construct($env)
    {
        $this->env = $env;
        $this->db_connect();

        //set the timezone
        date_default_timezone_set($this->get_timezone());
    }

    public function get_timezone()
    {

        if (isset($_SERVER['TZ'])) {
            return $_SERVER['TZ'];
        } else if (isset($_ENV['TZ'])) {
            return $_ENV['TZ'];
        } else {
            return 'America/New_York';
        }
    }

    //connects to a postgre database using PDO
    public function db_connect()
    {
        //this function will connect to the database
        try {
            $pdo = new PDO("pgsql:host=" . $this->get_config_value('DB_HOST') . ";dbname=" . $this->get_config_value('DB_NAME') . ";port=" . $this->get_config_value('DB_PORT'), $this->get_config_value('DB_USER'), $this->get_config_value('DB_PASS'));
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db_connection = $pdo;
        } catch (Exception $e) {
            echo "Database Host: " . $this->get_config_value('DB_HOST') . "<br/>";
            echo "Database Name: " . $this->get_config_value('DB_NAME') . "<br/>";
            echo "Database Port: " . $this->get_config_value('DB_PORT') . "<br/>";
            echo "Database User: " . $this->get_config_value('DB_USER') . "<br/>";
            echo "Database Password: Will not be displayed for security reasons. <br/>";
            throw new Exception("Database connection failed: " . $e->getMessage(), 1);
        }
    }

    public function get_config_value($setting)
    {
        //linux uses $_ENV, windows uses $_SERVER for environment variables
        //get it from the environment variable first, then the server variable, then the database
        //ENV file takes presidence over server/environment variables
        //Database is used if no other value is found
        if (isset($this->env[$setting])) {
            return $this->env[$setting];
        } else if (isset($_ENV[$setting])) {
            return $_ENV[$setting];
        } else if (isset($_SERVER[$setting])) {
            return $_SERVER[$setting];
        } else {
            try {
                $stmt = $this->db_connection->prepare("SELECT value FROM config WHERE setting = :setting");
                $stmt->bindParam(':setting', $setting);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['value'];
            } catch (Exception $e) {
                throw new Exception("Get config value failed: " . $e->getMessage(), 1);
            }
        }
    }

    //function to get all config values
    public function get_all_config_values()
    {
        try {
            $stmt = $this->db_connection->prepare("SELECT * FROM config");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $e) {
            throw new Exception("Get all config values failed: " . $e->getMessage(), 1);
        }
    }

    public function query_to_sd_array($queryText, $queryParams = false)
    {
        try {
            $stmt = $this->db_connection->prepare($queryText);
            if ($queryParams && is_array($queryParams)) {
                foreach ($queryParams as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
            }
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $e) {
            //unparameterizes the query for logging
            if (is_array($queryParams)) {
                $queryText = str_replace(array_keys($queryParams), array_map(function ($value) {
                    return "'" . $value . "'";
                }, array_values($queryParams)), $queryText);
            }
            $log = ['query' => $queryText, 'params' => $queryParams, 'error' => $e->getMessage()];
            $this->write_to_log('database', 'Database query failed', $log);
            throw new Exception("Database query failed: " . $e->getMessage() . "\n Query: $queryText", 1);
        }
    }

    public function query_to_md_array($queryText, $queryParams = false)
    {
        try {
            $stmt = $this->db_connection->prepare($queryText);
            if ($queryParams && is_array($queryParams)) {
                foreach ($queryParams as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
            }
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $e) {
            //unparameterizes the query for logging
            if (is_array($queryParams)) {
                $queryText = str_replace(array_keys($queryParams), array_map(function ($value) {
                    return "'" . $value . "'";
                }, array_values($queryParams)), $queryText);
            }
            $log = ['query' => $queryText, 'params' => $queryParams, 'error' => $e->getMessage()];
            $this->write_to_log('database', 'Database query failed', $log);
            throw new Exception("Database query failed: " . $e->getMessage() . "\n Query: $queryText", 1);
        }
    }

    //function to check if a table exists
    public function does_table_exist($table_name)
    {
        $queryText = "SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE  table_schema = 'public'
            AND    table_name   = :table_name
            ) as \"exist\";";
        $queryParams = array(':table_name' => $table_name);
        $result = $this->query_to_sd_array($queryText, $queryParams);
        if ($result['exist'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function get_db_connection()
    {
        return $this->db_connection;
    }

    public function local_only()
    {
        //prevents leaks from non_local or allowed_ips
        $allowed_ips = explode(',', $this->get_config_value('ALLOWED_IP'));

        //get the ip address of the user - handles proxy or real ip
        $ip = $this->get_ip();

        //allows all IPs if 0.0.0.0 is in the array
        if (in_array('0.0.0.0', $allowed_ips)) {
            return false;
        }

        if (!$this->is_privateIP($ip) && !in_array($ip, $allowed_ips)) {
            $this->write_to_log('security', 'IP denied access to front-end.', $ip);
            die('Non_local_address: ' . $ip);
        }
    }

    public function get_ip()
    {
        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (isset($_SERVER['REMOTE_HOST'])) {
            $ip = $_SERVER['REMOTE_HOST'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    //checks if the ip is in the CIDR private ip range
    public function is_privateIP($ip)
    {
        $ip_parts = explode('.', $ip);
        $first = (int) $ip_parts[0];
        $second = (int) $ip_parts[1];
        $third = (int) $ip_parts[2];
        $fourth = (int) $ip_parts[3];

        if (
            $first == 10 ||
            ($first == 192 && $second == 168) ||
            ($first == 172 && $second >= 16 && $second <= 31) ||
            ($first == 127) ||
            ($first >= 224 && $first <= 239)
        ) {
            return true;
        } else {
            return false;
        }
    }

    //deprecated
    public function get_env_value($value)
    {
        try {
            return $this->env[$value];
        } catch (Exception $e) {
            throw new Exception("Environment variable " . $value . " not found", 1);
        }
    }

    public function security_check()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->write_to_log('security', 'No Login', $_SERVER);
            die("No Login");
        }
    }

    public function validate_api_key($api_key)
    {
        $data = $this->query_to_sd_array("SELECT * FROM client WHERE api_key = :api_key", array(':api_key' => $api_key));
        if ($data) {
            return true;
        } else {
            $this->write_to_log('security', 'Invalid API Key', $api_key);
            return false;
        }
    }

    public function api_key_to_client_id($api_key)
    {
        $data = $this->query_to_sd_array("SELECT * FROM client WHERE api_key = :api_key", array(':api_key' => $api_key));
        if ($data) {
            return $data['id'];
        } else {
            throw new Exception("API key not found", 1);
        }
    }

    public function datetime_to_postgres_format($datetime)
    {
        if (empty($datetime)) {
            return 'null';
        } else {
            return date('Y-m-d H:i:s', strtotime($datetime));
        }
    }

    public function sql2date($date)
    {
        if (empty($date)) {
            return 'null';
        } else {
            return date('m/d/Y', strtotime($date));
        }
    }

    public function sql2date_military_time($date)
    {
        if (empty($date) || strlen($date) === 0 || $date == '0') {
            return null;
        } else {
            return date('m/d/Y H:i:s', strtotime($date));
        }
    }

    public function humanFileSize($size, $unit = "")
    {
        if ((!$unit && $size >= 1 << 30) || $unit == "GB")
            return number_format($size / (1 << 30), 2) . "GB";
        if ((!$unit && $size >= 1 << 20) || $unit == "MB")
            return number_format($size / (1 << 20), 2) . "MB";
        if ((!$unit && $size >= 1 << 10) || $unit == "KB")
            return number_format($size / (1 << 10), 2) . "KB";
        return number_format($size) . " bytes";
    }

    public function write_to_log($log_name, $title, $data = '')
    {
        //only write to logs if debugging is enabled
        if ($this->get_config_value('DEBUGGING') == '0') {
            return;
        }

        $log_file = $_SERVER['DOCUMENT_ROOT'] . '/logs/' . $log_name . '.log';

        if (is_array($data)) {
            $data = json_encode($data);
        }
        $data = date('Y-m-d H:i:s') . ' - ' . $title . ' ::: ' . $data . "\n";
        try {
            file_put_contents($log_file, $data, FILE_APPEND);
        } catch (Exception $e) {
            // Throw a warning exception
            throw new Exception("Warning: Error writing to log: " . $e->getMessage(), E_WARNING);
        }
    }

    public function get_protocol()
    {
        $secure = false;
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $secure = true;
        }
        if ((!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
        ) {
            $secure = true;
        }
        $protocol = $secure ? 'https' : 'http';
        return $protocol;
    }

    public function anti_cache_headers()
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Expires: -1");
    }

    public function get_network_root_path($path)
    {
        // Find the position of the last backslash
        $lastBackslashPos = strrpos($path, '\\');

        // If a backslash was found, truncate the string up to the last backslash
        if ($lastBackslashPos !== false) {
            return substr($path, 0, $lastBackslashPos);
        } else {
            // If no backslash was found, return the original string
            return $path;
        }
    }

    public function respond_with_error($message)
    {
        $this->respond_with_json(['error' => $message], 400);
    }

    public function respond_with_json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }


    public function print_template_card($title, $type = 'start')
    {
        if ($type == 'start_no_title') {
            echo '<div id="main-card-container" class="card" style="">
                    <div class="card-body pt-3">
                        <div class="media">
                        <div class="media-body" style="">';
        } else if ($type == 'start') {
            echo '<div id="main-card-container" class="card" style="">
                <h5 class="card-header">' . $title . '</h5>
                    <div class="card-body pt-3">
                        <div class="media">
                        <div class="media-body" style="">';
        } else if ($type == 'end') {
            echo '<p style="visibility: hidden">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas ultrices venenatis suscipit. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Vestibulum vel nisi ac leo pulvinar mollis. Suspendisse lacus lorem, euismod id libero non, sodales varius nunc. Proin scelerisque aliquam turpis et dignissim. Ut volutpat condimentum libero, quis facilisis metus accumsan ac. Etiam quis mi ut tortor lacinia laoreet. Nunc eget felis sed erat tincidunt laoreet. Nullam quis turpis sed orci vehicula euismod.
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas ultrices venenatis suscipit. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Vestibulum vel nisi ac leo pulvinar mollis. Suspendisse lacus lorem, euismod id libero non, sodales varius nunc. Proin scelerisque aliquam turpis et dignissim. Ut volutpat condimentum libero, quis facilisis metus accumsan ac. Etiam quis mi ut tortor lacinia laoreet. Nunc eget felis sed erat tincidunt laoreet. Nullam quis turpis sed orci vehicula euismod.
            </p>';
            echo '</div></div></div></div>';
        }
    }
}
