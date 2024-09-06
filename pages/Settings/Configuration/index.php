<?php
$queryText = "SELECT * FROM config";
$data = $common->query_to_md_array($queryText);
$common->print_template_card('Configuration', 'start');
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
            <th>Setting Name</th>
            <th>Value</th>
            <th>Editable</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <?PHP

        foreach ($data as $d) {
            if ($d['editable'] == 1) {
                $editable = 'Yes';
            } else {
                $editable = 'No';
            }
            echo "<tr>
                        <td><a href=\"?s1=Settings&s2=Configuration&s3=Detail&id=$d[id]\">$d[setting]</a></td>
                        <td>$d[value]</td>
                        <td>$editable</td>
                        <td>$d[description]</td>
                    </tr>";
        }
        ?>
    </tbody>
</table>

<?PHP
$common->print_template_card(null, 'end');
?>