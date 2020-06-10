/*******************************************************************************
 * @category   Entrepids
 * @package    Entrepids_Maintenance
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 *
 * Don't copy this file to your Varnish configuration file
 * Please, add the required code in each specific subroutine
 * The purpose of this file is only proporcionate the code you would need add 
 * to your varnish configuration file.
 * Feel free of delete this file in any moment.
 *
 * No copies este archivo al contenido tu archivo de configuración de Varnish
 * Por favor, copia cada fragemento de código en su correspondiente subrutina
 * El propósito de éste archivo es solo facilitar el código que deberás agregar
 * a tu archivo de configuración de Varnish.
 * Sientete libre de eliminar éste archivo en cualquier momento.
 ******************************************************************************/

[...]
sub vcl_recv{
    [...]
    if(req.url ~ "entrepids="){
	return(pipe);
    }
    if(req.http.Cookie ~ "\bentrepidsbypass="){
        set req.http.X-Entrepids-Maintenance = regsub(
        req.http.Cookie, ".*\bentrepidsbypass=([^;]*).*", "\1");
    }
    [...]
} 
[...]
sub vcl_hash {
    [...]
    if(req.http.X-Entrepids-Maintenance){
        hash_data(";entrepidsbypass=" + req.http.X-Entrepids-Maintenance);
    }
    [...]
}
[...]
sub vcl_deliver {
    [...]
    if (true || client.ip ~ debug_acl) {
        [...]
        set resp.http.X-Entrepids-Maintenance = req.http.X-Entrepids-Maintenance;
    }
    [...]
}