<?php

namespace Application\Core\Modules\Images;

class Resizer 
{
	
	protected $_imageDir;
	
	private function generateRandomString($length = 8) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
	
	public function __construct($imgDir)
	{
		$this->_imageDir = $imgDir;
	}
	
	public function resize($file){
		
		$targetFile = self::generateRandomString();
		
		$info = getimagesize(MAIN_DIR. $this->_imageDir .$file);
		$mime = $info['mime'];
		
		switch ($mime) {
            case 'image/jpeg':
                    $image_create_func = 'imagecreatefromjpeg';
                    $image_save_func = 'imagejpeg';
                    $new_image_ext = 'jpg';
                    break;

            case 'image/png':
                    $image_create_func = 'imagecreatefrompng';
                    $image_save_func = 'imagepng';
                    $new_image_ext = 'png';
                    break;

            case 'image/gif':
                    $image_create_func = 'imagecreatefromgif';
                    $image_save_func = 'imagegif';
                    $new_image_ext = 'gif';
                    break;

            default: 
                    throw new \Exception('Unknown image type.');
		}
		
		$img = $image_create_func(MAIN_DIR. $this->_imageDir .$file);
		list($width, $height) = getimagesize(MAIN_DIR. $this->_imageDir .$file);
		
		#resize to 38px
		$newHeight = ($height / $width) * 38;
		$tmp = imagecreatetruecolor(38, $newHeight);
		imagecopyresampled($tmp, $img, 0, 0, 0, 0, 38, $newHeight, $width, $height);

		while(file_exists(MAIN_DIR. $this->_imageDir . $targetFile.'_38.'.$new_image_ext)) {
				$targetFile = self::generateRandomString();
		}
		$image_save_func($tmp, MAIN_DIR. $this->_imageDir .$targetFile.'_38.'.$new_image_ext);
		$return[0] = $targetFile.'_38.'.$new_image_ext;
		
		#resize to 85px
		$newHeight = ($height / $width) * 85;
		$tmp = imagecreatetruecolor(85, $newHeight);
		imagecopyresampled($tmp, $img, 0, 0, 0, 0, 85, $newHeight, $width, $height);

		while(file_exists(MAIN_DIR. $this->_imageDir .$targetFile.'_85.'.$new_image_ext)) {
				$targetFile = self::generateRandomString();
		}
		$image_save_func($tmp, MAIN_DIR. $this->_imageDir .$targetFile.'_85.'.$new_image_ext);
		$return[1] = $targetFile.'_85.'.$new_image_ext;
		
		#resize to 150px
		$newHeight = ($height / $width) * 150;
		$tmp = imagecreatetruecolor(150, $newHeight);
		imagecopyresampled($tmp, $img, 0, 0, 0, 0, 150, $newHeight, $width, $height);

		while(file_exists(MAIN_DIR. $this->_imageDir .$targetFile.'_150.'.$new_image_ext)) {
				$targetFile = self::generateRandomString();
		}
		$image_save_func($tmp, MAIN_DIR. $this->_imageDir .$targetFile.'_150.'.$new_image_ext);
		$return[2] = $targetFile.'_150.'.$new_image_ext;

		return $return;
	}
		
}