<?PHP
$common->print_template_card('Similar Files', 'start');

$queryText = "SELECT * FROM network_file WHERE hash = :hash";
$queryParams = array(':hash' => $_GET['hash']);
$files = $common->query_to_md_array($queryText, $queryParams);
?>

<script type="text/javascript">
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
        })
    });
</script>
<div class="alert alert-info" role="alert">
    <h4 class="alert-heading">Files with the same hash: <?PHP echo $_GET['hash']; ?></h4>
    <p>This display all files with a matching hash.</p>
    <hr>
</div>
<table class="dataTable stripe w-100" id="table_1">
    <thead>
        <tr>
            <th>File Name</th>
            <th>AI Title</th>
            <th>File Path</th>
            <th>Last Found</th>
            <th>Date Created</th>
            <th>Date Modified</th>
            <th class="none">AI Summary</th>
            <th class="none">AI Tags</th>
        </tr>
    </thead>
    <tbody>
        <?PHP
        $ai_summary = '####';
        $ai_contact = '####';
        foreach ($files as $d) {
            //prevents summaries from being too long
            /*if (strlen($ai_summary) < $common->get_config_value('AI_PROCESSING_CHAT_MAX_LENGTH')) {
                $ai_summary .= $d['ai_summary'] . "\n\n";
            }
            if (strlen($ai_contact) < $common->get_config_value('AI_PROCESSING_CHAT_MAX_LENGTH')) {
                $ai_contact .= $d['ai_contact_information'] . "\n\n";
            }*/
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
                        <td>$d[path]</td>
                        
                        <td data-sort=\"" . strtotime($d['last_found']) . "\">$d[last_found]</td>
                        <td data-sort=\"" . strtotime($d['date_created']) . "\">$d[date_created]</td>
                        <td data-sort=\"" . strtotime($d['date_modified']) . "\">$d[date_modified]</td>
                        <td>$d[ai_summary]</td>
                        <td>$d[ai_tags]</td>
                    </tr>";
        }
        $ai_summary .= '####';
        $ai_contact .= '####';
        ?>
    </tbody>
</table>

<?PHP
$common->print_template_card(null, 'end');
?>