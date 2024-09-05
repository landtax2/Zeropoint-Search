<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/common.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/router.php');
session_start();
$env = parse_ini_file('.env');
//instantiate the common class
$common = new common($env);

//instantiate the router class - determines the page to load
$router = new router();


($common->get_env_value('DEBUGGING') == '1') ? ini_set('display_errors', 1) : ini_set('log_errors', 0); //turns off error logging if not debugging

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: -1"); //for the above - prevents browsers from caching dynamic page.



//allows only local access or otherwise defined from .env file
$common->local_only();

//checks if the user is logged in
if (!isset($_SESSION['userID'])) {
    //header('Location: /pages/login/login.php');
    //die();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <base href="./">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="<?= $common->get_env_value('APPLICATION_DESCRIPTION') ?>">
    <meta name="author" content="Landtax">
    <meta name="keyword" content="File Search, AI, ZeroPoint">
    <title><?= $common->get_env_value('APPLICATION_NAME') ?></title>
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
    <link href="/coreui/vendors/@coreui/chartjs/css/coreui-chartjs.css" rel="stylesheet">

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
    <link rel="stylesheet" href="/vendors/prism/prism.css">

    <!-- Sweet Alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/90add283c4.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="sidebar sidebar-dark sidebar-fixed border-end" id="sidebar">
        <div class="sidebar-header border-bottom">
            <div class="sidebar-brand">
                <svg class="sidebar-brand-full" width="88" height="32" alt="CoreUI Logo">
                    <use xlink:href="/assets/brand/coreui.svg#full"></use>
                </svg>
                <svg class="sidebar-brand-narrow" width="32" height="32" alt="CoreUI Logo">
                    <use xlink:href="/assets/brand/coreui.svg#signet"></use>
                </svg>
            </div>
            <button class="btn-close d-lg-none" type="button" data-coreui-dismiss="offcanvas" data-coreui-theme="dark" aria-label="Close" onclick="coreui.Sidebar.getInstance(document.querySelector(&quot;#sidebar&quot;)).toggle()"></button>
        </div>
        <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">
            <li class="nav-item"><a class="nav-link" href="/">
                    <svg class="nav-icon">
                        <use xlink:href="/coreui/vendors/@coreui/icons/svg/free.svg#cil-speedometer"></use>
                    </svg> Dashboard<span class="badge badge-sm bg-info ms-auto">NEW</span></a></li>
            <li class="nav-title">Core Functions</li>
            <li class="nav-group"><a class="nav-link nav-group-toggle" href="#">
                    <i class="fa fa-search"></i> &nbsp;Searches</a>
                <ul class="nav-group-items">
                    <li class="nav-item"><a class="nav-link" href="/?page=Search&sub_dir=Main"><span class="nav-icon"></span><i class="fa fa-fire"></i> &nbsp; Main Search</a></li>
                </ul>
            </li>
            <li class="nav-item mt-auto"><a class="nav-link" href="/?page=Docs" target="_blank">
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
                            <div class="avatar avatar-md"><img class="avatar-img" src="/coreui/assets/img/avatars/8.jpg" alt="user@email.com"></div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end pt-0">
                            <div class="dropdown-header bg-body-tertiary text-body-secondary fw-semibold my-2">
                                <div class="fw-semibold">Settings</div>
                            </div><a class="dropdown-item" href="#">
                                <svg class="icon me-2">
                                    <use xlink:href="/coreui/vendors/@coreui/icons/svg/free.svg#cil-user"></use>
                                </svg> Profile</a><a class="dropdown-item" href="#">
                                <svg class="icon me-2">
                                    <use xlink:href="/coreui/vendors/@coreui/icons/svg/free.svg#cil-settings"></use>
                                </svg> Settings</a><a class="dropdown-item" href="#">

                                <div class="dropdown-divider"></div><a class="dropdown-item" href="#">
                                    <svg class="icon me-2">
                                        <use xlink:href="/coreui/vendors/@coreui/icons/svg/free.svg#cil-account-logout"></use>
                                    </svg> Logout</a>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="container-fluid px-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb my-0">
                        <li class="breadcrumb-item"><a href="/">Home</a>
                        </li>
                        <li class="breadcrumb-item active"><span><?= $router->get_page(); ?></span>
                        </li>
                    </ol>
                </nav>
            </div>
        </header>
        <div class="body flex-grow-1">
            <div class="container-lg px-4">
                <?php
                try {
                    $router->route();
                } catch (Exception $e) {
                    include($_SERVER['DOCUMENT_ROOT'] . '/pages/ERROR/404.html');
                }
                ?>
            </div>
        </div>
        <footer class="footer px-4">
            <div><?= ($common->get_env_value('DEBUGGING') == '1') ? 'File Path: ' . $router->get_file_path() : ''; ?></div>
            <div class="ms-auto">Powered by&nbsp;<a href="https://coreui.io/">CoreUI</a></div>
        </footer>
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
    </script>
    <!-- Plugins and scripts required by this view-->
    <script src="/coreui/vendors/chart.js/js/chart.umd.js"></script>
    <script src="/coreui/vendors/@coreui/chartjs/js/coreui-chartjs.js"></script>
    <script src="/coreui/vendors/@coreui/utils/js/index.js"></script>
    <script src="/coreui/js/main.js"></script>
    <script>
    </script>

</body>

</html>