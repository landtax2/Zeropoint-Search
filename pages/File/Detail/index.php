<?php

$queryText = "SELECT t1.*, t2.client_name, t2.id as client_id
  FROM network_file t1
  LEFT OUTER JOIN client t2 ON t1.client_id = t2.id 
  WHERE t1.id = :file_id";

$params = [':file_id' => $_GET['id']];

$file_data = $common->query_to_sd_array($queryText, $params);

if (strlen($file_data['comment']) == 0) {
    $file_data['comment'] = '<span style="color: var(--cui-link-color); cursor:pointer" onclick="set_comment(\'\')">' . 'Set Comment' . '</span>';
} else {
    $file_data['comment_escaped'] = str_replace("'", "\\'", $file_data['comment']);
    $file_data['comment'] = '<span style="color: var(--cui-link-color); cursor:pointer;" onclick="set_comment(\'' . $file_data['comment_escaped'] . '\')">' . $file_data['comment'] . '</span>';
}

if ($file_data['remediated'] == 0) {
    $file_data['remediated'] = '<span style="color:red; cursor:pointer;" onclick="mark_remediated()">NOT REMEDIATED</span>';
} else {
    $file_data['remediated'] = '<span style="color:green; cursor:pointer;" onclick="unmark_remediated()">Remediated</span>';
}


$common->print_template_card('File Detail', 'start');
?>

<script type="text/javascript">
    id = '<?= $_GET['id']; ?>';

    function mark_remediated() {
        Swal.fire({
            title: "Update File",
            text: "Are you sure you want to mark this file as remediated?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#00d049",
            confirmButtonText: "Confirm",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/pages/utilities/network_file_search/handlers/main.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${id}&action=set_file_remediated`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: "Success",
                                text: "File marked as remediated.",
                                icon: "success",
                                showCancelButton: false,
                                confirmButtonColor: "#00d049",
                                confirmButtonText: "Acknowledged"
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Problem updating.',
                                text: data.message || 'An error occurred.',
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Problem updating.',
                            text: 'An error occurred while processing the request.',
                        });
                    });
            }
        });
    }

    function unmark_remediated() {
        Swal.fire({
            title: "Update Analysis",
            text: "Are you sure you want to unmark this record as remediated?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#00d049",
            confirmButtonText: "Confirm",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/pages/utilities/network_file_search/handlers/main.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${id}&action=unset_file_remediated`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: "Success",
                                text: "File removed as remediated.",
                                icon: "success",
                                showCancelButton: false,
                                confirmButtonColor: "#00d049",
                                confirmButtonText: "Acknowledged",
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Problem updating.',
                                text: data.message || 'An error occurred.',
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Problem updating.',
                            text: 'An error occurred while processing the request.',
                        });
                    });
            }
        });
    }

    function set_comment(comment) {
        Swal.fire({
            title: "Set Comment",
            input: "text",
            inputValue: comment,
            showCancelButton: true,
            confirmButtonColor: "#00d049",
            confirmButtonText: "Confirm",
            cancelButtonText: "Cancel",
            preConfirm: (text) => {
                return fetch('/pages/utilities/network_file_search/handlers/main.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${id}&action=set_file_comment&comment=${text}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: "Success",
                                text: "Comment Set",
                                icon: "success",
                                showCancelButton: false,
                                confirmButtonColor: "#00d049",
                                confirmButtonText: "Acknowledged",
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Problem updating.');
                        }
                    })
                    .catch(error => {
                        Swal.showValidationMessage(
                            `Request failed: ${error}`
                        );
                    });
            },
            allowOutsideClick: () => !Swal.isLoading()
        });
    }
</script>
<div class="left w-100">
    <dl class="row">
        <dt class="col-sm-2">Hash SHA256</dt>
        <dd class="col-sm-10"><a href="/?page=detections&sub=detail&hash=<?= $file_data['hash']; ?>"><?= $file_data['hash']; ?></a></dd>
        <dt class="col-sm-2">Client Name </dt>
        <dd class="col-sm-10"><a href="?page=clients&sub=detail&id=<?= $file_data['client_id']; ?>"><?= $file_data['client_name']; ?></a></dd>

        <dt class="col-sm-2">&nbsp;</dt>
        <dd class="col-sm-10"></dd>

        <dt class="col-sm-2">File Name </dt>
        <dd class="col-sm-10"><?= $file_data['name']; ?></dd>
        <dt class="col-sm-2">Internal Name </dt>
        <dd class="col-sm-10"><?= $file_data['internal_name']; ?></dd>
        <dt class="col-sm-2">Extension </dt>
        <dd class="col-sm-10"><?= $file_data['extension']; ?></dd>
        <dt class="col-sm-2">Path </dt>
        <dd class="col-sm-10"><span id="path"><?= $file_data['path']; ?> <i onclick="copyToClipboard('#path'); this.style.color = 'green'" class="fa fa-copy" style="color: var(--cui-link-color); cursor:pointer"></i></span></dd>
        <dt class="col-sm-2">Network Folder</dt>
        <dd class="col-sm-10"><span id="folder"><?= dirname($file_data['path']); ?> <i onclick="copyToClipboard('#folder'); this.style.color = 'green'" class="fa fa-copy" style="color: var(--cui-link-color); cursor:pointer"></i></span></dd>
        <dt class="col-sm-2">File Size</dt>
        <dd class="col-sm-10"><?= $common->humanFileSize($file_data['size']); ?></dd>
        <dt class="col-sm-2">File Created</dt>
        <dd class="col-sm-10"><?= $common->sql2date_military_time($file_data['date_created']); ?></dd>
        <dt class="col-sm-2">Record Created </dt>
        <dd class="col-sm-10"><?= $common->sql2date_military_time($file_data['record_created']); ?></dd>
        <dt class="col-sm-2">Last Found </dt>
        <dd class="col-sm-10"><?= $common->sql2date_military_time($file_data['last_found']); ?></dd>
        <dt class="col-sm-2">Found at last scan </dt>
        <dd class="col-sm-10"><?= $common->boolean[$file_data['found_last']]; ?></dd>
        <dt class="col-sm-2">&nbsp;</dt>
        <dd class="col-sm-10"></dd>

        <dt class="col-sm-2">Ai Title </dt>
        <dd class="col-sm-10"><?= $file_data['ai_title']; ?></dd>

        <dt class="col-sm-2">Ai Summary </dt>
        <dd class="col-sm-8"><?= nl2br($file_data['ai_summary']); ?></dd>
        <dd class="col-sm-2"></dd>
        <dt class="col-sm-2">Ai Tags </dt>
        <dd class="col-sm-8"><?= $file_data['ai_tags']; ?></dd>
        <dd class="col-sm-2"></dd>
        <dt class="col-sm-2">Ai Contact Information </dt>
        <dd class="col-sm-8"><?= $file_data['ai_contact_information']; ?></dd>
        <dd class="col-sm-2"></dd>

        <dt class="col-sm-2">&nbsp;</dt>
        <dd class="col-sm-10"></dd>


        <dt class="col-sm-2">PII Severity</dt>
        <dd class="col-sm-10"><?= $file_data['ai_severity']; ?></dd>
        <dt class="col-sm-2">AI SSN</dt>
        <dd class="col-sm-10"><?= $file_data['ai_pii_ssn']; ?></dd>
        <dt class="col-sm-2">AI Phone</dt>
        <dd class="col-sm-10"><?= $file_data['ai_pii_phone']; ?></dd>
        <dt class="col-sm-2">AI Address</dt>
        <dd class="col-sm-10"><?= $file_data['ai_pii_address']; ?></dd>
        <dt class="col-sm-2">AI Name</dt>
        <dd class="col-sm-10"><?= $file_data['ai_name']; ?></dd>
        <dt class="col-sm-2">AI Medical</dt>
        <dd class="col-sm-10"><?= $file_data['ai_medical']; ?></dd>
        <dt class="col-sm-2">AI Email</dt>
        <dd class="col-sm-10"><?= $file_data['ai_email']; ?></dd>
        <dt class="col-sm-2">AI Credit Card</dt>
        <dd class="col-sm-10"><?= $file_data['ai_credit_card']; ?></dd>
        <dt class="col-sm-2">&nbsp;</dt>
        <dd class="col-sm-10"></dd>


        <dt class="col-sm-2">Regex SSN Hard</dt>
        <dd class="col-sm-10"><?= $file_data['ssn_hard']; ?></dd>
        <dt class="col-sm-2">Regex SSN Soft</dt>
        <dd class="col-sm-10"><?= $file_data['ssn_soft']; ?></dd>
        <dt class="col-sm-2">Regex Phone</dt>
        <dd class="col-sm-10"><?= $file_data['phone_number']; ?></dd>
        <dt class="col-sm-2">Regex Email</dt>
        <dd class="col-sm-10"><?= $file_data['email']; ?></dd>
        <dt class="col-sm-2">Regex Password</dt>
        <dd class="col-sm-10"><?= $file_data['password']; ?></dd>
        <dt class="col-sm-2">&nbsp;</dt>
        <dd class="col-sm-10"></dd>


        <dt class="col-sm-2">Remediated </dt>
        <dd class="col-sm-10"><?= $file_data['remediated']; ?></dd>
        <dt class="col-sm-2">Comment </dt>
        <dd class="col-sm-10"><?= $file_data['comment']; ?></dd>
        <dt class="col-sm-2">&nbsp;</dt>
        <dd class="col-sm-10"></dd
            </dl>
        <br />


</div>
<?PHP
$common->print_template_card(null, 'end');

?>