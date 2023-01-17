<?php

namespace App\Service;

use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadHelper
{
    /**
     * @var string
     */
    private $uploadsPath;

    /**
     * @param $uploadsPath
     */
    public function __construct(string $uploadsPath)
    {
        $this->uploadsPath = $uploadsPath;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param string $dir
     * @param null $oldFile
     * @return string
     */
    public function uploadImage(UploadedFile $uploadedFile, $dir = 'others', $oldFile = null): string
    {
        // kernel.project_dir odkazuje do rootu webu
        $destination = $this->uploadsPath.'/'.$dir;
        $originalFileName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFileName = Urlizer::urlize($originalFileName) . '.' . $uploadedFile->guessExtension();

        // Removes old records of images if set
        if ($oldFile && $oldFile !== '' && file_exists($destination.'/'.$oldFile)) {
            unlink($destination.'/'.$oldFile);
        }

        // Ochrana proti zhroucení FTP - nepřepisuje obrázky, jenom updatuje název v db
        if (file_exists($destination.'/'.$newFileName)) {
            return $newFileName;
        } else {
            $uploadedFile->move($destination, $newFileName);
        }

        return $newFileName;
    }

    /*
    public function getPublicPath(string $path): string
    {
        return 'uploads/products/'.$path;
    }
    */
}