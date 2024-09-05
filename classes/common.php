<?PHP
//this is a class that will contain the common functions that are used throughout the project, such as database connections, session management, and other common functions.
class common
{

    private $db_connection;
    private $env;

    public function __construct($env)
    {
        $this->env = $env;
        //set the timezone
        date_default_timezone_set($env['TIME_ZONE']);
        $this->db_connect();
    }

    //connects to a postgre database using PDO
    public function db_connect()
    {
        //this function will connect to the database
        try {
            $pdo = new PDO("pgsql:host=" . $this->env['DB_HOST'] . ";dbname=" . $this->env['DB_NAME'], $this->env['DB_USER'], $this->env['DB_PASS']);
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
            return $result;
        } catch (Exception $e) {
            throw new Exception("Database connection failed: " . $e->getMessage(), 1);
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
            throw new Exception("Database connection failed: " . $e->getMessage(), 1);
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
            throw new Exception("Database query failed: " . $e->getMessage(), 1);
        }
    }

    public function query_to_md_array($queryText, $queryParams = false)
    {
        try {
            $stmt = $this->db_connection->prepare($queryText);
            if ($queryParams) {
                foreach ($queryParams as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
            }
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $e) {
            throw new Exception("Database query failed: " . $e->getMessage(), 1);
        }
    }

    public function get_db_connection()
    {
        return $this->db_connection;
    }

    public function local_only()
    {
        //prevents leaks from non_local or allowed_ips
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/.env')) {
            $env = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/.env');
        } else {
            $env = parse_ini_file('.env');
        }

        $allowed_ips = isset($env['ALLOWED_IP']) ? explode(',', $env['ALLOWED_IP']) : [];

        //get the ip address of the user - handles proxy or real ip
        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } else {
            $ip = $_SERVER['REMOTE_HOST'];
        }

        if (!$this->is_privateIP($ip) && !in_array($ip, $allowed_ips)) {
            die('Non_local_address');
        }
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

    public static function security_check()
    {
        if (!isset($_SESSION['userID'])) {
            die("No Login");
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
