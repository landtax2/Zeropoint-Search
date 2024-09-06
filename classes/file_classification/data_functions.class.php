<?PHP

class DataFunctions
{
    private $common;

    public function __construct($common)
    {
        $this->common = $common;
    }

    public function updateNetworkFile($updateData)
    {
        $updateQuery = "UPDATE network_file
        SET
            \"name\" = :name,
            \"extension\" = :extension,
            \"path\" = :path,
            \"hash\" = :hash,
            \"date_created\" = :date_created,
            \"date_modified\" = :date_modified,
            \"client_id\" = :client_id,
            \"size\" = :size,
            \"internal_name\" = :internal_name,
            \"product_version\" = :product_version,
            \"file_version\" = :file_version,
            \"last_found\" = :last_found,
            \"found_last\" = '1',
            \"ai_title\" = :ai_title,
            \"ai_summary\" = :ai_summary,
            \"ai_tags\" = :ai_tags,
            \"ai_contact_information\" = :ai_contact_information,
            \"ai_pii_ssn\" = :ai_pii_ssn,
            \"ai_pii_phone\" = :ai_pii_phone,
            \"ai_pii_address\" = :ai_pii_address,
            \"ai_name\" = :ai_name,
            \"ai_medical\" = :ai_medical,
            \"ai_email\" = :ai_email,
            \"ai_credit_card\" = :ai_credit_card,
            \"ai_severity\" = :ai_severity,
            \"ssn_hard\" = :ssn_hard,
            \"ssn_soft\" = :ssn_soft,
            \"phone_number\" = :phone_number,
            \"email\" = :email,
            \"password\" = :password
        WHERE file_id = :file_id";

        //fix dates
        $updateData['sanitized']['date_created'] = $this->common->datetime_to_postgres_format($updateData['sanitized']['date_created']);
        $updateData['sanitized']['date_modified'] = $this->common->datetime_to_postgres_format($updateData['sanitized']['date_modified']);

        $params = [
            ':name' => $updateData['sanitized']['name'],
            ':extension' => $updateData['sanitized']['extension'],
            ':path' => $updateData['sanitized']['path'],
            ':hash' => $updateData['sanitized']['hash'],
            ':date_created' => $updateData['sanitized']['date_created'],
            ':date_modified' => $updateData['sanitized']['date_modified'],
            ':client_id' => $updateData['client_id'],
            ':size' => $updateData['sanitized']['size'],
            ':internal_name' => $updateData['sanitized']['internal_name'],
            ':product_version' => $updateData['sanitized']['product_version'],
            ':file_version' => $updateData['sanitized']['file_version'],
            ':last_found' => $updateData['last_found'],
            ':ai_title' => $updateData['title'],
            ':ai_summary' => $updateData['summary'],
            ':ai_tags' => $updateData['tags'],
            ':ai_contact_information' => $updateData['contact_information'],
            ':ai_pii_ssn' => $updateData['pii']['contains_social_security_number'],
            ':ai_pii_phone' => $updateData['pii']['contains_phone_number'],
            ':ai_pii_address' => $updateData['pii']['contains_street_address'],
            ':ai_name' => $updateData['pii']['contains_first_and_last_name'],
            ':ai_medical' => $updateData['pii']['contains_medical_information'],
            ':ai_email' => $updateData['pii']['contains_email_address'],
            ':ai_credit_card' => $updateData['pii']['contains_credit_card'],
            ':ai_severity' => $updateData['severity'],
            ':ssn_hard' => $updateData['ssn_hard'],
            ':ssn_soft' => $updateData['ssn_soft'],
            ':phone_number' => $updateData['phone_number'],
            ':email' => $updateData['email'],
            ':password' => $updateData['password'],
            ':file_id' => $updateData['sanitized']['file_id']
        ];

        $this->common->query_to_sd_array($updateQuery, $params);
    }

    public function check_and_create_network_file($client_id, $post_data)
    {
        //Handles files that have been changed.
        $queryText = "UPDATE network_file SET found_last = 0 WHERE \"path\" = :path";
        $params = [':path' => $post_data['path']];
        $this->common->query_to_sd_array($queryText, $params);

        //Checks if the file exists in the database.
        $queryText = "SELECT COUNT(*) as count FROM network_file WHERE file_id = :file_id";
        $params = [':file_id' => $post_data['file_id']];
        $count = $this->common->query_to_sd_array($queryText, $params)['count'];

        //If the file does not exist in the database, it inserts it.
        if ($count == 0) {
            $queryText = "INSERT INTO network_file (file_id, last_found, found_last, \"path\", client_id) VALUES (:file_id, current_timestamp, '1', :path, :client_id)";
            $params = [':file_id' => $post_data['file_id'], ':path' => $post_data['path'], ':client_id' => $client_id];
            $this->common->query_to_sd_array($queryText, $params);
            $json_result = ['classification' => true];
        } else {
            //If the file exists in the database, it updates the last_found date.
            $queryText = "UPDATE network_file SET last_found = current_timestamp, found_last = 1 WHERE file_id = :file_id";
            $params = [':file_id' => $post_data['file_id']];
            $this->common->query_to_sd_array($queryText, $params);

            $queryText = " SELECT \"hash\" FROM network_file WHERE file_id = :file_id";
            $params = [':file_id' => $post_data['file_id']];
            $hash = $this->common->query_to_sd_array($queryText, $params)['hash'];
            if (isset($hash)) {
                $json_result = ['classification' => false];
            } else {
                $json_result = ['classification' => true];
            }
        }
        return $json_result;
    }
}
