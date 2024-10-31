<?php
namespace PdfFormsLoader\Services\PdfFillerManager;

use PDFfiller\OAuth2\Client\Provider\PDFfiller;

class PdfFillerManager
{
    private $PDFFillerProvider;

    public function __construct(PDFfiller $PDFfiller)
    {
        $this->setPdfFillerProvider($PDFfiller);
    }

    /**
     * @return PDFfiller | null
     */
    public function getPDFFillerProvider()
    {
        return $this->PDFFillerProvider;
    }

    /**
     * @param PDFfiller $PDFFillerProvider
     */
    public function setPDFFillerProvider(PDFfiller $PDFFillerProvider)
    {
        $this->PDFFillerProvider = $PDFFillerProvider;
    }

}
