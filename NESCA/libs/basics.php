<?php

if( !defined('LIB_BASICS') ){

    define('LIB_BASICS','LIB_BASICS v0.9');
    define('BR',"\n#. ");

    // determina si el programa esta ejecutandose en terminal
        function is_terminal(){
            if( isset( $_SERVER['TERM'] ) ){ return true; }
            return false;
        }
    // regresa el numero de argumento por terminal
        function nargs(){ return $_SERVER['argc']; }
    // regresa un arrgelo con los argumentos desde terminal
        function args(){ return $_SERVER['argv']; }
    // ordena los argumentos por funciones y cada funcion con sus datos
        function args_list(){
            $g = $_SERVER['argv'];
            $a = null;
            $base = $g[0];
            $a[ $base ] = null;
            unset( $g[0] );

            $reg = '';
            foreach ($g as $et => $r) {
                if( $r[0] == '-' ){
                    $reg = $r;
                    $a[ $base ][ $reg ] = null;
                }else{
                    $a[ $base ][ $reg ][] = $r;
                }
            }

            return $a;
        }

    function system_user(){
        if( isset( $_SERVER['USER'] ) )
            return $_SERVER['USER'];
        return 'none';
    }

    /* imprime una linea de texto de texto */
    function tt($s=''){
        if($s==''){ tt('null'); }

        echo BR.$s;
        return; 
    }
    /* imprime el contenido de un arreglo */
    function pre($a=null){
        if($a==null){ return tt(); }

        echo BR;
        print_r($a);
        return;
    }
    /* determina la cadena mas larga dentro de un arreglo unidimensional */
    function array_strlen($a=null,$v=0){
        if($a==null){ return 0; }

        $n=0;
        foreach ($a as $et => $r) {
            $nn=strlen($r);
            if($nn>$n) $n=$nn;
        }

        return $n;
    }
    /* obtiene las cabeceras de un arreglo unidimencional */
    function array_cabs($a=null){
        if($a==null){ return null; }

        $b=null;
        foreach ($a as $et => $r) {
            $b[] = $et;
        }

        return $b;
    }
    /* regresa el listado del nombre corto de los meses del año en español */
    function mes_min(){
        return array( 'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic' );
    }

    // detectando longitud de datos 
        function print_table_cols_long($a=null,$max_long=0){
            $b=null;

            /* tamanio de cabecera */
            foreach ($a as $et => $r) {
                foreach ($r as $etr => $rr) {
                    $s="| $etr ";
                    $b[$etr]=strlen($s);

                    if( $max_long ){
                        if( $b[$etr]>$max_long ){
                            $b[$etr] = $max_long;
                        }
                    }
                }
                break;
            }

            /* tamanio de datos */
            foreach ($a as $et => $r) {
                foreach ($r as $etr => $rr) {
                    if( is_array( $rr ) ){ $rr = '--array--'; }

                    $s="| $rr ";
                    $n=strlen($s);
                    if( $n>$b[$etr] ){
                        $b[$etr]=$n;
                    }

                    if( $max_long ){
                        if( $b[$etr]>$max_long ){ $b[$etr] = $max_long; }
                    }
                }
            }

            return $b;
        }
    // balancenado datos
        function print_table_balanced_cabs($a=null,$b=null){

            $c=null;
            foreach ($a as $et => $r) {
                foreach ($r as $etr => $rr) {
                    $c[$etr]=str_pad( "| $etr ", $b[$etr], ' ' );
                }
            }

            return $c;
        }
        function print_table_balanced($a=null,$b=null,$max_long=0){

            foreach ($a as $et => $r) {
                foreach ($r as $etr => $rr) {
                    if( is_array( $rr ) ){ $rr = '--array--'; }

                    $s = "| $rr ";
                    if( $max_long ){
                        if( strlen( $s ) > ( $max_long - 4 ) ){
                            $s = substr($s, 0, ($max_long - 4) )." ...";
                        }
                    }

                    $s = str_pad( $s, $b[$etr], ' ' );
                    $a[$et][$etr]=$s;
                }
            }

            return $a;
        }
    function print_table_line($a=null){

        $s='';
        foreach ($a as $et => $r) {
            $s=$s.str_pad( '+',$r,'=' );
        }
        $s="\n".$s."+";

        return $s;
    }
    function print_table_string($a=null){
        $s='';
        foreach ($a as $et => $r) {
            $b = implode( '',   explode("\r", $r) );
            $b = implode(' / ', explode("\n", $b) );

            $s=$s.$b;
        }
        $s="\n".$s.'|';

        return $s;
    }
    /* convierte un arreglo bidimencional en una tabla en formato texto */
    function print_table($a=null,$max_log=0){
        if($a==null){ return ''; }

        $s='';

        /* string to arreglo bidimencional */
        if( is_string($a) ){ $a = array( 'default' => array( 'default' => $a ) ); }

        if( is_array($a) ){
            /* array unidimencional to array bidimencional */
            foreach ($a as $et => $r) {
                if( !is_array($r) ){
                    $a=array( 'default' => $a );
                    break;
                }
            }

            // determinando longitud de datos
            $d=print_table_cols_long( $a, $max_log );
            // balanceando datos
            $b=print_table_balanced( $a, $d, $max_log );
            $c=print_table_balanced_cabs( $a, $d );

            $line = print_table_line($d);

            $s = $s.$line.print_table_string($c).$line;

            foreach ($b as $et => $r) {
                $s = $s.print_table_string($r);
            }

            $s = $s.$line.print_table_string($c).$line."\n";
        }

        return $s;
    }
    // regresa un string con una tabla horizontal
        function print_table_horizontal( $a=null ){
            if( $a==null ){ return ''; }
            return print_table_h( $a );
        }
        function print_table_h( $a=null ){
            if( $a==null ){ return ''; }

            // obteniendo longitudes de cadena
                $s = '';
                $c1 = 0;
                $c2 = 0;
                foreach ($a as $et => $r) {
                    foreach ($r as $etr => $rr) {
                        $v1 = strlen( $etr );
                        $v2 = strlen( $rr );
                        if( $v1>$c1 ){ $c1=$v1; }
                        if( $v2>$c2 ){ $c2=$v2; }
                    }
                }

                $v1 = strlen( 'index' );
                $v2 = strlen( 'value' );
                if( $v1>$c1 ){ $c1=$v1; }
                if( $v2>$c2 ){ $c2=$v2; }

            // obteniendo linea de separacion
                $line = "\n+".str_pad( '', ($c1+2), "=" ).'+'.str_pad( '', ($c2+2), "=" ).'+';

            // obteniendo cabecera
                $cab = "\n"."| ".str_pad( 'index', ( $c1 ), " " )." | ".str_pad( 'value', ( $c2 ), " " )." |";

            // generando listado
                $s = '';
                $s = $line.$cab.$line;

                foreach ($a as $et => $r) {
                    foreach ($r as $etr => $rr) {
                        $etr = str_pad( $etr, ( $c1 ), " " );
                        $rr  = str_pad( $rr,  ( $c2 ), " " );
                        $s = $s."\n"."| $etr | $rr |";
                    }
                    $s = $s.$line;
                }

                $s = $s.$cab.$line;

            return $s;
        }
    /* transpone los datos de un arreglo, convirtiendo las columnas en renglones y los renglones en columnas */
        function transponer($a=null){
            if($a==null){ return null; }

            $b=null;
            foreach ($a as $et => $r) {
                foreach ($r as $etr => $rr) {
                    $b[ $etr ][ $et ] = $rr;
                }
            }

            return $b;
        }
    /* produce la salida de una cadena a un archivo */
    function output_file($dir='',$nombarch='',$dat='', $modo='a'){
        // v0.2
        if( $nombarch=='' ){ tt('sin nombre de archivo'); return false; }
        if( $nombarch=='.in1' ){ tt('sin nombre de archivo'); return false; }

        $ruta = $dir.$nombarch;

        /*
        if( file_exists( $ruta ) ){
            tt('archivo ['.$ruta.'] ya existe');
            return false;
        }*/

        if($archivo = fopen($ruta, $modo)){
            fwrite($archivo, $dat);
            fclose($archivo);
            //tt($ruta.' [ok]');
            return true;
        }

        tt('imposible crear archivo ['.$ruta.']');
        return false;
    }
    /* proporciona un arreglo con el listado de los archivos de un directorio, 
        filtro es para determinar si es un directorio, archivo, o link */
    function list_files_array($dir='.',$filtro=''){
        $l = null;
        $dln = strlen($dir);

        if (is_dir($dir)) {
            if($d = opendir($dir)){
                if( $dir[$dln-1]!='/' ){ $dir = $dir.'/'; }

                $add=0;
                while( ($f = readdir($d)) !== false ){
                    $t = filetype( $dir.$f);
                    if($filtro!=''){
                        $add = 0;
                        if($t==$filtro){
                            $add = 1;
                        }
                    }else{ $add=1; }

                    if( $add ){
                        $l[] = array( 'name'=>$f, 'type'=>$t );
                    }
                }
            }
        }

        return $l;
    }

    /* filtra un arreglo */
    function array_filter_cols( $data=null,$cabs=null ){
        if( $data==null ){ return null; }
        if( $cabs==null ){ return null; }

        $c = null;
        foreach ($data as $et => $r) {
            foreach ($cabs as $etr => $rr) {
                $c[$et][ $rr ] = $r[ $rr ]; 
            }
        }

        return $c;
    }

    /* imprime un arreglo */
    function print_array( $data=null,$cabs=null ){
        $a = array_filter_cols( $data,$cabs );
        if( $a==null ){ return 'null'; }

        return print_table( $a );
    }
}

?>