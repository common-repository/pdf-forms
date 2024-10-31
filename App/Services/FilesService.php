<?php
namespace PdfFormsLoader\Services;

class FilesService
{
    const DEFAULT_FILE_NAME = 'attach.pdf';

    protected $content;
    protected $fileName;
    protected $path;
    protected $documentId;

    /**
     * @param $templateId
     * @return $this
     * @throws \PDFfiller\OAuth2\Client\Provider\Exceptions\InvalidQueryException
     * @throws \PDFfiller\OAuth2\Client\Provider\Exceptions\InvalidRequestException
     * @throws \ReflectionException
     */
    public function setFileFromPDFFiller($templateId) {
        $pdffiller = PdfFillerApiService::getInstance();

        $content = $pdffiller->getTemplateContent($templateId);
        $document = $pdffiller->getTemplate($templateId);
        $name = $document->name;

        $file = tempnam(sys_get_temp_dir(), 'pdfforms');
        $newFileName = dirname($file) . '/' . $name;
        rename($file, $newFileName);
        $fp = fopen($newFileName, "w");
        fwrite($fp, $content);
        fclose($fp);

        $this->setContent($content);
        $this->setFileName($name);
        $this->setPath(dirname($file));

        return $this;
    }

    public function removeFile() {
        unlink($this->getFullPath());
    }

    public function removeAfterLoadSite() {
        add_action( 'shutdown', [$this, 'removeFile'] );
        return $this;
    }

    public function setContent($content) {
        $this->content = $content;
        return $this;
    }

    public function getContent() {
        return $this->content;
    }

    public function setFileName($name = self::DEFAULT_FILE_NAME) {
        /*$newFileName = dirname($this->path) . $name;
        rename($this->fileName, $newFileName);*/

        $this->fileName = $name;
        return $this;
    }

    public function getFileName() {
        return $this->fileName;
    }

    public function setPath($path) {
        $this->path = $path;
        return $this;
    }

    public function getPath() {
        return $this->path;
    }

    public function getFullPath() {
        return $this->path . '/' . $this->fileName;
    }
}
