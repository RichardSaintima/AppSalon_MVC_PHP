<?php

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController {
    public static function login( Router $router){
        $alertas = [];
        $auth = new Usuario;

        if($_SERVER['REQUEST_METHOD'] ==='POST') {
            
            $auth = new Usuario($_POST);

            $alertas = $auth->validarLogin();

            if(empty($alertas)) {
                // Comprobar si existe Usuario
                $usuario = Usuario::where('email', $auth->email);

                if($usuario) {
                    //Verificar el Password
                   if ($usuario->comprobarPasswordAndVerificacion($auth->password)) {
                        // Autenticar el usuario
                       session_start();

                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre . " " . $usuario->apellido;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true ;

                        // Rediccionamiento 
                        if($usuario->admin === "1") {
                            $_SESSION['admin'] = $usuario->admin ?? null;

                            header('Location: /admin');
                        }else {
                            header('Location: /cita');
                        }
                    }
                }else {
                    Usuario::setAlerta('error', 'Usuario no encuentrado');
                }
            }
        }

    $alertas = Usuario::getAlertas();        

        $router->render('auth/login', [
            'alertas' => $alertas,
            'auth' => $auth,
        ]);
    }

    public static function logout(){
        // session_start();
        $_SESSION = [];
        header('Location: /');        
    }

    public static function olvide(Router $router){
       
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] ==='POST') {
            $auth = new Usuario($_POST);
            $alertas = $auth->validarEmail();

            if(empty($alertas)) {
                $usuario =Usuario::where('email', $auth->email);
                
                if($usuario &&$usuario->confirmado === "1") {
                    
                    // Generar un Token
                    $usuario->crearToken();
                    $usuario->guardar();

                    // Enviar el email
                    $email = new Email( $usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();

                    // Alerta de exito
                    Usuario::setAlerta('exito', 'Revisa tu E-mail');
                }else {
                    Usuario::setAlerta('error', 'El usuario no existe o no esta confirmado');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/olvide-password', [
            'alertas' => $alertas,
        ]);
    }

    public static function recuperar( Router $router ){

        $alertas = [];

        $token = s($_GET['token']);
        
        // Buscar usuario por su token
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Token no Valido');
            $error = true;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lleer el nuevo password y guadarlo

            $password = new Usuario($_POST);
            $alertas = $password->validarPassword();


            if(empty($alertas)) {
                $usuario->password =null;

                $usuario->password = $password->password;
                $usuario->HashearPassword();
                $usuario->token =null;
                
                $resultado = $usuario->guardar();
                if($resultado) {
                    header('Location: /');
                }
            }
        }

        $alertas = Usuario::getAlertas();
        $router->render('auth/recuperar-password', [
            // 'usuario' => $usuario,
            'alertas' => $alertas,
            'error' => $error,
       ]);
    }

    public static function crear( Router $router){
        $usuario = new Usuario;

        // Alerta Vacias
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {       
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();

            // Revisar que alerta esta vacio
            if(empty($alertas)) {
                // Verificar si el usuario no esta registrado
                $resultado = $usuario->existeUsuario();
                
                if($resultado->num_rows) {
                    $alertas = Usuario::getAlertas();
                }else {
                    // Hashear el Password
                    $usuario->HashearPassword();

                    // Generar un token único
                    $usuario->crearToken();

                    // Enviar el email
                    $email = new Email($usuario->nombre, $usuario->email, $usuario->token);
                    $email->enviarConfirmacion();

                    // Crear usuario
                    $resultado = $usuario->guardar();
                    // debuguear($usuario);

                    if($resultado) {
                        header('Location: /mensaje');
                    }
                }
            }
        }
        
        $router->render('auth/crear-cuenta', [
             'usuario' => $usuario,
             'alertas' => $alertas,
        ]);
    }

    public static function mensaje( Router $router){

        $router->render('auth/mensaje');
    }   

    public static function confirmar( Router $router){

        $alertas = [];

        $token = s($_GET['token']);

        $usuario = Usuario::where('token' , $token);
        
        if(empty($usuario)) {
            // Mostrar mensaje de Error
            Usuario::setAlerta('error', 'Token no Válido');

        }else {
            // Modificar a usuario confimado
            $usuario->confirmado = '1';
            $usuario->token = null;
            $usuario->guardar();
            Usuario::setAlerta('exito', 'Cuenta Comprobada Correctamente');
        
        }

        // Obtener alertas
        $alertas = Usuario::getAlertas();

        // Renderizar la vista
        $router->render('auth/confirmar-cuenta', [
            'alertas' => $alertas,
        ]);
    }   
}