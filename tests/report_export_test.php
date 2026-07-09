<?php
$phpBinary = escapeshellarg(PHP_BINARY);
$tempFile = tempnam(sys_get_temp_dir(), 'pdf-export-');
$errorFile = tempnam(sys_get_temp_dir(), 'pdf-export-errors-');
$bootstrapPath = __DIR__ . '/../config/bootstrap.php';
$publicPath = __DIR__ . '/../public/index.php';
$scriptPath = tempnam(sys_get_temp_dir(), 'pdf-export-script-');
$scriptContent = <<<'PHP'
<?php
session_id('pdf-export-test');
require 'C:\laragon\www\sellingshop\config\bootstrap.php';
$_SESSION['user'] = ['id' => 1, 'name' => 'Administrator', 'email' => 'admin@example.com', 'role' => 'admin'];
$_GET = ['action' => 'reports', 'export' => 'pdf', 'type' => 'daily'];
require 'C:\laragon\www\sellingshop\public\index.php';
PHP;

file_put_contents($scriptPath, $scriptContent);
$command = $phpBinary . ' ' . escapeshellarg($scriptPath) . ' > ' . escapeshellarg($tempFile) . ' 2> ' . escapeshellarg($errorFile);
shell_exec($command);

if (!file_exists($tempFile)) {
    throw new Exception('PDF export test failed: the export output file was not created.');
}

$output = file_get_contents($tempFile);
$errors = file_get_contents($errorFile);
unlink($tempFile);
unlink($errorFile);
unlink($scriptPath);

if ($errors !== '' && trim($errors) !== '') {
    throw new Exception("PDF export test failed: {$errors}");
}

if ($output === '' || strpos($output, '%PDF-') !== 0) {
    throw new Exception('PDF export test failed: expected a PDF stream from the reports export endpoint.');
}

echo "PDF export test passed.\n";
