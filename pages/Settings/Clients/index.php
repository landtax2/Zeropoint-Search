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

<table class="dataTable stripe w-100" id="table_1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>API Key</th>
            <th>Alert Email</th>
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
                        <td>$d[alert_email]</td>
                    </tr>";
            $order++;
        }
        ?>
    </tbody>
</table>

<?PHP
$common->print_template_card(null, 'end');
?>