<?php

if( !defined('LIB_EMAIL') ){

    define('LIB_EMAIL','v0.2');
    include('basics.php');
    include('logs.php');

    class vEmail{
        public $destinatarios = '';
        public $titulo = 'Pruebas';
        public $mensaje = '';
        public $cabecera = '';

        public $_to = '';
        public $_from = '';
        public $_cc = '';
        public $_bcc = '';

        public function enviar_email(){
            if( $this->destinatarios == '' ){ return false; }
            if( $this->mensaje == '' ){ return false; }

            $d = $this->destinatarios;
            $m = $this->mensaje;
            $t = $this->titulo;
            $c = $this->cabecera;

            //log_data( '/home/rmorales/magento/NESCA/log/email_send', "==> $d\n==> $c\n==> $t\n==> contenido del email\n" );

            mail($d, $t, $m, $c);

            //echo "\n ==> ".$d." ==> ".$t."\n";
            return true;
        }

        public function add_send_to( $email='' ){
            if( $email=='' ){ return false; }
            if( $this->destinatarios != '' ){ $this->destinatarios = $this->destinatarios.', '; }

            $this->destinatarios = $this->destinatarios.$email;

            return true;
        }

        public function add_title( $t='' ){
            if($t==''){ return false; }

            $this->titulo = $t;
            return true;
        }

        public function add_message( $msg='',$t='text' ){
            if($msg==''){ return false; }

            $this->mensaje = $msg;
            if( $t == 'text' ){
                $this->cabecera = '';
            }
            if( $t == 'html' ){
                $this->cabecera = 'MIME-Version: 1.0' . "\r\n".'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                $this->cabecera = 'MIME-Version: 1.0' . "\r\n".'Content-type: text/html; charset=utf-8' . "\r\n";
            }

            if( $this->_to != '' ){   $this->cabecera = $this->cabecera.'To: '. $this->_to . "\r\n"; }
            if( $this->_from != '' ){ $this->cabecera = $this->cabecera.'From: '. $this->_from . "\r\n"; }

            return true;
        }
    }

    /*
        // Varios destinatarios
        $para  = 'aidan@example.com' . ', '; // atención a la coma
        $para .= 'wez@example.com';

        // título
        $título = 'Recordatorio de cumpleaños para Agosto';

        // mensaje
        $mensaje = '
            <html>
            <head>
              <title>Recordatorio de cumpleaños para Agosto</title>
            </head>
            <body>
              <p>¡Estos son los cumpleaños para Agosto!</p>
              <table>
                <tr>
                  <th>Quien</th><th>Día</th><th>Mes</th><th>Año</th>
                </tr>
                <tr>
                  <td>Joe</td><td>3</td><td>Agosto</td><td>1970</td>
                </tr>
                <tr>
                  <td>Sally</td><td>17</td><td>Agosto</td><td>1973</td>
                </tr>
              </table>
            </body>
            </html>
            ';

        // Para enviar un correo HTML, debe establecerse la cabecera Content-type
        $cabeceras  = 'MIME-Version: 1.0' . "\r\n";
        $cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        // Cabeceras adicionales
        $cabeceras .= 'To: Mary <mary@example.com>, Kelly <kelly@example.com>' . "\r\n";
        $cabeceras .= 'From: Recordatorio <cumples@example.com>' . "\r\n";
        $cabeceras .= 'Cc: birthdayarchive@example.com' . "\r\n";
        $cabeceras .= 'Bcc: birthdaycheck@example.com' . "\r\n";

        // Enviarlo
        mail($para, $título, $mensaje, $cabeceras);
    */
}
?>
