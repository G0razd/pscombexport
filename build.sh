#!/bin/bash
# Build script for PsCombExport PrestaShop module
# Creates a release-ready ZIP package

MODULE_NAME="pscombexport"
VERSION="2.2"
OUTPUT_DIR="./release"
TEMP_DIR="./temp_build"

echo "Building $MODULE_NAME v$VERSION..."

# Clean up old builds
if [ -d "$OUTPUT_DIR" ]; then
    echo "Cleaning old release directory..."
    rm -rf "$OUTPUT_DIR"/*
else
    mkdir -p "$OUTPUT_DIR"
fi

if [ -d "$TEMP_DIR" ]; then
    rm -rf "$TEMP_DIR"
fi
mkdir -p "$TEMP_DIR/$MODULE_NAME"

# Copy module files
echo "Copying module files..."
FILES_TO_INCLUDE=(
    "pscombexport.php"
    "index.php"
	"logo.png"
)

for file in "${FILES_TO_INCLUDE[@]}"; do
    if [ -f "$file" ]; then
        cp "$file" "$TEMP_DIR/$MODULE_NAME/"
        echo "  ✓ $file"
    fi
done

# Create index.php if it doesn't exist (PrestaShop security requirement)
if [ ! -f "index.php" ]; then
    echo "Creating index.php for security..."
    cat > "$TEMP_DIR/$MODULE_NAME/index.php" << 'EOF'
<?php
/**
 * Security: Prevent directory browsing
 */
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Location: ../');
exit;
EOF
fi

# Create ZIP package
ZIP_FILE_NAME="$MODULE_NAME-v$VERSION.zip"
ZIP_PATH="$OUTPUT_DIR/$ZIP_FILE_NAME"

echo "Creating ZIP package..."
cd "$TEMP_DIR" && zip -r "../$ZIP_PATH" "$MODULE_NAME" && cd ..

# Clean up temp directory
rm -rf "$TEMP_DIR"

# Display results
if [ -f "$ZIP_PATH" ]; then
    ZIP_SIZE=$(du -k "$ZIP_PATH" | cut -f1)
    echo ""
    echo "✓ Build complete!"
    echo "  Package: $ZIP_PATH"
    echo "  Size: ${ZIP_SIZE} KB"
    echo ""
    echo "Ready for distribution to PrestaShop modules directory."
else
    echo "Error: Failed to create ZIP package"
    exit 1
fi
