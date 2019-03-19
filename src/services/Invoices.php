<?php
/**
 * Affiliate plugin for Craft CMS 3.x
 *
 * Plugin to add affiliates to Craft Commerce
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\affiliate\services;

use kuriousagency\affiliate\Affiliate;
use kuriousagency\affiliate\elements\Invoice;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\helpers\stringhelper;
use craft\helpers\FileHelper;
use craft\web\View;
use Dompdf\Dompdf;
use Dompdf\Options;
use yii\base\Exception;

/**
 * @author    Kurious Agency
 * @package   Affiliate
 * @since     1.0.0
 */
class Invoices extends Component
{

	public function generateCartNumber(): string
    {
        return md5(uniqid(mt_rand(), true));
	}

	public function markAsPaid($id)
	{
		
		$invoice = Invoice::findOne($id);
		$invoice->paid = 1;

		Craft::$app->getElements()->saveElement($invoice, false);

		return true;
	}
	
	
	public function renderPdfForInvoice(Invoice $invoice, $option = '', $templatePath = null): string
    {
        if (null === $templatePath) {
            $templatePath = Affiliate::$plugin->getSettings()->invoicePdfTemplatePath;
		}

        // // Trigger a 'beforeRenderPdf' event
        // $event = new PdfEvent([
        //     'order' => $order,
        //     'option' => $option,
        //     'template' => $templatePath,
        // ]);
        // $this->trigger(self::EVENT_BEFORE_RENDER_PDF, $event);

        // if ($event->pdf !== null) {
        //     return $event->pdf;
        // }

        // Set Craft to the site template mode
        $view = Craft::$app->getView();
        $oldTemplateMode = $view->getTemplateMode();
        $view->setTemplateMode(View::TEMPLATE_MODE_SITE);

        if (!$templatePath || !$view->doesTemplateExist($templatePath)) {
            // Restore the original template mode
            $view->setTemplateMode($oldTemplateMode);

            throw new Exception('PDF template file does not exist.');
        }

        try {
            $html = $view->renderTemplate($templatePath, compact('invoice', 'option'));
        } catch (\Exception $e) {
			// Set the pdf html to the render error.

			// Craft::dd($e->getMessage());

            Craft::error('Invoice PDF render error. Order number: ' . $invoice->getShortNumber() . '. ' . $e->getMessage());
            Craft::$app->getErrorHandler()->logException($e);
            $html = Craft::t('affilliate', 'An error occurred while generating this PDF.');
        }

        // Restore the original template mode
        $view->setTemplateMode($oldTemplateMode);

        $dompdf = new Dompdf();

        // Set the config options
		$pathService = Craft::$app->getPath();
        $dompdfTempDir = $pathService->getTempPath() . DIRECTORY_SEPARATOR . 'affiliate_dompdf';
        $dompdfFontCache = $pathService->getCachePath() . DIRECTORY_SEPARATOR . 'affiliate_dompdf';
        $dompdfLogFile = $pathService->getLogPath() . DIRECTORY_SEPARATOR . 'affiliate_dompdf.htm';

        // Should throw an error if not writable
        FileHelper::isWritable($dompdfTempDir);
        FileHelper::isWritable($dompdfLogFile);

        // $isRemoteEnabled = Plugin::getInstance()->getSettings()->pdfAllowRemoteImages;

        $options = new Options();
        $options->setTempDir($dompdfTempDir);
        $options->setFontCache($dompdfFontCache);
        $options->setLogOutputFile($dompdfLogFile);
        // $options->setIsRemoteEnabled($isRemoteEnabled);

        // Set the options
        $dompdf->setOptions($options);

        // Paper size and orientation
        // $pdfPaperSize = Plugin::getInstance()->getSettings()->pdfPaperSize;
        // $pdfPaperOrientation = Plugin::getInstance()->getSettings()->pdfPaperOrientation;
        $dompdf->setPaper('a4','portrait');

        $dompdf->loadHtml($html);
        $dompdf->render();

        // Trigger an 'afterRenderPdf' event
        // $event = new PdfEvent([
        //     'order' => $order,
        //     'option' => $option,
        //     'template' => $templatePath,
        //     'pdf' => $dompdf->output(),
        // ]);
        // $this->trigger(self::EVENT_AFTER_RENDER_PDF, $event);

		// return $event->pdf;
		
		return $dompdf->output();
    }
}