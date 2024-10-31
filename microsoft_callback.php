<?php
session_start();  // Ensure session is started

// Include necessary libraries
require 'outh2/vendor/autoload.php';

// Replace 'YOUR_TENANT_ID' with your actual tenant ID
$tenantId = '8b5f60ac-e420-4280-b0a5-0d6fd7eaf2d7';
$provider = new League\OAuth2\Client\Provider\GenericProvider([
    'clientId'                => '8cbc3a8d-d804-4d71-bc69-cf9797e66a84',
    'clientSecret'            => 'sZA8Q~PjVvGHVVdOilCTXj4O-_BGJ7wWr5ZgZanR',
    'redirectUri'             => 'http://localhost/requests/microsoft_callback.php',
    'urlAuthorize'            => "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/authorize",
    'urlAccessToken'          => "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/token",
    'urlResourceOwnerDetails' => 'https://graph.microsoft.com/v1.0/me',
    'scopes'                  => 'openid profile email User.Read'
]);

// Validate the state parameter to prevent CSRF attacks
if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);  // Clear invalid state
    header('Location: http://localhost/requests/login.php');  // Redirect to login page
    exit('Invalid OAuth state, restarting authorization...');
}

try {
    // Exchange the authorization code for an access token
    $accessToken = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Clear the authorization code and state from the session
    unset($_SESSION['oauth2state']);
    unset($_GET['code']);  // Clear the code from the request

    // Use the access token to get the user's profile from Microsoft Graph API
    $resourceOwner = $provider->getResourceOwner($accessToken);
    $user = $resourceOwner->toArray();

    // Example data you can retrieve
    $email = $user['userPrincipalName'];  // Email from Microsoft
    $displayName = $user['displayName'];  // User's name from Microsoft

    // Store user info in session
    $_SESSION['email'] = $email;
    $_SESSION['username'] = $displayName;

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'company2');
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    // Check if the user already exists in the database
    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($result->num_rows == 0) {
        // New user, add to the database with "pending" role
        $conn->query("INSERT INTO users (email, username, type, department, active) VALUES ('$email', '$displayName', 'pending', 'default_department', 0)");
        header('Location: logout.php?message=awaiting_approval');  // Redirect to logout if not approved
        exit();
    } else {
        // Existing user, check status
        $row = $result->fetch_assoc();

        // If user is not yet approved, log them out
        if ($row['type'] == 'pending' || $row['active'] == 0) {
            header('Location: logout.php?message=awaiting_approval');  // Redirect to logout if not approved
            exit();
        }

        // Approved user, proceed to homepage or dashboard
        $_SESSION['type'] = $row['type'];
        $_SESSION['department'] = $row['department'];

        if ($row['type'] === 'Helpdesk') {
            header('Location: help_home.php');
        } else {
            header('Location: home.php');
        }
    }

    $conn->close();  // Close database connection

} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
    exit('Failed to get access token: ' . $e->getMessage());
} catch (Exception $e) {
    exit('An unexpected error occurred: ' . $e->getMessage());
}
