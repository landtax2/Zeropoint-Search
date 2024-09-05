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
    }

    //connects to a postgre database using PDO
    public function db_connect()
    {
        //this function will connect to the database
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
}
