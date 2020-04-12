<?php 

//
//	This library use part of other functions that I recolect from internet, if you find them, send the link to add them as contribution...
//
//	This library was created by 
//  Jesus Alberto Acosta Heredia
//	email: info@xmediasoftware.com
//	
//	Contains these functions:
//		IfFileExistsRename()
//		UploadPictureRealSize()
//		getHeightFromPicture()
//		getWidthFromPicture()
//		UploadPictureTumbnail()
//
//	version: 1.0

$thumb_max_width_default = 100;
$msg_error_file_not_found = "Error: Please select a file to be uploaded!<br>";
$msg_error_file_format_worng = "incorrect File Type or not permitted, only these files are allowed<br>";
$msg_library_not_found = "Error: Please check that you have the GD library version 2+<br>";

// IfFileExistsRename ($file_name, $new_location)
//
//		If $file_name exists in $new_location it returns a new name so you can upload a file and do not overwrite another file with same name
//

function IfFileExistsRename($file_name, $new_location){
	if(substr($new_location,-1,1) != "/"){
		$new_location += $new_location . "/";
	}

	if(file_exists($new_location . $file_name)){
		$cont = 0;
		$original_fila_name = $file_name;
		do{
			$file_parts = explode(".",$original_fila_name,2);	
			$file_name = $file_parts[0] . $cont . "." . $file_parts[1];
			$cont++;
		}while(file_exists($new_location . $file_name));
	}
	
	$file_name  = str_replace(" ","_",strtolower($file_name));

	return $file_name;
}

//
//  UploadPictureRealSize($posted_picture, $new_location, $overwrite)
//
//  you cant upload $posted_picture or any kind of file as is, using POST variable to $new_location, if $overwrite is set to true, 
//  it will find the file with this name and overwirte it, else IfFileExistsRename function will be used
//  to verify if this name exists on $new_location and returns a new name
//

function UploadPictureRealSize($posted_picture, $new_location, $overwrite){
	$file_name ="";

	if(substr($new_location,-1,1) != "/"){
		$new_location += $new_location . "/";
	}

	if(!$overwrite){
	//echo "Renombrando";
		$file_name = IfFileExistsRename($posted_picture['name'], $new_location);
	}else{
		$file_name = $posted_picture['name'];
	}
	
	// checamos que haya sido enviado por post
	
	if(is_uploaded_file($posted_picture['tmp_name'])){
		// subimos el archivo con el nombre nuevo
		if(!copy($posted_picture['tmp_name'], $new_location . $file_name)){
		 	echo "Imposible copiar el archivo " . $posted_picture['tmp_name'] . " al servidor";
		}else{
			echo "<!-- Archivo Copiado -->";
		}
	}else{
		echo "Imposible detectar la fuente de el archivo";
	}
	
	return $file_name;

}


//   getHeightFromPicture($file_name_location)
//
//   simply, returns the image height in picels
//
//


function getHeightFromPicture($file_name_location){
	if(!file_exists($file_name_location)){
		return false;	
	}
	
	$size = getimagesize($file_name_location);
	
	return $size[1];
	
}


//   getWidthFromPicture($file_name_location)
//
//   simply, returns the image width in picels
//
//


function getWidthFromPicture($file_name_location){
	if(!file_exists($file_name_location)){
		return false;	
	}
	
	$size = getimagesize($file_name_location);
	
	return $size[0];
	
}

/*

Clase FileInfoTool

gracias a ccbsschucko@gmail.com 



*/


class FileInfoTool {

    /**
    * @var str => $file = caminho para o arquivo (ABSOLUTO OU RELATIVO)
    * @var arr => $file_info = array contendo as informações obtidas do arquivo informado
    */
    private $file;
    private $file_info;

    /**
    * @param str => $file = caminho para o arquivo (ABSOLUTO OU RELATIVO)
    */
    public function get_file(string $file){
        clearstatcache();
        $file = str_replace(array('/', '\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), $file);
        if(!is_file($file) && !is_executable($file) && !is_readable($file)){
            throw new \Exception('O arquivo informado não foi encontrado!');
        }
        $this->file = $file;
        $this->set_file_info($this->file);
        return $this;
    }

    /**
    * @param str => $index = se for informado um indice é retornada uma informação específica do arquivo
    */
    public function get_info($index = ''){
        if($this->get_file_is_called()){
            if($index === ''){
                return $this->file_info;
            }
            if($index != ''){
                if(!array_key_exists($index, $this->file_info)){
                    throw new \Exception('A informação requisitada não foi encontrada!');                   
                }
                return $this->file_info;
            }
        }
    }

    /**
    * @todo verifica se o método get_file() foi utilizado para informar o caminho do arquivo
    */
    private function get_file_is_called(){
        if(!$this->file){
            throw new \Exception('Nenhum arquivo foi fornecido para análise. Utilize o método get_file() para isso!');
            return false;
        }
        return true;
    }

    /**
    * @todo preencher a array com as infos do arquivo
    */
    private function set_file_info(){
        $this->file_info = array();
        $pathinfo = pathinfo($this->file);
        $stat = stat($this->file);
        $this->file_info['realpath'] = realpath($this->file);
        $this->file_info['dirname'] = $pathinfo['dirname'];
        $this->file_info['basename'] = $pathinfo['basename'];
        $this->file_info['filename'] = $pathinfo['filename'];
        $this->file_info['extension'] = $pathinfo['extension'];
        $this->file_info['mime'] = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->file);
        $this->file_info['encoding'] = finfo_file(finfo_open(FILEINFO_MIME_ENCODING), $this->file);
        $this->file_info['size'] = $stat[7];
        $this->file_info['size_string'] = $this->format_bytes($stat[7]);
        $this->file_info['atime'] = $stat[8];
        $this->file_info['mtime'] = $stat[9];
        $this->file_info['permission'] = substr(sprintf('%o', fileperms($this->file)), -4);
        $this->file_info['fileowner'] = getenv('USERNAME');
    }

    /**
    * @param int => $size = valor em bytes a ser formatado
    */
    private function format_bytes(int $size){
        $base = log($size, 1024);
        $suffixes = array('', 'KB', 'MB', 'GB', 'TB');  
        return round(pow(1024, $base-floor($base)), 2).''.$suffixes[floor($base)];
    }
}






//
//
//  UploadPictureTumbnail($posted_picture, $new_location, $overwrite, $img_thumb_width)
//
//		This function can use ".gif",".jpg",".png",".jpeg",".bmp" and generates a small picture using $posted_picture to be uploaded to $new_location, if $overwrite = true the file will be overwrited, 
//		if $img_thumb_width is set this apply to width, if not set $thumb_max_width_default, 
//		this function was checked for png transparency
//
//		It returns the new filename

function UploadPictureTumbnail($posted_picture, $new_location, $overwrite, $img_thumb_width){
	$file_name ="";
	global $thumb_max_width_default;
	global $msg_error_file_not_found;
	$error    = $posted_picture['error'];
	$size     = $posted_picture['size'];
	$phisical_ext      = strtolower(pathinfo($posted_picture['name'], PATHINFO_EXTENSION));

	if(substr($new_location,-1,1) != "/"){
		$new_location = $new_location . "/";
	}
	
	if($img_thumb_width < $thumb_max_width_default){
		$img_thumb_width = $thumb_max_width_default; // 
	}

	$extlimit = "yes"; //Limit allowed extensions? (no for all extensions allowed)
	//List of allowed extensions if extlimit = yes
	$limitedext = array(".gif",".jpg",".png",".jpeg",".bmp");		
	//the image -> variables
	$file_type = $posted_picture['type'];
	$file_name = $posted_picture['name'];
	$file_size = $posted_picture['size'];
	$file_tmp = $posted_picture['tmp_name'];

	// Correccion, en caso de que exista finfo se utiliz pra detectar el tipo de imagen
	//if(extension_loaded("file_info")){
		$all_new_fileinfo = (new FileInfoTool)->get_file($file_tmp)->get_info();
		$file_type = $all_new_fileinfo['mime'];
		$ext = explode ("/", $all_new_fileinfo['mime'])[1];
		echo "Si existe finfo	<br>";
	//}

	echo "Tipo de Archivo: " . $file_type . "<br>";
	echo "Extension: " . $ext . "<br>";
	echo "Extension Fisica: " . $phisical_ext . "<br>";
	/////////////////////////////////////////

	$original_filename =  $posted_picture['name']; 

	echo "Nombre de e archivo original: " . $original_filename . "<br>";
	
	if(!$overwrite){
		$file_name = IfFileExistsRename($original_filename,  $new_location);
	}else{
		$file_name = $posted_picture['name'];
	}

	$file_name = str_replace($phisical_ext, $ext, $file_name);

	echo "Nuevo nombre de archivo: " . $file_name . "<br>";
	

	switch ($error) {
        case UPLOAD_ERR_OK:
            $valid = true;
            //validate file extensions
            if ( !in_array($ext, $limitedext) ) {
            	global $msg_error_file_format_worng;
                $valid = false;
                $response = $msg_error_file_format_worng . '<br>';
            }
            //validate file size
            if ( $size/1024/1024 > 2 ) {
                $valid = false;
                $response = 'File size is exceeding maximum allowed size.<br>';
            }
            //upload file
            if ($valid) {
                continue;
                exit;
            }
            break;
        case UPLOAD_ERR_INI_SIZE:
            $response = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.<br>';
            break;
        case UPLOAD_ERR_FORM_SIZE:
            $response = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.<br>';
            break;
        case UPLOAD_ERR_PARTIAL:
            $response = 'The uploaded file was only partially uploaded.<br>';
            break;
        case UPLOAD_ERR_NO_FILE:
            $response = 'No file was uploaded.<br>';
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $response = 'Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.<br>';
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $response = 'Failed to write file to disk. Introduced in PHP 5.1.0.<br>';
            break;
        case UPLOAD_ERR_EXTENSION:
            $response = 'File upload stopped by extension. Introduced in PHP 5.2.0.<br>';
            break;
        default:
            $response = 'Unknown error<br>';
        break;
    }




/*
	echo "Revisando si se sube: " . $file_tmp . "<br>";
	if(!is_uploaded_file($file_tmp)){
	echo $msg_error_file_not_found;
	exit(1); //exit the script and don't process the rest of it!
	}*/

	//check the file's extension
	/*$ext = strrchr($file_name,'.');
	$ext = strtolower($ext);*/


	//so, whats the file's extension?
	/*$getExt = explode ('.', $file_name);
	$file_ext = $getExt[count($getExt)-1];*/

	//the new width variable
	$ThumbWidth = $img_thumb_width;
	
	/////////////////////////////////
	// CREATE THE THUMBNAIL //
	////////////////////////////////
	
	//keep image type
	if($file_size){
		if($file_type == "image/jpg" || $file_type == "image/jpeg"){
			$new_img = imagecreatefromjpeg($file_tmp);
		}elseif($file_type == "image/x-png" || $file_type == "image/png"){
			$new_img = imagecreatefrompng($file_tmp);
		}elseif($file_type == "image/gif"){
			$new_img = imagecreatefromgif($file_tmp);
		}
		
		
		
	//list the width and height and keep the height ratio.
	list($width, $height) = getimagesize($file_tmp);
	$imgInfo = getimagesize($file_tmp);

	
	//calculate the image ratio
	$imgratio=$width/$height;
	if ($imgratio>1){
		$newwidth = $ThumbWidth;
		$newheight = $ThumbWidth/$imgratio;
	}else{
		$newheight = $ThumbWidth;
		$newwidth = $ThumbWidth*$imgratio;
	}
	//function for resize image.
	if (function_exists("imagecreatetruecolor")){
		$resized_img = imagecreatetruecolor($newwidth,$newheight);
	}else{
		die($msg_library_not_found);
	}
	
	/* Check if this image is PNG or GIF, then set if Transparent*/  
	
	if(($imgInfo[2] == 1) || ($imgInfo[2]==3)){
		imagealphablending($resized_img, false);
	
		imagesavealpha($resized_img,true);
	
		$transparent = imagecolorallocatealpha($resized_img, 255, 255, 255, 127);
		
		imagefilledrectangle($resized_img, 0, 0, $newwidth, $newheight, $transparent);

	}

	
	//the resizing is going on here!
	imagecopyresized($resized_img, $new_img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

	//finally, save the image
		
	 switch ($imgInfo[2]) {
	  case 1: imagegif($resized_img, $new_location . $file_name); break;
	  case 2: imagejpeg($resized_img, $new_location . $file_name);  break;
	  case 3: imagepng($resized_img, $new_location . $file_name); break;
	  default:  trigger_error('Failed resize image!', E_USER_WARNING);  break;
	 }

	
	ImageJpeg ($resized_img, $new_location . $file_name);
	
	
	
	ImageDestroy ($resized_img);
	ImageDestroy ($new_img);

	}
	
	return $file_name;

}



?>
