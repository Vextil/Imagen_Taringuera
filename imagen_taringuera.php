<?php

# Usuario a cargar, modificar libremente
$user = "USERNAME";

# Decirle al explorador que estamos haciendo una imagen
header("Content-type: image/png");

# Imagename en minuscula para mantener consistencia
$imagename = strtolower($user);

# Cachear la imagen para que cargue mas rapido
$cachefile = 'cache/' . md5($imagename);
$cachetime = 60 * 60; # 1 hora
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

# Transformar el fondo a imagen php
$im = imagecreatefrompng("background.png");
imagealphablending( $im, true );
imagesavealpha( $im, true );

# Definir fuentes como variables
$bold = 'fonts/bold.otf';
$regular = 'fonts/regular.otf';
$light = 'fonts/light.otf';

# Conseguir datos de la API de Vextil (Se puede usar API_Taringa (taringa_user))
$url = "http://api.vextil.com/taringa/user/{$user}";
$content = file_get_contents($url);
$data = json_decode($content);

# Transformar el avatar a imagen php
$avtr = imagecreatefromjpeg($data->avatar);

# Transformar el pais a imagen php
$countries = 'countries/' . $data->pais . '.png';
$ctry = imagecreatefrompng($countries);

# Definir distintos tamaños de fuente
$usrsize = "30";
$msgsize = "11";

# Calcular la distancia que el username tiene que tener del lado izquierdo
$usrdim = imagettfbbox ($usrsize, 0, $bold , $data->usuario);
$usrwidth = abs($usrdim[4] - $usrdim[0]);
$usrx = imagesx($im) - $usrwidth - "455";

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
$message = substrwords($data->mensaje, 45);
if (strlen($data->mensaje) > 45) { $message = $message.'...'; $msgremove = '443'; } 
else { $msgremove = '450'; }
$message = str_replace('&quot;', '"', $message);

# Calcular la distancia que el mensaje tiene que tener del lado izquierdo
$msgdim = imagettfbbox ($msgsize, 0, $regular , $message);
$msgwidth = abs($msgdim[4] - $usrdim[0]);
$msgx = imagesx($im) - $msgwidth - $msgremove;

# Definir distintos colores de texto
$white = imagecolorallocate($im, 255, 255, 255);
$black = imagecolorallocate($im, 000, 000, 000);
$darkblue = imagecolorallocate($im, 2, 61, 78);
$lightblue = imagecolorallocate($im, 67, 132, 165);
$lighterblue = imagecolorallocate($im, 67, 176, 197);

# Mostrar username
imagettftext ($im, $usrsize, 0, $usrx, 80, $black, $bold, $data->usuario);
imagettftext ($im, $usrsize, 0, $usrx, 79, $white, $bold, $data->usuario);

# Mostrar mensaje personal
imagettftext ($im, $msgsize, 0, $msgx, 110, $lightblue, $regular, $message);
imagettftext ($im, $msgsize, 0, $msgx, 109, $darkblue, $regular, $message);

# Mostrar puntos
imagettftext ($im, 30, 0, 440, 66, $lighterblue, $bold, $data->puntos);
imagettftext ($im, 30, 0, 440, 65, $darkblue, $bold, $data->puntos);

# Mostrar posts
imagettftext ($im, 30, 0, 440, 134, $lightblue, $bold, $data->posts);
imagettftext ($im, 30, 0, 440, 133, $darkblue, $bold, $data->posts);

# Mostrar avatar
imagecopy ($im, $avtr, 314, 14, 0, 0, 120, 120);

# Mostrar bandera de pais
imagecopy ($im, $ctry, 634, 28, 0, 0, 95, 95);

imagepng($im);
imagedestroy($im);

# Crear archivo de cache y guardarlo en carpeta
$fp = fopen($cachefile, 'w'); # Abrir el archivo de cache para escritura
fwrite($fp, ob_get_contents()); # Guardar el contenido del output a archivo
fclose($fp); # Cerrar el archivo
ob_end_flush(); # Enviar el resultado al explorador