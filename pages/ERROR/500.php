<?PHP
$message = $e->getMessage();
$log = [
    'message' => $message,
    'IP' => $common->get_ip(),
    'User ID' => $_SESSION['user_id'],
    'Arguments' => $_GET,
    'User Agent' => $_SERVER['HTTP_USER_AGENT'],
    'URI' => $_SERVER['REQUEST_URI'],
];
$common->write_to_log('error', '500', $log);
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="mt-5">
                <h1 class="display-1 fw-bold text-danger">500</h1>
                <p class="fs-3 mb-4">Internal Server Error</p>
                <div class="alert alert-danger">
                    <p class="mb-0">An unexpected error occurred. Our team has been notified and is working on resolving
                        the issue.</p>
                </div>
                <div class="bg-light p-4 rounded mb-4">
                    <h5 class="text-muted mb-3">Error Details:</h5>
                    <pre
                        class="mb-0"><code class="language-plaintext" style="max-height: 300px; overflow-y: auto;"><?PHP echo $message; ?></code></pre>
                </div>
                <div>
                    <a href="/" class="btn btn-primary me-2">Go Home</a>
                </div>
            </div>
        </div>
    </div>
</div>