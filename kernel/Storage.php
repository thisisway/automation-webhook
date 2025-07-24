<?php

namespace Kernel;

use Aws\S3\S3Client;

class Storage
{
    public static function store($requestFile)
    {
        $requestFile->name = str_replace(' ', '_', $requestFile->name);

        $file_path = dirname(__FILE__, 2) . '/storage/temp/' . $requestFile->name;
        move_uploaded_file($requestFile->tmp_name, $file_path);

        $file = new File();
        $file->name = $requestFile->name;
        $file->size = $requestFile->size;
        $file->type = $requestFile->type;
        $file->extension = array_reverse(explode('.', $requestFile->name))[0];
        $file->path = $file_path;

        return $file;
    }

    public static function remove(File $requestFile)
    {
        if(file_exists($requestFile->path))
        unlink($requestFile->path);
        return true;
    }

    public static function log($filename, $text)
    {
        $file = fopen(dirname(__FILE__, 2) . '/storage/logs/' . $filename, 'a+');
        fwrite($file, $text);
        fclose($file);
    }

    public static function printAndLog($filename, $text)
    {
        echo $text;
        $file = fopen(dirname(__FILE__, 2) . '/storage/logs/' . $filename, 'a+');
        fwrite($file, $text);
        fclose($file);
    }

    public static function getContent($file_path)
    {
        return file_get_contents($file_path);
    }

    public static function storeS3(File $file, $folder)
    {
        // Configurações do S3
        $s3 = new S3Client([
            'version'     => 'latest',
            'region'      => 'us-east-1', // Pode ser qualquer um, o MinIO não se importa
            'endpoint'    => getenv('AWS_ENDPOINT'), // Altere para seu IP ou domínio do MinIO
            'use_path_style_endpoint' => true, // ESSENCIAL para MinIO
            'credentials' => [
                'key'    => getenv('AWS_ACCESS_KEY_ID'),
                'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
            ]
        ]);

        // Upload para o S3
        $upload = $s3->putObject([
            'Bucket' => Env::get('AWS_BUCKET'),
            'Key'    => trim($folder, '/') . '/' . $file->name,
            'SourceFile' => $file->path
        ]);

        $file->remote_path = $upload['ObjectURL'];
        return $file;
    }

    public static function removeS3($url)
    {
        $path = Self::getS3Path($url);
        
        $s3 = new S3Client([
            'version' => 'latest',
            'region'  => 'sa-east-1',
            'credentials' => [
                'key'    => Env::get('AWS_ACCESS_KEY_ID'),
                'secret' => Env::get('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        $s3->deleteObject([
            'Bucket' => Env::get('AWS_BUCKET'),
            'Key'    => $path
        ]);

        return true;
    }

    public static function getS3Url($path)
    {
        return 'https://' . Env::get('AWS_BUCKET') . '.s3.sa-east-1.amazonaws.com/' . trim($path, '/');
    }   

    public static function getS3Path($url)
    {
        return str_replace('https://' . Env::get('AWS_BUCKET') . '.s3.sa-east-1.amazonaws.com/', '', $url);
    }

    public static function fileExistsS3($path)
    {
        $s3 = new S3Client([
            'version' => 'latest',
            'region'  => 'sa-east-1',
            'credentials' => [
                'key'    => Env::get('AWS_ACCESS_KEY_ID'),
                'secret' => Env::get('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        return $s3->doesObjectExist(Env::get('AWS_BUCKET'), $path);
    }
}
