<?php

declare(strict_types=1);

/*
 * This file is part of the package sci/sci-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Sci\SciApi\Backend\Preview;

use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Uses the Fluid-template defined in mod.web_layout.tt_content.preview.<CType>.
 * Wraps the backend preview in class="contentblock-preview".
 */
class PreviewRenderer extends StandardContentPreviewRenderer
{
    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $record = $item->getRecord();

        $fluidTemplateFile = BackendUtility::getPagesTSconfig($record['pid'])
            ['mod.']['web_layout.']['tt_content.']['preview.'][$record['CType']]
            ?? null;

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename($fluidTemplateFile);
        $view->assign('data', $record);
        if (!empty($record['content_block'])) {
            $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
            $view->assignMultiple(
                $flexFormService->convertFlexFormContentToArray($record['content_block'])
            );
        }
        return '<div class="contentblock-preview">' .  $view->render() . '</div>';
    }
}
