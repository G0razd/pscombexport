<?php
if (!defined('_PS_VERSION_')) { exit; }

trait PsCombExportUI
{
    private function renderSettingsTab()
    {
        $config = $this->getConfig();
        $action = $this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;

        $html = '<form method="post" action="'.$action.'" class="form-horizontal">';
        
        // Columns Section
        $html .= '<h4>'.$this->l('Columns Configuration').'</h4>';
        $html .= '<p class="help-block">'.$this->l('Enable/Disable columns and rename headers.').'</p>';
        
        foreach ($config['columns'] as $key => $col) {
            $html .= '<div class="form-group">';
            $html .= '<label class="control-label col-lg-2">'.ucfirst($key).'</label>';
            $html .= '<div class="col-lg-1">';
            $html .= '<span class="switch prestashop-switch fixed-width-lg">';
            $html .= '<input type="radio" name="columns['.$key.'][active]" id="col_'.$key.'_on" value="1" '.($col['active'] ? 'checked="checked"' : '').'>';
            $html .= '<label for="col_'.$key.'_on">Yes</label>';
            $html .= '<input type="radio" name="columns['.$key.'][active]" id="col_'.$key.'_off" value="0" '.(!$col['active'] ? 'checked="checked"' : '').'>';
            $html .= '<label for="col_'.$key.'_off">No</label>';
            $html .= '<a class="slide-button btn"></a>';
            $html .= '</span>';
            $html .= '</div>';
            $html .= '<div class="col-lg-3">';
            $html .= '<input type="text" name="columns['.$key.'][label]" value="'.htmlspecialchars($col['label']).'" class="form-control" placeholder="'.$this->l('Header Label').'">';
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '<hr>';

        // Styles Section
        $html .= '<h4>'.$this->l('Visual Styles').'</h4>';
        $html .= '<div class="form-group">';
        $html .= '<label class="control-label col-lg-2">'.$this->l('Header Background').'</label>';
        $html .= '<div class="col-lg-2"><input type="color" name="styles[header_bg]" value="'.$config['styles']['header_bg'].'" class="form-control"></div>';
        $html .= '</div>';

        $html .= '<div class="form-group">';
        $html .= '<label class="control-label col-lg-2">'.$this->l('Header Text Color').'</label>';
        $html .= '<div class="col-lg-2"><input type="color" name="styles[header_text]" value="'.$config['styles']['header_text'].'" class="form-control"></div>';
        $html .= '</div>';

        $html .= '<div class="form-group">';
        $html .= '<label class="control-label col-lg-2">'.$this->l('Row Background').'</label>';
        $html .= '<div class="col-lg-2"><input type="color" name="styles[row_bg]" value="'.$config['styles']['row_bg'].'" class="form-control"></div>';
        $html .= '</div>';

        $html .= '<div class="form-group">';
        $html .= '<label class="control-label col-lg-2">'.$this->l('Out of Stock Background').'</label>';
        $html .= '<div class="col-lg-2"><input type="color" name="styles[oos_bg]" value="'.$config['styles']['oos_bg'].'" class="form-control"></div>';
        $html .= '</div>';

        $html .= '<div class="form-group">';
        $html .= '<label class="control-label col-lg-2">'.$this->l('Out of Stock Text').'</label>';
        $html .= '<div class="col-lg-2"><input type="color" name="styles[oos_text]" value="'.$config['styles']['oos_text'].'" class="form-control"></div>';
        $html .= '</div>';

        $html .= '<div class="panel-footer">';
        $html .= '<button type="submit" name="submit_settings" class="btn btn-default pull-right"><i class="process-icon-save"></i> '.$this->l('Save Settings').'</button>';
        $html .= '</div>';

        $html .= '</form>';
        return $html;
    }

    private function renderUpdateTab()
    {
        $repoOwner = 'G0razd';
        $repoName = 'pscombexport';
        $apiUrl = "https://api.github.com/repos/$repoOwner/$repoName/releases/latest";
        
        // Simple caching to avoid hitting API limits too often
        $cacheId = 'pscombexport_update_check';
        $latestRelease = false;
        
        // In a real scenario, use PrestaShop Cache or a file. 
        // For now, we fetch live but suppress errors.
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: PrestaShop-Module-PsCombExport'
                ]
            ]
        ]);

        $json = @Tools::file_get_contents($apiUrl, false, $ctx);
        if ($json) {
            $data = json_decode($json, true);
            if (isset($data['tag_name'])) {
                $latestRelease = $data;
            }
        }

        $html = '';
        if ($latestRelease) {
            $latestVersion = ltrim($latestRelease['tag_name'], 'v');
            $currentVersion = $this->version;

            if (version_compare($latestVersion, $currentVersion, '>')) {
                $html .= '<div class="alert alert-warning">';
                $html .= '<h4>'.$this->l('New version available!').'</h4>';
                $html .= '<p>'.$this->l('Current version:').' <strong>'.$currentVersion.'</strong></p>';
                $html .= '<p>'.$this->l('Latest version:').' <strong>'.$latestVersion.'</strong></p>';
                $html .= '<p>'.$this->l('Release notes:').' <br>'.nl2br(htmlspecialchars($latestRelease['body'])).'</p>';
                $html .= '<br>';
                $html .= '<a href="'.$latestRelease['html_url'].'" target="_blank" class="btn btn-primary"><i class="icon-download"></i> '.$this->l('Download from GitHub').'</a>';
                $html .= '</div>';
            } else {
                $html .= '<div class="alert alert-success">';
                $html .= '<i class="icon-check"></i> '.$this->l('You are using the latest version.').' (v'.$currentVersion.')';
                $html .= '</div>';
            }
        } else {
            $html .= '<div class="alert alert-info">';
            $html .= $this->l('Could not check for updates. Please check manually on GitHub.');
            $html .= ' <a href="https://github.com/'.$repoOwner.'/'.$repoName.'/releases" target="_blank" class="alert-link">'.$this->l('Go to Releases').'</a>';
            $html .= '</div>';
        }

        return $html;
    }

    private function renderActiveProductsList($idLang)
    {
        $db = Db::getInstance();
        $products = $db->executeS('
            SELECT p.id_product, pl.name, cl.name as category_name, pl.link_rewrite
            FROM '._DB_PREFIX_.'product p
            INNER JOIN '._DB_PREFIX_.'product_lang pl
                ON pl.id_product = p.id_product AND pl.id_lang = '.(int)$idLang.'
            LEFT JOIN '._DB_PREFIX_.'category_lang cl
                ON cl.id_category = p.id_category_default AND cl.id_lang = '.(int)$idLang.'
            WHERE p.active = 1
            ORDER BY cl.name ASC, pl.name ASC
        ');

        if (!$products) {
            return '<div class="alert alert-info">'.$this->l('No active products found.').'</div>';
        }

        $grouped = [];
        foreach ($products as $p) {
            $cat = $p['category_name'] ?: $this->l('Uncategorized');
            $grouped[$cat][] = $p;
        }

        $html = '';
        foreach ($grouped as $category => $items) {
            $html .= '<h4 style="margin-top:20px; border-bottom:1px solid #eee; padding-bottom:5px;">'.htmlspecialchars($category).'</h4>';
            $html .= '<table class="table table-striped table-hover">';
            $html .= '<thead><tr>
                        <th width="50">ID</th>
                        <th>'.$this->l('Name').'</th>
                        <th width="150" class="text-right">'.$this->l('Action').'</th>
                      </tr></thead>';
            $html .= '<tbody>';
            
            foreach ($items as $p) {
                $embedUrl = $this->context->link->getModuleLink('pscombexport', 'embed', ['id_product' => $p['id_product'], 'rewrite' => $p['link_rewrite']]);
                $iframeCode = '<iframe src="'.$embedUrl.'" width="100%" height="800" frameborder="0" style="border:0; overflow:hidden;" scrolling="no"></iframe>';
                $inputId = 'embed_code_'.$p['id_product'];

                $html .= '<tr>';
                $html .= '<td>'.(int)$p['id_product'].'</td>';
                $html .= '<td><a href="'.$this->context->link->getAdminLink('AdminProducts', true, ['id_product' => $p['id_product'], 'updateproduct' => 1]).'" target="_blank">'.htmlspecialchars($p['name']).'</a></td>';
                $html .= '<td class="text-right">';
                // Hidden input for copy
                $html .= '<input type="text" id="'.$inputId.'" value="'.htmlspecialchars($iframeCode).'" style="position:absolute; left:-9999px;">';
                $html .= '<button class="btn btn-default btn-sm" onclick="document.getElementById(\''.$inputId.'\').select();document.execCommand(\'copy\'); $(this).find(\'i\').text(\'check\'); setTimeout(()=>{$(this).find(\'i\').text(\'content_copy\')}, 2000);">';
                $html .= '<i class="material-icons" style="font-size:16px; vertical-align:middle;">content_copy</i> '.$this->l('Copy Embed');
                $html .= '</button>';
                $html .= '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        }

        return $html;
    }

    private function renderSingleProductForm($idLang, $productId, $btnImg, $btnLabel, $addEmptyRow)
    {
        $db = Db::getInstance();
        $products = $db->executeS('
            SELECT p.id_product, pl.name
            FROM '._DB_PREFIX_.'product p
            INNER JOIN '._DB_PREFIX_.'product_lang pl
                ON pl.id_product = p.id_product AND pl.id_lang = '.(int)$idLang.'
            WHERE p.active = 1
            ORDER BY pl.name ASC
            LIMIT 1000
        ');

        // Use tokenized admin URL and POST to satisfy CSRF protection
        $action = $this->context->link->getAdminLink('AdminModules', true);

        $html = '<form method="post" action="'.htmlspecialchars($action).'" class="form-horizontal" style="margin-top:15px">';

        // Keep BO routing params (token is already in $action)
        $html .= '<input type="hidden" name="configure" value="'.Tools::safeOutput($this->name).'">';
        $html .= '<input type="hidden" name="tab_module" value="'.Tools::safeOutput($this->tab).'">';
        $html .= '<input type="hidden" name="module_name" value="'.Tools::safeOutput($this->name).'">';

        $html .= '<div class="form-group">
                    <label class="control-label col-lg-2">'.$this->l('Language ID').'</label>
                    <div class="col-lg-3">
                      <input class="form-control" type="number" name="id_lang" value="'.(int)$idLang.'" min="1">
                    </div>
                  </div>';

        $html .= '<div class="form-group">
                    <label class="control-label col-lg-2">'.$this->l('Product').'</label>
                    <div class="col-lg-6">
                      <select class="form-control" name="product_id" required>
                        <option value="">'.$this->l('Select…').'</option>';
        foreach ($products as $p) {
            $sel = ($productId == (int)$p['id_product']) ? ' selected' : '';
            $html .= '<option value="'.(int)$p['id_product'].'"'.$sel.'>'.
                     htmlspecialchars(sprintf('[%d] %s', (int)$p['id_product'], $p['name'])).
                     '</option>';
        }
        $html .=     '</select>
                      <p class="help-block" style="margin:5px 0 0">'.
                      $this->l('List limited to 1000 products for speed.').
                      '</p>
                    </div>
                  </div>';

        $html .= '<div class="form-group">
                    <label class="control-label col-lg-2">'.$this->l('Button image URL (preview absolute, export relative)').'</label>
                    <div class="col-lg-6">
                      <input class="form-control" type="text" name="btn_img" value="'.Tools::safeOutput($btnImg).'">
                      <p class="help-block">'
                        .$this->l('Preview forces https://abakuslearning.cz/images/checkout1.png; export forces /images/checkout1.png for SEO/portability.').'
                      </p>
                    </div>
                  </div>';

        $html .= '<div class="form-group">
                    <label class="control-label col-lg-2">'.$this->l('Button label').'</label>
                    <div class="col-lg-3">
                      <input class="form-control" type="text" name="btn_label" value="'.Tools::getValue('btn_label', $btnLabel).'">
                    </div>
                  </div>';

        $html .= '<div class="form-group">
                    <div class="col-lg-offset-2 col-lg-6">
                      <div class="checkbox">
                        <label><input type="checkbox" name="add_empty_row" value="1"'.($addEmptyRow?' checked':'').'> '.
                        $this->l('Add final empty row (like your example)').'</label>
                      </div>
                    </div>
                  </div>';

        $html .= '<div class="form-group">
                    <div class="col-lg-offset-2 col-lg-6">
                      <button class="btn btn-primary" type="submit" name="submit_'.$this->name.'" value="1">
                        <i class="icon-magic"></i> '.$this->l('Generate table').'
                      </button>
                    </div>
                  </div>';

        $html .= '<p class="help-block" style="margin-top:10px">'.
                 $this->l('The table reads attribute groups (by slug/name):').
                 ' <code>den</code>, <code>od_do</code>, <code>zacatek_kurzu</code></p>';

        $html .= '</form>';
        return $html;
    }

    private function renderPreviewAndTextarea($previewHtml, $exportHtml, $startsDisplay, $productId = 0, $idLang = 0)
    {
        $html  = '<h4 style="margin-top:25px">'.$this->l('Preview (absolute image URL)').'</h4>';
        $html .= '<div style="border:1px solid #eee; padding:12px; max-height:45vh; overflow:auto; background:#fff">'.$previewHtml.'</div>';

        if ($productId > 0 && $idLang > 0) {
            $prod = new Product($productId, false, $idLang);
            $rewrite = $prod->link_rewrite;
            // Force front office link generation
            $embedUrl = $this->context->link->getModuleLink('pscombexport', 'embed', ['id_product' => $productId, 'rewrite' => $rewrite]);
            $iframeCode = '<iframe src="'.$embedUrl.'" width="100%" height="800" frameborder="0" style="border:0; overflow:hidden;" scrolling="no"></iframe>';
            
            $html .= '<h4 style="margin-top:20px">'.$this->l('Embed Code (Iframe)').'</h4>';
            $html .= '<div class="input-group">';
            $html .= '<input type="text" class="form-control" id="embedUrlInput" value="'.htmlspecialchars($iframeCode).'" readonly>';
            $html .= '<span class="input-group-btn">';
            $html .= '<button class="btn btn-default" type="button" onclick="document.getElementById(\'embedUrlInput\').select();document.execCommand(\'copy\');"><i class="icon-copy"></i> '.$this->l('Copy').'</button>';
            $html .= '</span>';
            $html .= '</div>';
            $html .= '<p class="help-block">'.$this->l('Copy this code to embed the table. Adjust height="800" if needed.').'</p>';
        }

        $html .= '<h4 style="margin-top:20px">'.$this->l('Copy this HTML (relative /images/... for SEO & portability)').'</h4>';
        $html .= '<textarea class="form-control" rows="20" onclick="this.select()">'.htmlspecialchars($exportHtml).'</textarea>';

        if ($startsDisplay) {
            $html .= '<p class="help-block" style="margin-top:8px"><strong>'.$this->l('Začátky kurzu:').'</strong> '.htmlspecialchars($startsDisplay).'</p>';
        }
        return $html;
    }
}
