# PsCombExport - Dokumentace

## O modulu

PsCombExport je PrestaShop modul pro export kombinací produktu do přehledné HTML tabulky s pokročilým zpracováním termínů kurzů.

## Hlavní funkce

✅ Export kombinací jednoho produktu do HTML tabulky  
✅ Automatické seskupení podle termínů kurzu  
✅ Podpora vícedenních kurzů (PO+ČT, ÚT+ST, atd.)  
✅ Normalizace času (formát HH:MM - HH:MM)  
✅ SEO optimalizované HTML s nadpisy `<h2>` pro každý termín  
✅ Podpora českých názvů měsíců (nominativ i genitiv)  
✅ Náhled s absolutními URL obrázků  
✅ Export s relativními URL pro přenositelnost  

## Instalace

### Krok 1: Stažení
Stáhněte nejnovější verzi z [releases](https://github.com/G0razd/pscombexport/releases).

### Krok 2: Instalace do PrestaShop

1. Přihlaste se do administrace PrestaShop
2. Přejděte na **Moduly → Správce modulů**
3. Klikněte na **Nahrát modul**
4. Vyberte stažený ZIP soubor
5. Klikněte na **Instalovat**
6. Po instalaci klikněte na **Konfigurovat**

## Konfigurace atributů produktu

Modul očekává následující skupiny atributů:

### 1. Den (Day)
**Slug skupiny:** `den`

**Podporované formáty:**
- Zkratky: `PO`, `ÚT`, `ST`, `ČT`, `PÁ`, `SO`, `NE`
- Celá slova: `pondělí`, `úterý`, `středa`, `čtvrtek`, `pátek`, `sobota`, `neděle`
- Kombinace: `po+čt`, `út/st`, `po,čt`
- Spojené: `poct`, `utst`

**Příklady:**
```
PO          → PO
pondělí     → PO
po+čt       → PO+ČT
út/st       → ÚT+ST
poct        → PO+ČT
```

### 2. Čas (Od-Do)
**Slug skupiny:** `od_do`, `oddo`, `cas`, `vyukovy_cas`, `kurz_cas`

**Podporované formáty:**
- `15:00 - 16:35`
- `15:00-16:35`
- `1500_1635`
- `15.00 16.35`

**Příklady:**
```
15:00 - 16:35  → 15:00 - 16:35
1500_1635      → 15:00 - 16:35
15.00 16.35    → 15:00 - 16:35
```

### 3. Začátek kurzu
**Slug skupiny:** `zacatek_kurzu`, `zacatek`, `start_kurzu`, `startkurzu`

**Podporované formáty:**
- `29. září 2025`
- `29_zari_2025`
- `29_září_2025`
- `od_29_zari_2025`

**Podporované měsíce:**

| Nominativ | Genitiv  | Číslo |
|-----------|----------|-------|
| leden     | ledna    | 1     |
| únor      | února    | 2     |
| březen    | března   | 3     |
| duben     | dubna    | 4     |
| květen    | května   | 5     |
| červen    | června   | 6     |
| červenec  | července | 7     |
| srpen     | srpna    | 8     |
| září      | září     | 9     |
| říjen     | října    | 10    |
| listopad  | listopadu| 11    |
| prosinec  | prosince | 12    |

**Příklady:**
```
29_zari_2025      → 29. září 2025
29_ledna_2025     → 29. ledna 2025
15. února 2025    → 15. února 2025
od_1_unora_2025   → 1. února 2025
```

## Použití modulu

### 1. Výběr produktu
V konfiguraci modulu vyberte produkt z rozbalovacího seznamu.

### 2. Nastavení parametrů
- **ID jazyka**: Většinou 1 pro češtinu
- **Produkt**: Vyberte produkt s připravenými kombinacemi
- **URL obrázku tlačítka**: `/images/checkout1.png` (nebo vlastní cesta)
- **Text tlačítka**: `OBJEDNAT` (nebo vlastní text)
- **Přidat prázdný řádek**: Zaškrtněte, pokud chcete prázdný řádek na konci tabulky

### 3. Generování tabulky
1. Klikněte na **Generate table**
2. Prohlédněte si náhled nad textovým polem
3. Zkopírujte HTML kód z textového pole
4. Vložte do CMS stránky nebo popisu produktu

## Výstup

### Struktura HTML
```html
<section id="termin-1">
<h2 style="margin:10px 0">Začátek kurzu: 29. září 2025</h2>
<table style="width: 100%;">
<tbody>
<tr>
<td style="width: 50px;"><strong>Číslo</strong></td>
<td style="width: 40%;"><strong>Název</strong></td>
<td><strong>Den</strong></td>
<td><strong>Od-Do</strong></td>
<td>&nbsp;</td>
<td colspan="2"><strong>KOUPIT</strong>&nbsp;</td>
</tr>
<tr>
<td>123</td>
<td>Kurz angličtiny</td>
<td>PO+ČT</td>
<td>&nbsp;15:00 - 16:35</td>
<td>&nbsp;</td>
<td><a href="..."><img src="/images/checkout1.png" alt="checkout" width="24" height="24" />OBJEDNAT</a></td>
<td>&nbsp;</td>
</tr>
</tbody>
</table>
</section>
```

### SEO výhody
- Strukturovaný obsah s `<h2>` nadpisy
- Sémantické HTML značky
- Přehledné odkazy na kombinace produktu
- Relativní URL pro lepší přenositelnost

## Řazení

Modul automaticky řadí výstup podle:

1. **Termín kurzu** (vzestupně podle data)
2. **Den v týdnu** (PO, ÚT, ST, ČT, PÁ, SO, NE)
3. **Čas začátku** (nejdříve začínající kurzy první)

## Příklad konfigurace produktu

### Produkt: Kurz angličtiny pro začátečníky

**Kombinace 1:**
- Číslo: 1001
- Den: `po+čt`
- Od-Do: `15:00-16:35`
- Začátek kurzu: `29_zari_2025`

**Kombinace 2:**
- Číslo: 1002
- Den: `út+st`
- Od-Do: `16:00-17:30`
- Začátek kurzu: `29_zari_2025`

**Kombinace 3:**
- Číslo: 1003
- Den: `po+čt`
- Od-Do: `15:00-16:35`
- Začátek kurzu: `15_ledna_2026`

**Vygenerovaný výstup:**

```
Začátek kurzu: 29. září 2025
┌────────┬─────────────────────────────┬────────┬──────────────┬─────────┐
│ Číslo  │ Název                       │ Den    │ Od-Do        │ KOUPIT  │
├────────┼─────────────────────────────┼────────┼──────────────┼─────────┤
│ 1001   │ Kurz angličtiny pro...      │ PO+ČT  │ 15:00-16:35  │ [BTN]   │
│ 1002   │ Kurz angličtiny pro...      │ ÚT+ST  │ 16:00-17:30  │ [BTN]   │
└────────┴─────────────────────────────┴────────┴──────────────┴─────────┘

Začátek kurzu: 15. ledna 2026
┌────────┬─────────────────────────────┬────────┬──────────────┬─────────┐
│ Číslo  │ Název                       │ Den    │ Od-Do        │ KOUPIT  │
├────────┼─────────────────────────────┼────────┼──────────────┼─────────┤
│ 1003   │ Kurz angličtiny pro...      │ PO+ČT  │ 15:00-16:35  │ [BTN]   │
└────────┴─────────────────────────────┴────────┴──────────────┴─────────┘
```

## Časté problémy

### Modul se nezobrazí po nahrání
- Zkontrolujte logy v `/var/logs/`
- Ověřte kompatibilitu verze PrestaShop (1.7.0.0+)
- Zkontrolujte, zda ZIP soubor není poškozený

### Nezobrazují se žádné kombinace
- Ověřte, že produkt má vytvořené kombinace atributů
- Zkontrolujte názvy skupin atributů
- Ujistěte se, že produkt je aktivní

### Vygenerovaný HTML vypadá špatně
- Ověřte formát hodnot atributů
- Zkontrolujte speciální znaky v názvech atributů
- Prohlédněte si náhled před kopírováním exportu

### Obrázek tlačítka se nezobrazuje
- Ověřte, že cesta k obrázku je správná
- Zkontrolujte oprávnění k souboru
- Použijte absolutní URL v náhledu, relativní v exportu

## Technické informace

- **Verze:** 2.2
- **Kompatibilita:** PrestaShop 1.7.0.0 - 8.x
- **Autor:** Lukáš Gorazd Hrodek
- **Licence:** MIT
- **GitHub:** https://github.com/G0razd/pscombexport

## Podpora

Pro nahlášení chyb nebo požadavků na nové funkce použijte:
- GitHub Issues: https://github.com/G0razd/pscombexport/issues

## Licence

MIT License - modul je zdarma k použití a úpravám.
