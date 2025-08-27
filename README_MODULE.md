# PsCombExport

**PrestaShop Module for Combinations Export to HTML Table**

## Description

Generate SEO-friendly HTML tables from product combinations with advanced course term management. Perfect for educational platforms and course management systems.

## Features

- 📊 Export single product combinations to HTML table
- 📅 Automatic grouping by course start dates
- 📆 Multi-day course support (PO+ČT, ÚT+ST, etc.)
- ⏰ Time normalization (HH:MM - HH:MM format)
- 🎯 SEO optimized with `<h2>` headings per term
- 🇨🇿 Full Czech month names support (nominative & genitive)
- 🖼️ Preview with absolute URLs, export with relative URLs
- 📋 One-click copy to clipboard

## Installation

1. Download the latest release ZIP file
2. Go to PrestaShop Admin → Modules → Module Manager
3. Click "Upload a module"
4. Select the downloaded ZIP file
5. Install and configure

## Configuration

The module expects the following attribute groups (by slug/name):

### Day (Den)
Supported formats:
- Abbreviations: `PO`, `ÚT`, `ST`, `ČT`, `PÁ`, `SO`, `NE`
- Full words: `pondělí`, `úterý`, `středa`, `čtvrtek`, `pátek`, `sobota`, `neděle`
- Combined: `po+čt`, `út/st`, `po,čt`
- Concatenated: `poct`, `utst`

### Time Range (Od-Do)
Attribute group slug: `od_do`, `oddo`, `cas`, `vyukovy_cas`, `kurz_cas`

Supported formats:
- `15:00 - 16:35`
- `15:00-16:35`
- `1500_1635`
- `15.00 16.35`

### Course Start (Začátek kurzu)
Attribute group slug: `zacatek_kurzu`, `zacatek`, `start_kurzu`, `startkurzu`

Supported formats:
- `29. září 2025`
- `29_zari_2025`
- `29_září_2025`
- `od_29_zari_2025`

### Supported Month Names

**Nominative (1st case):** leden, únor, březen, duben, květen, červen, červenec, srpen, září, říjen, listopad, prosinec

**Genitive (2nd case):** ledna, února, března, dubna, května, června, července, srpna, září, října, listopadu, prosince

## Usage

1. Select a product from the dropdown
2. Set the button image URL and label
3. Optionally add an empty row at the end
4. Click "Generate table"
5. Copy the generated HTML from the textarea
6. Paste into your content management system

## Output Example

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
...
</tbody>
</table>
</section>
```

## Technical Details

- **Version:** 2.2
- **Compatibility:** PrestaShop 1.7.0.0 - 8.x
- **Author:** Lukáš Gorazd Hrodek
- **License:** MIT

## Changelog

### Version 2.2
- ✅ Added genitive month forms support (ledna, února, etc.)
- ✅ Improved build scripts for Windows and Linux
- ✅ Added GitHub Actions workflow for automated releases
- ✅ Enhanced documentation

### Version 2.1
- ✅ Multi-day course support
- ✅ Robust Czech month name handling
- ✅ SEO optimization with `<h2>` headings

## Support

For bug reports or feature requests, please open an issue on GitHub.

## License

MIT License - feel free to use and modify for your needs.
