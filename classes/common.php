<?PHP
//this is a class that will contain the common functions that are used throughout the project, such as database connections, session management, and other common functions.
class common
{

    private $db_connection;
    private $env;
    public $db_version = '107';
    public $boolean = array('0' => 'False', '1' => 'True');

    public function __construct($env)
    {
        $this->env = $env;
        $this->db_connect();

        //set the timezone
        try {
            date_default_timezone_set($this->get_config_value('TIME_ZONE'));
        } catch (Exception $e) {
            echo 'Unable to set timezone.  Defaulting to UTC.<br/>';
        }
    }

    //connects to a postgre database using PDO
    public function db_connect()
    {
        //this function will connect to the database
        try {
            $pdo = new PDO("pgsql:host=" . $this->env['DB_HOST'] . ";dbname=" . $this->env['DB_NAME'] . ";port=" . $this->env['DB_PORT'], $this->env['DB_USER'], $this->env['DB_PASS']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db_connection = $pdo;
        } catch (Exception $e) {
            throw new Exception("Database connection failed: " . $e->getMessage(), 1);
        }
    }

    public function get_config_value($setting)
    {
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

    public function query_to_sd_array($queryText, $queryParams)
    {
        try {
            $stmt = $this->db_connection->prepare($queryText);
            if ($queryParams) {
                foreach ($queryParams as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
            }
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $e) {
            $this->write_to_log('database', 'Database query failed: ' . $e->getMessage() . ' in ' . $queryText . ' with params ' . json_encode($queryParams));
            throw new Exception("Database query failed: " . $e->getMessage() . " in " . $queryText . " with params " . json_encode($queryParams), 1);
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
            $this->write_to_log('database', 'Database query failed: ' . $e->getMessage() . ' in ' . $queryText . ' with params ' . json_encode($queryParams));
            throw new Exception("Database query failed: " . $e->getMessage() . " in " . $queryText . " with params " . json_encode($queryParams), 1);
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
        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } else {
            $ip = $_SERVER['REMOTE_HOST'];
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
        } else {
            $ip = $_SERVER['REMOTE_HOST'];
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
        if ($this->get_env_value('DEBUGGING') == '0') {
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
