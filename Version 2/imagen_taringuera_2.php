<?php

# Usuario a cargar, modificar libremente
////////////////////////////////////////
$user = "USERNAME";
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

# Posts o Post
if ($data->posts == "1") {
	$post = "Post";
} else {
    $post = "Posts"; 
}
# Seguidor o Seguidores
if ($data->seguidores == "1") {
	$seguidor = "Seguidor";
} else {
    $seguidor = "Seguidores"; 
}

# Transformar el fondo a imagen php
$im = imagecreatefrompng("imagenes/background.png");
imagealphablending( $im, true );
imagesavealpha( $im, true );

# Overlay para el avatar (bordes redondos)
$avatar_overlay = imagecreatefrompng("imagenes/avatar_overlay.png");

# Definir colores de fuentes como variables
$colorA = imagecolorallocate($im, 0, 47, 68); //Blue shadow
$colorB = imagecolorallocate($im, 0, 86, 125); //Dark blue
$colorC = imagecolorallocate($im, 255, 255, 255); //White
$colorD = imagecolorallocate($im, 153, 153, 153); //Grey
$colorE = imagecolorallocate($im, 66, 66, 66); //Dark grey
$colorF = imagecolorallocate($im, 0, 43, 58); //Darker blue
$colorG = imagecolorallocate($im, 0, 114, 160); //Belevel blue

# Distintos tamaños de fuente
$usrsize = "29";
$msgsize = "12";
$thrdsize = "23";
$fthsize = "10";

# Directorio de fuentes
$fontA = "fuentes/fontA.otf";
$fontB = "fuentes/fontB.otf";
$fontC = "fuentes/fontA.otf";

# Calcular la distancia que los puntos deben tener del lado izquierdo
$scrdim = imagettfbbox ($usrsize, 0, $fontC , $data->puntos);
$scrwidth = abs($scrdim[4] - $scrdim[0]);
$scrx = imagesx($im) - $scrwidth - "35";
$ptsx = imagesx($im) - $scrwidth - "77";
# Calcular la distancia que el pais debe tener del lado izquierdo
$ctrydim = imagettfbbox ($fthsize, 0, $fontB , $data->pais);
$ctrywidth = abs($ctrydim[4] - $ctrydim[0]);
$ctryx = imagesx($im) - $ctrywidth - "82";
# Calcular el tamaño del numero de puntos
$postdim = imagettfbbox ($thrdsize, 0, $fontC , $data->posts);
$postwidth = abs($postdim[4] - $postdim[0]);
# Calcular el tamaño del numero de seguidores
$foldim = imagettfbbox ($thrdsize, 0, $fontC , $data->seguidores);
$folwidth = abs($foldim[4] - $foldim[0]);
# Calcular el tamaño del numero de karma
$kardim = imagettfbbox ($thrdsize, 0, $fontC , $data->karma);
$karwidth = abs($kardim[4] - $kardim[0]);
# Calcular la distancia que el rango debe tener del lado izquierdo
$usrdim = imagettfbbox ($usrsize, 0, $fontA , $data->usuario);
$usrwidth = abs($usrdim[4] - $usrdim[0]);

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
$message = substrwords($data->mensaje, 65);
if (strlen($data->mensaje) > 65) {
	$message = $message.'...';
}

#Mostrar username
imagettftext ($im, $usrsize, 0, 62, 55, $colorA, $fontA, $data->usuario);
imagettftext ($im, $usrsize, 0, 62, 54, $colorC, $fontA, $data->usuario);

# Mostrar rango
if (file_exists('rango/' .$data->rango. '.png')) {
	$rango = 'rango/' .$data->rango. '.png';
	$rnk = imagecreatefrompng($rango);
	imagecopy ($im, $rnk, 615, 115, 0, 0, 16, 16);
}

# Mostrar mensaje personal
imagettftext ($im, $msgsize, 0, 40, 129, $colorC, $fontA, $message);
imagettftext ($im, $msgsize, 0, 40, 128, $colorD, $fontA, $message);

# Mostrar puntos
imagettftext ($im, $usrsize, 0, $scrx, 54, -$colorA, $fontC, $data->puntos);
imagettftext ($im, $usrsize, 0, $scrx, 53, $colorC, $fontC, $data->puntos);
# Mostrar palabra "punto/s"
imagettftext ($im, $fthsize, 0, $ptsx, 45, $colorG, $fontB, 'Puntos');
imagettftext ($im, $fthsize, 0, $ptsx, 44, $colorF, $fontB, 'Puntos');

# Mostrar cantidad de posts
imagettftext ($im, $thrdsize, 0, 46, 96, $colorC, $fontC, $data->posts);
imagettftext ($im, $thrdsize, 0, 46, 95, $colorB, $fontC, $data->posts);
# Mostrar la palabra "post/s"
imagettftext ($im, $fthsize, 0, $postwidth + 55, 90, $colorC, $fontB, $post);
imagettftext ($im, $fthsize, 0, $postwidth + 55, 89, $colorE, $fontB, $post);

# Agregar separador a la imagen
$sep = imagecreatefrompng('imagenes/separador.png');
imagecopy ($im, $sep, $postwidth + 95, 68, 0, 0, 2, 36);

# Mostrar seguidores
imagettftext ($im, $thrdsize, 0, $postwidth + 110, 96, $colorC, $fontC, $data->seguidores);
imagettftext ($im, $thrdsize, 0, $postwidth + 110, 95, $colorB, $fontC, $data->seguidores);
# Mostrar la palabra "seguidor/es"
imagettftext ($im, $fthsize, 0, $postwidth + $folwidth + 118, 90, $colorC, $fontB, $seguidor);
imagettftext ($im, $fthsize, 0, $postwidth + $folwidth + 118, 89, $colorE, $fontB, $seguidor);

if ($data->karma != null) {
	# Agregar separador a la imagen
	imagecopy ($im, $sep, $postwidth + $folwidth + 188, 68, 0, 0, 2, 36);
	# Mostrar karma
	imagettftext ($im, $thrdsize, 0, $postwidth + $folwidth + 200, 96, $colorC, $fontC, $data->karma);
	imagettftext ($im, $thrdsize, 0, $postwidth + $folwidth + 200, 95, $colorB, $fontC, $data->karma);
	# Mostrar la palabra "karma"
	imagettftext ($im, $fthsize, 0, $postwidth + $folwidth + $karwidth + 208, 90, $colorC, $fontB, 'Karma');
	imagettftext ($im, $fthsize, 0, $postwidth + $folwidth + $karwidth + 208, 89, $colorE, $fontB, 'Karma');
}

# Mostrar pais
imagettftext ($im, $fthsize, 0, $ctryx, 90, $colorC, $fontB, $data->pais);
imagettftext ($im, $fthsize, 0, $ctryx, 89, $colorE, $fontB, $data->pais);
# Mostrar la bandera del pais
if (file_exists("paises/".$data->pais.".png")) {
	$countryflag = imagecreatefrompng("paises/".$data->pais.".png");
	imagecopy ($im, $countryflag, 600, 69, 0, 0, 32, 32);
}

# Mostrar avatar
$avatar = str_replace('120_', '32_', $data->avatar);
$avatar = imagecreatefromjpeg($avatar);
imagecopy($im, $avatar, 25, 25, 0, 0, 32, 32);
imagecopy($im, $avatar_overlay, 25, 25, 0, 0, 32, 32);

# Finalizar imagen
imagepng($im);
imagedestroy($im);

# Crear archivo de cache y guardarlo en carpeta
$fp = fopen($cachefile, 'w'); # Abrir el archivo de cache para escritura
fwrite($fp, ob_get_contents()); # Guardar el contenido del output a archivo
fclose($fp); # Cerrar el archivo
ob_end_flush(); # Enviar el resultado al explorador

?>
