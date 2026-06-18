<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

/**
 * Loader.lua — satu-satunya cara inject via executor:
 *
 *   script_key = "LZD-XXXX-..."
 *   loadstring(game:HttpGet("https://DOMAIN/Loader.lua"))()
 */
class LoaderController extends Controller
{
    public function serve(): Response
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $lua = $this->buildLoader($baseUrl.'/api/license/get');

        return response($lua, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'ngrok-skip-browser-warning' => 'true',
        ]);
    }

    private function buildLoader(string $getUrl): string
    {
        $lua = <<<'LUA'
-- LimeHub Loader

if type(script_key) ~= "string" or #script_key < 10 then
    error("[LimeHub] script_key belum diset. Contoh:\nscript_key = \"LZD-XXXX-XXXX-XXXX-XXXX\"\nloadstring(game:HttpGet(\".../Loader.lua\"))()", 0)
end

local Players = game:GetService("Players")
local HttpService = game:GetService("HttpService")
local lp = Players.LocalPlayer

local hwid = tostring(lp and lp.UserId or 0)
local okHwid, clientId = pcall(function()
    return game:GetService("RbxAnalyticsService"):GetClientId()
end)
if okHwid and clientId and #tostring(clientId) > 4 then
    hwid = tostring(clientId)
end

local username = (lp and lp.Name) or "unknown"
local placeId = tostring(game.PlaceId)

local url = "GET_URL_PLACEHOLDER"
    .. "?key=" .. HttpService:UrlEncode(script_key)
    .. "&hwid=" .. HttpService:UrlEncode(hwid)
    .. "&username=" .. HttpService:UrlEncode(username)
    .. "&place=" .. HttpService:UrlEncode(placeId)

local ok, scriptContent = pcall(function()
    return game:HttpGet(url)
end)

if not ok or not scriptContent or #scriptContent < 10 then
    error("[LimeHub] HttpGet gagal: " .. tostring(scriptContent), 0)
end

if scriptContent:sub(1, 5) == "error" then
    local errFn = loadstring(scriptContent)
    if errFn then pcall(errFn) end
    return
end

local fn, loadErr = loadstring(scriptContent)
if not fn then
    error("[LimeHub] Parse error: " .. tostring(loadErr), 0)
end

local runOk, runErr = pcall(fn)
if not runOk then
    error("[LimeHub] Runtime error: " .. tostring(runErr), 0)
end
LUA;

        return str_replace('GET_URL_PLACEHOLDER', $getUrl, $lua);
    }
}
