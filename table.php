<?php
session_start();
require 'outh2/vendor/autoload.php';

use League\OAuth2\Client\Provider\GenericProvider;

$provider = new GenericProvider([
    'clientId'                => 'your-client-id',   
    'clientSecret'            => 'your-client-secret',
    'redirectUri'             => 'http://localhost/login.php',
    'urlAuthorize'            => 'https://login.microsoftonline.com/your-tenant-id/oauth2/v2.0/authorize',
    'urlAccessToken'          => 'https://login.microsoftonline.com/your-tenant-id/oauth2/v2.0/token',
    'urlResourceOwnerDetails' => '',
    'scopes'                  => 'openid profile email'
]);

if (!isset($_GET['code'])) {
    // Redirect the user to Microsoft's login page
    $authorizationUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authorizationUrl);
    exit;

} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Invalid state');
} else {
    try {
        // Get access token
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        // Get user details
        $resourceOwner = $provider->getResourceOwner($accessToken);
        $userData = $resourceOwner->toArray();
        $windowsEmail = $userData['email'];  // Get user's Windows email

        // Check if user is already in the system
        $conn = new mysqli('localhost', 'root', '', 'company');
        $sql = "SELECT * FROM users WHERE email='$windowsEmail'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['role'] && $row['department']) {
                // User has role and department, allow them to log in
                $_SESSION['username'] = $row['username'];
                $_SESSION['type'] = $row['type'];
                $_SESSION['department'] = $row['department'];
                header('Location: home.php');
            } else {
                // User exists but no role/department assigned
                echo "Admin has not assigned your role and department.";
            }
        } else {
            // User doesn't exist, inform admin to register the user
            echo "You are not registered in the system. Contact admin.";
        }
    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        exit($e->getMessage());
    }
}
?>




