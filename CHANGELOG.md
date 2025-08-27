# Changelog

All notable changes to the PsCombExport PrestaShop module will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.2] - 2025-10-13

### Added
- Support for genitive month forms (ledna, února, března, etc.)
- Build scripts for Windows (PowerShell) and Linux (Bash)
- GitHub Actions workflow for automated release creation
- Comprehensive documentation (README.md, INSTALL.md, DOKUMENTACE.md)
- `.gitignore` file for clean repository
- `index.php` security file (PrestaShop requirement)

### Changed
- Improved month name detection to handle both nominative and genitive cases
- Updated `czMonthGenitive()` method to map genitive forms
- Updated `czMonthNumber()` method to recognize genitive forms
- Enhanced build process with proper file structure

### Fixed
- Date parsing now works with genitive month names (e.g., "29_ledna_2025")
- PowerShell build script syntax errors resolved

## [2.1] - 2024-XX-XX

### Added
- Multi-day course support (PO+ČT, ÚT+ST combinations)
- Robust Czech month name handling
- SEO optimization with `<h2>` section headings per term
- Proper admin token handling for CSRF protection

### Changed
- Improved day sequence comparison algorithm
- Enhanced time range normalization
- Better handling of combined day formats

### Fixed
- CSRF token validation in admin forms
- Day ordering for multi-day courses
- Time format parsing edge cases

## [2.0] - 2024-XX-XX

### Added
- Single product export functionality
- HTML table generation with preview and export modes
- Course term grouping by start date
- Automatic sorting by date, day, and time
- Configurable button image and label
- Optional empty row at table end

### Changed
- Complete rewrite from previous version
- Modern PrestaShop 1.7+ compatibility
- Bootstrap-based admin UI

## [1.0] - Initial Release

### Added
- Basic combination export functionality
- Simple HTML table generation

---

## Legend

- **Added**: New features
- **Changed**: Changes in existing functionality
- **Deprecated**: Soon-to-be removed features
- **Removed**: Removed features
- **Fixed**: Bug fixes
- **Security**: Security vulnerability fixes
