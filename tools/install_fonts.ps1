$targetDir = [System.IO.Path]::Combine($env:LOCALAPPDATA, 'Microsoft', 'Windows', 'Fonts')
if (-not (Test-Path $targetDir)) {
    New-Item -ItemType Directory -Path $targetDir -Force | Out-Null
}

$files = Get-ChildItem -Path (Join-Path $PSScriptRoot '..\fonts\*.*')
foreach ($f in $files) {
    $dest = Join-Path $targetDir $f.Name
    Copy-Item $f.FullName -Destination $dest -Force
    
    $regKey = 'HKCU:\Software\Microsoft\Windows NT\CurrentVersion\Fonts'
    $fontName = [System.IO.Path]::GetFileNameWithoutExtension($f.Name) + ' (TrueType)'
    Set-ItemProperty -Path $regKey -Name $fontName -Value $dest -Force
    Write-Host "Installed & Registered: $($f.Name)"
}

Add-Type -TypeDefinition @"
using System;
using System.Runtime.InteropServices;
public class FontBroadcaster {
    [DllImport("user32.dll", SetLastError = true, CharSet = CharSet.Auto)]
    public static extern IntPtr SendMessageTimeout(IntPtr hWnd, uint Msg, UIntPtr wParam, IntPtr lParam, uint fuFlags, uint uTimeout, out UIntPtr lpdwResult);
}
"@

$HWND_BROADCAST = [IntPtr]0xffff
$WM_FONTCHANGE = 0x001D
$result = [UIntPtr]::Zero
[FontBroadcaster]::SendMessageTimeout($HWND_BROADCAST, $WM_FONTCHANGE, [UIntPtr]::Zero, [IntPtr]::Zero, 2, 1000, [ref]$result) | Out-Null
Write-Host "WM_FONTCHANGE broadcast complete."
