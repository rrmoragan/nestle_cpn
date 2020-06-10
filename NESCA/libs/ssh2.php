<?php

include('basics.php');
include('ssh2.define.php');

class serverSSH{
    private $ssh_host = ''; 
    private $ssh_port = 22; 
    private $ssh_server_fp = '';    // SSH Server Fingerprint 
    private $ssh_auth_user = ''; 
    private $ssh_auth_pass = '';    // SSH Private Key Passphrase (null == no passphrase) 
 
    private $ssh_auth_pub = '';     // SSH Public Key File
    private $ssh_auth_priv = '';    // SSH Private Key File 

    private $connection = null;             // SSH Connection 
    private $connection_sftp = null;        // SSH Connection sftp
    private $login = null;                  // SSH Connection 
    private $connection_status = 0;         // SSH Connection status
    private $connection_sftp_status = 0;    // SSH Connection status
    public  $message = '';                  // SSH system message
    public  $debug = false;                 // opcion debug

    /* proporciona los datos de conexion */
    public function connexion_data($d=null){
        if( $this->debug ){ tt('cargando configuracion'); }

        if($d==null){
            $this->connection_status = false;
            if( $this->debug ){ tt('sin datos de configuracion'); }
            return false;
        }

        $error = 0;
        if( !isset( $d['host'] ) ){         $error++; }
        if( !isset( $d['user'] ) ){         $error++; }
        if( !isset( $d['pass'] ) ){         $error++; }
        if( !isset( $d['server_ftp'] ) ){   $d['server_ftp'] = ''; }
        if( !isset( $d['port'] ) ){         $d['port'] = 22; }
        if( !isset( $d['public_key'] ) ){   $d['public_key'] = ''; }
        if( !isset( $d['private_key'] ) ){  $d['private_key'] = ''; }

        if($error){
            $this->connection_status = false;
            if( $this->debug ){ tt('faltan datos'); }
            return false;
        }

        $this->ssh_host         = $d['host'];
        $this->ssh_port         = $d['port'];
        $this->ssh_auth_user    = $d['user'];
        $this->ssh_auth_pass    = $d['pass'];

        $this->ssh_server_fp    = $d['server_ftp'];
        $this->ssh_auth_pub     = $d['public_key'];
        $this->ssh_auth_priv    = $d['private_key'];

        $this->connection_status = true;
        if( $this->debug ){
            tt('... user ==> '.$this->ssh_auth_user);
            tt('... host ==> '.$this->ssh_host);
            tt('... port ==> '.$this->ssh_port);
        }
        return false;
    }
    /* inicia una conexion al servidor */
    public function connexion_start(){
        if( $this->debug ){ tt('iniciando conexion con el servidor'); }
        $this->connection_status = 0;

        if( !defined('SSH2_DEFAULT_TERMINAL') ){ tt('ERROR ==> no se encuentran instaladas las librerias ssh2 para php.'); return false; }

        /* conectando con el servidor */
        $cnx = ssh2_connect( $this->ssh_host, $this->ssh_port );

        /* si hay error salir */
        if ( !$cnx ){
            $this->message = "There was a problem connecting to the Host.";
            if( $this->debug ){ tt($this->message); }
            return 0;
        }

        $this->connection_status = 1;
        $this->connection = $cnx;

        /* haciendo login */
        $login = ssh2_auth_password( $cnx, $this->ssh_auth_user, $this->ssh_auth_pass );

        /* si hay error en login salir */
        if ( !$login ){
            $this->message = "Invalid sFTP credentials.";
            if( $this->debug ){ tt($this->message); }

            $this->connection_close();
            return false;
        }

        $this->login = $login;
        $this->connection_status = 2;
        $this->connection = $cnx;
        if( $this->debug ){ tt('conexion iniciada'); }

        return true;
    }
    public function connection_close(){
        $this->login = null;
        $this->connection = null;
        $this->connection_status = 0;

        if( $this->debug ){ tt('cerrando conexion'); }

        return true;
    }
    /* inicia la conexion por sftp */
    public function connexion_sftp_start(){
        if( $this->debug ){ tt('iniciando conexion sftp'); }
        if( $this->connection_status == false ){
            if( $this->debug ){ tt('sin conexion'); }
            return false;
        }

        /* iniciando conexion sftp */
        $this->connection_sftp = @ssh2_sftp($this->connection);
        if( !$this->connection_sftp ){
            $this->message = "Could not initialize SFTP subsystem.";
            $this->connection_sftp_status = 0;
            if( $this->debug ){ tt($this->message); }
            return false;
        }

        if( $this->debug ){ tt('ssh2_sftp ==> '.$this->connection_sftp); }

        $this->connection_sftp_status = 1;
        if( $this->debug ){ tt('conexion sftp iniciada'); }

        return true;
    }
    /* termina la conexion por sftp */
    public function connexion_sftp_close(){
        $this->connection_sftp_status == 0;
        $this->connection_sftp = null;

        if( $this->debug ){ tt('conexion sftp cerrada'); }

        return true;
    }
    /* sube un archivo por sftp
        ssh_upload_file( string local_dir, local_file, remote_dir );
    */
    public function ssh_upload_file($ld='',$lf='',$rd='/'){
        if( $this->debug ){ tt('subiendo archivo '.$ld.$lf.' to '.$rd); }
        if( $this->connection_status == 0 ){
            if( $this->debug ){ tt('no hay conexion'); }
            return false;
        }
        if($lf==''){
            if( $this->debug ){ tt('sin archivo origen'); }
            return false;
        }

        if( $this->connection_sftp_status == 0 ){ return false; }

        $local  = $ld . $lf;
        $remote = $rd . $lf;
        $content = file_get_contents( $local );

        $sftp = $this->connection_sftp;
        $s = "ssh2.sftp://$sftp"."/$remote";
        if( $this->debug ){ tt('sftp url ==> '.$s."\n"); }

        $stream = fopen($s, 'w');
        if ( !$stream ){
            $this->connexion_sftp_close();
            $this->message = "Could not open remote server to write.";
            if( $this->debug ){ tt($this->message); }
            return FALSE;
        }

        if ( fwrite($stream, $content) ) {
            fclose($stream);
        }

        return true;
    }

    public function debug_on(){  $this->debug = true; }
    public function debug_off(){ $this->debug = false; }
}


$ssh = new serverSSH();
$ssh->connexion_data( $ssh_conexion_credentials );
$ssh->connexion_start();
$ssh->connection_close();

?>