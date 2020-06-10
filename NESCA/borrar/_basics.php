<?php

if( !defined('LIB_BASICS') ){

    define('LIB_BASICS','LIB_BASICS v0.7.4');
    define('BR',"\n#. ");

    function system_user(){
        return $_SERVER['USER'];
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
    function print_table_cols_long($a=null){
        $b=null;

        /* tamanio de cabecera */
        foreach ($a as $et => $r) {
            foreach ($r as $etr => $rr) {
                $s="| $etr ";
                $b[$etr]=strlen($s);
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
            }
        }

        return $b;
    }
    function print_table_balanced_cabs($a=null,$b=null){

        $c=null;
        foreach ($a as $et => $r) {
            foreach ($r as $etr => $rr) {
                $c[$etr]=str_pad( "| $etr ", $b[$etr], ' ' );
            }
        }

        return $c;
    }
    function print_table_balanced($a=null,$b=null){

        foreach ($a as $et => $r) {
            foreach ($r as $etr => $rr) {
                if( is_array( $rr ) ){ $rr = '--array--'; }
                $a[$et][$etr]=str_pad( "| $rr ", $b[$etr], ' ' );
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
            $s=$s.$r;
        }
        $s="\n".$s.'|';

        return $s;
    }
    /* convierte un arreglo bidimencional en una tabla en formato texto */
    function print_table($a=null){
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

            $d=print_table_cols_long($a);
            $b=print_table_balanced( $a, $d );
            $c=print_table_balanced_cabs( $a, $d );

            $s = $s.print_table_line($d);
            $s = $s.print_table_string($c);
            $s = $s.print_table_line($d);
            foreach ($b as $et => $r) {
                $s = $s.print_table_string($r);
            }
            $s = $s.print_table_line($d);
            $s = $s."\n";
        }

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

        if( file_exists( $ruta ) ){
            tt('archivo ['.$ruta.'] ya existe');
            return false;
        }

        if($archivo = fopen($ruta, $modo)){
            fwrite($archivo, $dat);
            fclose($archivo);
            tt($ruta.' [ok]');
            return true;
        }

        tt('imposible crear archivo ['.$ruta.']');
        return false;
    }
    /* proporciona un arreglo con el listado de los archivos de un directorio, filtro es para determinar si es un directorio, archivo, o link */
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
}

?>