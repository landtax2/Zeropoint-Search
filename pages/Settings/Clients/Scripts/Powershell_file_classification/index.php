<?PHP

$queryText = "SELECT *
  FROM client t1
  WHERE t1.id = :id";
$queryParams = array(':id' => $_GET['id']);
$data = $common->query_to_sd_array($queryText, $queryParams);

$common->print_template_card('Powershell File Classification', 'start');

?>
<div class="alert alert-warning" role="alert">
    <strong>Warning:</strong> This script must be run in PowerShell 7 or later. Earlier versions of PowerShell do not support all the features used in this script.
    <br /><br /><a href="https://learn.microsoft.com/en-us/powershell/scripting/install/installing-powershell-on-windows?view=powershell-7.4" target="_blank">How to install PowerShell 7</a>
</div>

<table>
    <tr>
        <td>Name &nbsp;&nbsp;&nbsp;</td>
        <td><?PHP echo $data['client_name']; ?></td>
    </tr>
    <tr>
        <td>API Key &nbsp;&nbsp;&nbsp;</td>
        <td><?PHP echo $data['api_key']; ?></td>
    </tr>
</table>
<pre style="height: 1500px" class="line-numbers"><code class="language-powershell">

function Get-StringSHA256 {
    [CmdletBinding()]
    param(
        [Parameter(Mandatory=$true, Position=0)]
        [string]$InputString
    )

    $sha256 = New-Object System.Security.Cryptography.SHA256CryptoServiceProvider
    $inputBytes = [System.Text.Encoding]::UTF8.GetBytes($InputString)
    $hashBytes = $sha256.ComputeHash($inputBytes)
    $hashString = [System.BitConverter]::ToString($hashBytes).Replace("-", "")
    return $hashString
}


function Invoke-FileClassification {
    param (
        [string]$Path
    )
    $api_key = "<?PHP echo $data['api_key']; ?>"

    # Ensure the path exists
    if (-Not (Test-Path $Path)) {
        Write-Host "Path does not exist: $Path" -ForegroundColor Red
        return
    }

    # Get all pdf, doc, and docx files from the directory and subdirectories
    $files = Get-ChildItem -Path $Path -Recurse -Include *.pdf, *.doc, *.docx

    # Loop through each file
    foreach ($file in $files) {
        try {

            $filePath = $file.FullName
            $hash_obj = Get-FileHash -LiteralPath $file.FullName -Algorithm SHA256 2>$null
            Write-Host "Working on $filePath" -ForegroundColor Cyan
            # Run curl command for each file

            $filePath = $file.FullName
            $name = $file.Name
            $internal_name = $file.VersionInfo.InternalName
            $product_version = $file.VersionInfo.ProductVersion
            $file_version = $file.VersionInfo.FileVersion
            $size = $file.Length
            $extension = $file.Extension.Replace('.', '')
            $path = $file.FullName
            $hash = $hash_obj.Hash
            $date_created = $file.CreationTime.ToString("yyyy-MM-dd HH:mm:ss")
            $date_modified = $file.LastWriteTime.ToString("yyyy-MM-dd HH:mm:ss")
            $file_id = Get-StringSHA256 -InputString ($file.FullName + $hash)
            $folder = $folder
			
			$Form = @{
				path = $path
				hash = $hash
				file_id = $file_id
				api_key = $api_key
			}
			$Uri = "<?PHP echo 'https://' . $_SERVER['HTTP_HOST'] . '/public_api/file_classification.php?action=check_file_id'; ?>"
            $result = Invoke-WebRequest -Uri $Uri -Method Post -Form $Form -SkipHttpErrorCheck
            Write-Host $result
            $resultObject = ConvertFrom-JSON -InputObject $result

            if($resultObject.classification -eq $True){

                $Form = @{
				    file = Get-Item -LiteralPath $filePath
				    name = $name
				    internal_name = $internal_name
				    product_version = $product_version
				    file_version = $file_version
				    size = $size
                    extension = $extension
                    path = $path
                    hash = $hash
                    date_created = $date_created
                    file_id = $file_id
                    folder = $folder
                    date_modified = $date_modified
                    api_key = $api_key
                }
                $Uri = "<?PHP echo 'https://' . $_SERVER['HTTP_HOST'] . '/public_api/file_classification.php?action=extract'; ?>" 
                $result = Invoke-WebRequest -Uri $Uri -Method Post -Form $Form -SkipHttpErrorCheck
                Write-Host $result
            }

        }
        catch {
            Write-Host "Error processing $filePath : $_" -ForegroundColor Red
        }
    }
}

# Usage Examples:
#Invoke-FileClassification -Path "C:\Users\test\downloads"
#Invoke-FileClassification -Path "\\storage\share"
</code></pre>

<?PHP
$common->print_template_card(null, 'end');
?>