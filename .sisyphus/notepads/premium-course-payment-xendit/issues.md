
## 2026-04-26 — T7 verification gotcha

- Raw PHP/PDO boolean `false` values were serialized to PostgreSQL as an empty string during ad-hoc fixture setup, which reproduced the same `invalid input syntax for type boolean: ""` hazard seen earlier in app code. For DB writes on this repo, normalize booleans explicitly (`'true'/'false'` or SQL casts) instead of trusting loose PHP truthiness.

## 2026-04-26 — Verification note

- `composer test` still fails in the stock `ExampleDatabaseTest` because this repo has no configured `tests` database group in `app/Config/Database.php`. This was reproduced after T8 and appears unrelated to the admin payment-monitoring changes.

## 2026-04-26 — Concurrency-fix verification gotchas

- The local Xendit stub that was already running for checkout QA returned the same hard-coded invoice ID for every request (`stub-invoice-123`), which reproduced false `xendit_invoice_id` uniqueness failures unrelated to the checkout-lock fix. I had to restart the stub with per-request invoice IDs before verifying the happy path.
- The built-in PHP development server in this repo is effectively single-request for QA purposes, so true HTTP-level request races are not reliable there; the verification leaned on the new PostgreSQL invariant directly by proving the partial unique pending index rejects a second active pending insert for the same `user_id + course_id`.

## 2026-04-26 — Final-wave closeout blocker

- All implementation tasks and all four final-wave reviews are complete and approved, but `.sisyphus/plans/premium-course-payment-xendit.md` explicitly forbids checking F1-F4 before the user's explicit `okay` (lines 474-476). Session remains blocked on that approval gate only.
