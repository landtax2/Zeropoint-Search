<?PHP
class router
{
    private $s1;
    private $s2;
    private $s3;
    private $s4;
    private $file;
    private $file_path;


    public function __construct()
    {
        $this->s1 = isset($_GET['s1']) ? $_GET['s1'] : 'Dashboard';
        $this->s2 = isset($_GET['s2']) ? $_GET['s2'] : false;
        $this->s3 = isset($_GET['s3']) ? $_GET['s3'] : false;
        $this->s4 = isset($_GET['s4']) ? $_GET['s4'] : false;
        $this->file = isset($_GET['file']) ? $_GET['file'] : false;

        $basePath = $_SERVER['DOCUMENT_ROOT'] . '/pages/';

        //s1 is always set
        $this->file_path = $basePath . $this->s1 . '/';

        if (!empty($this->s2)) {
            $this->file_path .= $this->s2 . '/';
        }
        if (!empty($this->s3)) {
            $this->file_path .= $this->s3 . '/';
        }
        if (!empty($this->s4)) {
            $this->file_path .= $this->s4 . '/';
        }

        if (empty($this->file)) {
            $this->file_path .= 'index.php';
        } else {
            $this->file_path .= $this->file . '.php';
        }
    }

    public function route()
    {
        if (file_exists($this->file_path)) {
            include($this->file_path);
        } else {
            // If the file doesn't exist throw an exception
            throw new Exception('File: ' . $this->file_path . ' not found');
        }
    }

    public function get_file()
    {
        return $this->file;
    }

    public function get_file_path()
    {
        if (file_exists($this->file_path)) {
            return $this->file_path;
        } else {
            // If the file doesn't exist throw an exception
            throw new Exception('File: ' . $this->file_path . ' not found');
        }
    }
}
