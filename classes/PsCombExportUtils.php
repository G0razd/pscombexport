<?php
if (!defined('_PS_VERSION_')) { exit; }

trait PsCombExportUtils
{
    private function getConfig()
    {
        $default = [
            'columns' => [
                'cislo' => ['active' => 1, 'label' => $this->l('Number')],
                'nazev' => ['active' => 1, 'label' => $this->l('Name')],
                'den' => ['active' => 1, 'label' => $this->l('Day')],
                'oddo' => ['active' => 1, 'label' => $this->l('Time')],
                'spacer' => ['active' => 1, 'label' => ''],
                'buy' => ['active' => 1, 'label' => $this->l('Buy')],
            ],
            'styles' => [
                'header_bg' => '#ffffff',
                'header_text' => '#000000',
                'row_bg' => '#ffffff',
                'oos_bg' => '#f9f9f9',
                'oos_text' => '#999999',
            ]
        ];

        $stored = Configuration::get('PSCOMBEXPORT_CONFIG');
        if ($stored) {
            $decoded = json_decode($stored, true);
            if ($decoded) {
                // Merge with default to ensure all keys exist
                return array_replace_recursive($default, $decoded);
            }
        }
        return $default;
    }

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
            'PO'=>1, 'MON'=>1,
            'ÚT'=>2, 'UT'=>2, 'TUE'=>2,
            'ST'=>3, 'WED'=>3,
            'ČT'=>4, 'CT'=>4, 'THU'=>4,
            'PÁ'=>5, 'PA'=>5, 'FRI'=>5,
            'SO'=>6, 'SAT'=>6,
            'NE'=>7, 'SUN'=>7
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
