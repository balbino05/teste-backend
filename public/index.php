<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PicPay\Controller\TransferController;
use PicPay\Infrastructure\Database\ConnectionFactory;
use PicPay\Repository\TransactionRepository;
use PicPay\Repository\UserRepository;
use PicPay\Service\External\AuthorizationService;
use PicPay\Service\External\NotificationService;
use PicPay\Service\TransferService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

// Carrega variáveis de ambiente
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Configura logger
$logger = new Logger('picpay');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/app.log', Logger::DEBUG));

// Cria conexão com banco de dados
$dbConfig = [
    'host' => $_ENV['DB_HOST'] ?? 'mysql',
    'port' => (int) ($_ENV['DB_PORT'] ?? 3306),
    'name' => $_ENV['DB_NAME'] ?? 'picpay_db',
    'user' => $_ENV['DB_USER'] ?? 'picpay_user',
    'password' => $_ENV['DB_PASS'] ?? 'picpay_password',
];

$connection = ConnectionFactory::create($dbConfig);

// Cria repositórios
$userRepository = new UserRepository($connection);
$transactionRepository = new TransactionRepository($connection);

// Cria serviços externos
$httpClient = new \GuzzleHttp\Client();
$authorizationService = new AuthorizationService(
    $httpClient,
    $_ENV['AUTHORIZE_SERVICE_URL'] ?? 'https://util.devi.tools/api/v2/authorize',
    $logger
);
$notificationService = new NotificationService(
    $httpClient,
    $_ENV['NOTIFY_SERVICE_URL'] ?? 'https://util.devi.tools/api/v1/notify',
    $logger
);

// Cria serviço de transferência
$transferService = new TransferService(
    $userRepository,
    $transactionRepository,
    $authorizationService,
    $notificationService,
    $connection,
    $logger
);

// Cria controller
$transferController = new TransferController($transferService, $logger);

// Configura Slim
$app = AppFactory::create();

// Middleware para parsing JSON
$app->addBodyParsingMiddleware();

// Middleware de erro
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType('application/json');

// Rota de transferência
$app->post('/transfer', function (Request $request, Response $response) use ($transferController) {
    return $transferController->transfer($request, $response);
});

// Rota de health check
$app->get('/health', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode(['status' => 'ok']));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();

