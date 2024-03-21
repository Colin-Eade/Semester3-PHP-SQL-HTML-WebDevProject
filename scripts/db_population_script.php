<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename db_population_script.php
 */

require_once '../vendor/autoload.php';
require_once 'config/constants.php';
require_once 'lib/db.php';
$faker = Faker\Factory::create();

try{

    // connect to the database
    $db_connect = db_connect();

    if(isset($_POST['generate'])){

        for ($i=0; $i<10; $i++){

            $email_address = $faker->safeEmail();
            $first_name = $faker->firstName();
            $last_name = $faker->lastName();

            $password = hash_password($faker->password());

            $phone_extension = $faker->randomNumber(4, true);
            $user_type = $faker->randomElement(['a', 'c']);

            $profile_img = file_get_contents($faker->imageUrl(100, 100, "people"));
            $escaped_profile_img = pg_escape_bytea($db_connect, $profile_img);

            $query = 'INSERT INTO users (email_address, first_name, last_name, password, phone_extension, 
                                        user_type, profile_img) 
                            VALUES($1, $2, $3, $4, $5, $6, $7)';
            pg_query_params($db_connect, $query, array($email_address, $first_name, $last_name, $password,
                            $phone_extension, $user_type, $escaped_profile_img));
        }
        echo "50 Users Generated";
    }

} catch (Exception $e){
    echo $e->getMessage();
}
?>

<form method="post">
    <button type="submit" name="generate">Generate Random Users</button>
</form>
