<?PHP
$log = [
    'IP' => $common->get_ip(),
    'User ID' => $_SESSION['user_id'],
    'Arguments' => $_GET,
    'User Agent' => $_SERVER['HTTP_USER_AGENT'],
    'URI' => $_SERVER['REQUEST_URI'],
];
$common->write_to_log('error', '404', $log);
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="mt-5">
                <h1 class="display-1 fw-bold text-danger">404</h1>
                <p class="fs-3 mb-4">Page Not Found</p>
                <div class="alert alert-danger">
                    <p class="mb-0">The page you're looking for doesn't exist.</p>
                </div>
                <div>
                    <a href="/" class="btn btn-primary me-2">Go Home</a>
                </div>
            </div>
        </div>
    </div>
</div>