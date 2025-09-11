# PsCombExport - PrestaShop Module

[🇨🇿 Česká verze (Czech Version)](README.cs.md)

PrestaShop module for exporting product combinations into an HTML table with advanced course term handling.

## Features

- Export combinations of a single product to an HTML table.
- **Embed Support (v3.0+):** Generate iframe embed codes for external sites.
- **Active Products List (v3.3+):** View all active products categorized by default category with quick "Copy Embed" buttons.
- Automatic grouping by course terms (Course Start).
- Support for multi-day courses (Mon+Thu, Tue+Wed, etc.).
- Time normalization (From-To format HH:MM - HH:MM).
- SEO optimized HTML with `<h2>` headings for each term.
- Support for Czech month names (nominative and genitive).
- Preview with absolute image URLs.
- Export with relative URLs for portability.
- **Stock Handling:** Visual strikethrough for out-of-stock items.

## Installation

1. Download the latest version from [Releases](../../releases).
2. Log in to PrestaShop administration.
3. Go to **Modules → Module Manager**.
4. Click **Upload a module**.
5. Select the downloaded ZIP file `pscombexport-vX.X.zip`.
6. Click **Upload this module**.
7. After uploading, click **Configure**.

Detailed instructions: [INSTALL.md](INSTALL.md) (Czech)  
Documentation: [DOKUMENTACE.md](DOKUMENTACE.md) (Czech)  
Troubleshooting: [TROUBLESHOOTING.md](TROUBLESHOOTING.md) (Czech)

## Usage

### Generator Tab
1. Select a product in the module configuration.
2. Set the button image URL and label.
3. Choose whether to add an empty row at the end.
4. Click **Generate table**.
5. Copy the generated HTML code or the Embed URL/Iframe code.

### Active Products List Tab
1. Switch to the "Active Products List" tab.
2. Browse products categorized by their default category.
3. Click the **Copy Embed** button to instantly get the iframe code.

### Product Page
1. Go to **Catalog → Products**.
2. Open any product.
3. Scroll to the bottom of the "Basic settings" tab (or check the "Pscombexport" tab).
4. Copy the Embed Code from the provided block.

## Supported Attributes

The module expects the following attribute groups (by slug/name):

- **Day**: `den` (supports formats: PO, pondělí, po+čt, po/st, etc.)
- **From-To**: `od_do`, `oddo`, `cas` (supports formats: 15:00-16:35, 1500_1635, etc.)
- **Course Start**: `zacatek_kurzu`, `zacatek`, `start_kurzu` (supports formats: 29. září 2025, 29_zari_2025, etc.)

### Supported Month Formats (Czech)

**Nominative:** leden, únor, březen, duben, květen, červen, červenec, srpen, září, říjen, listopad, prosinec  
**Genitive:** ledna, února, března, dubna, května, června, července, srpna, září, října, listopadu, prosince

## Project Structure

```
pscombexport/
├── pscombexport.php      # Main module file
├── index.php             # Security file
├── build.js              # Node.js build script
├── README.md             # This file (English)
├── README.cs.md          # Czech README
├── INSTALL.md            # Installation instructions
├── DOKUMENTACE.md        # Documentation
├── CHANGELOG.md          # Changelog
└── .github/
    └── workflows/
        └── release.yml   # GitHub Actions workflow
```

## Development

### Build

To create a release package, use:

**Node.js:**
```bash
node build.js
```

The package will be created in the `release/` directory.

### GitHub Actions

The project includes an automated workflow for creating release packages:

#### Automatic release on tag creation:
```bash
git tag v3.3
git push origin v3.3
```
