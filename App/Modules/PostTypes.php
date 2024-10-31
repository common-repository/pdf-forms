<?php
namespace PdfFormsLoader\Modules;

use PdfFormsLoader\Facades\PostTypesFacade;

class PostTypes
{
    public function __construct() {
        PostTypesFacade::createPostType('pdfforms', 'PDFForms', 'PDFForm');
    }
}
