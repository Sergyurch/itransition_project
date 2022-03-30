<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

class FileUploader
{
    private $profileImageTargetDirectory;
    private $imageTargetDirectory;
    private $slugger;

    public function __construct($profileImageTargetDirectory, $imageTargetDirectory, SluggerInterface $slugger)
    {
        $this->profileImageTargetDirectory = $profileImageTargetDirectory;
        $this->imageTargetDirectory = $imageTargetDirectory;
        $this->slugger = $slugger;

        Configuration::instance([
            'cloud' => [
                'cloud_name' => '', 
                'api_key' => '', 
                'api_secret' => ''
            ]
        ]);

    }

    public function upload(UploadedFile $file, $fileSource)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
        $file->move($this->getTargetDirectory($fileSource), $fileName);
        return $fileName;
    }

    public function getTargetDirectory($fileSource)
    {
        return ($fileSource == 'profile') ? $this->profileImageTargetDirectory : $this->imageTargetDirectory;
    }

    public function uploadImageToCloudinary(UploadedFile $file, $fileSource)
    {
        $fileName = $file->getRealPath();
        
        $imageUploaded = (new UploadApi())->upload($fileName, [
            'folder' => ($fileSource == 'profile') ? 'profile' : 'images',
            'format' => $file->guessExtension()
        ]);

        return $imageUploaded['secure_url'];
    }

    public function destroyImageCloudinary($cloudPath)
    {
        return (new UploadApi())->destroy( basename(dirname($cloudPath)) . '/' . pathinfo($cloudPath, PATHINFO_FILENAME), ["invalidate" => true] );
    }
}
