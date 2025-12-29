# Changelog

All notable changes to the PsCombExport PrestaShop module will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.5.5] - 2025-12-29
### Added
- Grouping Configuration: Option to change the grouping attribute name (default: `zacatek_kurzu`).
- Grouping Configuration: Option to change the grouping title prefix (default: `Začátek kurzu: `).

## [3.5.4] - 2025-12-29
### Changed
- Default column labels and section headings are now in Czech (Číslo, Název, Den, Čas, Objednat, Začátek kurzu).

## [3.5.3] - 2025-12-29
### Fixed
- Iframe embed links now properly redirect the parent window (`target="_top"`).
- Reverted day name localization in table content to keep original format (PO, ÚT, etc.).

## [3.5.2] - 2025-12-01
### Fixed
- Switched to `ncipollo/release-action` to support `allowUpdates` and `removeArtifacts` in CI/CD.
- Updated module description.

## [3.5.1] - 2025-11-10
### Added
- Pre-commit hook to enforce version bumping on file changes.
- Improved localization by wrapping hardcoded strings in `l()` and using English defaults.

## [3.5.0] - 2025-10-01
### Refactored
- Split main class into traits for better maintainability.
- Fixed GitHub Action and README encoding.

## [3.4.0] - 2025-09-13
### Added
- Auto-update check from GitHub.
- CI: Auto-release when version in `pscombexport.php` changes.
### Changed
- Updated module name to 'Product Combinations Export plugin for PrestaShop'.
- Refined README.md with badges, Ko-fi button, and debloated structure.
- Added English README, renamed Czech README, setup translations structure.

## [3.3.0] - 2025-09-08
### Added
- Active Products List tab with categorized tables and copy embed buttons.
- Ko-fi username to FUNDING.yml.
### Changed
- Updated GitHub Action to use Node.js build script.

## [3.2.0] - 2025-09-06
### Added
- Auto-register hooks.
- Fallback tab for product page embed code.

## [3.1.0] - 2025-09-05
### Added
- Embed code copy button to product administration page.

## [3.0.0] - 2025-09-03
### Added
- Embed endpoint with logical slug structure and copy button.
### Changed
- Refined embed styles (blue links, bigger title) and iframe code generation.

## [2.6.0] - 2025-09-02
### Changed
- Improved stock handling visuals (row-based strikethrough).

## [2.5.0] - 2025-09-01
### Added
- Stock handling with cell-based strikethrough.

## [1.0.0] - 2025-08-25
### Added
- Initial release.
- Module creation.
- Fix: update months genitiv.
