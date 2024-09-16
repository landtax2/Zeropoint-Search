<?php
$queryText = "SELECT * FROM config";
$data = $common->query_to_md_array($queryText);
$common->print_template_card('Configuration', 'start');
?>


<script type="text/javascript">
    $(document).ready(function() {
        $('#table_1').DataTable({
            "paging": true,
            "responsive": true,
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
<div class="alert alert-warning" role="alert">
    <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Warning!</h4>
    <div class="row">
        <div class="col-md-6">
            <h5>Caution when modifying settings:</h5>
            <ul>
                <li>Changes can significantly impact application functionality</li>
                <li>Only modify if you fully understand the implications</li>
                <li>Incorrect changes may cause system instability</li>
                <li>Consult documentation or an administrator if unsure</li>
            </ul>
        </div>
        <div class="col-md-6">
            <h5>Environment Variable Overrides:</h5>
            <ul>
                <li>Docker environment variables take precedence over database values</li>
                <li>Allows flexible configuration without direct database changes</li>
                <li>"Override Value" column shows current environment variable overrides</li>
                <li>Empty "Override Value" means the database value is in use</li>
            </ul>
        </div>
    </div>
</div>

<table class="dataTable stripe w-100" id="table_1">
    <thead>
        <tr>
            <th>Setting Name</th>
            <th>DB Value</th>
            <th>Override Value</th>
            <th>Override Type</th>
            <th>Editable</th>
            <th class="none">Description</th>
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

            $override_value = '';
            $override_type = '';

            if (isset($common->env[$d['setting']])) {
                $override_value = $common->env[$d['setting']];
                $editable = 'No';
                $override_type = 'ENV File';
            } else if (isset($_ENV[$d['setting']])) {
                $override_value = $_ENV[$d['setting']];
                $editable = 'No';
                $override_type = 'Container Environment Variable';
            } else if (isset($_SERVER[$d['setting']])) {
                $override_value = $_SERVER[$d['setting']];
                $editable = 'No';
                $override_type = 'Container Environment Variable';
            }


            echo "<tr>
                        <td><a href=\"?s1=Settings&s2=Configuration&s3=Detail&id=$d[id]\">$d[setting]</a></td>
                        <td>$d[value]</td>
                        <td>$override_value</td>
                        <td>$override_type</td>
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