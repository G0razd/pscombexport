# Troubleshooting Guide - PsCombExport

## Chyba při instalaci: "Tento soubor není platným souborem zip modulu"

Tato chyba může mít několik příčin. Postupujte podle následujících kroků:

### 1. Ověřte strukturu ZIP souboru

ZIP soubor MUSÍ obsahovat složku s názvem modulu a soubory uvnitř:

```
pscombexport-v2.2.zip
└── pscombexport/
    ├── index.php
    └── pscombexport.php
```

**Zkontrolujte:**
```powershell
# Windows PowerShell
Expand-Archive -Path "pscombexport-v2.2.zip" -DestinationPath ".\test" -Force
Get-ChildItem -Path ".\test" -Recurse
Remove-Item -Path ".\test" -Recurse -Force
```

### 2. Zkontrolujte soubor pscombexport.php

Soubor MUSÍ obsahovat:

```php
<?php
if (!defined('_PS_VERSION_')) { exit; }

class PsCombExport extends Module
{
    public function __construct()
    {
        $this->name = 'pscombexport';
        // ... zbytek konstruktoru
    }

    public function install() { return parent::install(); }
    public function uninstall() { return parent::uninstall(); }
    
    // ... ostatní metody
}
```

**Důležité kontroly:**
- ✅ Třída se jmenuje `PsCombExport` (CamelCase)
- ✅ Soubor se jmenuje `pscombexport.php` (lowercase)
- ✅ `$this->name = 'pscombexport';` (lowercase)
- ✅ Složka se jmenuje `pscombexport` (lowercase)
- ✅ Třída rozšiřuje `Module`
- ✅ Má metody `install()` a `uninstall()`

### 3. Znovu vytvořte ZIP balíček

**Windows:**
```powershell
.\build.ps1
```

**Linux/Mac:**
```bash
chmod +x build.sh
./build.sh
```

### 4. Ověřte práva souborů (Linux/Mac)

```bash
chmod 644 pscombexport.php
chmod 644 index.php
```

### 5. Zkuste manuální instalaci

Pokud automatická instalace nefunguje:

1. Rozbalte ZIP na vašem počítači
2. Připojte se přes FTP/SFTP k serveru
3. Nahrajte složku `pscombexport` do `/modules/`
4. V PrestaShop Admin přejděte na Moduly → Module Manager
5. Vyhledejte "pscombexport"
6. Klikněte na "Install"

### 6. Zkontrolujte logy PrestaShop

Podívejte se do logů pro podrobnější informace:

```
/var/logs/
```

Nebo v Admin:
**Pokročilé parametry → Logy**

### 7. Zkontrolujte PHP syntaxi

```bash
php -l pscombexport.php
```

Mělo by vrátit:
```
No syntax errors detected in pscombexport.php
```

### 8. Zkontrolujte kompatibilitu PHP verze

Modul vyžaduje:
- PHP 7.1 nebo vyšší
- PrestaShop 1.7.0.0 nebo vyšší

Zkontrolujte verzi PHP:
```bash
php -v
```

### 9. Vyčistěte cache PrestaShop

V Admin:
1. **Pokročilé parametry → Výkon**
2. Klikněte na **Vyčistit cache**
3. Zkuste instalaci znovu

### 10. Zkontrolujte encoding souborů

Soubory MUSÍ být v UTF-8 bez BOM:

**Windows (PowerShell):**
```powershell
$content = Get-Content -Path "pscombexport.php" -Raw
$content | Out-File -FilePath "pscombexport.php" -Encoding UTF8NoBOM
```

### 11. Zkuste alternativní způsob vytvoření ZIP

**Windows (7-Zip):**
```cmd
7z a -tzip pscombexport-v2.2.zip pscombexport\
```

**Linux:**
```bash
zip -r pscombexport-v2.2.zip pscombexport/
```

### 12. Zkontrolujte, zda ZIP není poškozený

**Windows:**
```powershell
Test-Archive -Path "pscombexport-v2.2.zip"
```

**Linux:**
```bash
unzip -t pscombexport-v2.2.zip
```

## Další běžné problémy

### Modul se nainstaloval, ale nezobrazuje se v konfiguraci

Zkontrolujte, že:
- Máte metodu `getContent()` v třídě
- Vrací nějaký HTML obsah

### Modul zobrazuje prázdnou stránku

- Zkontrolujte PHP error log
- Zapněte debug mode v PrestaShop
- Zkontrolujte syntaxi PHP

### Kombinace produktů se nezobrazují

- Ověřte, že produkt má kombinace
- Zkontrolujte názvy skupin atributů (den, od_do, zacatek_kurzu)
- Ověřte, že atributy mají správný formát

## Kontakt pro podporu

Pokud problém přetrvává:

1. Otevřete issue na GitHub: https://github.com/G0razd/pscombexport/issues
2. Uveďte:
   - Verzi PrestaShop
   - Verzi PHP
   - Kompletní chybovou zprávu
   - Screenshot chyby
   - Obsah ZIP souboru (seznam souborů)

## Debug režim

Pro detailnější informace zapněte debug v PrestaShop:

1. Upravte soubor `/config/defines.inc.php`
2. Změňte:
```php
define('_PS_MODE_DEV_', true);
```

3. V Admin: **Pokročilé parametry → Výkon**
4. Nastavte **Debug mode** na **Ano**
5. Zkuste instalaci znovu - uvidíte podrobnější chyby

---

**Tip:** Nejčastější příčinou je špatná struktura ZIP nebo neshoda mezi názvem třídy a názvem souboru/složky.
