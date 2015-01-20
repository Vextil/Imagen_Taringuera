<?php

# Usuario a cargar, modificar libremente
/////////////////////////////////////////
$user = "USERNAME";
$cookies = "CANTIDAD DE GALLETAS"; //Conseguir por ejemplo desde una base de datos
////////////////////////////////////////

# Incluir la API de Taringa! para usuarios (No oficial)
require('../common/taringa_user_api.php');

# Decirle al explorador que estamos haciendo una imagen
header("Content-type: image/png");

# Imagename en minuscula para mantener consistencia
$imagename = strtolower($user);

# Cachear la imagen para que cargue mas rapido
$cachefile = 'cache/' . md5($imagename);
$cachetime = 30 * 60; # 30 minutos
# Cargar desde cache si es mas "joven" que $cachetime
if (file_exists($cachefile) && (time() - $cachetime < filemtime($cachefile))) {
	$expire = date("Y-m-d H:i:s", strtotime ("+1 hour"));
	header("Expires: ".$expire);
	$cache = imagecreatefrompng($cachefile);
	imagealphablending( $cache, true );
	imagesavealpha( $cache, true );
	imagepng($cache);
	imagedestroy($cache);
	echo "<!-- Cached ".date('jS F Y H:i', filemtime($cachefile))." -->";
	exit;
}
ob_start(); # Iniciar el output

# Conseguir datos de la API
$data = new taringa_user_api;
$data->process($user);

# Cambiar tamaño del avatar y capitalizar username
$avatar = str_replace('120_', '48_', $data->avatar);
$user = $data->user;

# Transformar el fondo a imagen php
$im = imagecreatefrompng('imagenes/background.png');
$overlay = imagecreatefrompng('imagenes/lu1sh.png');
$avatar = imagecreatefromjpeg($avatar);
imagecopyresampled($im, $avatar, 9, 9, 0, 0, 40, 40, 48, 48);
imagecopy($im, $overlay, 0, 0, 0, 0, 675, 132);

# Definir colores de fuentes como variables
$usr = imagecolorallocate($im, 3, 48, 77);
$usrshadow = imagecolorallocate($im, 7, 116, 188);
$white = imagecolorallocate($im, 255, 255, 255); # White
$grey = imagecolorallocate($im, 100, 100, 100); # Grey

# Procesar rangos
$original = Array('Desarrollador','Moderador');
$replacements = Array('admin','mod');
$rank = str_replace($original,$replacements,$data->rank);
$ranks = array(
	'Amateur' => 'imagenes/4MIDF.png', 
	'Aprendiz' => 'imagenes/hrCY3.png', 
	'Avanzado' => 'imagenes/kkpqA.png', 
	'admin' => 'imagenes/ELOdC.png', 
	'Diamond' => 'imagenes/NiBsW.png', 
	'Elite' => 'imagenes/czRKU.png', 
	'Experto' => 'imagenes/iAWw9.png', 
	'Flamer' => 'imagenes/ZDSsK.png', 
	'Gold' => 'imagenes/tQhAi.png', 
	'Inexperto' => 'imagenes/fyrjT.png', 
	'Iniciado' => 'imagenes/2jnVN.png', 
	'mod' => 'imagenes/U58H3.png', 
	'Oficial' => 'imagenes/ukXAR.png', 
	'Platinum' => 'imagenes/mPWcR.png', 
	'Regular' => 'imagenes/gqS9s.png', 
	'Silver' => 'imagenes/hOson.png', 
	'Troll' => 'imagenes/0JlnX.png',
	'Músico' => 'imagenes/1XIA2.png'
);

# Distintos tamaños de fuente
$usrsize = "34";
$msgsize = "10";
$number = "18";

# Directorio de fuentes
$bebas = 'fuentes/bebas.ttf';
$helv = 'fuentes/helvetica.ttf';

# Funcion para cortar un string si es mas largo de lo que se necesita
function substrwords($text,$maxchar){
	if (strlen($text) > $maxchar) { 
		$words = explode(" ", $text); 
		$output = ''; 
		$i = 0; 
		while (true) { 
			$length = (strlen($output) + strlen($words[$i]));
			if ($length > $maxchar) { 
				break; 
			} else { 
				$output = $output . " " . $words[$i]; 
				++$i; 
			} 
		}
	} else { 
		$output = $text;
	}
	 return $output;
}

# Cortar el mensaje personal si es muy largo
$message = substrwords($data['message'],65);
if (strlen($data['message']) > 65) {
	$message = $message.'...';
}

# Mostrar username
imagettftext ($im, $usrsize, 0, 58, 46, $usrshadow, $bebas, $data->user);
imagettftext ($im, $usrsize, 0, 58, 45, $usr, $bebas, $data->user);

# Mostrar mensaje personal
imagettftext ($im, $msgsize, 0, 48, 120, $white, $helv, $message);
imagettftext ($im, $msgsize, 0, 48, 119, $grey, $helv, $message);

# Mostrar puntos
imagettftext ($im, $number, 0, 7, 79, $usr, $bebas, $data->score);

# Mostrar posts
imagettftext ($im, $number, 0, 460, 79, $usr, $bebas, $data->posts);

# Mostrar seguidores
imagettftext ($im, $number, 0, 236, 79, $usr, $bebas, $data->followers);

# Mostrar karma
if ($data->karma == null){$karma = 'No usa';}else{$karma = $data->karma;}
imagettftext ($im, $number, 0, 121, 79, $usr, $bebas, $karma);

# Mostrar rango
imagettftext ($im, $number, 0, 347, 79, $usr, $bebas, $rank);
$rankimg = imagecreatefrompng($ranks[$rank]);
imagecopy($im, $rankimg, 431, 69, 0, 0, 16, 16);

# Mostrar galletas
imagettftext ($im, $number, 0, 570, 79, $usr, $bebas, $cookies);

# Finalizar imagen
imagepng($im); 
imagedestroy($im);

# Crear archivo de cache y guardarlo en carpeta
$fp = fopen($cachefile, 'w'); # Abrir el archivo de cache para escritura
fwrite($fp, ob_get_contents()); # Guardar el contenido del output a archivo
fclose($fp); # Cerrar el archivo
ob_end_flush(); # Enviar el resultado al explorador

?>