<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

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
}