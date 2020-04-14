<?php
namespace core;

class image
{
    protected $image;
    protected $file;

    public function __construct($file)
    {
        $extension = strtolower(strrchr($file, '.'));

        switch($extension)
        {
            case '.jpg':
            case '.jpeg':
                $img = @imagecreatefromjpeg($file);
                break;
            case '.gif':
                $img = @imagecreatefromgif($file);
                break;
            case '.png':
                $img = @imagecreatefrompng($file);
                break;
            default:
                $img = false;
                break;
        }

        $this->file = $file;
        $this->image = $img;
    }

    public function resize($maxWidth, $maxHeight)
    {
        $width = imagesx($this->image);
        $height = imagesy($this->image);

        list($newWidth, $newHeight) = $this->getNewSize($width, $height, $maxWidth, $maxHeight);

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($this->image, false);
        imagesavealpha($this->image, true);
        imagecopyresampled($resized, $this->image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        imagedestroy($this->image);

        $this->image = $resized;

        return $this;
    }

    public function resizeIfBigger($maxWidth, $maxHeight)
    {
        $width = imagesx($this->image);
        $height = imagesy($this->image);

        if ($maxWidth < $width || $maxHeight < $height) {

            list($newWidth, $newHeight) = $this->getNewSize($width, $height, $maxWidth, $maxHeight);

            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($this->image, false);
            imagesavealpha($this->image, true);
            imagecopyresampled($resized, $this->image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            imagedestroy($this->image);

            $this->image = $resized;

        } else {
            imagealphablending($this->image, false);
            imagesavealpha($this->image, true);
        }

        return $this;
    }

    private function getNewSize($width, $height, $maxWidth, $maxHeight) {
        if ($maxHeight == 0){

            $newWidth = $maxWidth;
            $newHeight = $height * $newWidth / $width;

        } else {
            if ($width > $height) {
                $newWidth = $maxWidth;
                $newHeight = $height * $newWidth / $width;
            } else {
                $newHeight = $maxHeight;
                $newWidth = $width * $newHeight / $height;
            }
        }

        if ( ($maxHeight > 0)&&($newHeight > $maxHeight) ){
            $newHeight = $maxHeight;
            $newWidth = $width * $newHeight / $height;
        }

        return array($newWidth, $newHeight);
    }

    public function crop($maxWidth, $maxHeight)
    {
        $cropped = imagecreatetruecolor($maxWidth, $maxHeight);
        imagealphablending($this->image, false);
        imagesavealpha($this->image, true);
        imagecopy ( $cropped , $this->image , 0 , 0 , 0 , 0 , $maxWidth , $maxHeight );
        imagedestroy($this->image);

        $this->image = $cropped;

        return $this;
    }

    public function save($savePath = false, $imageQuality="100")
    {
        if (!$savePath) {
            $savePath = $this->file;
        }

        $extension = strtolower(strrchr($savePath, '.'));

        switch($extension)
        {
            case '.jpg':
            case '.jpeg':
                if (imagetypes() & IMG_JPG) {
                    imagejpeg($this->image, $savePath, $imageQuality);
                }
                break;

            case '.gif':
                if (imagetypes() & IMG_GIF) {
                    imagegif($this->image, $savePath);
                }
                break;

            case '.png':
                // *** Переводим шкалу качества с 0 - 100 в 0 - 9
                $scaleQuality = round(($imageQuality/100) * 9);

                // *** Инвертируем качество.
                $invertScaleQuality = 9 - $scaleQuality;

                if (imagetypes() & IMG_PNG) {
                    imagepng($this->image, $savePath, $invertScaleQuality);
                }
                break;

            default:
                // *** Нет расширение - не сохраняем.
                break;
        }
    }
}