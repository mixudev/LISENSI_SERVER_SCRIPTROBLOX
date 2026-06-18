<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

/**
 * Menyajikan Loader.lua yang di-HttpGet oleh Roblox executor.
 *
 * Cara pakai di executor — DUA metode tersedia:
 *
 * METODE 1 (Paling simpel, direkomendasikan):
 *   loadstring(game:HttpGet("https://DOMAIN/api/license/get?key=LZD-XXXX&hwid=HWID_KAMU&username=RobloxName&place=PLACE_ID"))()
 *   → Langsung return script Lua, tidak perlu Loader.lua sama sekali
 *
 * METODE 2 (Pakai Loader.lua — untuk kompatibilitas format lama):
 *   script_key = "LZD-XXXX-XXXX-XXXX-XXXX"
 *   loadstring(game:HttpGet("https://DOMAIN/Loader.lua"))()
 *   → Loader baca script_key dari getgenv()/_G, lalu HttpGet ke endpoint GET
 *
 * Kenapa GET bukan POST?
 * - game:HttpGet() tersedia di SEMUA executor
 * - HttpService:PostAsync() sering blocked/gagal di executor environment
 * - GET parameter di URL tidak ada isu CSRF atau content-type
 * - Lebih simpel, lebih reliable
 */
class LoaderController extends Controller
{
    public function serve(): Response
    {
        $baseUrl   = rtrim(config('app.url'), '/');
        $getUrl    = $baseUrl.'/api/license/get';
        $modulesUrl = $baseUrl.'/modules';

        $lua = $this->buildLoader($getUrl, $modulesUrl);

        return response($lua, 200, [
            'Content-Type'  => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma'        => 'no-cache',
            // Skip ngrok interstitial browser warning
            'ngrok-skip-browser-warning' => 'true',
        ]);
    }

    private function buildLoader(string $getUrl, string $modulesUrl): string
    {
        return <<<LUA
-- ╔══════════════════════════════════════════════════════╗
-- ║        Protected Script Loader  — LimeHub            ║
-- ╚══════════════════════════════════════════════════════╝

local Players = game:GetService("Players")
local lp      = Players.LocalPlayer

-- ─── Dapatkan script_key dari berbagai sumber ─────────
-- Executor menyimpan variabel global di getgenv() atau _G
local key = nil

-- Coba getgenv() (tersedia di sebagian besar executor modern)
local ok1, genv = pcall(getgenv)
if ok1 and type(genv) == "table" then
    key = genv.script_key or genv.SCRIPT_KEY or genv.key
end

-- Fallback ke _G (Roblox global table)
if not key then
    key = _G.script_key or _G.SCRIPT_KEY or _G.key
end

-- Fallback ke variabel lokal script_key (jika di-set sebelum loadstring)
if not key and type(script_key) == "string" then
    key = script_key
end

if not key or type(key) ~= "string" or #key < 10 then
    error("[LimeHub] ❌ script_key tidak ditemukan!\\n"
        .. "Set sebelum menjalankan loader:\\n"
        .. "  getgenv().script_key = \\"LZD-XXXX-XXXX-XXXX-XXXX\\"\\n"
        .. "  loadstring(game:HttpGet(\\"URL/Loader.lua\\"))()", 0)
    return
end

print("[LimeHub] ✅ Key: " .. key:sub(1,7) .. "...")

-- ─── Dapatkan HWID ────────────────────────────────────
local hwid = tostring(lp and lp.UserId or 0)
local ok2, clientId = pcall(function()
    return game:GetService("RbxAnalyticsService"):GetClientId()
end)
if ok2 and clientId and #tostring(clientId) > 4 then
    hwid = tostring(clientId)
end

-- ─── Dapatkan info game ───────────────────────────────
local username = lp and lp.Name or "unknown"
local placeId  = tostring(game.PlaceId)

print("[LimeHub] � User: " .. username .. " | Place: " .. placeId)

-- ─── Set LIMEHUB_BASE_URL untuk loader.lua sub-modules ─
_G.LIMEHUB_BASE_URL = "{$modulesUrl}"
pcall(function()
    local genv = getgenv()
    genv.LIMEHUB_BASE_URL = "{$modulesUrl}"
end)

-- ─── Build URL dan HttpGet langsung ───────────────────
-- Encode key untuk URL (ganti - dengan -, aman di URL)
local url = "{$getUrl}"
    .. "?key=" .. key
    .. "&hwid=" .. hwid
    .. "&username=" .. username
    .. "&place=" .. placeId

print("[LimeHub] 🌐 Requesting: " .. url:sub(1, 60) .. "...")

local scriptContent
local ok3, err3 = pcall(function()
    scriptContent = game:HttpGet(url)
end)

if not ok3 then
    error("[LimeHub] ❌ HttpGet gagal: " .. tostring(err3), 0)
    return
end

if not scriptContent or #scriptContent < 5 then
    error("[LimeHub] ❌ Response kosong dari server.", 0)
    return
end

-- Cek apakah server return error Lua
if scriptContent:sub(1, 5) == "error" then
    -- Server return Lua error string — jalankan agar error tampil di executor
    local errFn = loadstring(scriptContent)
    if errFn then pcall(errFn) end
    error("[LimeHub] ❌ Server error: " .. scriptContent:sub(8, 200), 0)
    return
end

print("[LimeHub] ✅ Script diterima (" .. #scriptContent .. " bytes)")

-- ─── Jalankan script ──────────────────────────────────
local fn, loadErr = loadstring(scriptContent)
if not fn then
    error("[LimeHub] ❌ Parse error: " .. tostring(loadErr), 0)
    return
end

local runOk, runErr = pcall(fn)
if not runOk then
    error("[LimeHub] ❌ Runtime error: " .. tostring(runErr), 0)
end
LUA;
    }
}
