<?PHP
class router
{
    private $page;
    private $sub_dir;
    private $file;
    private $file_path;


    public function __construct()
    {
        $this->page = isset($_GET['page']) ? $_GET['page'] : 'home';
        $this->sub_dir = isset($_GET['sub_dir']) ? $_GET['sub_dir'] : false;
        $this->file = isset($_GET['file']) ? $_GET['file'] : false;

        $basePath = $_SERVER['DOCUMENT_ROOT'] . '/pages/';
        $this->file_path = $basePath . $this->page . '/';

        if (!empty($this->sub_dir)) {
            $this->file_path .= $this->sub_dir . '/';
        } else {
            $this->file_path .= 'index.php';
        }

        if (!empty($this->file)) {
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

    public function get_page()
    {
        return $this->page;
    }

    public function get_sub_dir()
    {
        return $this->sub_dir;
    }

    public function get_file()
    {
        return $this->file;
    }
}
