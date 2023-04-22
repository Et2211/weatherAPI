<?php

use App\Models\db;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable('C:\xampp\htdocs\weatherAPI');
$dotenv->load();


$app = AppFactory::create();

$app->addRoutingMiddleware();
$app->add(new BasePathMiddleware($app));
$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response) {
   $response->getBody()->write('Hello World!');
   return $response;
});

$app->get('/city-data', function (Request $request, Response $response) {
   $sql = "SELECT * FROM cities";
  
   try {
     $db = new db();
     $conn = $db->connect();
     $stmt = $conn->query($sql);
     $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
     $db = null;
    
     $response->getBody()->write(json_encode($customers));
     return $response
       ->withHeader('content-type', 'application/json')
       ->withStatus(200);
    } catch (PDOException $e) {
     $error = array(
       "message" => $e->getMessage()
     );
  
     $response->getBody()->write(json_encode($error));
     return $response
       ->withHeader('content-type', 'application/json')
       ->withStatus(500);
    }
  });

  $app->get('/city-data/{cityId}', function (Request $request, Response $response, $args) {
    
    $cityId = $args['cityId'];
    $sql = "SELECT * FROM cities WHERE cityId = :cityId";
    try {
      $db = new db();
      $conn = $db->connect();
      $stmt = $conn->prepare($sql);
      $stmt->execute([":cityId"=>$cityId]);
      $cityData = $stmt->fetchAll(PDO::FETCH_OBJ);
      $db = null;

      $lat = $cityData[0]->lat;
      $lon = $cityData[0]->lon;


      $client = new GuzzleHttp\Client(['verify' => 'C:\xampp\php\extras\ssl\cacert.pem']);
      $res = $client->request('GET', 'https://api.openweathermap.org/data/2.5/weather?lat='.$lat.'&lon='.$lon.'&appid='.$_ENV["OPENWEATHER_APIKEY"], []);
      
      if ($res->getStatusCode() == 200) {
        $data = json_decode($res->getBody());
      }

 
      $response->getBody()->write(json_encode($data));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(200);
     } catch (PDOException $e) {
      $error = array(
        "message" => $e->getMessage()
      );
   
      $response->getBody()->write(json_encode($error));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(500);
     }
  });

$app->run();