<?php
/**
 * Подключение вспомогательного скрипта:
 */
require_once(LIBS_DIR .'/3rdparty/uploader.class.php');

/**
 * Модель, отвечающая за функционал загрузок в разделы:
 */
class Board_UploadModel
{
	const IMAGE_MAX_SIZE = 6291456; // bytes

	/**
	 * Данные загрузки:
	 */
	 private $data = null;

	/**
	 * Создание экземпляра объекта загрузки:
	 */
	public function __construct($upload = null)
	{
		$this -> data = $upload;
	}

	/**
	 * Проверка существования:
	 */
	public function exists()
	{
		if (!is_null($this -> data))
		{
			if (
				file_exists(UPLOAD_PATH .'/'. $this -> data['board'] .'/'. $this -> data['file_name']) &&
				file_exists(UPLOAD_PATH .'/'. $this -> data['board'] .'/'. $this -> data['thumb_name'])
			)
				return true;
		}
		return false;
	}

	/**
	 * Удаление файлов:
	 */
	public function remove()
	{
		@unlink(UPLOAD_PATH .'/'. $this -> data['board'] .'/'. $this -> data['file_name']);
		@unlink(UPLOAD_PATH .'/'. $this -> data['board'] .'/'. $this -> data['thumb_name']);
	}

	/**
	 * Загрузка и обработка файла:
	 */
	public function process($board)
	{
		if (array_key_exists('upload', $_FILES) && $_FILES['upload']['error'] != 4)
		{
			$kvs      = KVS::getInstance();
			$upload   = $_FILES['upload'];
			$pathinfo = pathinfo($upload['name']);
			$data     = array();

			$data['board']         = $board;
			$data['original_name'] = $upload['name'];
			$data['size']          = TemplateHelper::format_bytes($upload['size']);

			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			switch(finfo_file($finfo, $upload['tmp_name'])) {
				case 'image/jpeg':
				case 'image/jpg':
					$ext = 'jpg';
					break;
				case 'image/png':
					$ext = 'png';
					break;
				case 'image/gif':
					$ext = 'gif';
					break;
				default:
					return false;
			}
			finfo_close($finfo);

			$name = time() . rand(0, 100000);
			copy($upload['tmp_name'], UPLOAD_PATH .'/'. $board .'/'. $name .'.'. $ext);

			$data['file_name'] = $name .'.'. $ext;
			$full_size         = getimagesize(UPLOAD_PATH .'/'. $data['board'] .'/'. $data['file_name']);
			$data['full_size'] = array($full_size[0], $full_size[1]);

			$this -> createThumbnail(
				$upload['tmp_name'], UPLOAD_PATH .'/'. $board .'/thumb_'. $name .'.'.$ext
			);

			$data['thumb_name'] = 'thumb_'. $name .'.'.$ext;
			$thumb_size = getimagesize(UPLOAD_PATH .'/'. $data['board'] .'/'. $data['thumb_name']);
			$data['thumb_size'] = array($thumb_size[0], $thumb_size[1]);

			$data['web_full']  = 'uploads/'. $board .'/'. $data['file_name'];
			$data['web_thumb'] = 'uploads/'. $board .'/'. $data['thumb_name'];

			$this -> data = $data;
		}/* 
		elseif (!empty($_POST['upload']))
		{
			$kvs      = KVS::getInstance();
			$upload   = $_POST['upload'];
			$data     = array();

			$data['board']         = $board;
			$data['original_name'] = $upload['name'];
			$data['size']          = TemplateHelper::format_bytes($upload['size']);

			$uploader = new uploader();
			$uploader -> destDir   = UPLOAD_PATH .'/'. $board;
			$uploader -> upload($upload['url']);

			$data['file_name'] = $uploader -> fileName;
			$full_size                 = getimagesize(UPLOAD_PATH .'/'. $data['board'] .'/'. $data['file_name']);
			$data['full_size']    = array($full_size[0], $full_size[1]);

			$uploader -> resizeDir = UPLOAD_PATH .'/'. $board .'/thumb_';

			$data['thumb_name'] = 'thumb_'. $uploader -> resize('', min(125, $data['full_size'][0]), 200);
			$thumb_size = getimagesize(UPLOAD_PATH .'/'. $data['board'] .'/'. $data['thumb_name']);
			$data['thumb_size'] = array($thumb_size[0], $thumb_size[1]);

			$data['web_full']       = 'uploads/'. $board .'/'. $data['file_name'];
			$data['web_thumb'] = 'uploads/'. $board .'/'. $data['thumb_name'];
		
			die;
		}*/

		return true;
	}

	/**
	 * Получение данных о файле:
	 */
	public function getData()
	{
		return $this -> data;
	}

	/**
	 * Проверка аплоада:
	 */
	static public function checkUpload()
	{
		if (array_key_exists('upload', $_FILES) && $_FILES['upload']['error'] != 4)
		{
			$upload = $_FILES['upload'];

			if ($upload['error'] == 0)
				if (preg_match('/\.(jpg|png|gif|jpeg)$/i', $upload['name']))
					if (in_array($upload['type'], array('image/jpeg', 'image/gif', 'image/png')))
						if ($upload['size'] < self::IMAGE_MAX_SIZE)
							return true;

			return false;
		}/*
		elseif (!empty($_POST['upload']))
		{
			$headers = @get_headers($_POST['upload'], 1);
			if(substr($headers[0], 9, 3) == 200)
				if ($headers['Content-Length'] > 0 && $headers['Content-Length'] < self::IMAGE_MAX_SIZE)
					if (in_array($headers['Content-Type'], array('image/jpeg', 'image/gif', 'image/png')))
					{
						$url = $_POST['upload'];
						$_POST['upload'] = array(
							'name' => 'Uploaded via URL',
							'url'  => $url,
							'size' => $headers['Content-Length']
						);
						return true;
					}			

			$_POST['upload'] = null;
			return false;
		}*/

		return true;
	}
	
	/**
	 * Создание тамбнейла:
	 */
	private static function createThumbnail($path, $name)
	{
		$thumb  = new Imagick($path);

		$thumb  = $thumb -> coalesceImages();
		$width  = $thumb -> getImageWidth();
		$height = $thumb -> getImageHeight();
		$s      = self::scaleImage($width, $height, 150, 200);
		
		foreach($thumb as $frame) {
			$thumb -> scaleImage($s[0], $s[1]);
		}

		$thumb = $thumb -> deconstructImages();
		file_put_contents($name, $thumb -> getImagesBlob());

		$thumb->destroy();
		return true;
	}

	/**
	**/
	private static function scaleImage($x,$y,$cx,$cy) {
		//Set the default NEW values to be the old, in case it doesn't even need scaling
		list($nx,$ny)=array($x,$y);
		
		//If image is generally smaller, don't even bother
		if ($x>=$cx || $y>=$cx) {
		        
		    //Work out ratios
		    if ($x>0) $rx=$cx/$x;
		    if ($y>0) $ry=$cy/$y;
		    
		    //Use the lowest ratio, to ensure we don't go over the wanted image size
		    if ($rx>$ry) {
		        $r=$ry;
		    } else {
		        $r=$rx;
		    }
		    
		    //Calculate the new size based on the chosen ratio
		    $nx=intval($x*$r);
		    $ny=intval($y*$r);
		}    
		
		//Return the results
		return array($nx,$ny);
	}
}
