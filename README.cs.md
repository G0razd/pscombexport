# PsCombExport - PrestaShop Module

PrestaShop modul pro export kombinací produktů do HTML tabulky s pokročilým zpracováním termínů kurzů.

## Features

- Export kombinací jednoho produktu do HTML tabulky
- Automatické seskupení podle termínů kurzu (Začátek kurzu)
- Podpora vícedenních kurzů (PO+ČT, ÚT+ST, atd.)
- Normalizace času (Od-Do formát HH:MM - HH:MM)
- SEO optimalizované HTML s `<h2>` nadpisy pro každý termín
- Podpora českých názvů měsíců (nominativ i genitiv)
- Preview s absolutními URL obrázků
- Export s relativními URL pro přenositelnost

## Instalace

1. Stáhněte nejnovější verzi z [Releases](../../releases)
2. Přihlaste se do administrace PrestaShop
3. Přejděte na **Moduly → Module Manager**
4. Klikněte na **Nahrát modul**
5. Vyberte stažený ZIP soubor `pscombexport-vX.X.zip`
6. Klikněte na **Nahrát tento modul**
7. Po nahrání klikněte na **Konfigurovat**

Podrobný návod: [INSTALL.md](INSTALL.md)  
Česká dokumentace: [DOKUMENTACE.md](DOKUMENTACE.md)  
Řešení problémů: [TROUBLESHOOTING.md](TROUBLESHOOTING.md)

## Použití

1. V konfiguraci modulu vyberte produkt
2. Nastavte URL obrázku tlačítka a text
3. Vyberte, zda chcete přidat prázdný řádek na konci
4. Klikněte na **Generate table**
5. Zkopírujte vygenerovaný HTML kód z textového pole

## Podporované atributy

Modul očekává následující skupiny atributů (podle slug/name):

- **Den**: `den` (podporuje formáty: PO, pondělí, po+čt, po/st, atd.)
- **Od-Do**: `od_do`, `oddo`, `cas` (podporuje formáty: 15:00-16:35, 1500_1635, atd.)
- **Začátek kurzu**: `zacatek_kurzu`, `zacatek`, `start_kurzu` (podporuje formáty: 29. září 2025, 29_zari_2025, 29_září_2025, atd.)

### Podporované formáty měsíců

**Nominativ:** leden, únor, březen, duben, květen, červen, červenec, srpen, září, říjen, listopad, prosinec  
**Genitiv:** ledna, února, března, dubna, května, června, července, srpna, září, října, listopadu, prosince

## Struktura projektu

```
pscombexport/
├── pscombexport.php      # Hlavní soubor modulu
├── index.php             # Bezpečnostní soubor
├── build.ps1             # Build skript pro Windows
├── build.sh              # Build skript pro Linux/Mac
├── README.md             # Tento soubor
├── INSTALL.md            # Instalační návod
├── DOKUMENTACE.md        # Česká dokumentace
├── CHANGELOG.md          # Historie změn
├── .gitignore            # Git ignore soubory
└── .github/
    └── workflows/
        └── release.yml   # GitHub Actions workflow
```

## Vývoj

### Build

Pro vytvoření release balíčku použijte:

**Windows (PowerShell):**
```powershell
.\build.ps1
```

**Linux/Mac:**
```bash
chmod +x build.sh
./build.sh
```

Balíček bude vytvořen v adresáři `release/`.

### GitHub Actions

Projekt obsahuje automatizovaný workflow pro vytváření release balíčků:

#### Automatický release při vytvoření tagu:
```bash
git tag v2.2
git push origin v2.2
```

#### Manuální build přes GitHub Actions:
1. Jděte na záložku **Actions**
2. Vyberte **Build and Release**
3. Klikněte **Run workflow**
4. Zadejte verzi (např. 2.2)

### Přidání loga modulu (volitelné)

Pokud chcete přidat logo pro modul v PrestaShop:

1. Vytvořte obrázek `logo.png` (doporučené rozměry: 57x57 px nebo 114x114 px)
2. Umístěte ho do kořenového adresáře modulu
3. Aktualizujte `build.ps1` a `build.sh` pro zahrnutí loga do balíčku

## Verze

**Aktuální verze: 2.2**

### Changelog

**2.2** (2025-10-13)
- ✅ Přidána podpora genitivních tvarů měsíců (ledna, února, atd.)
- ✅ Vylepšené build skripty pro Windows a Linux
- ✅ Přidán GitHub Actions workflow pro automatické release
- ✅ Kompletní dokumentace (EN + CZ)

**2.1**
- ✅ Podpora vícedenních kurzů
- ✅ Robustní zpracování českých názvů měsíců
- ✅ SEO optimalizace s `<h2>` nadpisy

Úplný changelog: [CHANGELOG.md](CHANGELOG.md)

## Kompatibilita

- **PrestaShop:** 1.7.0.0 - 8.x
- **PHP:** 7.1+
- **Bootstrap:** 3.x (PrestaShop Admin)

## Licence

MIT License

## Autor

Lukáš Gorazd Hrodek

## Podpora

Pro nahlášení chyb nebo požadavků na nové funkce použijte [Issues](../../issues).

---

**Vytvořeno s ❤️ pro PrestaShop komunitu**
