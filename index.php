<?PHP
//start the session
session_start();

//include the common and router classes
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/common.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/router.php');

//parse the .env file if it exists
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/.env')) {
    $env = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/.env');
} else {
    $env = [];
}

//instantiate the common class
try {
    //env is passed to the common class to be loaded into the class
    $common = new common($env);
} catch (Exception $e) {
    echo json_encode(array('success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()));
    exit;
}

/*standard headers to prevent caching*/
$common->anti_cache_headers();

//checks if the user is logged in - needs to come before local_only to allow for database creation
if (!isset($_SESSION['user_id'])) {
    header('Location: /account/login/index.php');
    exit;
}

//local only after setup
$common->local_only();

//instantiate the router class - determines the content to load
$router = new router();

($common->get_config_value('DEBUGGING') == '1') ? ini_set('display_errors', 1) : ini_set('log_errors', 0); //turns off error logging if not debugging



//check for database updates
$queryText = "SELECT * FROM config WHERE setting = 'DB_VERSION'";
$db_version = $common->query_to_sd_array($queryText);
if ($db_version['value'] < $common->db_version) {
    header('Location: /setup/update/index.php');
    exit;
}

//log access to the front-end
$access = [
    'IP' => $common->get_ip(),
    'User ID' => $_SESSION['user_id'],
    'Arguments' => $_GET,
    'User Agent' => $_SERVER['HTTP_USER_AGENT']
];
$common->write_to_log('access', $_SERVER['REQUEST_URI'], $access);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <base href="./">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="<?= $common->get_config_value('APPLICATION_DESCRIPTION') ?>">
    <meta name="author" content="Landtax">
    <meta name="keyword" content="File Search, AI, ZeroPoint">
    <title><?= $common->get_config_value('APPLICATION_NAME') ?></title>
    <link rel="apple-touch-icon" sizes="57x57" href="/assets/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/assets/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/assets/favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/assets/favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/assets/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/assets/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/assets/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/assets/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/assets/favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/assets/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
    <link rel="manifest" href="/assets/favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/assets/favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <!-- Vendors styles-->
    <link rel="stylesheet" href="/coreui/vendors/simplebar/css/simplebar.css">
    <link rel="stylesheet" href="/coreui/css/vendors/simplebar.css">
    <!-- Main styles for this application-->
    <link href="/coreui/css/style.css" rel="stylesheet">
    <!-- CoreUI JS -->
    <script src="/coreui/js/config.js"></script>
    <script src="/coreui/js/color-modes.js"></script>
    <!-- CoreUI Chartjs -->
    <!--<link href="/coreui/vendors/@coreui/chartjs/css/coreui-chartjs.css" rel="stylesheet">-->

    <!--Non CoreUI Components-->
    <!-- Data Tables Main-->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <link href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css" rel="stylesheet">

    <!-- Data Tables Buttons-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.js"></script>

    <!-- Data Tables Responsive -->
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.js"></script>

    <!-- Prism -->
    <script src="/vendors/prism/prism.js"></script>
    <link rel="stylesheet" href="/vendors/prism/prism.css">

    <!-- Sweet Alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/90add283c4.js" crossorigin="anonymous"></script>

    <!-- Chat JS -->
    <script src="/scripts/js/chat.js"></script>

    <!-- Common -->
    <script src="/scripts/js/common.js"></script>

    <!-- CSS Overrides -->
    <link rel="stylesheet" href="/scripts/css/main.css">
</head>

<body>
    <div class="sidebar sidebar-dark sidebar-fixed border-end" id="sidebar">
        <div class="sidebar-header border-bottom">
            <div class="sidebar-brand">
                <!--<svg class="sidebar-brand-full" width="88" height="32" alt="CoreUI Logo">
                    <use xlink:href="/assets/brand/coreui.svg#full"></use>
                </svg>
                <svg class="sidebar-brand-narrow" width="32" height="32" alt="CoreUI Logo">
                    <use xlink:href="/assets/brand/coreui.svg#signet"></use>
                </svg>-->
                <div class="sidebar-brand-full">
                    <i class="fa-brands fa-galactic-republic fa-2x"></i>
                    <span class="ms-2"><?= $common->get_config_value('APPLICATION_NAME') ?></span>
                </div>
                <div class="sidebar-brand-narrow">
                    <i class="fa-brands fa-galactic-republic fa-2x"></i>
                </div>
            </div>
            <button class="btn-close d-lg-none" type="button" data-coreui-dismiss="offcanvas" data-coreui-theme="dark" aria-label="Close" onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()"></button>
        </div>
        <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">
            <li class="nav-item"><a class="nav-link" href="/">
                    <svg class="nav-icon">
                        <use xlink:href="/coreui/vendors/@coreui/icons/svg/free.svg#cil-speedometer"></use>
                    </svg> Dashboard<span class="badge badge-sm bg-info ms-auto">Home</span></a></li>
            <li class="nav-title">Core Functions</li>
            <li class="nav-group">
                <a class="nav-link nav-group-toggle" href="#">
                    <svg class="nav-icon">
                        <use xlink:href="/coreui/vendors/@coreui/icons/svg/free.svg#cil-search"></use>
                    </svg> Searches
                </a>
                <ul class="nav-group-items">
                    <li class="nav-item">
                        <a class="nav-link" href="/?s1=Search&s2=Main">
                            <span class="nav-icon"></span><i class="fa fa-search"></i> &nbsp; Main Search
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/?s1=Search&s2=Summary&s3=Ranked">
                            <span class="nav-icon"></span><i class="fa fa-ranking-star"></i> &nbsp; Ranked Summary
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/?s1=Search&s2=Fulltext&s3=Ranked">
                            <span class="nav-icon"></span><i class="fa fa-search"></i> &nbsp; Ranked Fulltext
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/?s1=Search&s2=Tag">
                            <span class="nav-icon"></span><i class="fa fa-tag"></i> &nbsp; Tag Search
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/?s1=Search&s2=Magic">
                            <span class="nav-icon"></span><i class="fa fa-magic"></i> &nbsp; Magic Search
                        </a>
                    </li>
                </ul>
            </li>
            <li class="nav-group">
                <a class="nav-link nav-group-toggle" href="#">
                    <svg class="nav-icon">
                        <use xlink:href="/coreui/vendors/@coreui/icons/svg/free.svg#cil-settings"></use>
                    </svg> Settings
                </a>
                <ul class="nav-group-items">
                    <li class="nav-item">
                        <a class="nav-link" href="/?s1=Settings&s2=Clients">
                            <span class="nav-icon"></span><i class="fa fa-person"></i> &nbsp; Clients
                        </a>
                        <a class="nav-link" href="/?s1=Settings&s2=Configuration">
                            <span class="nav-icon"></span><i class="fa fa-gear"></i> &nbsp; Configuration
                        </a>
                        <a class="nav-link" href="/?s1=Settings&s2=Changelog">
                            <span class="nav-icon"></span><i class="fa fa-history"></i> &nbsp; Changelog
                        </a>
                        <a class="nav-link" href="/?s1=Settings&s2=Database">
                            <span class="nav-icon"></span><i class="fa fa-database"></i> &nbsp; Database
                        </a>
                        <a class="nav-link" href="/?s1=Settings&s2=Integrations">
                            <span class="nav-icon"></span><i class="fa fa-plug"></i> &nbsp; Integrations
                        </a>
                        <a class="nav-link" href="/?s1=Settings&s2=Classification Prompts">
                            <span class="nav-icon"></span><i class="fa fa-file-alt"></i> &nbsp; Classification Prompts
                        </a>

                        <?php
                        if ($common->get_config_value('DEBUGGING') == "1") {
                        ?>
                            <a class="nav-link" href="/?s1=Settings&s2=Logs">
                                <span class="nav-icon"></span><i class="fa fa-file-alt"></i> &nbsp; Logs
                            </a>
                            <a class="nav-link" href="/?s1=Settings&s2=PHP">
                                <span class="nav-icon"></span><i class="fa-brands fa-php"></i> &nbsp; PHP
                            </a>
                        <?php
                        }
                        ?>
                    </li>
                </ul>
            </li>
            <li class="nav-group">
                <a class="nav-link nav-group-toggle" href="#">
                    <svg class="nav-icon">
                        <use xlink:href="/coreui/vendors/@coreui/icons/svg/free.svg#cil-book"></use>
                    </svg> Reports
                </a>
                <ul class="nav-group-items">
                    <li class="nav-item"><a class="nav-link" href="/?s1=Reports&s2=Pii">
                            <span class="nav-icon"></span><i class="fa fa-file-alt"></i> &nbsp; PII Reports
                        </a>
                    </li>
                </ul>
            </li>
            <li class="nav-item mt-auto"><a class="nav-link" href="/?s1=Docs" target="_blank">
                    <svg class="nav-icon">
                        <use xlink:href="/coreui/vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                    </svg> Docs</a></li>
        </ul>
        <div class="sidebar-footer border-top d-none d-md-flex">
            <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
        </div>
    </div>
    <div class="wrapper d-flex flex-column min-vh-100">
        <header class="header header-sticky p-0 mb-4">
            <div class="container-fluid border-bottom px-4">
                <button class="header-toggler" type="button" onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()" style="margin-inline-start: -14px;">
                    <svg class="icon icon-lg">
                        <use xlink:href="/coreui/vendors/@coreui/icons/svg/free.svg#cil-menu"></use>
                    </svg>
                </button>
                <ul class="header-nav d-none d-lg-flex">
                    <!--<li class="nav-item"><a class="nav-link" href="/">Dashboard</a></li>-->
                </ul>
                <ul class="header-nav ms-auto">
                </ul>
                <ul class="header-nav">
                    <li class="nav-item py-1">
                        <div class="vr h-100 mx-2 text-body text-opacity-75"></div>
                    </li>
                    <li class="nav-item dropdown">
                        <button class="btn btn-link nav-link py-2 px-2 d-flex align-items-center" type="button" aria-expanded="false" data-coreui-toggle="dropdown">
                            <svg class="icon icon-lg theme-icon-active">
                                <use xlink:href="/coreui/vendors/@coreui/icons/svg/free.svg#cil-contrast"></use>
                            </svg>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" style="--cui-dropdown-min-width: 8rem;">
                            <li>
                                <button class="dropdown-item d-flex align-items-center" type="button" data-coreui-theme-value="light">
                                    <svg class="icon icon-lg me-3">
                                        <use xlink:href="/coreui/vendors/@coreui/icons/svg/free.svg#cil-sun"></use>
                                    </svg>Light
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item d-flex align-items-center" type="button" data-coreui-theme-value="dark">
                                    <svg class="icon icon-lg me-3">
                                        <use xlink:href="/coreui/vendors/@coreui/icons/svg/free.svg#cil-moon"></use>
                                    </svg>Dark
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item d-flex align-items-center active" type="button" data-coreui-theme-value="auto">
                                    <svg class="icon icon-lg me-3">
                                        <use xlink:href="/coreui/vendors/@coreui/icons/svg/free.svg#cil-contrast"></use>
                                    </svg>Auto
                                </button>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item py-1">
                        <div class="vr h-100 mx-2 text-body text-opacity-75"></div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link py-0 pe-0" data-coreui-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                            <div class="avatar avatar-md"><img class="avatar-img" src="/assets/avatar/zp1.png" alt="user@email.com"></div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end pt-0">
                            <div class="dropdown-header bg-body-tertiary text-body-secondary fw-semibold my-2">
                                <div class="fw-semibold">Settings</div>
                            </div><a class="dropdown-item" href="/?s1=Settings&s2=Clients">
                                <svg class="icon me-2">
                                    <use xlink:href="/coreui/vendors/@coreui/icons/svg/free.svg#cil-user"></use>
                                </svg> Clients</a><a class="dropdown-item" href="/?s1=Settings&s2=Configuration">
                                <svg class="icon me-2">
                                    <use xlink:href="/coreui/vendors/@coreui/icons/svg/free.svg#cil-settings"></use>
                                </svg> Settings</a><a class="dropdown-item" href="#">
                                <div class="dropdown-divider"></div><a id="logout-button" class="dropdown-item" href="#">
                                    <svg class="icon me-2">
                                        <use xlink:href="/coreui/vendors/@coreui/icons/svg/free.svg#cil-account-logout"></use>
                                    </svg> Logout</a>
                        </div>
                    </li>
                </ul>
            </div>
            <!--<div class="container-fluid px-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb my-0">
                        <li class="breadcrumb-item"><a href="/">Home</a>
                        </li>
                        <li class="breadcrumb-item active"><span></span>
                        </li>
                    </ol>
                </nav>
            </div>-->
        </header>
        <div class="body flex-grow-1">
            <div class="px-4">
                <?php
                try {
                    include($router->get_file_path());
                } catch (Exception $e) {
                    // Check if the exception message contains "not found"
                    if (stripos($e->getMessage(), "not found") !== false) {
                        // If "not found" is in the message, include the 404 page
                        include($_SERVER['DOCUMENT_ROOT'] . '/pages/ERROR/404.php');
                    } else {
                        // If it's a different error, you might want to show a generic error page
                        // or rethrow the exception for further handling
                        include($_SERVER['DOCUMENT_ROOT'] . '/pages/ERROR/500.php');
                    }
                }
                ?>
            </div>
        </div>
        <footer class="footer px-4">
            <div><?= ($common->get_config_value('DEBUGGING') == '1') ? 'File Path: ' . $router->get_file_path() : ''; ?></div>
            <div class="ms-auto">Powered by&nbsp;<a href="https://coreui.io/">CoreUI</a></div>
        </footer>
    </div>
    <!-- Chat modal -->
    <div class="modal fade" id="ai_chat_modal" tabindex="-1" aria-labelledby="ai_chat_modal_label" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title h4" id="ai_chat_modal_label">AI Chat</h5>
                    <button class="btn-close btn-close-white" type="button" data-coreui-dismiss="modal" aria-label="Close" onclick="resetAIChatModal()"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3" <?php echo (isset($env['CHAT_API_SOURCE']) && $env['CHAT_API_SOURCE'] === "OLLAMA") ? 'style="display:none;"' : ''; ?>>
                        <label for="system_prompt" class="form-label fw-bold">System Prompt</label>
                        <textarea class="form-control" id="system_prompt" rows="2" placeholder="Enter system prompt..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="user_prompt" class="form-label fw-bold" id="user_prompt_label">User Prompt</label>
                        <textarea class="form-control" id="user_prompt" rows="6" placeholder="Enter user prompt..."></textarea>
                    </div>
                    <div id="user_data_div" class="mb-3">
                        <label for="user_data" class="form-label fw-bold" id="user_data_label">User Data</label>
                        <textarea class="form-control" id="user_data" rows="6" placeholder="Enter user data..."></textarea>
                    </div>
                    <div class="d-flex justify-content-between mb-3 ">
                        <button type="button" onclick="send_chat()" class="btn btn-primary">Send Chat</button>
                        <button type="button" onclick="rechat()" id="rechat_button" class="btn btn-outline-secondary d-none">Rechat</button>
                    </div>
                    <div class="mb-3">
                        <label for="chat_result" class="form-label fw-bold">Chat Result</label>
                        <textarea class="form-control" id="chat_result" rows="10" readonly></textarea>
                    </div>
                    <div class="text-end">
                        <button type="button" onclick="copyTextFromChatResult()" class="btn btn-outline-primary">
                            <i class="cil-copy mr-2"></i> Copy Chat Result
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- CoreUI and necessary plugins-->
    <script src="/coreui/vendors/@coreui/coreui/js/coreui.bundle.min.js"></script>
    <script src="/coreui/vendors/simplebar/js/simplebar.min.js"></script>
    <script>
        const header = document.querySelector('header.header');

        document.addEventListener('scroll', () => {
            if (header) {
                header.classList.toggle('shadow-sm', document.documentElement.scrollTop > 0);
            }
        });

        function logout() {
            // Send a POST request to logout
            fetch('/application_api/account/index.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'logout'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Logout Successful',
                            text: 'Redirecting to login page...',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '/';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Logout Failed',
                            text: data.message || 'An error occurred during logout',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                })
                .catch(error => {
                    console.error('Error during logout:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Logout Error',
                        text: 'An unexpected error occurred during logout',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
        }

        // Add event listener to the logout button
        document.addEventListener('DOMContentLoaded', function() {
            const logoutButton = document.querySelector('#logout-button');
            if (logoutButton) {
                logoutButton.addEventListener('click', logout);
            }

            // Check if the theme is set to dark
            const isDarkTheme = localStorage.getItem('coreui-free-bootstrap-admin-template-theme') === 'dark';
            if (isDarkTheme) {
                document.documentElement.classList.add('dark');
            }

        });
    </script>

</body>

</html>