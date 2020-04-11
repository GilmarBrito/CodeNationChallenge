<?php
namespace GilmarBrito;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "./vendor/autoload.php";

use GilmarBrito\Classes\CaesarCipher; 
use GuzzleHttp\Client as Client;
use Symfony\Component\Dotenv\Dotenv;



$dotenv = new Dotenv(true);
$dotenv->load(__DIR__.'/.env');

const ENV_BASE_URI = 'CN_BASEURI';
const ENV_GET_URI = 'CN_GENERATE_DATA_URI';
const ENV_POST_URI = 'CN_SUBMIT_SOLUTION_URI';
const ENV_QUERY = 'CN_PARAM_QUERY';
const ENV_TOKEN = 'CN_TOKEN';
const FIELD_BASE_URI = 'base_uri';
const FIELD_QUERY = 'query';
const FIELD_DISPLACEMENT = 'numero_casas';
const FIELD_MSG_ENCRYPTED = 'cifrado';
const FIELD_MSG_DECRYPTED = 'decifrado';
const FIELD_MSG_SHA1 = 'resumo_criptografico';
const PARAM_FIELD_CONTENT_TYPE = 'multipart';
const PARAM_FIELD_NAME = 'name';
const PARAM_FIELD_NAME_VALUE = 'answer';
const PARAM_FIELD_TYPE = 'type';
const PARAM_FIELD_TYPE_VALUE = 'file';
const PARAM_FIELD_CONTENTS = 'contents';
const PARAM_FIELD_FILENAME = 'filename';
const PARAM_FIELD_FILENAME_VALUE = 'answer.json';

$baseUri = $_ENV[ENV_BASE_URI]; 
$cnGenerateDataUri = $_ENV[ENV_GET_URI];
$cnSubmitSolutionUri = $_ENV[ENV_POST_URI];
$cnParamQuery = $_ENV[ENV_QUERY];
$cnToken = $_ENV[ENV_TOKEN];

$client = new Client([
        FIELD_BASE_URI => $baseUri,
    ]
);

$response = $client->request('GET', $cnGenerateDataUri, [
        FIELD_QUERY => [$cnParamQuery => $cnToken]
    ]
);

$json = $response->getBody();

$file = fopen(__DIR__ . '/Original' . PARAM_FIELD_FILENAME_VALUE,'w');
fwrite($file, $json);
fclose($file);

$jsonArray = json_decode($json, true);

$displacement = $jsonArray[FIELD_DISPLACEMENT];
$encryptedMessage =  $jsonArray[FIELD_MSG_ENCRYPTED];

$caesar = new CaesarCipher($displacement, $encryptedMessage);

$jsonArray[FIELD_MSG_DECRYPTED] = $caesar->getDecryptedMessage();
$jsonArray[FIELD_MSG_SHA1] = $caesar->getSha1HashMessage();

$json = json_encode($jsonArray);

$file = fopen(__DIR__ . '/' . PARAM_FIELD_FILENAME_VALUE,'w');
fwrite($file, $json);
fclose($file);

$file = file_get_contents(__DIR__ . '/' . PARAM_FIELD_FILENAME_VALUE);

$response = $client->request('POST', $cnSubmitSolutionUri, [
    FIELD_QUERY => [$cnParamQuery => $cnToken],
    PARAM_FIELD_CONTENT_TYPE => [
            [
                PARAM_FIELD_NAME        => PARAM_FIELD_NAME_VALUE,
                PARAM_FIELD_TYPE        => PARAM_FIELD_TYPE_VALUE,
                PARAM_FIELD_CONTENTS    => $file,
                PARAM_FIELD_FILENAME    => PARAM_FIELD_FILENAME,
            ],
        ],
    ]
);

$json = $response->getBody();

$file = fopen(__DIR__ . '/Result' . PARAM_FIELD_FILENAME_VALUE,'w');
fwrite($file, $json);
fclose($file);

echo $json;
