<?php

namespace PdfFormsLoader\Models;

use PDFfiller\OAuth2\Client\Provider\PDFfiller;

/**
 * Class PDFFillerModel
 * @property PDFfiller $PDFFillerProvider
 * @package PdfFormsLoader\Models
 */
class PDFFillerModel
{
    const EXPIRES = 60;
    const EXPIRES_DOCUMENT = 86400;
    const PDFFILLER_SITE = "https://www.pdffiller.com";
    const PDFFILLER_HELP_LINK = "https://www.pdffiller.com/en/support";
    const PDFFILLER_API_DOMAIN = "https://api.pdffiller.com";
    const ALLOWED_FIELD_TYPE = ['text', 'number', 'date', 'checkmark'];

    protected $lastAttachId = 0;
}
