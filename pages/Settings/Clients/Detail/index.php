<?php
$queryText = "SELECT *
  FROM client t1
  WHERE t1.id = :id";
$queryParams = array(':id' => $_GET['id']);
$data = $common->query_to_sd_array($queryText, $queryParams);
$client_inactive = $data['days_till_client_inactive']  * -1;

$common->print_template_card('Client Details', 'start');
?>

<table>
    <tr>
        <td>Name &nbsp;&nbsp;&nbsp;</td>
        <td><?PHP echo $data['client_name']; ?></td>
    </tr>
    <tr>
        <td>API Key &nbsp;&nbsp;&nbsp;</td>
        <td><?PHP echo $data['api_key']; ?></td>
    </tr>
    <tr>
        <td>Alert Email &nbsp;&nbsp;&nbsp;</td>
        <td><?PHP echo $data['alert_email']; ?></td>
    </tr>
    <tr>
        <td>Days until endpoint inactive &nbsp;&nbsp;&nbsp;</td>
        <td><?PHP echo $data['days_till_client_inactive']; ?></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td></td>
    </tr>
</table>
<br />




<h4>Grids</h4>
<ul>
    <li>
        <a href="/?page=clients&sub=grid_endpoints&id=<?PHP echo $_GET['id']; ?>">Sample</a>
    </li>
</ul>
<br />

<h4>Scripts</h4>
<ul>
    <li>
        <a href="/?s1=Settings&s2=Clients&s3=Scripts&s4=Powershell_file_classification&id=<?PHP echo $_GET['id']; ?>">Powershell File Classification</a>
    </li>
</ul>

<?PHP
$common->print_template_card(null, 'end');
?>