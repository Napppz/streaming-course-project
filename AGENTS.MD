# AGENTS.MD

Repository-level instruction for all agent work in this project.

## Review and Verification Policy

- Do **not** run hands-on QA sessions, exploratory QA, browser QA, or QA sub-agents by default.
- Do **not** launch dedicated QA tasks such as app-driving verification unless the user explicitly asks for QA/testing-by-usage.
- Focus on **non-QA validation** only:
  - code review
  - static analysis
  - syntax checks
  - type/lint diagnostics
  - targeted logic verification
  - security review
  - git/context review
- If post-implementation review is needed, prefer non-QA reviewers and skip QA execution unless explicitly requested by the user.

## User Preference Override

Treat the following as an explicit repository preference:

> "tidak perlu menjalankan QA, fokus non-QA saja"

This preference remains in effect for future work in this repository unless the user overrides it in a later message.
