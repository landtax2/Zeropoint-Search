<?php
$report_name = 'Security Report - Documents Containing an AI Match for Credentials';
$common->print_template_card($report_name, 'start');
?>
<script type="text/javascript">
    var title = '<?PHP echo $report_name; ?>';
    $(document).ready(function() {
        $('#table_1').DataTable({
            "paging": true,
            "ordering": true,
            "responsive": true,
            "info": false,
            "searching": true,
            "order": [
                [0, "asc"]
            ],
            "sScrollX": "100%",
            "buttons": [{
                    extend: 'pdf',
                    orientation: 'landscape',
                    title: title,
                    exportOptions: {
                        columns: ':not(.notexport)'
                    }
                },
                {
                    extend: 'excel',
                    title: title,
                    exportOptions: {
                        columns: ':not(.notexport)'
                    }
                },
                {
                    extend: 'csv',
                    title: title,
                    exportOptions: {
                        columns: ':not(.notexport)'
                    }
                },
                {
                    extend: 'print',
                    title: title,
                    exportOptions: {
                        columns: ':not(.notexport)'
                    }
                },
                'copy',
            ],
            dom: 'Blfrtip',
        })
    });

    // Function to reload the page with the path filter
    function reloadWithPathFilter() {
        var pathFilter = document.getElementById('path_filter').value;
        var currentUrl = new URL(window.location.href);

        // Preserve existing GET parameters
        var params = new URLSearchParams(currentUrl.search);

        // Set or update the path_filter parameter
        params.set('path_filter', pathFilter);

        // Reconstruct the URL with all parameters
        currentUrl.search = params.toString();

        // Reload the page with the new URL
        window.location.href = currentUrl.search;
    }


    // Function to be executed on document ready
    $(document).ready(function() {


        // Add event listener for 'Enter' key press in the input field
        document.getElementById('path_filter').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                reloadWithPathFilter();
            }
        });

    });
</script>


<?php
$where = '';
$params = [];

if (isset($_GET['path_filter']) && !empty($_GET['path_filter'])) {
    $_GET['path_filter'] = trim($_GET['path_filter']);
    $where = "AND path ILIKE :path_filter";
    $path_filter = str_replace("\\", "\\\\", $_GET['path_filter']);
    $params[':path_filter'] = $path_filter . '%';
}

$queryText = "SELECT *
  FROM public.network_file
  WHERE found_last = '1'
  AND ai_credentials = '1'
  AND remediated = '0'
  $where
  LIMIT 100
  ";

$files = $common->query_to_md_array($queryText, $params);

//replaces the parameter in the query text with the actual value and adds the single quotes
$queryText = str_replace(array_keys($params), array_map(function ($value) {
    return "'" . $value . "'";
}, array_values($params)), $queryText);
?>

<p class="mb-3">
    A list of all network files containing an AI match for credentials such as usernames and passwords.
</p>
<br />
<div class="row">
    <div class="col-md-3">

        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text">Path Filter:</span>
            </div>
            <input type="text" class="form-control" id="path_filter" placeholder="Enter file path to filter" value="<?php echo isset($_GET['path_filter']) ? htmlspecialchars($_GET['path_filter']) : ''; ?>">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" onclick="reloadWithPathFilter()">Apply Filter</button>
            </div>
        </div>
    </div>
</div>
<hr>

<table class="dataTable stripe w-100" id="table_1">
    <thead>
        <tr>
            <th>File Name</th>
            <th>AI Title</th>

            <th>Last Found</th>
            <th>Date Created</th>
            <th>Date Modified</th>
            <th class="none notexport">AI Summary</th>
            <th class="none notexport">AI Tags</th>
            <th class="none">File Path</th>
        </tr>
    </thead>
    <tbody>
        <?PHP
        $ai_summary = '####';
        $ai_contact = '####';
        foreach ($files as $d) {
            $ai_summary .= $d['ai_summary'] . "\n\n";
            $ai_contact .= $d['ai_contact_information'] . "\n\n";
            $d['last_found'] = $common->sql2date($d['last_found']);
            $d['date_created'] = $common->sql2date($d['date_created']);
            $d['date_modified'] = $common->sql2date($d['date_modified']);
            $d['ai_summary'] = nl2br("\n\n" . $d['ai_summary']);
            echo "<tr>
                        <td>
                            <a target=\"_BLANK\" href=\"/?s1=File&s2=Detail&id=$d[id]\">$d[name]</a>
                        </td>
                        <td>$d[ai_title]</td> 
                        <td data-sort=\"" . strtotime($d['last_found']) . "\">$d[last_found]</td>
                        <td data-sort=\"" . strtotime($d['date_created']) . "\">$d[date_created]</td>
                        <td data-sort=\"" . strtotime($d['date_modified']) . "\">$d[date_modified]</td>
                        <td>$d[ai_summary]</td>
                        <td>$d[ai_tags]</td>
                        <td>$d[path]</td>
                    </tr>";
        }
        $ai_summary .= '####';
        $ai_contact .= '####';
        ?>
    </tbody>
</table>
<h4>Query</h4>
<pre class="line-numbers"><code class="language-sql">
    <?PHP echo preg_replace("/^[ \t]*[\r\n]+/m", "", $queryText); ?>
</code></pre>

<?PHP
$common->print_template_card(null, 'end');
?>