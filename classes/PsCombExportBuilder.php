<?php
if (!defined('_PS_VERSION_')) { exit; }

trait PsCombExportBuilder
{
    public function buildSingleProductTables($productId, $idShop, $idLang, $btnImg, $btnLabel, $addEmptyRow)
    {
        $config = $this->getConfig();
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

            $exportHtml  .= $this->sectionTable($heading, $bucket['rows'], $exportImgPath, $btnLabel, false, $sectionIndex, $addEmptyRow, $config);
            $previewHtml .= $this->sectionTable($heading, $bucket['rows'], $previewImgPath, $btnLabel, true,  $sectionIndex, $addEmptyRow, $config);

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

    private function sectionTable($heading, array $rows, $imgPath, $btnLabel, $isPreview, $sectionIndex, $addEmptyRow, $config = null)
    {
        if ($config === null) {
            $config = $this->getConfig();
        }
        
        $styles = $config['styles'];
        $columns = $config['columns'];

        $id = 'termin-'.$sectionIndex;
        $html  = '<section id="'.htmlspecialchars($id).'">'."\n";
        $html .= '<h2 style="margin:10px 0">'.htmlspecialchars($heading).'</h2>'."\n";
        $html .= '<table style="width: 100%; border-collapse: collapse;">'."\n";
        $html .= "<thead>\n";
        $html .= '<tr style="background-color: '.$styles['header_bg'].'; color: '.$styles['header_text'].';">'."\n";
        
        foreach ($columns as $key => $col) {
            if (!$col['active']) continue;
            
            $label = $col['label'];
            $style = '';
            
            if ($key == 'cislo') $style = 'width: 50px;';
            if ($key == 'nazev') $style = 'width: 40%;';
            
            if ($key == 'buy') {
                 $html .= '<td colspan="2" style="'.$style.'"><strong>'.htmlspecialchars($label).'</strong>&nbsp;</td>'."\n";
            } else {
                 $html .= '<td style="'.$style.'"><strong>'.htmlspecialchars($label).'</strong></td>'."\n";
            }
        }
        $html .= "</tr>\n";
        $html .= "</thead>\n";
        $html .= "<tbody>\n";

        foreach ($rows as $r) {
            $isOutOfStock = (isset($r['quantity']) && $r['quantity'] <= 0);
            
            $rowStyle = 'background-color: '.$styles['row_bg'].';';
            if ($isOutOfStock) {
                $rowStyle = 'text-decoration:line-through; color:'.$styles['oos_text'].'; background-color:'.$styles['oos_bg'].';';
            }
            
            $html .= "<tr style=\"{$rowStyle}\">\n";
            
            foreach ($columns as $key => $col) {
                if (!$col['active']) continue;
                
                switch ($key) {
                    case 'cislo':
                        $html .= '<td>'.htmlspecialchars((string)$r['cislo'])."</td>\n";
                        break;
                    case 'nazev':
                        $html .= '<td>'.htmlspecialchars((string)$r['nazev'])."</td>\n";
                        break;
                    case 'den':
                        $html .= '<td>'.htmlspecialchars((string)$r['den'])."</td>\n";
                        break;
                    case 'oddo':
                        $html .= '<td>&nbsp;'.htmlspecialchars((string)$r['oddo'])."</td>\n";
                        break;
                    case 'spacer':
                        $html .= '<td>&nbsp;</td>'."\n";
                        break;
                    case 'buy':
                        if ($isOutOfStock) {
                            $html .= '<td><img src="'.htmlspecialchars($imgPath).'" alt="checkout" width="24" height="24" style="opacity:0.5; filter:grayscale(100%)" /> '.
                                      htmlspecialchars($btnLabel)."</td>\n";
                        } else {
                            $html .= '<td><a href="'.htmlspecialchars($r['link']).'"><img src="'.
                                      htmlspecialchars($imgPath).'" alt="checkout" width="24" height="24" />'.
                                      htmlspecialchars($btnLabel)."</a></td>\n";
                        }
                        $html .= '<td>&nbsp;</td>'."\n";
                        break;
                }
            }
            $html .= "</tr>\n";
        }

        if ($addEmptyRow) {
             $html .= "<tr>\n";
             foreach ($columns as $key => $col) {
                 if (!$col['active']) continue;
                 if ($key == 'buy') {
                     $html .= "<td>&nbsp;</td>\n<td>&nbsp;</td>\n";
                 } else {
                     $html .= "<td>&nbsp;</td>\n";
                 }
             }
             $html .= "</tr>\n";
        }

        $html .= "</tbody>\n</table>\n";
        $html .= "</section>\n";
        return $html;
    }
}
