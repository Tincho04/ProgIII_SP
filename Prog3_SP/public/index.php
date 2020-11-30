<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Config\Database;
use App\Controllers\AlumnoController;
use App\Middleware\JsonMiddleware;
use App\Middleware\AuthMiddleware;


require __DIR__ . '/../vendor/autoload.php';

$conn = new Database;

$app = AppFactory::create();
$app->addErrorMiddleware(true, false, false);
$app->setBasePath('/Prog3_SP/public');

$app->group('', function (RouteCollectorProxy $group) {

    // $group->get('[/]', AlumnoController::class . ":getAll");
    //1
    $group->post('/users', AlumnoController::class . ":addOneUsuario");
    //2    
    $group->post('/login', AlumnoController::class . ":login");
    //3
    $group->post('/materia', AlumnoController::class . ":addOneMateria")->add(new AuthMiddleware('admin'));
    //4
    $group->post('/inscripcion/{id}', AlumnoController::class . ":inscripcionMaterias")->add(new AuthMiddleware('alumno'));
    //5
    $group->put('/notas/{id}', AlumnoController::class . ":updateMateria")->add(new AuthMiddleware('profesor'));
//6
    $group->get('/inscripcion/{id}', AlumnoController::class . ":getMateriasById");
//7
    $group->get('/materia', AlumnoController::class . ":getAllMaterias");
//8
    $group->get('/notas/{id}', AlumnoController::class . ":getNotasById");
})->add(new JsonMiddleware);

$app->addBodyParsingMiddleware();
$app->run();
