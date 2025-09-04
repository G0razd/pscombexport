<?php

class PsCombExportEmbedModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $id_product = (int)Tools::getValue('id_product');
        if (!$id_product) {
            die('Product ID missing');
        }

        $module = $this->module;
        $id_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;
        
        // Default values for embed
        // We could allow overriding these via GET parameters if needed
        $btnImg = Tools::getValue('btn_img', 'https://abakuslearning.cz/images/checkout1.png');
        $btnLabel = Tools::getValue('btn_label', 'OBJEDNAT');
        $addEmptyRow = (bool)Tools::getValue('add_empty_row', false);

        // Generate tables
        // Returns: [$startsDisplay, $exportHtml, $previewHtml]
        // We use $previewHtml because it has absolute image paths, which is better for embedding.
        list($starts, $exportHtml, $previewHtml) = $module->buildSingleProductTables(
            $id_product, $id_shop, $id_lang, $btnImg, $btnLabel, $addEmptyRow
        );

        // Output simple HTML page
        echo '<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kurzy</title>
<style>
    body { font-family: "Open Sans", sans-serif; margin: 0; padding: 0; background: transparent; overflow: hidden; }
    /* Ensure table styles from module are respected, add basic reset */
    table { width: 100%; border-collapse: collapse; }
    td { padding: 8px; border-bottom: 1px solid #eee; vertical-align: middle; }
    h2 { font-size: 24px; margin: 15px 0 15px; color: #333; }
    a { text-decoration: none; color: #2fb5d2; font-weight: bold; }
    a:hover { text-decoration: underline; }
    img { vertical-align: middle; }
</style>
</head>
<body>
' . $previewHtml . '
</body>
</html>';
        exit;
    }
}
