<?php
if (!defined('_PS_VERSION_')) { exit; }

class PsCombExport extends Module
{
    public function __construct()
    {
        $this->name = 'pscombexport';
        $this->tab = 'administration';
        $this->version = '3.4'; // auto-update from github
        $this->author = 'Lukáš Gorazd Hrodek';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Combinations Export (Single Product HTML Table)');
        $this->description = $this->l('Generate a copy-ready HTML table for one product: Číslo / Název / Den / Od-Do + Začátky kurzu (grouped by term); preview absolute image, export relative; robust combined-day handling and proper admin token.');
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

        // TAB 3: Update
        $html .= '<div class="tab-pane" id="tab_update">';
        $html .= '<div class="panel" style="border-top:0">';
        $html .= '<h3><i class="icon icon-refresh"></i> '.$this->l('Module Update').'</h3>';
        $html .= $this->renderUpdateTab();
        $html .= '</div>';
        $html .= '</div>'; // end tab_update

        $html .= '</div>'; // end tab-content

        return $html;
    }

    /* ========================== UI ========================== */

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

    /* ====================== TABLE BUILDING ====================== */

    public function buildSingleProductTables($productId, $idShop, $idLang, $btnImg, $btnLabel, $addEmptyRow)
    {
        $ctx  = Context::getContext();
        $link = $ctx->link;
        $prod = new Product((int)$productId, false, (int)$idLang, (int)$idShop);
        if (!Validate::isLoadedObject($prod)) {
            $err = '<div class="alert alert-danger">'.$this->l('Product not found.').'</div>';
            return ['', $err, $err];
        }

        $comb = $prod->getAttributeCombinations($idLang) ?: [];

        // Collect per-combination data and gather distinct starts
        $byIpa = [];
        $starts = []; // key (YYYYMMDD or 00000000) => ['disp'=>..., 'rows'=>[]]
        foreach ($comb as $c) {
            $ipa = (int)$c['id_product_attribute'];
            if (!isset($byIpa[$ipa])) {
                $byIpa[$ipa] = [
                    'ipa'        => $ipa,              // original combination id (Číslo)
                    'reference'  => (string)$c['reference'],
                    'den'        => '',
                    'oddo'       => '',
                    'start_key'  => '',
                    'start_disp' => '',
                    'quantity'   => (int)$c['quantity'],
                ];
            }
            $groupSlug = $this->slugSimple($c['group_name']);
            $val = (string)$c['attribute_name'];

            if ($this->isDayGroup($groupSlug)) {
                $byIpa[$ipa]['den'] = $this->normalizeDayDisplay($val); // supports "po+čt", separators, words
            }
            if ($this->isOdDoGroup($groupSlug)) {
                $byIpa[$ipa]['oddo'] = $this->normalizeOdDo($val);
            }
            if ($this->isStartGroup($groupSlug)) {
                list($disp, $key) = $this->normalizeStartDateParts($val);
                if ($key !== '') {
                    $byIpa[$ipa]['start_key']  = $key;
                    $byIpa[$ipa]['start_disp'] = $disp;
                    $starts[$key] = ['disp'=>$disp, 'rows'=>[]];
                }
            }
        }

        // Fallbacks
        foreach ($byIpa as $ipa => &$row) {
            if ($row['oddo'] === '') $row['oddo'] = $this->guessOdDoFromAny($ipa, $comb);
            if ($row['den']  === '') $row['den']  = $this->normalizeDayDisplay($this->guessDayFromAny($ipa, $comb));
        } unset($row);

        // Export relative path forced; Preview absolute path forced.
        $exportImgPath  = '/images/checkout1.png';
        $previewImgPath = 'https://abakuslearning.cz/images/checkout1.png';

        // Distribute rows into term buckets
        foreach ($byIpa as $ipa => $data) {
            $url = $link->getProductLink($prod, null, null, null, $idLang, $idShop, (int)$ipa, false, false, true);

            $row = [
                'cislo'  => $data['ipa'],       // ORIGINAL COMBINATION ID
                'nazev'  => $prod->name,
                'den'    => $data['den'],
                'oddo'   => $data['oddo'],
                'link'   => $url,
                'quantity' => $data['quantity'],
            ];

            $key = $data['start_key'] ?: '00000000';
            $disp= $data['start_disp'] ?: '';
            if (!isset($starts[$key])) { $starts[$key] = ['disp'=>$disp, 'rows'=>[]]; }
            $starts[$key]['rows'][] = $row;
        }

        // Sort starts by date key ascending (00000000 bucket last)
        ksort($starts, SORT_STRING);

        // Within each start: sort by day sequence (multi-token compare), then start time
        foreach ($starts as &$bucket) {
            usort($bucket['rows'], function($a,$b){
                $dc = $this->compareDaySequence($a['den'], $b['den']);
                if ($dc !== 0) return $dc;
                return $this->timeStartKey($a['oddo']) - $this->timeStartKey($b['oddo']);
            });
        } unset($bucket);

        // Build export & preview HTML with <h2> per term (SEO-friendly sections)
        $exportHtml = '';
        $previewHtml = '';
        $sectionIndex = 0;
        $totalSections = count($starts);

        foreach ($starts as $key => $bucket) {
            $disp = trim($bucket['disp']);
            $sectionIndex++;
            $heading = $disp !== '' ? 'Začátek kurzu: '.$disp : $this->l('Termín nezjištěn');

            $exportHtml  .= $this->sectionTable($heading, $bucket['rows'], $exportImgPath, $btnLabel, false, $sectionIndex, $addEmptyRow);
            $previewHtml .= $this->sectionTable($heading, $bucket['rows'], $previewImgPath, $btnLabel, true,  $sectionIndex, $addEmptyRow);

            if ($sectionIndex < $totalSections) {
                $exportHtml  .= "\n<hr />\n";
                $previewHtml .= "\n<hr />\n";
            }
        }

        // “Začátky kurzu” inline summary
        $startsDisplay = implode(', ', array_values(array_map(
            function($b){ return $b['disp']; },
            array_filter($starts, function($k){ return $k !== '00000000'; }, ARRAY_FILTER_USE_KEY)
        )));

        return [$startsDisplay, $exportHtml, $previewHtml];
    }

    private function sectionTable($heading, array $rows, $imgPath, $btnLabel, $isPreview, $sectionIndex, $addEmptyRow)
    {
        $id = 'termin-'.$sectionIndex;
        $html  = '<section id="'.htmlspecialchars($id).'">'."\n";
        $html .= '<h2 style="margin:10px 0">'.htmlspecialchars($heading).'</h2>'."\n";
        $html .= '<table style="width: 100%; border-collapse: collapse;">'."\n";
        $html .= "<tbody>\n";
        $html .= "<tr>\n";
        $html .= '<td style="width: 50px;"><strong>Číslo</strong></td>'."\n";
        $html .= '<td style="width: 40%;"><strong>Název</strong></td>'."\n";
        $html .= '<td><strong>Den</strong></td>'."\n";
        $html .= '<td><strong>Od-Do</strong></td>'."\n";
        $html .= '<td>&nbsp;</td>'."\n";
        $html .= '<td colspan="2"><strong>KOUPIT</strong>&nbsp;</td>'."\n";
        $html .= "</tr>\n";

        foreach ($rows as $r) {
            $isOutOfStock = (isset($r['quantity']) && $r['quantity'] <= 0);
            // Apply style to TR for row-wide effect (visuals depend on client support)
            // We also add background to make it distinct.
            $rowStyle = $isOutOfStock ? ' style="text-decoration:line-through; color:#999; background-color:#f9f9f9;"' : '';
            
            // For cells, we might need to reinforce text-decoration if TR doesn't inherit in some contexts,
            // but user specifically asked to avoid "individual columns" look. 
            // Let's trust TR style + background.
            
            $html .= "<tr{$rowStyle}>\n";
            $html .= '<td>'.htmlspecialchars((string)$r['cislo'])."</td>\n";
            $html .= '<td>'.htmlspecialchars((string)$r['nazev'])."</td>\n";
            $html .= '<td>'.htmlspecialchars((string)$r['den'])."</td>\n";
            $html .= '<td>&nbsp;'.htmlspecialchars((string)$r['oddo'])."</td>\n";
            $html .= '<td>&nbsp;</td>'."\n";

            if ($isOutOfStock) {
                // Image: grayscale and opacity. 
                // Note: text-decoration:line-through on TR usually strikes through text in TD.
                // We keep the image style.
                $html .= '<td><img src="'.htmlspecialchars($imgPath).'" alt="checkout" width="24" height="24" style="opacity:0.5; filter:grayscale(100%)" /> '.
                          htmlspecialchars($btnLabel)."</td>\n";
            } else {
                $html .= '<td><a href="'.htmlspecialchars($r['link']).'"><img src="'.
                          htmlspecialchars($imgPath).'" alt="checkout" width="24" height="24" />'.
                          htmlspecialchars($btnLabel)."</a></td>\n";
            }

            $html .= '<td>&nbsp;</td>'."\n";
            $html .= "</tr>\n";
        }

        if ($addEmptyRow) {
            $html .= "<tr>\n<td>&nbsp;</td>\n<td>&nbsp;</td>\n<td>&nbsp;</td>\n<td>&nbsp;</td>\n<td>&nbsp;</td>\n<td>&nbsp;</td>\n<td>&nbsp;</td>\n</tr>\n";
        }

        $html .= "</tbody>\n</table>\n";
        $html .= "</section>\n";
        return $html;
    }

    /* ===================== Normalizers & utils ===================== */

    private function slugSimple($s)
    {
        $s = Tools::strtolower(trim($s));
        $s = Tools::replaceAccentedChars($s);
        return preg_replace('/[^a-z0-9]+/', '_', $s);
    }

    private function isDayGroup($groupSlug)
    {
        return (bool)preg_match('/(^|_)den(_|$)/', $groupSlug);
    }
    private function isOdDoGroup($groupSlug)
    {
        return in_array($groupSlug, ['od_do','oddo','cas','vyukovy_cas','kurz_cas'], true);
    }
    private function isStartGroup($groupSlug)
    {
        return in_array($groupSlug, ['zacatek_kurzu','zacatek','start_kurzu','startkurzu'], true);
    }

    /** Convert a day string (diacritics and separators "+", "/", ",") into display like "PO+ČT". */
    private function normalizeDayDisplay($value)
    {
        if ($value === '') return '';
        $raw = trim($value);

        // 1) Split by explicit separators first
        $sepPattern = '/\s*(\+|\/|,)\s*/u';
        if (preg_match($sepPattern, $raw)) {
            $tokens = preg_split($sepPattern, $raw);
            $parts = [];
            foreach ($tokens as $tok) {
                $disp = $this->normalizeDayToken($tok);
                if ($disp !== '') { $parts[] = $disp; }
            }
            $parts = array_values(array_unique($parts));
            return implode('+', $parts);
        }

        // 2) Try single token (full word or 2-letter)
        $single = $this->normalizeDayToken($raw);
        if ($single !== '') return $single;

        // 3) Parse concatenated pairs like "poct"
        $parts = [];
        $rest = Tools::replaceAccentedChars(Tools::strtolower($raw));
        $tokens = ['po','ut','st','ct','pa','so','ne'];
        while ($rest !== '') {
            $matched = false;
            foreach ($tokens as $t) {
                if (strpos($rest, $t) === 0) {
                    $parts[] = $this->dayTokenToDisplay($t);
                    $rest = substr($rest, strlen($t));
                    $matched = true;
                    break;
                }
            }
            if (!$matched) break;
        }
        if ($parts) {
            $parts = array_values(array_unique($parts));
            return implode('+', $parts);
        }

        // 4) Fallback
        return $this->normalizeDayShort($raw);
    }

    /** Map a single day token/word (with/without diacritics) to PO/ÚT/ST/ČT/PÁ/SO/NE. */
    private function normalizeDayToken($token)
    {
        $t = trim($token);
        if ($t === '') return '';
        $slug = $this->slugSimple($t);

        $map = [
            'po' => 'PO', 'pondeli' => 'PO',
            'ut' => 'ÚT', 'utery' => 'ÚT', 'uteri' => 'ÚT',
            'st' => 'ST', 'streda' => 'ST',
            'ct' => 'ČT', 'ctvrtek' => 'ČT',
            'pa' => 'PÁ', 'patek' => 'PÁ',
            'so' => 'SO', 'sobota' => 'SO',
            'ne' => 'NE', 'nedele' => 'NE', 'neděle' => 'NE',
        ];
        if (isset($map[$slug])) return $map[$slug];

        if (preg_match('/^[a-z]{2}$/', $slug)) {
            return $this->dayTokenToDisplay($slug);
        }
        return '';
    }

    /** Map 2-letter token to display WITH diacritics. */
    private function dayTokenToDisplay($t)
    {
        switch ($t) {
            case 'po': return 'PO';
            case 'ut': return 'ÚT';
            case 'st': return 'ST';
            case 'ct': return 'ČT';
            case 'pa': return 'PÁ';
            case 'so': return 'SO';
            case 'ne': return 'NE';
        }
        $two = Tools::strtoupper(Tools::substr($t, 0, 2));
        if ($two === 'UT') return 'ÚT';
        if ($two === 'CT') return 'ČT';
        if ($two === 'PA') return 'PÁ';
        return $two;
    }

    /** Fallback single-day normalizer. */
    private function normalizeDayShort($value)
    {
        $slug = $this->slugSimple($value);
        $map = [
            'po'=>'PO','pondeli'=>'PO',
            'ut'=>'ÚT','utery'=>'ÚT','uteri'=>'ÚT',
            'st'=>'ST','streda'=>'ST',
            'ct'=>'ČT','ctvrtek'=>'ČT',
            'pa'=>'PÁ','patek'=>'PÁ',
            'so'=>'SO','sobota'=>'SO',
            'ne'=>'NE','nedele'=>'NE','neděle'=>'NE'
        ];
        if (isset($map[$slug])) return $map[$slug];
        $two = Tools::strtoupper(Tools::substr($value, 0, 2));
        if     ($two === 'UT') return 'ÚT';
        elseif ($two === 'CT') return 'ČT';
        elseif ($two === 'PA') return 'PÁ';
        return $two;
    }

    private function normalizeOdDo($value)
    {
        $raw = trim($value);

        // "1500_1635"
        if (preg_match('/\b(\d{3,4})_(\d{3,4})\b/', $this->slugSimple($raw), $m)) {
            return $this->formatHm($m[1]).' - '.$this->formatHm($m[2]);
        }

        // "15:00 - 16:35" (or dot, en-dash)
        if (preg_match('/(\d{1,2})[:\.]?(\d{0,2})\s*[-–]\s*(\d{1,2})[:\.]?(\d{0,2})/', $raw, $m)) {
            $a = $this->formatHm(($m[1]??'0').($m[2]??'00'));
            $b = $this->formatHm(($m[3]??'0').($m[4]??'00'));
            return $a.' - '.$b;
        }

        // "15:00 16:35"
        if (preg_match('/(\d{1,2})[:\.]?(\d{0,2})\D+(\d{1,2})[:\.]?(\d{0,2})/', $raw, $m)) {
            $a = $this->formatHm(($m[1]??'0').($m[2]??'00'));
            $b = $this->formatHm(($m[3]??'0').($m[4]??'00'));
            return $a.' - '.$b;
        }

        return $raw;
    }

    private function guessOdDoFromAny($ipa, $comb)
    {
        foreach ($comb as $c) {
            if ((int)$c['id_product_attribute'] !== (int)$ipa) continue;
            $try = $this->normalizeOdDo($c['attribute_name']);
            if ($try !== $c['attribute_name']) return $try;
        }
        return '';
    }

    private function guessDayFromAny($ipa, $comb)
    {
        foreach ($comb as $c) {
            if ((int)$c['id_product_attribute'] !== (int)$ipa) continue;
            return $c['attribute_name']; // raw; caller normalizes
        }
        return '';
    }

    /** Return [display, key] where display is "29. září 2025" and key is "20250929" */
    private function normalizeStartDateParts($value)
    {
        $orig = trim($value);
        $slug = $this->slugSimple($orig);
        $slug = preg_replace('/^od_?/', '', $slug); // strip leading "od"

        // "29_zari_2025"
        if (preg_match('/^(\d{1,2})_([a-z]+)_(\d{4})$/', $slug, $m)) {
            $day = (int)$m[1];
            $monSlug = $m[2];
            $year = (int)$m[3];
            $monthNo = $this->czMonthNumber($monSlug);
            $monthGen = $this->czMonthGenitive($monSlug);
            if ($monthNo && $monthGen) {
                $key = sprintf('%04d%02d%02d', $year, (int)$monthNo, $day);
                $disp = sprintf('%d. %s %d', $day, $monthGen, $year);
                return [$disp, $key];
            }
        }

        // "29. září 2025"
        if (preg_match('/^(\d{1,2})\.\s*([[:alpha:]]+)\s+(\d{4})$/u', $orig, $m)) {
            $day = (int)$m[1];
            $monthWord = $this->slugSimple($m[2]);
            $year = (int)$m[3];
            $monthNo = $this->czMonthNumber($monthWord);
            if ($monthNo) {
                $key = sprintf('%04d%02d%02d', $year, (int)$monthNo, $day);
                return [$m[0], $key];
            }
        }

        // Fallback
        return [$orig, ''];
    }

    private function formatHm($digits)
    {
        $digits = preg_replace('/\D/','', (string)$digits);
        if (strlen($digits) === 3) { $digits = '0'.$digits; }
        if (strlen($digits) < 4) { $digits = str_pad($digits, 4, '0', STR_PAD_LEFT); }
        $h = (int)substr($digits,0,2);
        $m = (int)substr($digits,2,2);
        return sprintf('%02d:%02d', $h, $m);
    }

    /** Time key (HHMM as int) for start time inside "HH:MM - HH:MM". */
    private function timeStartKey($oddo)
    {
        if (preg_match('/^\s*(\d{1,2}):(\d{2})/', (string)$oddo, $m)) {
            return (int)($m[1].$m[2]);
        }
        return 9999;
    }

    /** Compare two day sequences like "PO+ST" vs "PO+ČT" using per-token weekday order; shorter wins if prefix-equal. */
    private function compareDaySequence($dispA, $dispB)
    {
        $a = $this->daySequenceToOrderList($dispA);
        $b = $this->daySequenceToOrderList($dispB);

        $len = min(count($a), count($b));
        for ($i = 0; $i < $len; $i++) {
            if ($a[$i] !== $b[$i]) {
                return ($a[$i] < $b[$i]) ? -1 : 1;
            }
        }
        if (count($a) !== count($b)) {
            return (count($a) < count($b)) ? -1 : 1;
        }
        return 0;
    }

    /** Convert "PO+ČT" to [1,4], "ÚT+ST" -> [2,3], handles diacritic-less fallbacks. */
    private function daySequenceToOrderList($disp)
    {
        $tokens = explode('+', (string)$disp);
        $out = [];
        foreach ($tokens as $t) {
            $t = trim($t);
            if ($t === '') continue;
            $out[] = $this->dayOrderSingle($t);
        }
        return $out ?: [99];
    }

    /** Order index for a single day token (supports diacritics + fallbacks). */
    private function dayOrderSingle($abbr)
    {
        $map = [
            'PO'=>1,
            'ÚT'=>2, 'UT'=>2,
            'ST'=>3,
            'ČT'=>4, 'CT'=>4,
            'PÁ'=>5, 'PA'=>5,
            'SO'=>6,
            'NE'=>7
        ];
        $u = Tools::strtoupper($abbr);
        if (isset($map[$u])) return (int)$map[$u];
        $ua = Tools::strtoupper(Tools::replaceAccentedChars($abbr));
        if (isset($map[$ua])) return (int)$map[$ua];
        return 99;
    }

    /** Czech month names (input slug without accents) -> genitive form for display. */
    private function czMonthGenitive($slug)
    {
        $m = [
            // Nominativ (1. pád)
            'leden'     => 'ledna',
            'unor'      => 'února',
            'brezen'    => 'března',
            'duben'     => 'dubna',
            'kveten'    => 'května',
            'cerven'    => 'června',
            'cervenec'  => 'července',
            'srpen'     => 'srpna',
            'zari'      => 'září',
            'rijen'     => 'října',
            'listopad'  => 'listopadu',
            'prosinec'  => 'prosince',
            // Genitiv (2. pád) - stejný výstup jako nominativ
            'ledna'     => 'ledna',
            'unora'     => 'února',
            'brezna'    => 'března',
            'dubna'     => 'dubna',
            'kvetna'    => 'května',
            'cervna'    => 'června',
            'července'  => 'července',
            'cervence'  => 'července',
            'srpna'     => 'srpna',
            'zari'      => 'září',
            'rijna'     => 'října',
            'listopadu' => 'listopadu',
            'prosince'  => 'prosince',
        ];
        return isset($m[$slug]) ? $m[$slug] : '';
    }

    /** Czech month names (input slug without accents) -> month number 1..12 */
    private function czMonthNumber($slug)
    {
        $m = [
            // Nominativ (1. pád)
            'leden'=>1,'unor'=>2,'brezen'=>3,'duben'=>4,'kveten'=>5,'cerven'=>6,
            'cervenec'=>7,'srpen'=>8,'zari'=>9,'rijen'=>10,'listopad'=>11,'prosinec'=>12,
            // Genitiv (2. pád)
            'ledna'=>1,'unora'=>2,'brezna'=>3,'dubna'=>4,'kvetna'=>5,'cervna'=>6,
            'července'=>7,'cervence'=>7,'srpna'=>8,'rijna'=>10,'listopadu'=>11,'prosince'=>12
        ];
        return isset($m[$slug]) ? (int)$m[$slug] : 0;
    }
}
