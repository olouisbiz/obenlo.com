$filePath = "page-account.php"
$lines = Get-Content -LiteralPath $filePath

New-Item -ItemType Directory -Force -Path "template-parts/account"

function Write-Module {
    param($fileName, [int]$startLine, [int]$endLine)
    $content = [System.Collections.Generic.List[string]]::new()
    for ($j = $startLine - 1; $j -le $endLine - 1; $j++) {
        $content.Add($lines[$j])
    }
    Set-Content -Path $fileName -Value $content -Encoding UTF8
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
for ($i = 0; $i -lt $lines.Length; $i++) {
    $lineNum = $i + 1
    
    if ($lineNum -eq 108) {
        $newLines.Add("            get_template_part('template-parts/account/tab', 'dashboard');")
    } elseif ($lineNum -ge 108 -and $lineNum -le 153) {
        # skip
    } elseif ($lineNum -eq 155) {
        $newLines.Add("            get_template_part('template-parts/account/tab', 'profile');")
    } elseif ($lineNum -ge 155 -and $lineNum -le 189) {
        # skip
    } elseif ($lineNum -eq 191) {
        $newLines.Add("            get_template_part('template-parts/account/tab', 'trips');")
    } elseif ($lineNum -ge 191 -and $lineNum -le 328) {
        # skip
    } elseif ($lineNum -eq 331) {
        $newLines.Add("            get_template_part('template-parts/account/tab', 'messages');")
    } elseif ($lineNum -ge 331 -and $lineNum -le 334) {
        # skip
    } elseif ($lineNum -eq 337) {
        $newLines.Add("            get_template_part('template-parts/account/tab', 'announcements');")
    } elseif ($lineNum -ge 337 -and $lineNum -le 341) {
        # skip
    } elseif ($lineNum -eq 344) {
        $newLines.Add("            get_template_part('template-parts/account/tab', 'support');")
    } elseif ($lineNum -ge 344 -and $lineNum -le 347) {
        # skip
    } elseif ($lineNum -eq 350) {
        $newLines.Add("            get_template_part('template-parts/account/tab', 'guide');")
    } elseif ($lineNum -ge 350 -and $lineNum -le 383) {
        # skip
    } elseif ($lineNum -eq 386) {
        $newLines.Add("            get_template_part('template-parts/account/tab', 'testimony');")
    } elseif ($lineNum -ge 386 -and $lineNum -le 495) {
        # skip
    } elseif ($lineNum -eq 498) {
        $newLines.Add("            get_template_part('template-parts/account/tab', 'refunds');")
    } elseif ($lineNum -ge 498 -and $lineNum -le 527) {
        # skip
    } elseif ($lineNum -eq 534) {
        $newLines.Add("<?php get_template_part('template-parts/account/refund', 'modal'); ?>")
    } elseif ($lineNum -ge 534 -and $lineNum -le 578) {
        # skip
    } else {
        $newLines.Add($lines[$i])
    }
}
Set-Content -Path $filePath -Value $newLines -Encoding UTF8
Write-Host "Done!"
