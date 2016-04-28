# thumbnailgenerator
This is a thumbnail generator function, works with gif, bmp, jpg, png, and jpeg

I create this thumbnail generator using parts from other scripts and I add a png transparency fix that I found in the past, Currently I use it to generate thumbnails in some websites

Here is a Sample from how to use

	  if((isset($_FILES['new_picture']) && ($_FILES['new_picture']['tmp_name'] !=""))){
	    /* create a thumbnail with max width: 200 pixels */
  		$new_picture_name = UploadPictureRealSize($_FILES['new_picture'], "imgages/" false, 200);
	  }
