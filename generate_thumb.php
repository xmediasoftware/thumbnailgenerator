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
$msg_error_file_not_found = "Error: Please select a file to be uploaded!";
$msg_error_file_format_worng = "incorrect File Type or not permitted, only these files are allowed";
$msg_library_not_found = "Error: Please check that you have the GD library version 2+";

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

	if(substr($new_location,-1,1) != "/"){
		$new_location += $new_location . "/";
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
	
	if(!$overwrite){
		$file_name = IfFileExistsRename($posted_picture['name'], $new_location);
	}else{
		$file_name = $posted_picture['name'];
	}
	
	if(!is_uploaded_file($file_tmp)){
	echo $msg_error_file_not_found;
	exit(1); //exit the script and don't process the rest of it!
	}

	//check the file's extension
	$ext = strrchr($file_name,'.');
	$ext = strtolower($ext);

	if (($extlimit == "yes") && (!in_array($ext,$limitedext))) {
		echo  $msg_error_file_format_worng ." " . implode (", ",$limitedext);
		exit();
	}

	//so, whats the file's extension?
	$getExt = explode ('.', $file_name);
	$file_ext = $getExt[count($getExt)-1];

	//the new width variable
	$ThumbWidth = $img_thumb_width;
	
	/////////////////////////////////
	// CREATE THE THUMBNAIL //
	////////////////////////////////
	
	//keep image type
	if($file_size){
		if($file_type == "image/pjpeg" || $file_type == "image/jpeg"){
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
