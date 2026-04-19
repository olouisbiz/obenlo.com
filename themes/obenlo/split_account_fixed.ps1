$filePath = "page-account.php"

# Use .NET classes for pure UTF8 without BOM
$utf8NoBom = New-Object System.Text.UTF8Encoding $False
$lines = [System.IO.File]::ReadAllLines($filePath, $utf8NoBom)

New-Item -ItemType Directory -Force -Path "template-parts/account" | Out-Null

function Write-Module {
    param($fileName, [int]$startLine, [int]$endLine)
    $content = [System.Collections.Generic.List[string]]::new()
    $content.Add("<?php `$user = wp_get_current_user(); `$user_id = `$user->ID; ?>")
    
    for ($j = $startLine - 1; $j -le $endLine - 1; $j++) {
        $content.Add($lines[$j])
    }
    
    [System.IO.File]::WriteAllLines($fileName, $content, $utf8NoBom)
    Write-Host "Created $fileName"
}

Write-Module "template-parts/account/tab-dashboard.php" 108 153
Write-Module "template-parts/account/tab-profile.php" 155 189
Write-Module "template-parts/account/tab-trips.php" 191 328
Write-Module "template-parts/account/tab-messages.php" 331 334
Write-Module "template-parts/account/tab-announcements.php" 337 341
Write-Module "template-parts/account/tab-support.php" 344 347
Write-Module "template-parts/account/tab-guide.php" 350 383
Write-Module "template-parts/account/tab-testimony.php" 386 495
Write-Module "template-parts/account/tab-refunds.php" 498 527
Write-Module "template-parts/account/refund-modal.php" 534 578

Write-Host "Rebuilding page-account.php..."
$newLines = [System.Collections.Generic.List[string]]::new()

$skip = $false

for ($i = 0; $i -lt $lines.Length; $i++) {
    $lineNum = $i + 1
    
    if ($lineNum -eq 108) {
        $newLines.Add("            <?php get_template_part('template-parts/account/tab', 'dashboard'); ?>")
        $skip = $true
    } elseif ($lineNum -eq 154) {
        $skip = $false
        $newLines.Add($lines[$i])
    } elseif ($lineNum -eq 155) {
        $newLines.Add("            <?php get_template_part('template-parts/account/tab', 'profile'); ?>")
        $skip = $true
    } elseif ($lineNum -eq 190) {
        $skip = $false
        $newLines.Add($lines[$i])
    } elseif ($lineNum -eq 191) {
        $newLines.Add("            <?php get_template_part('template-parts/account/tab', 'trips'); ?>")
        $skip = $true
    } elseif ($lineNum -eq 329) {
        $skip = $false
        $newLines.Add($lines[$i])
    } elseif ($lineNum -eq 331) {
        $newLines.Add("            <?php get_template_part('template-parts/account/tab', 'messages'); ?>")
        $skip = $true
    } elseif ($lineNum -eq 335) {
        $skip = $false
        $newLines.Add($lines[$i])
    } elseif ($lineNum -eq 337) {
        $newLines.Add("            <?php get_template_part('template-parts/account/tab', 'announcements'); ?>")
        $skip = $true
    } elseif ($lineNum -eq 342) {
        $skip = $false
        $newLines.Add($lines[$i])
    } elseif ($lineNum -eq 344) {
        $newLines.Add("            <?php get_template_part('template-parts/account/tab', 'support'); ?>")
        $skip = $true
    } elseif ($lineNum -eq 348) {
        $skip = $false
        $newLines.Add($lines[$i])
    } elseif ($lineNum -eq 350) {
        $newLines.Add("            <?php get_template_part('template-parts/account/tab', 'guide'); ?>")
        $skip = $true
    } elseif ($lineNum -eq 384) {
        $skip = $false
        $newLines.Add($lines[$i])
    } elseif ($lineNum -eq 386) {
        $newLines.Add("            <?php get_template_part('template-parts/account/tab', 'testimony'); ?>")
        $skip = $true
    } elseif ($lineNum -eq 496) {
        $skip = $false
        $newLines.Add($lines[$i])
    } elseif ($lineNum -eq 498) {
        $newLines.Add("            <?php get_template_part('template-parts/account/tab', 'refunds'); ?>")
        $skip = $true
    } elseif ($lineNum -eq 528) {
        $skip = $false
        $newLines.Add($lines[$i])
    } elseif ($lineNum -eq 534) {
        $newLines.Add("<?php get_template_part('template-parts/account/refund', 'modal'); ?>")
        $skip = $true
    } elseif ($lineNum -eq 579) {
        $skip = $false
        $newLines.Add($lines[$i])
    } else {
        if (-not $skip) {
            $newLines.Add($lines[$i])
        }
    }
}

[System.IO.File]::WriteAllLines($filePath, $newLines, $utf8NoBom)
Write-Host "Done!"
