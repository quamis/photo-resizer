<?php
ini_set('display_errors', '1');
error_reporting(-1);
date_default_timezone_set('UTC');

$resizedImages = Array();

if (isset($_FILES['file'])) {
	// $_FILES['file']
	$sizes = Array(
            Array('w'=>640, 'h'=>480),
            Array('w'=>800, 'h'=>600),
            Array('w'=>1024, 'h'=>768),
            Array('w'=>1280, 'h'=>1024),
            Array('w'=>1440, 'h'=>900),
            Array('w'=>1920, 'h'=>1080),
	);
	
	$finfo = new SplFileInfo($_FILES['file']['name']);
	
        $img = new \Imagick($_FILES['file']['tmp_name']);
        $img->resizeImage(256, 256, imagick::FILTER_GAUSSIAN, 1.25, true);
        $img->setImageCompressionQuality(75);
        $newName = sprintf('thumb-%s-%sx%s.%s', $finfo->getBasename(".{$finfo->getExtension()}"), '000', '000', $finfo->getExtension());
        $newPath = sprintf("resized/%s", $newName);
        $img->writeImage($newPath);
        
        $thumbnail = Array(
            'path' =>   $newPath,
        );
        
	
	foreach ($sizes as $sz) {
            $resizedImage = Array();
            $img = new \Imagick($_FILES['file']['tmp_name']);
            $img->resizeImage($sz['w'], $sz['h'], imagick::FILTER_LANCZOS, 1, true);
            $img->setImageCompressionQuality(95);
            $newName = sprintf('%s-%sx%s.%s', $finfo->getBasename(".{$finfo->getExtension()}"), $sz['w'], $sz['h'], $finfo->getExtension());
            $newPath = sprintf("resized/%s", $newName);
            $img->writeImage($newPath);
            
            $resizedImage = Array(
                'path' =>       $newPath,
                'name' =>       $newName,
                'sz' =>         $sz,
                'thumbnail' =>  $thumbnail,
            );
            
            $resizedImages[]= $resizedImage;
        }
}
else {
	
}


?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, user-scalable=yes">
	
	<title>photo resizer - </title>
	<script>document.write('<base href="' + document.location + '" />');</script>
	
	<!-- CSS -->
	<link rel="stylesheet" type="text/css" href="css/index.css" media="only screen and (min-device-width: 100px)" />
</head>

<body>
        <h1>
            Photo resizer
        </h1>
	<div class='form'>
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="file" accept=".jpg,.jpeg,.png">
                <button type="submit">resize</button>
            </form>
        </div>
        
        <div class='images'>
            <?php foreach($resizedImages as $img) { ?>
                <div class="thumb">
                    <a href="<?=$img['path'] ?>" target="blank">
                        <img src="<?=$img['thumbnail']['path'] ?>" />
                        <div class="size" ><?=$img['sz']['w'] ?>x<?=$img['sz']['h'] ?></div>
                        <div class="name"><?=$img['name'] ?></div>
                    </a>
                </div>
            <?php } ?>
        </div>
	
	<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.2/underscore-min.js"></script>
	<script type="text/javascript" src= "lib/index.js"></script>
</body>
</html>
