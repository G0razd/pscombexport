const fs = require("fs-extra");
const path = require("path");
const archiver = require("archiver");

const MAIN_FILE = "pscombexport.php";
const DIST_DIR = "release";
const BUILD_DIR = "temp_build";

async function build() {
  console.log("🚀 Starting build process...");

  // 1. Read configuration from main PHP file
  if (!fs.existsSync(MAIN_FILE)) {
    console.error(`❌ Error: Main file ${MAIN_FILE} not found!`);
    process.exit(1);
  }

  const content = fs.readFileSync(MAIN_FILE, "utf8");

  // Extract Module Name
  const nameMatch = content.match(/\$this->name\s*=\s*['"](.*?)['"]/);
  if (!nameMatch) {
    console.error(
      `❌ Error: Could not find module name ($this->name) in ${MAIN_FILE}`
    );
    process.exit(1);
  }
  const moduleName = nameMatch[1];

  // Extract Version
  const versionMatch = content.match(/\$this->version\s*=\s*['"](.*?)['"]/);
  if (!versionMatch) {
    console.error(
      `❌ Error: Could not find version ($this->version) in ${MAIN_FILE}`
    );
    process.exit(1);
  }
  const version = versionMatch[1];

  console.log(`ℹ️  Module: ${moduleName}`);
  console.log(`ℹ️  Version: ${version}`);

  // 2. Prepare directories
  const outputDir = path.resolve(__dirname, DIST_DIR);
  const tempDir = path.resolve(__dirname, BUILD_DIR);
  const moduleDir = path.join(tempDir, moduleName);

  // Clean up
  await fs.remove(outputDir);
  await fs.remove(tempDir);

  // Create directories
  await fs.ensureDir(outputDir);
  await fs.ensureDir(moduleDir);

  // 3. Copy files
  console.log("📂 Copying files...");

  const filesToCopy = [
    "pscombexport.php",
    "index.php",
    "logo.png",
    "LICENSE",
    "README.md",
    "README.cs.md",
    "README_MODULE.md",
    "CHANGELOG.md",
    "INSTALL.md",
    "DOKUMENTACE.md",
    "TROUBLESHOOTING.md",
  ];

  const foldersToCopy = [
    "views",
    "controllers",
    "classes",
    "upgrade",
    "translations",
    "sql",
  ];

  // Copy individual files
  for (const file of filesToCopy) {
    if (fs.existsSync(file)) {
      await fs.copy(file, path.join(moduleDir, file));
      console.log(`  + ${file}`);
    }
  }

  // Copy folders
  for (const folder of foldersToCopy) {
    if (fs.existsSync(folder)) {
      await fs.copy(folder, path.join(moduleDir, folder));
      console.log(`  + ${folder}/`);
    }
  }

  // 4. Create ZIP
  const zipFileName = `${moduleName}-v${version}.zip`;
  const zipPath = path.join(outputDir, zipFileName);
  const output = fs.createWriteStream(zipPath);
  const archive = archiver("zip", {
    zlib: { level: 9 }, // Sets the compression level.
  });

  output.on("close", function () {
    const size = archive.pointer() / 1024;
    console.log("\n✅ Build complete!");
    console.log(`📦 Package: ${zipPath}`);
    console.log(`📊 Size: ${size.toFixed(2)} KB`);
    console.log("\nReady for distribution to PrestaShop modules directory.");
  });

  archive.on("error", function (err) {
    throw err;
  });

  archive.pipe(output);

  // Append the module directory to the zip
  // This ensures the zip root contains the folder 'pscombexport'
  archive.directory(moduleDir, moduleName);

  await archive.finalize();

  // Cleanup temp dir (optional, maybe wait for zip to finish?)
  // Note: archive.finalize() is async but the stream close event handles the completion log.
  // We shouldn't delete temp dir immediately if streaming from it, but archiver buffers or handles it.
  // However, to be safe, we can leave it or delete it in the 'close' event.
  // For simplicity in this script, we'll leave the temp build folder or delete it on next run.
}

build().catch((err) => {
  console.error(err);
  process.exit(1);
});
