<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title mb-4">Welcome to Your Dashboard</h2>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Document Count</h5>
                                <p class="card-text h3">
                                    <?= number_format($common->query_to_sd_array("SELECT COUNT(*) as count FROM network_file", null)['count']) ?>
                                </p>
                                <p class="card-text text-muted">Total documents in database</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Additional dashboard widget can be added here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>