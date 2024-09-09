<?PHP
$reports = [
    ['Name' => 'Rgx_SSN', 'Description' => 'Documents containing Social Security Numbers matched by Regex'],
    ['Name' => 'Ai_Medical', 'Description' => 'Documents containing potential medical information identified by AI'],
    ['Name' => 'Ai_credentials', 'Description' => 'Documents containing potential credentials identified by AI'],
    ['Name' => 'Ai_Severity', 'Description' => 'Documents that AI has deemed to be sensitive'],
];
$common->print_template_card('Dashboard', 'start');
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#table_1').DataTable({
            "paging": true,
            "ordering": true,
            "info": true,
            "searching": true,
            "responsive": true
        });
    });
</script>

<div class="table-responsive">
    <table class="dataTable stripe w-100" id="table_1">
        <thead>
            <tr>
                <th>Report Name</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reports as $report): ?>
                <tr>
                    <td><a href="?s1=Reports&s2=Pii&s3=<?php echo urlencode($report['Name']); ?>"><?php echo htmlspecialchars($report['Name']); ?></a></td>
                    <td><?php echo htmlspecialchars($report['Description']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


<?PHP
$common->print_template_card('PII Reports', 'end');
?>