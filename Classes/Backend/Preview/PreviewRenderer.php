<?php

declare(strict_types=1);

/*
 * This file is part of the package sci/sci-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Sci\SciApi\Backend\Preview;

use Sci\SciApi\DataProcessing\FlexFormProcessor;
use Sci\SciApi\Service\ConfigurationService;
use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Sets up Fluid and applies the same DataProcessor as the frontend to the data record.
 * Wraps the backend preview in class="cb-editor".
 */
class PreviewRenderer extends StandardContentPreviewRenderer
{
    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $record = $item->getRecord();

        $cbConfiguration = ConfigurationService::contentBlockConfiguration($record['CType']);

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename($cbConfiguration['EditorPreview.html']);

        // TODO use TypoScript configuration for paths
        $view->setPartialRootPaths([$cbConfiguration['srcPath']]);
        $view->setLayoutRootPaths([$cbConfiguration['srcPath']]);

        $view->assign('data', $record);
        $view->assign('EditorLLL', $cbConfiguration['EditorLLL'] ?? false);
        if (!empty($record['content_block'])) {
            $processedData = ['data' => $record];
            $processedData = GeneralUtility::makeInstance(FlexFormProcessor::class)
                ->process(
                    GeneralUtility::makeInstance(ContentObjectRenderer::class),
                    [],
                    [],
                    $processedData
                );
            $view->assignMultiple($processedData);
        }

        // TODO the wrapping class should go to a proper Fluid layout
        return '<div class="cb-editor">' . $view->render() . '</div>';
    }

    public function wrapPageModulePreview(string $previewHeader, string $previewContent, GridColumnItem $item): string
    {
        return $previewHeader . $previewContent;
    }
}
