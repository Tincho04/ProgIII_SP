<?php

namespace App\Controllers;

use \Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Materia;
use App\Models\Usuario;
use App\Models\Alumno;
use App\Models\Inscripcion;
use App\Models\Profesor;
use stdClass;

class AlumnoController
{

    public function getAll(Request $request, Response $response, $args)
    {
        $rta = Usuario::get();
        $response->getBody()->write(json_encode($rta));
        return $response;
    }

    public function getAllMaterias(Request $request, Response $response, $args)
    {
        $headers = getallHeaders();
        $token = $headers['token'];
        $decoded = JWT::decode($token, "Prog3_SP", array('HS256'));

        if ($decoded->tipo == "admin" || $decoded->tipo == "alumno" || $decoded->tipo == "profesor") {
            $materias = Materia::get();
            $response->getBody()->write(json_encode($materias));
        }

        return $response;
    }

    public function getMateriasById(Request $request, Response $response, $args)
    {
        $headers = getallHeaders();
        $token = $headers['token'];
        $decoded = JWT::decode($token, "Prog3_SP", array('HS256'));

        if ($decoded->tipo == "admin" || $decoded->tipo == "profesor") {
            $id = $args['id'];
            $materia = Inscripcion::find($id);

            $idAlumno = Inscripcion::select('id_alumno')->where('id_materia', $materia->id_materia)->get();
            $alumno = Alumno::select('nombre')->where('id', $idAlumno->id)->get();

            $response->getBody()->write(json_encode($alumno));
        }
        return $response;
    }

    
    public function getNotasById(Request $request, Response $response, $args)
    {
        $headers = getallHeaders();
        $token = $headers['token'];
        $decoded = JWT::decode($token, "Prog3_SP", array('HS256'));

        if ($decoded->tipo == "admin" || $decoded->tipo == "profesor") {
            $id = $args['id'];
            $materia = Inscripcion::get($id);
            $response->getBody()->write(json_encode($materia));
        }
        return $response;
    }

    public function getOne(Request $request, Response $response, $args)
    {
        $rta = Usuario::find($args['legajo']);;

        $response->getBody()->write(json_encode($rta));
        return $response;
    }


    public function addOneUsuario(Request $request, Response $response, $args)
    {
        $user = new Usuario;

        $user->nombre = $request->getParsedBody()['nombre'];
        $user->clave = $request->getParsedBody()['clave'];
        $user->tipo = $request->getParsedBody()['tipo'];
        $user->email = $request->getParsedBody()['email'];

        if ($user->nombre != '') {
            if ($user->tipo == "alumno" || $user->tipo == "profesor" || $user->tipo == "admin") {
                if (strlen($user->clave) >= 4) {
                    $foundmail = json_encode(Usuario::select()->where('email', $user->email)->get());
                    $foundname = json_encode(Usuario::select()->where('nombre', $user->nombre)->get());
                    if (!is_null($foundmail) && !is_null($foundname)) {
                        if ($user->tipo == "profesor") {
                            $profesor = new Profesor;
                            $profesor->mail = $user->email;
                            $profesor->nombre = $user->nombre;
                            $profesor->save();
                        } else if ($user->tipo == "alumno") {
                            $alumno = new Alumno;
                            $alumno->email = $user->email;
                            $alumno->nombre = $user->nombre;
                            $alumno->save();
                        }
                        $rta = $user->save();
                        $response->getBody()->write(json_encode($rta));
                    } else {
                        if ($foundmail != null) {
                            $response->getBody()->write("El mail ya se encuentra registrado");
                            return $response->withStatus(401);
                        } else {
                            $response->getBody()->write("el nombre ya se encuentra registrado");
                            return $response->withStatus(401);
                        }
                    }
                } else {
                    $response->getBody()->write("La clave debe poseer como minimo cuatro caracteres");
                    return $response->withStatus(401);
                }
            } else {
                $response->getBody()->write("El tipo de usuario seleccionado no es válido");
                return $response->withStatus(401);
            }
        } else {
            $response->getBody()->write("El nombre no puede contener espacios");
            return $response->withStatus(401);
        }
        return $response;
    }


    public function addOneMateria(Request $request, Response $response, $args)
    {
        $materia = new Materia;

        $materia->materia = $request->getParsedBody()['materia'];
        $materia->cuatrimestre = $request->getParsedBody()['cuatrimestre'];
        $materia->cupo = $request->getParsedBody()['cupos'];

        $rta = $materia->save();

        $response->getBody()->write(json_encode($rta));
        return $response;
    }

    public function updateOneUsuario(Request $request, Response $response, $args)
    {
        $id = $args['id'];

        $user = Usuario::find($id);

        $user->tipo = $request->getParsedBody()['tipo'];

        $rta = $user->save();

        $response->getBody()->write(json_encode($rta));
        return $response;
    }

    public function deleteOne(Request $request, Response $response, $args)
    {
        $id = $args['id'];

        $user = Usuario::find($id);

        $rta = $user->delete();

        $response->getBody()->write(json_encode($rta));
        return $response;
    }


    public function login(Request $request, Response $response, $args)
    {

        $body = $request->getParsedBody();
        $email = $body['email'];
        $clave = $body['clave'];

        $usuarioEncontrado = json_decode(Usuario::whereRaw('email = ? AND clave = ?', array($email, $clave))->get());
        $key = 'Prog3_SP';
        if ($usuarioEncontrado) {
            $payload = array(
                "email" => $usuarioEncontrado[0]->email,
                "clave" => $usuarioEncontrado[0]->clave,
                "tipo" => $usuarioEncontrado[0]->tipo,
            );

            $play = JWT::encode($payload, $key);
            $rta = new stdClass;
            $rta->data = $play;
            $response->getBody()->write(json_encode($rta));
        } else {
            $response->getBody()->write("Usuario no registrado");
            return $response->withStatus(402);
        }

        return $response->withHeader('Content-type', 'application/json');;
    }

    public function inscripcionMaterias(Request $request, Response $response, $args)
    {
        $headers = getallHeaders();
        $token = $headers['token'];
        $decoded = JWT::decode($token, "Prog3_SP", array('HS256'));
        $materia = Materia::find($args['id']);
        $idAlumno = Alumno::select('id')->where('email', $decoded->email)->get();
        if ($materia->cupo > 0) {
            $inscripcion = new Inscripcion();
            $inscripcion->id_alumno = $idAlumno[0]->id;
            $inscripcion->id_materia = $args['id'];
            $materia->cupo--;
            $materia->save();
            $rta = $inscripcion->save();
        } else {
            $response->getBody()->write("No se encuentran cupos disponibles");
            return $response->withStatus(402);
        }

        $response->getBody()->write(json_encode($rta));
        return $response;
    }

    public function modifica(Request $request, Response $response, $args)
    {
        $usuario = Usuario::find($args['legajo']);
        $req = $request->getParsedBody();
        $headers = getallHeaders();
        $token = $headers['token'];
        $decoded = JWT::decode($token, "Prog3_SP", array('HS256'));

        if ($decoded->legajo == $usuario->legajo && $decoded->tipo == "alumno" || $decoded->tipo == "admin") {

            $usuario->email = $request->getParsedBody()['email'];

            $usuario->save();
        } else if ($decoded->legajo == $usuario->legajo && $decoded->tipo == "profesor" || $decoded->tipo == "admin") {
            //$profesor= new Profesor;
            $profesor = Profesor::find($args['legajo']);
            $profesor->pEmail = $request->getParsedBody()['email'];
            $materias = implode("-", $req["materias"]);
            echo json_encode($materias);
            //$profesor->pLegajo = $usuario->legajo;
            $profesor->pMateria = $materias;
            $profesor->save();
        }
        $response->getBody()->write(json_encode($usuario));
        return $response->withHeader('Content-type', 'application/json');;
    }

    public function update(Request $request, Response $response, $args)
    {
        $usuario = Usuario::find($args['legajo']);
        $req = $request->getParsedBody();
        $headers = getallHeaders();
        $token = $headers['token'];
        $decoded = JWT::decode($token, "Prog3_SP", array('HS256'));

        if ($decoded->legajo == $usuario->legajo && $decoded->tipo == "alumno" || $decoded->tipo == "admin") {

            $usuario->email = $request->getParsedBody()['email'];

            $usuario->save();
        } else if ($decoded->legajo == $usuario->legajo && $decoded->tipo == "profesor" || $decoded->tipo == "admin") {
            //$profesor= new Profesor;
            $profesor = Profesor::find($args['legajo']);
            $profesor->pEmail = $request->getParsedBody()['email'];
            $materias = implode("-", $req["materias"]);
            echo json_encode($materias);
            //$profesor->pLegajo = $usuario->legajo;
            $profesor->pMateria = $materias;
            $profesor->save();
        }
        $response->getBody()->write(json_encode($usuario));
        return $response->withHeader('Content-type', 'application/json');;
    }

    public function updateMateria(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $nota = $args['nota'];
        $idAlumno = $args['idAlumno'];

        $user = Usuario::find($id);

        $user->tipo = $request->getParsedBody()['tipo'];

        $rta = $user->save();

        $response->getBody()->write(json_encode($rta));
        return $response;
    }
}
