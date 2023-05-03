<?php

namespace App\Chat\Contracts;

interface IAttachment
{
    public function message();
    public function getMessage();

    public function getContentType();
    public function setContentType($contentType);

    public function getContent();
    public function setContent($content);

    public function getFilePath();
    public function setFilePath($path);

    /**
     * @return string
     */
    public function getStoragePath();

}
