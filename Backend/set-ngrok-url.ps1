# set-ngrok-url.ps1
# Otomatis ambil URL ngrok yang aktif dan update APP_URL di .env
# Jalankan: .\set-ngrok-url.ps1
# Setelah ngrok http 8000 sudah running di terminal lain

$ngrokApi = "http://localhost:4040/api/tunnels"
$envFile  = ".env"

Write-Host "Mencari URL ngrok aktif..." -ForegroundColor Cyan

try {
    $resp    = Invoke-RestMethod -Uri $ngrokApi -ErrorAction Stop
    $tunnel  = $resp.tunnels | Where-Object { $_.proto -eq "https" } | Select-Object -First 1

    if (-not $tunnel) {
        Write-Host "Tidak ada tunnel HTTPS aktif. Pastikan 'ngrok http 8000' sudah berjalan." -ForegroundColor Red
        exit 1
    }

    $ngrokUrl = $tunnel.public_url
    Write-Host "URL ngrok ditemukan: $ngrokUrl" -ForegroundColor Green

    # Baca .env
    $content = Get-Content $envFile -Raw

    # Ganti APP_URL
    $content = $content -replace 'APP_URL=.*', "APP_URL=$ngrokUrl"

    # Simpan
    Set-Content $envFile $content -NoNewline
    Write-Host "APP_URL di .env berhasil diupdate ke: $ngrokUrl" -ForegroundColor Green

    # Clear Laravel config cache
    php artisan config:clear
    php artisan view:clear
    Write-Host ""
    Write-Host "Sekarang buka Roblox executor dan jalankan:" -ForegroundColor Yellow
    Write-Host "  script_key = `"LZD-XXXX-XXXX-XXXX-XXXX`"" -ForegroundColor White
    Write-Host "  loadstring(game:HttpGet(`"$ngrokUrl/Loader.lua`"))()" -ForegroundColor White
    Write-Host ""
    Write-Host "Dashboard admin: $ngrokUrl/admin/dashboard" -ForegroundColor Cyan
    Write-Host "Test inject:     $ngrokUrl/admin/inject-test" -ForegroundColor Cyan
    Write-Host "Log API:         $ngrokUrl/admin/api-logs" -ForegroundColor Cyan

} catch {
    Write-Host "Error: $_" -ForegroundColor Red
    Write-Host "Pastikan ngrok sudah berjalan ('ngrok http 8000') sebelum menjalankan script ini." -ForegroundColor Yellow
}
