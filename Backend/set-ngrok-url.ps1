# set-ngrok-url.ps1
# Menggunakan static ngrok URL yang sudah dikonfigurasi (tidak berubah-ubah).
# Jalankan: .\set-ngrok-url.ps1
# Pastikan ngrok sudah running: ngrok http --url=unfertile-proconsularly-dorris.ngrok-free.dev 8000

$NGROK_URL = "https://unfertile-proconsularly-dorris.ngrok-free.dev"
$envFile   = ".env"

Write-Host "Menggunakan static ngrok URL: $NGROK_URL" -ForegroundColor Cyan

# Baca .env
$content = Get-Content $envFile -Raw

# Ganti APP_URL
$content = $content -replace 'APP_URL=.*', "APP_URL=$NGROK_URL"

# Ganti ROBLOX_REDIRECT_URI jika ada
$content = $content -replace 'ROBLOX_REDIRECT_URI=.*', "ROBLOX_REDIRECT_URI=$NGROK_URL/roblox/callback"

# Ganti MIDTRANS_NOTIFICATION_URL jika ada
$content = $content -replace 'MIDTRANS_NOTIFICATION_URL=.*', "MIDTRANS_NOTIFICATION_URL=$NGROK_URL/api/midtrans/callback"

# Simpan
Set-Content $envFile $content -NoNewline
Write-Host "APP_URL di .env berhasil diset ke: $NGROK_URL" -ForegroundColor Green

# Clear Laravel config cache
Write-Host "Membersihkan cache Laravel..." -ForegroundColor Yellow
php artisan config:clear
php artisan view:clear
php artisan route:clear

Write-Host ""
Write-Host "=== URL APLIKASI ===" -ForegroundColor Cyan
Write-Host "  Dashboard Admin : $NGROK_URL/admin/dashboard" -ForegroundColor White
Write-Host "  Test Inject     : $NGROK_URL/admin/inject-test" -ForegroundColor White
Write-Host "  Log API         : $NGROK_URL/admin/api-logs" -ForegroundColor White
Write-Host ""
Write-Host "=== ROBLOX EXECUTOR ===" -ForegroundColor Yellow
Write-Host "  loadstring(game:HttpGet(`"$NGROK_URL/Loader.lua`"))()" -ForegroundColor White
Write-Host ""
Write-Host "=== MIDTRANS WEBHOOK ===" -ForegroundColor Magenta
Write-Host "  Notification URL: $NGROK_URL/api/midtrans/callback" -ForegroundColor White
Write-Host "  Daftarkan URL ini di: https://dashboard.sandbox.midtrans.com" -ForegroundColor Gray
Write-Host "  Settings > Configuration > Payment Notification URL" -ForegroundColor Gray
Write-Host ""
