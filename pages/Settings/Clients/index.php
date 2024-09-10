<?php
$queryText = "SELECT * FROM client";
$data = $common->query_to_md_array($queryText);
$common->print_template_card('Clients', 'start');
?>


<script type="text/javascript">
    $(document).ready(function() {
        $('#table_1').DataTable({
            "paging": true,
            "ordering": true,
            "info": false,
            "searching": true,
            "order": [
                [0, "asc"]
            ],
            "sScrollX": "100%",
        })
    });
</script>
<div class="alert alert-info" role="alert">
    <h4 class="alert-heading">Client API Keys</h4>
    <p>Each client is assigned a unique API key. This key is essential for securely importing documents into the application.</p>
    <hr>
    <p class="mb-0">When integrating with our system or importing documents, clients must use their specific API key for authentication and to ensure proper data association.</p>
    <p>For your convenience, API keys are automatically incorporated into the scripts generated from the clients page. This ensures seamless integration and eliminates the need for manual key insertion when setting up client-specific scripts.</p>
</div>

<table class="dataTable stripe w-100" id="table_1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>API Key</th>
        </tr>
    </thead>
    <tbody>
        <?PHP
        $order = 0;
        foreach ($data as $d) {
            echo "<tr>
                        <td><a href=\"?s1=Settings&s2=Clients&s3=Detail&id=$d[id]\">$d[id]</a></td>
                        <td><a href=\"?s1=Settings&s2=Clients&s3=Detail&id=$d[id]\">$d[client_name]</a></td>
                        <td>$d[api_key]</td>
                    </tr>";
            $order++;
        }
        ?>
    </tbody>
</table>

<?PHP
$common->print_template_card(null, 'end');
?>