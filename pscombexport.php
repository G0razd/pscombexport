<?php
if (!defined('_PS_VERSION_')) { exit; }

require_once __DIR__ . '/classes/PsCombExportUtils.php';
require_once __DIR__ . '/classes/PsCombExportBuilder.php';
require_once __DIR__ . '/classes/PsCombExportUI.php';

class PsCombExport extends Module
{
    use PsCombExportUtils;
    use PsCombExportBuilder;
    use PsCombExportUI;

    public function __construct()
    {
        $this->name = 'pscombexport';
        $this->tab = 'administration';
        $this->version = '3.5.2'; // customization settings
        $this->author = 'Lukáš Gorazd Hrodek';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Product Combinations Export plugin for PrestaShop');
        $this->description = $this->l('Generate a copy-ready HTML table for one product: Number / Name / Day / Time + Course Starts (grouped by term); preview absolute image, export relative; robust combined-day handling and proper admin token.');
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        return parent::install() 
            && $this->registerHook('moduleRoutes')
            && $this->registerHook('displayAdminProductsMainStepRightColumnBottom')
            && $this->registerHook('displayAdminProductsExtra')
            && $this->registerHook('actionAdminControllerSetMedia');
    }

    public function uninstall() { return parent::uninstall(); }

    public function hookModuleRoutes($params)
    {
        return [
            'module-pscombexport-embed' => [
                'controller' => 'embed',
                'rule' => 'kurzy-embed/{id_product}-{rewrite}',
                'keywords' => [
                    'id_product' => ['regexp' => '[0-9]+', 'param' => 'id_product'],
                    'rewrite' => ['regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'rewrite'],
                ],
                'params' => [
                    'fc' => 'module',
                    'module' => 'pscombexport',
                ],
            ],
        ];
    }

    public function hookDisplayAdminProductsMainStepRightColumnBottom($params)
    {
        return $this->renderEmbedBlock($params);
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        return $this->renderEmbedBlock($params);
    }

    public function hookActionAdminControllerSetMedia()
    {
        // Optional: Add CSS/JS for admin if needed
    }

    private function renderEmbedBlock($params)
    {
        $id_product = (int)$params['id_product'];
        if (!$id_product) {
            return '';
        }
        
        $prod = new Product($id_product, false, $this->context->language->id);
        $rewrite = $prod->link_rewrite;
        $embedUrl = $this->context->link->getModuleLink('pscombexport', 'embed', ['id_product' => $id_product, 'rewrite' => $rewrite]);
        $iframeCode = '<iframe src="'.$embedUrl.'" width="100%" height="800" frameborder="0" style="border:0; overflow:hidden;" scrolling="no"></iframe>';

        $html = '<div class="col-md-12">';
        $html .= '<h3>'.$this->l('Kurzy Embed Code').'</h3>';
        $html .= '<div class="input-group">';
        $html .= '<input type="text" class="form-control" id="embedUrlInputProduct'.rand().'" value="'.htmlspecialchars($iframeCode).'" readonly>';
        $html .= '<div class="input-group-append">';
        $html .= '<button class="btn btn-primary" type="button" onclick="this.parentElement.previousElementSibling.select();document.execCommand(\'copy\');"><i class="material-icons">content_copy</i> '.$this->l('Copy').'</button>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<p class="help-block text-muted small">'.$this->l('Copy this code to embed the table.').'</p>';
        $html .= '</div>';

        return $html;
    }

    public function getContent()
    {
        // Auto-register hooks for updates (self-healing)
        $this->registerHook('displayAdminProductsMainStepRightColumnBottom');
        $this->registerHook('displayAdminProductsExtra');

        // Handle Settings Save
        if (Tools::isSubmit('submit_settings')) {
            $config = [
                'columns' => Tools::getValue('columns'),
                'styles' => Tools::getValue('styles')
            ];
            Configuration::updateValue('PSCOMBEXPORT_CONFIG', json_encode($config));
            $this->context->controller->confirmations[] = $this->l('Settings updated');
        }

        $ctx     = Context::getContext();
        $idShop  = (int)$ctx->shop->id;
        $idLang  = (int)Tools::getValue('id_lang', (int)Configuration::get('PS_LANG_DEFAULT'));

        // Read values (supports POST/GET; we POST from the form to keep token valid)
        $productId   = (int)Tools::getValue('product_id', 0);
        $btnImg      = Tools::getValue('btn_img', '/images/checkout1.png'); // input kept for UX
        $btnLabel    = Tools::getValue('btn_label', 'OBJEDNAT');
        $addEmptyRow = (bool)Tools::getValue('add_empty_row', false);

        // Tabs Header
        $html = '<ul class="nav nav-tabs" role="tablist">
                    <li class="active"><a href="#tab_generator" role="tab" data-toggle="tab">'.$this->l('Generator').'</a></li>
                    <li><a href="#tab_list" role="tab" data-toggle="tab">'.$this->l('Active Products List').'</a></li>
                    <li><a href="#tab_settings" role="tab" data-toggle="tab">'.$this->l('Settings').'</a></li>
                    <li><a href="#tab_update" role="tab" data-toggle="tab">'.$this->l('Update').'</a></li>
                 </ul>';

        $html .= '<div class="tab-content">';

        // TAB 1: Generator
        $html .= '<div class="tab-pane active" id="tab_generator">';
        $html .= '<div class="panel" style="border-top:0">';
        $html .= '<h3><i class="icon icon-table"></i> '.$this->l('Single product → HTML table').'</h3>';
        $html .= $this->renderSingleProductForm($idLang, $productId, $btnImg, $btnLabel, $addEmptyRow);

        if ($productId > 0) {
            list($startsDisplay, $exportHtml, $previewHtml) = $this->buildSingleProductTables(
                $productId, $idShop, $idLang, $btnImg, $btnLabel, $addEmptyRow
            );
            $html .= $this->renderPreviewAndTextarea($previewHtml, $exportHtml, $startsDisplay, $productId, $idLang);
        }
        $html .= '</div>'; // end panel
        $html .= '</div>'; // end tab_generator

        // TAB 2: Active Products List
        $html .= '<div class="tab-pane" id="tab_list">';
        $html .= '<div class="panel" style="border-top:0">';
        $html .= '<h3><i class="icon icon-list"></i> '.$this->l('All Active Products').'</h3>';
        $html .= $this->renderActiveProductsList($idLang);
        $html .= '</div>';
        $html .= '</div>'; // end tab_list

        // TAB 3: Settings
        $html .= '<div class="tab-pane" id="tab_settings">';
        $html .= '<div class="panel" style="border-top:0">';
        $html .= '<h3><i class="icon icon-cogs"></i> '.$this->l('Customization Settings').'</h3>';
        $html .= $this->renderSettingsTab();
        $html .= '</div>';
        $html .= '</div>'; // end tab_settings

        // TAB 4: Update
        $html .= '<div class="tab-pane" id="tab_update">';
        $html .= '<div class="panel" style="border-top:0">';
        $html .= '<h3><i class="icon icon-refresh"></i> '.$this->l('Module Update').'</h3>';
        $html .= $this->renderUpdateTab();
        $html .= '</div>';
        $html .= '</div>'; // end tab_update

        $html .= '</div>'; // end tab-content

        return $html;
    }
}
