# PsCombExport - Installation Guide

## Quick Start

### Step 1: Download
Download the latest `pscombexport-vX.X.zip` file from the [Releases page](https://github.com/G0razd/pscombexport/releases).

### Step 2: Install in PrestaShop

1. **Login to PrestaShop Admin Panel**
   - Go to your PrestaShop admin area (usually `/admin` or custom admin folder)

2. **Navigate to Modules**
   - Click on **Modules** → **Module Manager** in the left menu

3. **Upload Module**
   - Click the **Upload a module** button (top right corner)
   - Click **select file** or drag and drop the ZIP file
   - Upload `pscombexport-vX.X.zip`

4. **Install**
   - PrestaShop will automatically extract and validate the module
   - Click **Install** when prompted
   - Wait for the installation to complete

5. **Configure**
   - After installation, click **Configure**
   - You'll be taken to the module configuration page

### Step 3: Configure Your Product

1. **Prepare Product Attributes**
   
   Your product must have the following attribute groups:
   
   - **Den** (Day): `po`, `út`, `st`, `čt`, `pá`, `so`, `ne` or combinations like `po+čt`
   - **Od-Do** (Time): Format like `15:00-16:35` or `1500_1635`
   - **Začátek kurzu** (Course Start): Format like `29. září 2025` or `29_zari_2025`

2. **Create Combinations**
   
   Example combination structure:
   ```
   Product: Kurz angličtiny
   
   Combination 1:
   - Den: PO+ČT
   - Od-Do: 15:00-16:35
   - Začátek kurzu: 29_zari_2025
   
   Combination 2:
   - Den: ÚT+ST
   - Od-Do: 16:00-17:30
   - Začátek kurzu: 29_zari_2025
   ```

### Step 4: Generate Table

1. **Select Product**
   - In module configuration, select your product from dropdown
   - Choose language ID (usually 1 for Czech)

2. **Configure Output**
   - Button image URL: `/images/checkout1.png` (or your custom path)
   - Button label: `OBJEDNAT` (or your custom text)
   - Optional: Check "Add empty row" if needed

3. **Generate**
   - Click **Generate table** button
   - Preview will show above the HTML code

4. **Copy HTML**
   - Click in the textarea with generated HTML
   - Select all (Ctrl+A) and copy (Ctrl+C)
   - Paste into your CMS page or product description

## Troubleshooting

### Module doesn't appear after upload
- Check PrestaShop error logs in `/var/logs/`
- Ensure ZIP file is not corrupted
- Verify PrestaShop version compatibility (1.7.0.0+)

### No combinations shown
- Verify product has attribute combinations created
- Check attribute group names match expected format
- Ensure product is active and visible

### Generated HTML looks wrong
- Verify attribute values follow supported formats
- Check for special characters in attribute names
- Review preview before copying export code

### Button image not showing
- Verify image path is correct and accessible
- Check file permissions on images directory
- Use absolute URL in preview, relative in export

## Build from Source

If you want to build from source code:

### Windows (PowerShell)
```powershell
.\build.ps1
```

### Linux/Mac (Bash)
```bash
chmod +x build.sh
./build.sh
```

Package will be created in `release/` directory.

## Support

- **GitHub Issues**: https://github.com/G0razd/pscombexport/issues
- **Documentation**: See README.md in repository
- **Author**: Lukáš Gorazd Hrodek

## License

MIT License - Free to use and modify.
