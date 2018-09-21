<?php

namespace App\Http\Controllers;

use App\Codigopostal;
use App\Denuncia;
use App\Token;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class DenunciaController extends Controller
{

    public function formulario() {
        return view('denuncia.formulario');
    }

    public function generarTokens(Request $request){
        if ($request->isMethod('get')) {
            $tiempo = time();
            $clave = '2018CyDORG-' . $tiempo;
            $token = password_hash($clave, PASSWORD_BCRYPT);
            $aleatorio = password_hash(time(), PASSWORD_BCRYPT);
            $aleatorio2 = password_hash("Morena" . time(), PASSWORD_BCRYPT);
            $obj = new Token();
            $obj->tiempo = $tiempo;
            $obj->token = $token;
            $obj->save();
            return response()->json(['app' => $aleatorio2, 'version' => "1.2.19.u.2", 'from' => $token, 'auth' => $aleatorio], 200);
        } else {
            return response()->json(['error' => 'error inesperado'], 401);
        }

    }

    public function insertarDenuncia(Request $request){
        if ($request->isMethod('post')) {

            $this ->validate($request, [
                'usuario' => 'bail|required',
                'latitud' => 'bail|required',
                'longitud' => 'bail|required',
                'estado' =>'bail|required',
                'municipio' => 'bail|required',
                'colonia' => 'bail|required',
                'direccion' => 'bail|required',
                'cp' => 'bail|required',
                'descripcion' => 'bail|required',
                'tipo'=> 'bail|required',
                'nosotros' => 'bail|required',
                'fecha' => 'bail|required',
                'token' => 'bail|required'
            ]);

            $usuarioToken = $request->input('token', 0);
            $token = Token::where('token',$usuarioToken)->first();
            if($token){
                $now = time();
                $diff =  $now - $token->tiempo;
                $diff = floor($diff / 60);
                if($diff >= 5){
                    return response()->json(['Error' => 'Tiempo de sesion finalizado'], 401);
                }
            }else {
                return response()->json(['Error' => 'Por el momento no se puede procesar'], 400);
            }

            $denuncia = new Denuncia();
            $denuncia->usuario = $request->usuario;
            $denuncia->latitud = (double)$request->latitud;
            $denuncia->longitud = (double)$request->longitud;
            $denuncia->estado = $request->estado;
            $denuncia->municipio = $request->municipio;
            $denuncia->colonia = $request->colonia;
            $denuncia->direccion = $request->direccion;
            $denuncia->cp = $request->cp;
            $denuncia->descripcion = $request->descripcion;
            $denuncia->tipo = $request->tipo;
            $denuncia->nosotros = (boolean)$request->nosotros;
            $denuncia->fecha = date('Y-m-d H:i:s', strtotime($request->fecha));

            if ($request->has('nombre')){
                $denuncia->nombre = $request->nombre;
            }
            if ($request->has('tel')){
                $denuncia->telefono = $request->tel;
            }
            if ($request->has('email')){
                $denuncia->email = $request->email;
            }
            $denuncia->status = 0;

            if ($denuncia->save()) {
                if($request->has('archivos')){
                    $archivos = $request->allFiles();
                    $documento = Denuncia::project(['id' => 1])->findOrFail($denuncia->id);
                    $pathFiles = Array();
                    foreach ($archivos as $archivo){
                        $mime = explode('/',$archivo->getMimeType());
                        if($mime[0] == 'video'){
                            $nuevoArchivo = $archivo->move('.', str_random(6).'.mp4');
                            array_push($pathFiles, Storage::disk('s3')->putFile('denuncias/'.$denuncia->id, new File($nuevoArchivo)));
                        } elseif($mime[0] == 'image'){
                            $nuevoArchivo = $archivo->move('.', str_random(6).'.jpeg');
                            array_push($pathFiles, Storage::disk('s3')->putFile('denuncias/'.$denuncia->id, new File($nuevoArchivo)));
                        }
                    }
                    $documento->archivos = $pathFiles;
                    if($documento->save()){
                        return response()->json(['mensaje' => 'exito'], 200);
                    } else {
                        return response()->json(['error' => 'no se subieron los archivos'], 400);
                    }
                }
                return response()->json(['mensaje' => 'exito'], 200);
            } else {
                return response()->json(['error' => 'no se creo el documento'], 404);
            }
        } else {
            return response()->json(['error' => 'datos incompletos'], 400);
        }

    }

    public function buscaCodigoPostal(Request $request){
        if($request->isMethod('get')){
            $this ->validate($request, ['cp' => 'bail|required|numeric']);
            $cp = $request -> input('cp');
            $direccion = Codigopostal::project(['Codigo' => 1,
                'Asentamiento' => 1,
                'Municipio' => 1,
                'Estado' => 1])->where('Codigo', '=', $cp)->get();
            $codigo = utf8_encode($direccion[0]['Codigo']);
            $colonia = utf8_encode($direccion[0]['Asentamiento']);
            $municipio = utf8_encode($direccion[0]['Municipio']);
            $estado = utf8_encode($direccion[0]['Estado']);

            $junto = array('codigo' => $codigo, 'colonia' => $colonia, 'municipio' => $municipio, 'estado' => $estado);

            return response()->json($direccion);
        }else{
            return response()->json(['Error' => 'metodo incorrecto']);
        }

    }

}