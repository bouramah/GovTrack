Path :
{{base_url}}/api/v1/decisions/stats?reunion_id={{reunion_test_id}}
{
    "success": false,
    "message": "Erreur interne du serveur : App\\Http\\Controllers\\Api\\Reunion\\ReunionDecisionController::getDecisions(): Argument #2 ($reunionId) must be of type int, string given, called in /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php on line 46",
    "error": "Internal Server Error",
    "debug": {
        "file": "/Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/app/Http/Controllers/Api/Reunion/ReunionDecisionController.php",
        "line": 23,
        "trace": "#0 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php(46): App\\Http\\Controllers\\Api\\Reunion\\ReunionDecisionController->getDecisions(Object(Illuminate\\Http\\Request), 'stats')\n#1 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Routing/Route.php(265): Illuminate\\Routing\\ControllerDispatcher->dispatch(Object(Illuminate\\Routing\\Route), Object(App\\Http\\Controllers\\Api\\Reunion\\ReunionDecisionController), 'getDecisions')\n#2 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Routing/Route.php(211): Illuminate\\Routing\\Route->runController()\n#3 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Routing/Router.php(808): Illuminate\\Routing\\Route->run()\n#4 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(169): Illuminate\\Routing\\Router->Illuminate\\Routing\\{closure}(Object(Illuminate\\Http\\Request))\n#5 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/app/Http/Middleware/CheckPermission.php(41): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Http\\Request))\n#6 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(208): App\\Http\\Middleware\\CheckPermission->handle(Object(Illuminate\\Http\\Request), Object(Closure), 'view_reunion_de...')\n#7 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Routing/Middleware/SubstituteBindings.php(50): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Http\\Request))\n#8 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(208): Illuminate\\Routing\\Middleware\\SubstituteBindings->handle(Object(Illuminate\\Http\\Request), Object(Closure))\n#9 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Auth/Middleware/Authenticate.php(63): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Http\\Request))\n#10 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(208): Illuminate\\Auth\\Middleware\\Authenticate->handle(Object(Illuminate\\Http\\Request), Object(Closure), 'sanctum')\n#11 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(126): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Http\\Request))\n#12 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Routing/Router.php(807): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#13 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Routing/Router.php(786): Illuminate\\Routing\\Router->runRouteWithinStack(Object(Illuminate\\Routing\\Route), Object(Illuminate\\Http\\Request))\n#14 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Routing/Router.php(750): Illuminate\\Routing\\Router->runRoute(Object(Illuminate\\Http\\Request), Object(Illuminate\\Routing\\Route))\n#15 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Routing/Router.php(739): Illuminate\\Routing\\Router->dispatchToRoute(Object(Illuminate\\Http\\Request))\n#16 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php(200): Illuminate\\Routing\\Router->dispatch(Object(Illuminate\\Http\\Request))\n#17 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(169): Illuminate\\Foundation\\Http\\Kernel->Illuminate\\Foundation\\Http\\{closure}(Object(Illuminate\\Http\\Request))\n#18 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php(21): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Http\\Request))\n#19 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/ConvertEmptyStringsToNull.php(31): Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest->handle(Object(Illuminate\\Http\\Request), Object(Closure))\n#20 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(208): Illuminate\\Foundation\\Http\\Middleware\\ConvertEmptyStringsToNull->handle(Object(Illuminate\\Http\\Request), Object(Closure))\n#21 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php(21): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Http\\Request))\n#22 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TrimStrings.php(51): Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest->handle(Object(Illuminate\\Http\\Request), Object(Closure))\n#23 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(208): Illuminate\\Foundation\\Http\\Middleware\\TrimStrings->handle(Object(Illuminate\\Http\\Request), Object(Closure))\n#24 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePostSize.php(27): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Http\\Request))\n#25 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(208): Illuminate\\Http\\Middleware\\ValidatePostSize->handle(Object(Illuminate\\Http\\Request), Object(Closure))\n#26 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestsDuringMaintenance.php(109): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Http\\Request))\n#27 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(208): Illuminate\\Foundation\\Http\\Middleware\\PreventRequestsDuringMaintenance->handle(Object(Illuminate\\Http\\Request), Object(Closure))\n#28 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Http/Middleware/TrustProxies.php(58): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Http\\Request))\n#29 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(208): Illuminate\\Http\\Middleware\\TrustProxies->handle(Object(Illuminate\\Http\\Request), Object(Closure))\n#30 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/InvokeDeferredCallbacks.php(22): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Http\\Request))\n#31 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(208): Illuminate\\Foundation\\Http\\Middleware\\InvokeDeferredCallbacks->handle(Object(Illuminate\\Http\\Request), Object(Closure))\n#32 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePathEncoding.php(26): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Http\\Request))\n#33 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(208): Illuminate\\Http\\Middleware\\ValidatePathEncoding->handle(Object(Illuminate\\Http\\Request), Object(Closure))\n#34 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Http/Middleware/HandleCors.php(61): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Http\\Request))\n#35 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(208): Illuminate\\Http\\Middleware\\HandleCors->handle(Object(Illuminate\\Http\\Request), Object(Closure))\n#36 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(126): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Http\\Request))\n#37 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php(175): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#38 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php(144): Illuminate\\Foundation\\Http\\Kernel->sendRequestThroughRouter(Object(Illuminate\\Http\\Request))\n#39 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Foundation/Application.php(1219): Illuminate\\Foundation\\Http\\Kernel->handle(Object(Illuminate\\Http\\Request))\n#40 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/public/index.php(20): Illuminate\\Foundation\\Application->handleRequest(Object(Illuminate\\Http\\Request))\n#41 /Users/ibrahimadoutyoulare/Documents/Code Perso/GovTrack/govtrack-backend/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php(23): require_once('/Users/ibrahima...')\n#42 {main}"
    }
}

---

## 0 · Familiarisation & Mapping

- **Reconnaissance first.** Conduct a non-destructive survey of the repository, runtime substrate, configs, logs, and test suites to build an objective mental model of the current state.
- Produce a ≤ 200-line digest anchoring all subsequent analysis. **No mutations during this phase.**

---

## 1 · Problem Framing & Success Criteria

- Restate the observed behaviour, expected behaviour, and impact.
- Define concrete success criteria (e.g., failing test passes, latency < X ms).
- Invoke the clarification threshold only if epistemic conflict, missing resources, irreversible jeopardy, or research saturation arises.

---

## 2 · Context Gathering

- Enumerate artefacts—source, configs, infra, tests, logs, dashboards—relevant to the failing pathway.
- Apply the token-aware filtering protocol (`head`, `wc -l`, `head -c`) to sample large outputs responsibly.
- Document scope: systems, services, data flows, security surfaces.

---

## 3 · Hypothesis Generation & Impact Assessment

- Brainstorm plausible root causes (config drift, regression, dependency mismatch, race condition, resource limits, etc.).
- Rank by likelihood × blast radius.
- Note instrumentation or log gaps that may impede verification.

---

## 4 · Targeted Investigation & Diagnosis

- Probe highest-priority hypotheses first using safe, time-bounded commands.
- Capture fused stdout+stderr and exit codes for every diagnostic step.
- Eliminate or confirm hypotheses with concrete evidence.

---

## 5 · Root-Cause Confirmation & Fix Strategy

- Summarise the definitive root cause.
- Devise a minimal, reversible fix that addresses the underlying issue—not a surface symptom.
- Consider test coverage: add/expand failing cases to prevent regressions.

---

## 6 · Execution & Autonomous Correction

- **Read before write; reread after write.**
- **Command-wrapper mandate:**

  ```bash
  timeout 30s <command> 2>&1 | cat
  ```

  Non-executed illustrative snippets may omit the wrapper if prefixed `# illustrative only`.

- Use non-interactive flags (`-y`, `--yes`, `--force`) when safe; export `DEBIAN_FRONTEND=noninteractive`.
- Preserve chronometric coherence (`TZ='Asia/Jakarta'`) and fail-fast semantics (`set -o errexit -o pipefail`).
- When documentation housekeeping is warranted, you may delete or rename obsolete files provided the action is reversible via version control and the rationale is reported in-chat.
- **Never create unsolicited `.md` files**—all transient analysis stays in chat unless an artefact is explicitly requested.

---

## 7 · Verification & Regression Guard

- Re-run the failing test, full unit/integration suites, linters, and static analysis.
- Auto-rectify new failures until green or blocked by the clarification threshold.
- Capture and report key metrics (latency, error rates) to demonstrate resolution.

---

## 8 · Reporting & Live TODO

- Summarise:

  - **Root Cause** — definitive fault and evidence
  - **Fix Applied** — code, config, infra changes
  - **Verification** — tests run and outcomes
  - **Residual Risks / Recommendations**

- Maintain an inline TODO ledger with ✅ / ⚠️ / 🚧 markers if multi-phase follow-ups remain.
- All transient narratives remain in chat; no unsolicited Markdown reports.

---

## 9 · Continuous Improvement & Prospection

- Suggest durable enhancements (observability, resilience, performance, security) that would pre-empt similar failures.
- Provide impact estimates and outline next steps.