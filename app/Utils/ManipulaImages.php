<?php

namespace App\Utils;

use Kernel\File;

class ManipulaImages
{

    public static function optimize(File $file)
    {
        $source_image_path = $file->path; // or .png
        $destination_image_path = dirname(__FILE__, 3) . '/storage/temp/' . uniqid() . '.webp';
        $quality = 80; // WebP quality (0-100)
        $mime_type = $file->type;

        if ($mime_type == 'image/jpeg') {
            $image = \imagecreatefromjpeg($source_image_path);
        } elseif ($mime_type == 'image/png') {
            $image = \imagecreatefrompng($source_image_path);
        }
        \imagewebp($image, $destination_image_path, $quality);
        \imagedestroy($image);

        if (file_exists($source_image_path))
            unlink($source_image_path);

        //resize image
        $destImage = imagecreatetruecolor(200, 200);
        $sourceImage = imagecreatefromwebp($destination_image_path);
        $info = getimagesize($destination_image_path);
        $newWidth = 200;
        $newHeightCalculated = 200;
        $originalWidth = $info[0];
        $originalHeight = $info[1];

        imagecopyresampled(
            $destImage,     // Imagem de destino
            $sourceImage,   // Imagem de origem
            0, 0,           // Coordenadas x, y do ponto de destino para começar a cópia
            0, 0,           // Coordenadas x, y do ponto de origem para começar a cópia
            $newWidth,      // Largura da imagem de destino
            $newHeightCalculated, // Altura da imagem de destino
            $originalWidth, // Largura da imagem de origem
            $originalHeight // Altura da imagem de origem
        );
        
        imagewebp($destImage, $destination_image_path, $quality);
        imagedestroy($destImage);
        imagedestroy($sourceImage);

        $file->path = $destination_image_path;
        $file->extension = 'webp';
        $file->type = 'image/webp';
        $file->size = filesize($destination_image_path);

        return $file;
    }
}
