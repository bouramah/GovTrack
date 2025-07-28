Path :
{{base_url}}/api/v1/notification-configs/{{config_id}}/toggle-actif
Malgré plusieurs appel à l'endpoint, actif reste à false sans passer à true
{
    "success": true,
    "message": "Statut de la configuration mis à jour avec succès",
    "data": {
        "id": 5,
        "type_reunion_id": 3,
        "type_notification": "PV_DISPONIBLE",
        "actif": false,
        "delai_jours": 0,
        "template_email": "Le procès-verbal de la réunion {{titre}} est maintenant disponible.",
        "destinataires_par_defaut": [
            "PARTICIPANTS",
            "ORGANISATEUR",
            "VALIDATEUR"
        ],
        "configuration_avancee": {
            "inclure_lien_pv": true,
            "inclure_resume": true,
            "notifier_actions": true
        },
        "date_creation": "2025-07-28T16:08:21.000000Z",
        "date_modification": "2025-07-28T16:14:17.000000Z",
        "creer_par": 97,
        "modifier_par": 97
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