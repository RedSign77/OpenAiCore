# Changelog — WebtechSolutions_AiCore

## 2026-03-20

- AI Logs grid: fixed empty columns (removed conflicting childDefaults), removed oversized text columns (prompt/request/response) from grid — full data visible in modal popup; added Est. Cost column with USD formatting; added View action button opening a slide-over modal with full log details (overview, prompt, request, response); added cron job `aicore_clean_ai_logs` running daily at 02:00 UTC deleting records older than 7 days; added `estimated_cost` DB column with per-model token pricing (gpt-4o-mini, gpt-4o, gpt-4-turbo, gpt-3.5-turbo, o1)
- Centralized AI request/response logging: minden OpenAI kérés és válasz (sikeres, hibás, rate-limited, auth-hiba) bekerül a `webtechsolutions_aicore_log` adatbázis táblába; az "AI Logs" adminisztrációs menüpont (Webtech Solutions > AI Logs) UI grid segítségével jeleníti meg a naplóbejegyzéseket szűrési és lapozási lehetőséggel

## 2026-03-19

- Added "Webtech Solutions" top-level admin menu entry (`WebtechSolutions_AiCore::webtech_solutions`, sortOrder 55); AI Core config link moved under it; MageSentinelPro and MageInsightPro now nest under this shared parent

## 2026-03-18

- Moved AI model sync controls (AI Models Last Synced label, Sync AI Models Now button, available_models hidden storage) from MageSentinelPro to AI Central Settings; Default AI Model select now refreshes dynamically from synced model list; added admin route and SyncModelsButton block to AiCore module
- Initial implementation: centralized OpenAI API client base module with encrypted API key storage, model selection (GPT-4o, GPT-4o-mini), organization ID support, configurable endpoint and timeout, PII sanitization (email, IP, bearer token, account number masking), dedicated request/response logging to magesentinel_ai_core.log, and specific error handling for rate limits (HTTP 429) and invalid/expired API keys (HTTP 401)
