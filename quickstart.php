<?php
require __DIR__ . '/vendor/autoload.php';

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('Google Drive API PHP Quickstart');
    $client->setScopes(Google_Service_Drive::DRIVE);
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('offline');

    // Load previously authorized credentials from a file.
    $credentialsPath = 'token.json';
    if (file_exists($credentialsPath)) {
        $accessToken = json_decode(file_get_contents($credentialsPath), true);
    } else {
        // Request authorization from the user.
        $authUrl = $client->createAuthUrl();
        printf("Open the following link in your browser:\n%s\n", $authUrl);
        print 'Enter verification code: ';
        $authCode = trim(fgets(STDIN));

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        // Check to see if there was an error.
        if (array_key_exists('error', $accessToken)) {
            throw new Exception(join(', ', $accessToken));
        }

        // Store the credentials to disk.
        if (!file_exists(dirname($credentialsPath))) {
            mkdir(dirname($credentialsPath), 0700, true);
        }
        file_put_contents($credentialsPath, json_encode($accessToken));
        printf("Credentials saved to %s\n", $credentialsPath);
    }
    $client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
    }
    return $client;
}


// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Drive($client);

// Print the names and IDs for up to 10 files.
$optParams = array(
  'pageSize' => 10,
  'fields' => 'nextPageToken, files(id, name)'
);
$url_do_arquivo = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ-J4JhsR1TFU9iKPJFPOE77U2VqYECdGp9eiP1_XRbUaLEoZu8PjrdODrxxSSsl7mBQRks3T_Y9WTy/pub?output=csv";

if (($identificador = fopen($url_do_arquivo, 'r')) !== FALSE) {
    $i = 0;
    while (($linha_do_arquivo = fgetcsv($identificador, 0, ",")) !== FALSE) { 
        if($linha_do_arquivo[2] != "" && $linha_do_arquivo[2] != "2. Informe o nome do produto para apresentar na vitrine:"){
            $nome[] = $linha_do_arquivo[2];
            $status = 1;
            } else {
                $status = 0;
            }
        if($linha_do_arquivo[1] != "" && $linha_do_arquivo[1] != "1. Informe o CNPJ da cooperativa/associação"){
            $cnpj[] = $linha_do_arquivo[1];   
        }
        if($linha_do_arquivo[3] != "" && $linha_do_arquivo[3] !="3. Aqui você pode incluir a foto do seu produto:"){
            $explode = explode('=', $linha_do_arquivo[3]);    
            $hash[] = $explode[1];    
        }  
        if($status == 1){
            $fileId = $hash[$i];
            $response = $service->files->get($fileId, array( 'alt' => 'media'));
            $content = $response->getBody()->getContents();
            file_put_contents("./imagens/$cnpj[$i]-$nome[$i].jpg", $content);  
            $i++; 
        }
    }
    fclose($identificador);
} else {
    
    echo 'Não foi possível abrir o arquivo';
}

 