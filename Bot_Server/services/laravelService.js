const axios = require('axios');
const config = require('../config');

/**
 * Instance Axios terpusat untuk komunikasi dengan Laravel Bot API.
 * Endpoint /api/bot/* dilindungi Bearer token — tidak public.
 */
const apiClient = axios.create({
  baseURL: config.laravel.apiUrl,
  timeout: config.laravel.timeout,
  headers: {
    Authorization: `Bearer ${config.laravel.apiToken}`,
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
});

/**
 * @param {Function} requestFn
 * @returns {Promise<{success: boolean, data: any, message: string, statusCode: number|null}>}
 */
async function safeRequest(requestFn) {
  try {
    const response = await requestFn();
    return {
      success: response.data?.status === true,
      data: response.data,
      message: response.data?.message || 'OK',
      statusCode: response.status,
    };
  } catch (error) {
    if (error.response) {
      const body = error.response.data;
      const isJson =
        body &&
        typeof body === 'object' &&
        !Array.isArray(body) &&
        !(typeof body === 'string' && body.trim().startsWith('<'));

      let message = body?.message;
      if (!message) {
        if (error.response.status === 404 && !isJson) {
          message =
            'Backend tidak dapat dijangkau (HTTP 404). ' +
            'Pastikan LARAVEL_API_URL=http://nginx saat bot berjalan di Docker — jangan gunakan URL ngrok.';
        } else {
          message = `Server menolak permintaan (HTTP ${error.response.status}).`;
        }
      }

      return {
        success: false,
        data: body || null,
        message,
        statusCode: error.response.status,
      };
    }

    if (error.request) {
      return {
        success: false,
        data: null,
        message: 'Gagal terhubung ke server Laravel. Pastikan Backend berjalan dan LARAVEL_API_URL benar.',
        statusCode: null,
      };
    }

    return {
      success: false,
      data: null,
      message: 'Terjadi kesalahan internal saat menghubungi server Laravel.',
      statusCode: null,
    };
  }
}

function checkHealth() {
  return safeRequest(() => apiClient.get('/api/bot/health'));
}

function getLicenseStats(discordId) {
  return safeRequest(() =>
    apiClient.get('/api/bot/stats', {
      params: { discord_id: discordId },
    })
  );
}

function resetHwid(discordId) {
  return safeRequest(() =>
    apiClient.post('/api/bot/reset-hwid', {
      discord_id: discordId,
    })
  );
}

function generateKey(targetDiscordId, actorDiscordId, durationDays) {
  return safeRequest(() =>
    apiClient.post('/api/bot/generate', {
      target_discord_id: targetDiscordId,
      actor_discord_id: actorDiscordId,
      duration_days: durationDays,
    })
  );
}

function getScriptTemplate(discordId) {
  return safeRequest(() =>
    apiClient.get('/api/bot/script-template', {
      params: { discord_id: discordId },
    })
  );
}

module.exports = {
  checkHealth,
  getLicenseStats,
  resetHwid,
  generateKey,
  getScriptTemplate,
};
