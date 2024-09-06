<?php
$queryText = "SELECT * FROM changelog";
$data = $common->query_to_md_array($queryText);
$common->print_template_card('Changelog', 'start');
?>


<script type="text/javascript">
    $(document).ready(function() {
        $('#table_1').DataTable({
            "responsive": true,
            "paging": true,
            "ordering": true,
            "info": false,
            "searching": true,
            "order": [
                [0, "desc"]
            ],
            "sScrollX": "100%",
        })
    });
</script>

<table class="dataTable stripe w-100" id="table_1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Database Version</th>
            <th>Application Version</th>
            <th>Author</th>
            <th>Date Created</th>
            <th class="none">Change Summary</th>
        </tr>
    </thead>
    <tbody>
        <?PHP
        $order = 0;
        foreach ($data as $d) {
            echo "<tr>
                        <td>$d[id]</td>
                        <td>$d[database_version]</td>
                        <td>$d[application_version]</td>
                        <td>$d[author]</td>
                        <td>$d[date_created]</td>
                        <td>$d[change_summary]</td>
                    </tr>";
            $order++;
        }
        ?>
    </tbody>
</table>

<?PHP
$common->print_template_card(null, 'end');
?>