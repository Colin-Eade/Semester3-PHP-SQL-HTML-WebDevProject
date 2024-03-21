<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename db.php
 */

$connection = null;

/**
 * Establishes a connection to the PostgreSQL database
 * @return mixed The database connection
 * @throws Exception
 */
function db_connect(): mixed
{
    global $connection;

    if(!$connection){
        $conn_string = sprintf("host=%s port=%s dbname=%s user=%s password=%s",
            DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);

        $connection = pg_connect($conn_string);
    }

    if(!$connection){
        throw new Exception("Database Connection Failed: ", pg_last_error());
    }

    return $connection;
}

/**
 * Hashes a password securely.
 * @param string $plainPassword The password to be hashed.
 * @return string The hashed password.
 */
function hash_password($plainPassword) : string {

    $connection = db_connect();

    // Generate random salt for crypt function
    $salt_query = "SELECT gen_salt('bf')";
    $salt_result = pg_query($connection, $salt_query);
    if(!$salt_result){
        throw new Exception("Salt generation failed: " . pg_last_error());
    }

    $salt_row = pg_fetch_row($salt_result);
    $salt = $salt_row[0];

    // Hash the password with the salt using PostgreSQL crypt() function
    $hash_query = "SELECT crypt($1, $2)";
    $hash_result = pg_query_params($connection, $hash_query, array($plainPassword, $salt));
    if(!$hash_result){
        throw new Exception("Salt generation failed: " . pg_last_error());
    }

    $hash_row = pg_fetch_row($hash_result);

    return $hash_row[0];
}

/**
 * Retrieves a users details by email
 * @param string $email
 * @return array|null
 * @throws Exception
 */
function user_select(string $email) : ?array
{
    $connection = db_connect();

    $query = "SELECT * FROM users WHERE email_address = $1";
    $result = pg_query_params($connection, $query, array($email));
    if(!$result){
        throw new Exception("Query failed: " . pg_last_error());
    }

    return pg_fetch_assoc($result) ?: null;
}

/**
 * Retrieves all user details
 * @return array
 * @throws Exception
 */
function getAllUsers() : array
{
    $connection = db_connect();

    $query = "SELECT * FROM users";
    $result = pg_query_params($connection, $query, array());

    if(!$result){
        throw new Exception("Query failed: " . pg_last_error());
    }

    $users = [];
    while($row = pg_fetch_assoc($result)){
        $users[] = $row;
    }

    return $users;
}

function searchUsers($search_field, $search_term) : array
{
    $connection = db_connect();

    // List of allowed fields
    $allowed_fields = ['id', 'email_address', 'first_name', 'last_name', 'user_type'];

    // Check if the provided field is allowed
    if (!in_array($search_field, $allowed_fields)) {
        throw new Exception("Invalid search field");
    }

    $query = "SELECT * FROM users WHERE $search_field = $1";
    $result = pg_query_params($connection, $query, array($search_term));

    if(!$result){
        throw new Exception("Query failed: " . pg_last_error());
    }

    $users = [];
    while($row = pg_fetch_assoc($result)){
        $users[] = $row;
    }

    return $users;
}

function getRecentlyActiveUsers() : array
{
    $connection = db_connect();

    $query = "SELECT * FROM users WHERE last_time > (NOW() - INTERVAL '24 hours')";
    $result = pg_query_params($connection, $query, array());

    if(!$result){
        throw new Exception("Query failed: " . pg_last_error());
    }

    $users = [];
    while($row = pg_fetch_assoc($result)){
        $users[] = $row;
    }

    return $users;
}

/**
 * Authenticates a user by checking the password against the hashed version in the database
 * @param $email
 * @param $plain_password
 * @return bool
 * @throws Exception
 */
function user_authenticate($email, $plain_password): bool
{
    $connection = db_connect();

    // retrieve the users hashed password from the database
    $user = user_select($email);
    if(!$user || !isset($user['password'])){
        return false;
    }

    $stored_hash = $user['password'];

    // Hash the provided password using the stored hash as the salt to compare to stored hash
    $verify_query = "SELECT (crypt($1, $2) = $2) AS password_match";
    $verify_result = pg_query_params($connection, $verify_query, array($plain_password, $stored_hash));

    if(!$verify_result){
        throw new Exception("Password verification failed: " . pg_last_error());
    }

    $verify_row = pg_fetch_assoc($verify_result);


    return $verify_row['password_match'] === 't';

}

/**
 * Closes the database connection
 * @return void
 */
function db_close() : void
{
    global $connection;
    if($connection){
        pg_close($connection);
    }
}

/**
 * Registers a new user in the database
 * @param string $email_address
 * @param string $first_name
 * @param string $last_name
 * @param string $password
 * @param string $phone_extension
 * @param string $user_type
 * @return bool
 * @throws Exception
 */
function register_user(string $email_address, string $first_name, string $last_name,
                       string $password, string $phone_extension, string $user_type) : bool
{
    $connection = db_connect();

    // check if user already exists
    $existingUser = user_select($email_address);
    if ($existingUser) {
        throw new Exception ("User with email {$email_address} already exists");
    }

    $profile_img = file_get_contents("images/profilePicDefault.jpg");
    $escaped_profile_img = pg_escape_bytea($connection, $profile_img);

    $query = "INSERT INTO users (email_address, first_name, last_name, password, phone_extension, user_type, profile_img) 
                VALUES($1, $2, $3, $4, $5, $6, $7)";
    $result = pg_query_params($connection, $query, array($email_address, $first_name, $last_name, $password,
        $phone_extension, $user_type, $escaped_profile_img));

    if(!$result){
        throw new Exception("Insertion Failed: " . pg_last_error());
    }

    // If registered user has been successfully entered
    return true;
}

/**
 * Updates a users information in the database based on input actually given
 * @param $userId
 * @param $newEmail
 * @param $newPassword
 * @param $newFirstName
 * @param $newLastName
 * @param $newProfileImg
 * @return bool
 * @throws Exception
 */
function update_user($userId, $newEmail, $newPassword, $newFirstName, $newLastName, $newProfileImg): bool
{
    // Connect to the database
    $db = db_connect();

    // begin SQL update statement
    $sql = "UPDATE users SET";
    $params = array();
    $param_count = 1;

    // checks if a new email was entered
    if (!empty($newEmail)) {
        // validates email
        if(!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            set_flash_message("Invalid email", ALERT_DANGER);
            return false;
        }
        // adds email field to update statement if provided
        $sql .= " email_address = $" . $param_count . ",";
        $params[] = $newEmail;
        $param_count++;
    }
    // checks if a new password was entered
    if (!empty($newPassword)) {
        // Hashes password
        $hashPassword = hash_password($newPassword);

        // adds new password to update statement
        $sql .= " password = $" . $param_count . ",";
        $params[] = $hashPassword;
        $param_count++;
    }
    // check if a new first name was entered
    if (!empty($newFirstName)) {
        if(!ctype_alpha($newFirstName)) {
            set_flash_message("Invalid first name entry.", ALERT_DANGER);
            return false;
        }
        $sql .= " first_name = $" . $param_count . ",";
        $params[] = $newFirstName;
        $param_count++;
    }
    // checks is a new last name was entered
    if (!empty($newLastName)) {
        if(!ctype_alpha($newFirstName)) {
            set_flash_message("Invalid last name entry.", ALERT_DANGER);
            return false;
        }
        $sql .= " last_name = $" . $param_count . ",";
        $params[] = $newLastName;
        $param_count++;
    }

    if (!empty($profileImg) && $_FILES["profile_img"]["error"] !== UPLOAD_ERR_NO_FILE) {
        $targetFile = basename($_FILES["profile_img"]["name"]);
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $uploadOK = 1;

        if ($_FILES["profile_img"]["size"] > 500000) {
            set_flash_message("Sorry, your file is too large.", ALERT_DANGER);
            $uploadOK = 0;
        }

        // Check the file type is valid - .jpg, .png, .jpeg
        if ($fileType != "jpg" && $fileType != "png" && $fileType != "jpeg") {
            set_flash_message("Sorry, only JPG, JPEG, and PNG files are allowed.", ALERT_DANGER);
            $uploadOK = 0;
        }

        if ($uploadOK == 1) {
            $profile_img = file_get_contents($_FILES["profile_img"]["tmp_name"]);
            $escapedProfileImg = pg_escape_bytea($db, $profile_img);
        } else {
            // Handle the case where the upload is not valid
            return false;
        }

        $sql .= " profile_img = $" . $param_count . ",";
        $params[] = $escapedProfileImg;
        $param_count++;
    }

    // Remove the trailing comma
    $sql = rtrim($sql, ',');

    // Add the WHERE clause
    $sql .= " WHERE id = $" . $param_count;

    $params[] = $userId;

    // Execute the query
    $result = pg_query_params($db, $sql, $params);



    if ($result) {
        return true; // User data updated successfully
    } else {
        return false; // Failed to update user data
    }
}

function admin_update_user($userId, $email, $firstName, $lastName, $password, $phone, $userType,
                           $profileImg): bool
{
    // Connect to the database
    $db = db_connect();

    // Begin the SQL update statement
    $sql = "UPDATE users SET";
    $params = array();
    $param_count = 1;

    // Email validation and update
    if (!empty($email)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash_message("Invalid email", ALERT_DANGER);
            return false;
        }
        $sql .= " email_address = $" . $param_count . ",";
        $params[] = $email;
        $param_count++;
    }

    // First Name validation and update
    if (!empty($firstName)) {
        if (!ctype_alpha($firstName)) {
            set_flash_message("Invalid first name", ALERT_DANGER);
            return false;
        }
        $sql .= " first_name = $" . $param_count . ",";
        $params[] = $firstName;
        $param_count++;
    }

    // Last Name validation and update
    if (!empty($lastName)) {
        if (!ctype_alpha($lastName)) {
            set_flash_message("Invalid last name", ALERT_DANGER);
            return false;
        }
        $sql .= " last_name = $" . $param_count . ",";
        $params[] = $lastName;
        $param_count++;
    }

    // Password hashing and update
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql .= " password = $" . $param_count . ",";
        $params[] = $hashedPassword;
        $param_count++;
    }

    // Phone number update
    if (!empty($phone)) {
        $sql .= " phone_extension = $" . $param_count . ",";
        $params[] = $phone;
        $param_count++;
    }

    // User type update
    if (!empty($userType)) {
        $sql .= " user_type = $" . $param_count . ",";
        $params[] = $userType;
        $param_count++;
    }

    // Profile image update
    if (!empty($profileImg) && $_FILES["profile_img"]["error"] !== UPLOAD_ERR_NO_FILE) {
        $targetFile = basename($_FILES["profile_img"]["name"]);
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $uploadOK = 1;

        if ($_FILES["profile_img"]["size"] > 500000) {
            set_flash_message("Sorry, your file is too large.", ALERT_DANGER);
            $uploadOK = 0;
        }

        // Check the file type is valid - .jpg, .png, .jpeg
        if ($fileType != "jpg" && $fileType != "png" && $fileType != "jpeg") {
            set_flash_message("Sorry, only JPG, JPEG, and PNG files are allowed.", ALERT_DANGER);
            $uploadOK = 0;
        }

        if ($uploadOK == 1) {
            $profile_img = file_get_contents($_FILES["profile_img"]["tmp_name"]);
            $escapedProfileImg = pg_escape_bytea($db, $profile_img);
        } else {
            // Handle the case where the upload is not valid
            return false;
        }

        $sql .= " profile_img = $" . $param_count . ",";
        $params[] = $escapedProfileImg;
        $param_count++;
    }

    // Remove the trailing comma and add WHERE clause with user's unique identifier (e.g., email)
    $sql = rtrim($sql, ',') . " WHERE id = $" . $param_count;
    $params[] = $userId;;

    // Execute the query
    $result = pg_query_params($db, $sql, $params);

    if ($result) {
        return true; // User data updated successfully
    } else {
        throw new Exception("Failed to update user data: " . pg_last_error($db));
    }
}

/**
 * Updates the last_login field for users
 * @param string $email
 * @return bool
 * @throws Exception
 */
function user_update_login_time(string $email) : bool
{
    $connection = db_connect();

    // Query to set the time stamp
    $query = "UPDATE users SET last_time = CURRENT_TIMESTAMP WHERE email_address = $1";

    // Run the query
    $result = pg_query_params($connection, $query, array($email));

    // Throw exception if query fails
    if(!$result){
        throw new Exception("Update failed: ", pg_last_error());
    }

    return true;
}

/**
 * Deletes a user by email
 * @param string $email
 * @return bool
 * @throws Exception
 */
/**
 * Deletes a user by email
 * @param string $email
 * @return bool
 * @throws Exception
 */
function delete_user(string $email) : bool
{
    $connection = db_connect();

    $query = "DELETE FROM users WHERE email_address = $1";
    $result = pg_query_params($connection, $query, array($email));
    if (!$result) {
        throw new Exception("Deletion failed: " . pg_last_error());
    }

    // Explicitly check if any rows were affected
    if (pg_affected_rows($result) > 0) {
        return true;
    } else {
        return false;
    }
}

/**
 * Adds a new text policy to a policy table
 * @param string $tableName The name of the table
 * @param string $fileContent The contents of the text file
 * @return bool
 * @throws Exception
 */
function update_policy_table(string $tableName, string $fileContent) : bool
{
    $connection = db_connect();

    // retrieve max policy file version
    $query = "SELECT MAX(version) as max_version FROM $tableName";
    $result = pg_query($connection, $query);

    if ($result) {
        $row = pg_fetch_assoc($result);
        $newVersion = $row ? $row['max_version'] + 1 : 1;

        $updateQuery = "INSERT INTO $tableName (content, version) VALUES ($1, $2)";
        $updateResult = pg_query_params($connection, $updateQuery, array($fileContent, $newVersion));

        if ($updateResult) {
            return true;
        }
    }

    throw new Exception("Update failed: ", pg_last_error());

}

/**
 * Gets the policy content from a policy table to display on a page
 * @param string $tableName The name of the table
 * @return string The policy content
 * @throws Exception
 */
function get_policy_content(string $tableName) : string
{
    $connection = db_connect();

    $query = "SELECT content FROM $tableName ORDER BY last_updated DESC LIMIT 1";
    $result = pg_query_params($connection, $query, []);

    if($result && pg_num_rows($result) > 0) {

        $row = pg_fetch_assoc($result);
        return $row['content'];

    } else {
        return "No content Available";
    }
}

/**
 * Stores an uploaded file
 * @param string $original_name
 * @param string $file_name
 * @param string $mime_type
 * @param string $created_by
 * @return bool
 * @throws Exception
 */
function store_File(string $directory, string $original_name, string $file_name, string $mime_type, string $created_by) : bool
{
    $dbConn = db_connect();

    // store the image file in the database
    $query = "INSERT INTO files (directory, original_name, file_name, mime_type, created_by) VALUES ($1, $2, $3, $4, $5)";
    $result = pg_query_params($dbConn, $query, array($directory, $original_name, $file_name, $mime_type, $created_by));

    if(!$result)
    {
        return false;
    }

    return true;
}

/**
 * Grabs the row data for a given file name
 * @param string $file_name
 * @return array|null
 * @throws Exception
 */
function file_select(string $file_name) : ?array
{
    $connection = db_connect();

    $query = "SELECT * FROM files WHERE file_name = $1";
    $result = pg_query_params($connection, $query, array($file_name));
    if(!$result){
        throw new Exception("Query failed: " . pg_last_error());
    }

    $file_data = pg_fetch_assoc($result);

    if ($file_data === false) {
        throw new Exception("No rows found for file_name: $file_name");
    }

    return $file_data;
}


/**
 * Function to update the file metadata if the original name of the file is changed.
 * @param string $baseFileName
 * @param $newFilePath
 * @return bool
 * @throws Exception
 */
function update_file_metadata(string $baseFileName, $newFilePath) : bool{

    $connection = db_connect();

    // Query to set the time stamp
    $query = "UPDATE files SET directory = $1 WHERE file_name = $2";

    // Run the query
    $result = pg_query_params($connection, $query, array($newFilePath, $baseFileName));

    // Throw exception if query fails
    if(!$result){
        throw new Exception("Update failed: ", pg_last_error());
    }
    return true;
}

/**
 * Stores the new name of the file in the database
 * @param $file_name
 * @param $new_name
 * @return void
 * @throws Exception
 */
function rename_file($file_name, $new_name){

    $dbConn = db_connect();

    // Fetch the file record by the original file name
    $querySelect = "SELECT * FROM files WHERE file_name = $1";
    $resultSelect = pg_query_params($dbConn, $querySelect, array($file_name));

    // If a record exists then prepare the update statement
    if ($row = pg_fetch_assoc($resultSelect)) {
        // Update the file record with the new name
        $queryUpdate = "UPDATE files SET original_name = $1 WHERE id = $2";
        $resultUpdate = pg_query_params($dbConn, $queryUpdate, array($new_name, $row['id']));

        // If result is stored, display success message
        if ($resultUpdate) {
            set_flash_message("File renamed successfully.", ALERT_SUCCESS);
        } else {
            // If error display failure
            set_flash_message("Failed to rename file.", ALERT_DANGER);
        }
    } else {
        // If no result returned then show file not found
        set_flash_message("File not found.", ALERT_DANGER);
    }
}