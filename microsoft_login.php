<?php
session_start();  

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

// Regenerate session ID for security
session_regenerate_id(true);

// If we don't have an authorization code then get one
if (!isset($_GET['code'])) {
    // Generate a random state value to protect against CSRF
    $authorizationUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();

    // Redirect the user to Microsoft's authorization URL
    header('Location: ' . $authorizationUrl);
    exit;
}
