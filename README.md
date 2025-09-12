#  PsCombExport for PrestaShop

[![Version](https://img.shields.io/badge/version-3.3-blue.svg)](CHANGELOG.md)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![PrestaShop](https://img.shields.io/badge/PrestaShop-1.7%2B-F35372.svg)](https://www.prestashop.com/)
[![Ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/lukasgorazdhrodek)

> **Advanced Product Combinations Export & Embed Module**  
> Generate SEO-friendly HTML tables, manage course terms, and embed product schedules anywhere.

---

###  Languages / Jazyky
[ **Èeská dokumentace (Czech Documentation)**](README.cs.md)

---

##  Features

*   ** HTML Table Generator:** Instantly create copy-ready HTML tables from product combinations.
*   ** Smart Term Grouping:** Automatically groups combinations by "Course Start" date.
*   ** Embed Anywhere (v3.0+):** Generate clean, responsive `<iframe>` codes to display course schedules on external websites (WordPress, landing pages, etc.).
*   ** Active Products Dashboard (v3.3+):** A dedicated tab listing all active products with one-click "Copy Embed" buttons.
*   ** Stock Management:** Visual strikethrough and disabled buttons for out-of-stock items.
*   ** Smart Date Parsing:** Handles complex inputs like "PO+ÈT" (Mon+Thu) or "15:00-16:30".
*   ** Czech Localization:** Native support for Czech month declension (nominative/genitive).

##  Installation

1.  Download the latest `pscombexport-vX.X.zip` from [**Releases**](../../releases).
2.  Go to **Module Manager** in your PrestaShop admin.
3.  Upload the ZIP file.
4.  Click **Configure**.

>  **Detailed Guide:** See [INSTALL.md](INSTALL.md)

##  Usage

### 1. Generator Tab
Perfect for fine-tuning the output for a specific product.
*   Select a product.
*   Customize the button image and label.
*   Click **Generate** to get the HTML code or Embed URL.

### 2. Active Products List
Quickly access embed codes for your catalog.
*   Go to the **Active Products List** tab.
*   Find your product (categorized by default category).
*   Click **Copy Embed**.

### 3. Product Page Integration
*   Open any product in **Catalog > Products**.
*   Look for the **Pscombexport** tab or the **Kurzy Embed Code** block.
*   Copy the code directly from the product detail.

##  Configuration (Attributes)

The module relies on specific attribute group codes (slugs) to function correctly:

| Attribute Type | Recommended Slugs | Example Values |
| :--- | :--- | :--- |
| **Day** | `den` | `PO`, `Pondìlí`, `PO+ÈT` |
| **Time (From-To)** | `od_do`, `oddo`, `cas` | `15:00-16:30`, `1500_1630` |
| **Start Date** | `zacatek_kurzu`, `start_kurzu` | `29. záøí 2025`, `29_zari_2025` |

##  Contributing & Support

Found a bug? Have a feature request?

*    **[Submit an Issue](../../issues)**
*    **[Request a Feature](../../issues)**

If this module saved you time, consider supporting its development!

[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/lukasgorazdhrodek)

##  License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
