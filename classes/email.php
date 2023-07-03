<?php

namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Email {
    
    public $email;
    public $nombre;
    public $token;

    public function __construct($email, $nombre, $token) {
        $this->email = $email;
        $this->nombre = $nombre;
        $this->token = $token;

    }

    public function enviarConfirmacion() {

        // Crear el Objeto de email
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Port = 2525;
        $mail->Username = '40e16833e5e7ed';
        $mail->Password = 'a5cbdba8d6dcb0';

        $mail->setFrom('cuentas@appsalon.com');
        $mail->addAddress('cuentas@appsalon.com', 'AppSalon.com');
        $mail->Subject = 'Confirma tu Cuenta';

        // Set HTML
        $mail->isHTML(TRUE);
        $mail->CharSet = 'UTF-8';

        $contenido = "<html>";
        $contenido .= "<p><trong>Hola " . $this->email . "</trong>";
        $contenido .= " Has creado tu Cuenta en AppSalon, solo debes confirmarla presionando en el siguiente enlace</p>";        
        $contenido .= "<p>Presiona aquí: <a href='http://localhost:3000/confirmar-cuenta?token=" .
         $this->token ."'>Confirmar Cuenta</a></p>";
        $contenido .= "<p>Si tu no solicitar esta cuenta, puedes igrorar el mensaje</p>";
        $contenido .= "</html>";

        $mail->Body = $contenido;

        // Enviar Email
        $mail->send();
        
    }

    public function enviarInstrucciones() {
        // Crear el Objeto de email
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Port = 2525;
        $mail->Username = '40e16833e5e7ed';
        $mail->Password = 'a5cbdba8d6dcb0';

        $mail->setFrom('cuentas@appsalon.com');
        $mail->addAddress('cuentas@appsalon.com', 'AppSalon.com');
        $mail->Subject = 'Reestablece tu password';

        // Set HTML
        $mail->isHTML(TRUE);
        $mail->CharSet = 'UTF-8';

        $contenido = "<html>";
        $contenido .= "<p><trong>Hola " . $this->nombre . "</trong>";
        $contenido .= " Has solicitado restablecer tu password, sique el siguiente enlace para hacerlo</p>";        
        $contenido .= "<p>Presiona aquí: <a href='http://localhost:3000/recuperar?token=" .
         $this->token ."'>Restablecer Password</a></p>";
        $contenido .= "<p>Si tu no solicitar esta cuenta, puedes igrorar el mensaje</p>";
        $contenido .= "</html>";

        $mail->Body = $contenido;

        // Enviar Email
        $mail->send();
         
    }
}