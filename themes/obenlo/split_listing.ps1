$filePath = "single-listing.php"
$lines = Get-Content -LiteralPath $filePath

New-Item -ItemType Directory -Force -Path "template-parts/listing"

function Write-Module {
    param($fileName, [int]$startLine, [int]$endLine)
    $content = [System.Collections.Generic.List[string]]::new()
    for ($j = $startLine - 1; $j -le $endLine - 1; $j++) {
        $content.Add($lines[$j])
    }
    Set-Content -Path $fileName -Value $content -Encoding UTF8
    Write-Host "Created $fileName"
}

# Extract Header
Write-Module "template-parts/listing/header.php" 157 245

# Extract Gallery
Write-Module "template-parts/listing/gallery.php" 247 353

# Extract Main Content
Write-Module "template-parts/listing/main-content.php" 356 631

# Extract Sidebar
Write-Module "template-parts/listing/sidebar.php" 633 1309

Write-Host "Rebuilding single-listing.php..."
$newLines = [System.Collections.Generic.List[string]]::new()

$skip = $false

for ($i = 0; $i -lt $lines.Length; $i++) {
    $lineNum = $i + 1

    if ($lineNum -eq 157) {
        $newLines.Add("        <?php include(locate_template('template-parts/listing/header.php')); ?>")
        $skip = $true
    } elseif ($lineNum -eq 246) {
        $skip = $false
        $newLines.Add($lines[$i])
    } elseif ($lineNum -eq 247) {
        $newLines.Add("        <?php include(locate_template('template-parts/listing/gallery.php')); ?>")
        $skip = $true
    } elseif ($lineNum -eq 354) {
        $skip = $false
        $newLines.Add($lines[$i])
    } elseif ($lineNum -eq 356) {
        $newLines.Add("            <?php include(locate_template('template-parts/listing/main-content.php')); ?>")
        $skip = $true
    } elseif ($lineNum -eq 632) {
        $skip = $false
        $newLines.Add($lines[$i])
    } elseif ($lineNum -eq 633) {
        $newLines.Add("            <?php include(locate_template('template-parts/listing/sidebar.php')); ?>")
        $skip = $true
    } elseif ($lineNum -eq 1310) {
        $skip = $false
        $newLines.Add($lines[$i])
    } else {
        if (-not $skip) {
            $newLines.Add($lines[$i])
        }
    }
}

Set-Content -Path $filePath -Value $newLines -Encoding UTF8
Write-Host "Done splitting single-listing.php"
