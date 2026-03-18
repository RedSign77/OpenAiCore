# Changelog — WebtechSolutions_AiCore

## 2026-03-18

- Moved AI model sync controls (AI Models Last Synced label, Sync AI Models Now button, available_models hidden storage) from MageSentinelPro to AI Central Settings; Default AI Model select now refreshes dynamically from synced model list; added admin route and SyncModelsButton block to AiCore module
- Initial implementation: centralized OpenAI API client base module with encrypted API key storage, model selection (GPT-4o, GPT-4o-mini), organization ID support, configurable endpoint and timeout, PII sanitization (email, IP, bearer token, account number masking), dedicated request/response logging to magesentinel_ai_core.log, and specific error handling for rate limits (HTTP 429) and invalid/expired API keys (HTTP 401)
