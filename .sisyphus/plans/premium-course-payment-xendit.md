# Premium Course Payment via Xendit Payment Links + Admin/Super Admin RBAC

## TL;DR
> **Summary**: Tambahkan monetisasi course premium berbasis Xendit payment link dengan webhook sebagai source of truth, tanpa mengubah pola halaman admin yang sudah ada. Sekaligus upgrade RBAC dari `admin|user` menjadi `super_admin|admin|user`, dengan hak khusus super admin untuk membuat admin baru.
> **Deliverables**:
> - premium flag + pricing pada course admin existing
> - user checkout page + redirect ke Xendit + success/pending/failed pages
> - transaction persistence + webhook settlement + enrollment grant idempotent
> - admin payment monitoring tanpa redesign admin shell
> - super admin user-management guard untuk membuat admin baru
> **Effort**: Large
> **Parallel**: YES - 2 waves
> **Critical Path**: Task 1 → Task 2 → Task 5 → Task 6 → Task 7

## Context
### Original Request
- Tambahkan fitur pembayaran course premium di admin.
- Buat pembayaran di page user.
- Gunakan payment link Xendit.
- Database sedang migrasi dari MySQL ke PostgreSQL.
- Tambahkan role admin dan super admin.
- Super admin bisa buat admin baru.
- Fitur/page admin existing harus tetap sama.

### Interview Summary
- Admin scope yang dipilih: pricing + payment status.
- User flow yang dipilih: checkout internal + redirect Xendit + success/pending/failed pages.
- DB target: PostgreSQL-first.
- Payment confirmation: webhook + redirect, webhook sebagai source of truth.
- Test strategy: no automated tests; tetap wajib QA agent-executed per task.

### Metis Review (gaps addressed)
- Enrollment tidak boleh dibuat dari redirect return page; hanya dari trusted transaction state.
- Perlu entitas transaksi terpisah dari `enrollments`.
- Wajib ada idempotency untuk webhook retries dan repeated checkout.
- Scope dibatasi ke one-time premium course purchase; tidak ada refund/coupon/subscription/cart.
- Karena admin page harus tetap sama, monetization field ditambahkan in-place di halaman admin existing, bukan lewat redesign/dashboard baru.

## Work Objectives
### Core Objective
Membuat alur pembelian course premium satuan menggunakan Xendit payment links, sehingga admin dapat menandai course sebagai premium dan memonitor transaksi, user dapat checkout lalu bayar, dan akses course premium hanya diberikan setelah pembayaran tervalidasi server-side.

### Deliverables
- Role model `super_admin|admin|user` diauth dan user-management.
- Existing admin course create/edit flow memiliki field premium + price + purchasable guard.
- Tabel transaksi pembayaran baru untuk lifecycle payment per user/course.
- Service integrasi Xendit payment link + callback verification config.
- Checkout page user, redirect flow, dan return-state pages.
- Webhook processing yang mengubah transaction status dan membuat enrollment idempotent.
- Admin payment monitoring list/detail di admin layout existing.
- Access gating agar premium course tidak bisa diakses tanpa enrollment valid.

### Definition of Done (verifiable conditions with commands)
- `php spark migrate` berhasil pada PostgreSQL target tanpa edit migration lama.
- Premium course yang belum dibayar tidak bisa membuat enrollment dari CTA maupun deep link.
- Checkout premium course membuat tepat satu transaksi aktif per user/course dan mengembalikan redirect/payment link Xendit.
- Webhook sukses membuat enrollment tepat satu kali walau callback dikirim ulang.
- Redirect success page tidak memberi akses jika webhook belum menyelesaikan settlement.
- Super admin bisa membuat admin baru dari halaman admin users existing; admin biasa tidak bisa membuat/mengubah user menjadi admin atau super admin.

### Must Have
- Additive migration only.
- Existing admin pages/layout tetap dipakai.
- Free courses tetap memakai enroll langsung yang ada.
- Premium courses menampilkan detail course tetapi tombol enroll diganti checkout/beli.
- Existing enrolled users tetap dapat akses ke course yang sudah dimiliki.
- Canonical transaction statuses internal: `pending`, `paid`, `failed`, `expired`, `cancelled`.
- Successful repurchase untuk course yang sudah dimiliki harus diblok.
- Satu unpaid transaction aktif per user/course direuse sampai terminal state atau expired.

### Must NOT Have (guardrails, AI slop patterns, scope boundaries)
- Jangan tambahkan refund, coupon, subscription, cart, invoice generator, atau laporan finance penuh.
- Jangan trust query params dari redirect Xendit untuk grant access.
- Jangan ubah struktur navigasi admin secara besar.
- Jangan edit migration lama; buat migration baru saja.
- Jangan tambahkan MySQL-specific SQL atau enum assumptions.
- Jangan memindahkan flow admin/user ke framework lain atau folder baru di luar pola CodeIgniter saat ini.

## Verification Strategy
> ZERO HUMAN INTERVENTION - all verification is agent-executed.
- Test decision: none automated; gunakan agent-executed HTTP/UI/DB verification saja.
- QA policy: Every task has agent-executed scenarios.
- Evidence: `.sisyphus/evidence/task-{N}-{slug}.{ext}`

## Execution Strategy
### Parallel Execution Waves
> Target: 5-8 tasks per wave. <3 per wave (except final) = under-splitting.
> Extract shared dependencies as Wave-1 tasks for max parallelism.

Wave 1: T1 RBAC foundation, T2 premium/payment schema, T3 Xendit integration foundation, T4 admin course monetization UI/API, T5 user checkout initiation

Wave 2: T6 webhook settlement + enrollment grant, T7 premium access gating, T8 admin payment monitoring + admin-management restrictions polish

### Dependency Matrix (full, all tasks)
- T1 blocks T4 and T8.
- T2 blocks T3, T4, T5, T6, T7, T8.
- T3 blocks T5 and T6.
- T4 blocks T5 because course premium metadata must exist in admin.
- T5 blocks T6 because checkout transaction creation must exist before callbacks settle it.
- T6 blocks T7 and T8 because persisted payment state and enrollment grant are the source of truth.
- T7 depends on T6 to enforce server-trusted access rules.
- T8 depends on T1, T2, and T6.

### Agent Dispatch Summary (wave → task count → categories)
- Wave 1 → 5 tasks → unspecified-high, quick
- Wave 2 → 3 tasks → unspecified-high, deep

## TODOs
> Implementation + Test = ONE task. Never separate.
> EVERY task MUST have: Agent Profile + Parallelization + QA Scenarios.

- [x] 1. Expand RBAC to `super_admin|admin|user` without changing admin page structure

  **What to do**: Add a new additive user-role capability so the app recognizes `super_admin`, while preserving the current admin route group and page layout. Update the users schema via a new migration only if needed for data normalization/backfill, then update role validation, session redirects, base helpers, and admin filter behavior so both `admin` and `super_admin` can access `/admin/*`, but only `super_admin` can create or elevate accounts to admin/super_admin. Keep the existing `admin/users` pages and forms; only extend field options/permissions in place.
  **Must NOT do**: Do not redesign admin navigation. Do not introduce a second admin portal. Do not allow ordinary `admin` to self-promote or create another admin/super_admin.

  **Recommended Agent Profile**:
  - Category: `unspecified-high` - Reason: touches auth, filters, session behavior, validation, and permissions.
  - Skills: `[]` - No special domain skill needed beyond repo-local RBAC edits.
  - Omitted: `[git-master]` - No git operation is part of execution.

  **Parallelization**: Can Parallel: YES | Wave 1 | Blocks: [4, 8] | Blocked By: []

  **References** (executor has NO interview context - be exhaustive):
  - Pattern: `app/Filters/AdminFilter.php:11-19` - current admin gate only allows `role === 'admin'`; extend this to admit `super_admin` too.
  - Pattern: `app/Controllers/AuthController.php:11-14, 44-52, 86-95` - current session role redirect logic and session payload.
  - Pattern: `app/Controllers/BaseController.php:64-106` - shared role/user helpers used by downstream controllers.
  - Pattern: `app/Controllers/Admin/UserController.php:31-60, 78-121, 124-135` - existing admin user CRUD logic to harden with super-admin-only privilege operations.
  - Pattern: `app/Views/admin/users/create.php:63-67` - current role select UI to extend in-place.
  - Pattern: `app/Views/admin/users/edit.php:63-67` - current role edit UI to extend in-place.
  - API/Type: `app/Models/UserModel.php:15-24, 31-37, 99-103` - allowed fields, validation, helper that currently only understands `admin|user`.
  - API/Type: `app/Database/Migrations/2024-03-22-000001_create_users_table.php:35-39` - original users role column; do not edit history, use additive migration only.

  **Acceptance Criteria** (agent-executable only):
  - [ ] A seeded or manually updated `super_admin` session can access all existing `/admin/*` routes without redirect rejection.
  - [ ] A plain `admin` can still access admin pages but cannot create or edit a user into `admin` or `super_admin`.
  - [ ] `signup` still creates `role = user` by default.
  - [ ] Login redirect sends both `admin` and `super_admin` to `/admin/dashboard`, while `user` stays on `/user/dashboard`.
  - [ ] Existing admin/users page URLs remain unchanged.

  **QA Scenarios** (MANDATORY - task incomplete without these):
  ```
  Scenario: Super admin creates a new admin from existing admin users page
    Tool: Bash
    Steps: Authenticate as seeded `super_admin` using HTTP form login; GET `/admin/users/create`; assert role field allows `admin`; POST form with `username=ops-admin`, `email=ops-admin@example.com`, `password=Password123!`, `role=admin`; then query users table or GET `/admin/users`.
    Expected: Create request succeeds; response redirects to `/admin/users`; resulting HTML or DB row shows `ops-admin` with role `admin`.
    Evidence: .sisyphus/evidence/task-1-rbac-superadmin-create-admin.png

  Scenario: Plain admin is blocked from privilege escalation
    Tool: Bash
    Steps: Authenticate as seeded `admin`; GET `/admin/users/create`; inspect returned HTML for forbidden role options; attempt direct POST with `role=admin` and another POST with `role=super_admin`.
    Expected: Forbidden role options are absent or POST is rejected server-side; no privileged account is created.
    Evidence: .sisyphus/evidence/task-1-rbac-admin-blocked.png
  ```

  **Commit**: NO | Message: `feat(auth): add super admin role guards` | Files: `app/Filters/AdminFilter.php`, `app/Controllers/AuthController.php`, `app/Controllers/BaseController.php`, `app/Controllers/Admin/UserController.php`, `app/Views/admin/users/create.php`, `app/Views/admin/users/edit.php`, `app/Models/UserModel.php`, `app/Database/Migrations/*`

- [x] 2. Add PostgreSQL-safe premium course metadata and payment transaction schema

  **What to do**: Create additive migrations that (a) extend `courses` with monetization fields and (b) introduce a dedicated payment transaction table for one-time premium course purchases. The course-side minimum fields are `is_premium` boolean, `price_amount` integer, and `price_currency` varchar default `IDR`; optionally include `is_purchasable` boolean if needed to pause checkout without unpublishing the course. The transaction table should minimally store `user_id`, `course_id`, internal `reference_code`, Xendit identifiers/URL fields, `amount`, `currency`, `status`, `checkout_url`, `paid_at`, `expired_at`, `last_webhook_at`, `status_payload_json`, and timestamps. Add uniqueness so only one successful enrollment per user/course exists and one active unpaid transaction per user/course can be enforced in application logic.
  **Must NOT do**: Do not overload `enrollments` with payment status fields. Do not modify legacy migration files. Do not rely on MySQL enum types or unsigned-only assumptions.

  **Recommended Agent Profile**:
  - Category: `unspecified-high` - Reason: schema design under ongoing DB migration with PostgreSQL-safe constraints.
  - Skills: `[]` - Standard CodeIgniter migration work only.
  - Omitted: `[elysiajs-expert]` - Unrelated framework skill.

  **Parallelization**: Can Parallel: YES | Wave 1 | Blocks: [3, 4, 5, 6, 7, 8] | Blocked By: []

  **References** (executor has NO interview context - be exhaustive):
  - Pattern: `app/Database/Migrations/2024-03-22-000003_create_courses_table.php:11-83` - existing course schema and naming conventions.
  - Pattern: `app/Database/Migrations/2024-03-22-000007_create_enrollments_table.php:11-56` - existing relationship style and unique key pattern.
  - API/Type: `app/Models/EnrollmentModel.php:15-23, 114-145` - enrollment remains separate grant artifact.
  - API/Type: `app/Config/Database.php:27-53` - PostgreSQL default config; use compatible types and defaults.
  - API/Type: `.env:35-39` - runtime DB target already points to PostgreSQL.
  - External: `https://docs.xendit.co/` - canonical source for payment link field naming and callback payload expectations.

  **Acceptance Criteria** (agent-executable only):
  - [ ] `php spark migrate` succeeds on PostgreSQL without altering old migration files.
  - [ ] `courses` can persist premium metadata with `is_premium`, amount, and currency.
  - [ ] A new transaction row can represent `pending`, `paid`, `failed`, `expired`, and `cancelled` states.
  - [ ] Schema allows a user/course to have historical failed/expired transactions while preventing duplicate access grant after success.
  - [ ] Enrollment schema remains unchanged as the source of learning access.

  **QA Scenarios** (MANDATORY - task incomplete without these):
  ```
  Scenario: PostgreSQL migration creates premium and payment schema successfully
    Tool: Bash
    Steps: Run `php spark migrate`; inspect migration output; optionally run `php spark db:table courses` and the new payment table command if available in project tooling.
    Expected: Migration exits 0; new columns/table exist; no SQL syntax error from Postgre-specific execution.
    Evidence: .sisyphus/evidence/task-2-premium-payment-schema.txt

  Scenario: Duplicate-success guard remains enforceable with historical failures
    Tool: Bash
    Steps: Insert one failed and one expired transaction for `student@example.com` + `premium-course-101`, then mark a third as paid and attempt to create another paid duplicate for the same user/course.
    Expected: Historical non-success rows remain allowed; duplicate terminal success/access grant path is rejected by app logic/constraint design.
    Evidence: .sisyphus/evidence/task-2-payment-uniqueness.txt
  ```

  **Commit**: NO | Message: `feat(db): add premium course and payment transaction schema` | Files: `app/Database/Migrations/*`, payment model files if created

- [x] 3. Build Xendit payment-link integration foundation and internal payment status mapping

  **What to do**: Add the configuration and service layer needed to create Xendit payment links and normalize callback/return states into the internal enum. Create dedicated config accessors for secret key, public/business identifier if needed, callback verification token, success URL, failure URL, and expiry defaults using `.env`. Use CodeIgniter HTTP client conventions. Define one canonical mapper so Xendit event/status variations resolve into the internal statuses `pending|paid|failed|expired|cancelled`, with `paid` as the only entitlement-granting terminal state. Generate deterministic reference codes like `COURSE-{courseId}-USER-{userId}` plus a suffix when historical attempts exist.
  **Must NOT do**: Do not scatter raw Xendit payload logic across controllers. Do not hardcode secrets in controller files. Do not create enrollment here.

  **Recommended Agent Profile**:
  - Category: `unspecified-high` - Reason: external API integration plus status normalization rules.
  - Skills: `[]` - No extra skill required; CodeIgniter service/config patterns suffice.
  - Omitted: `[gh-cli]` - Not a GitHub task.

  **Parallelization**: Can Parallel: YES | Wave 1 | Blocks: [5, 6] | Blocked By: [2]

  **References** (executor has NO interview context - be exhaustive):
  - Pattern: `app/Config/CURLRequest.php:7-19` - existing HTTP client config entrypoint.
  - Pattern: `app/Controllers/Users/CertificateController.php` - existing third-party integration style called out during exploration; follow config/service-style composition.
  - Pattern: `app/Controllers/AuthController.php:41-52` - current session-aware controller style for redirects and side effects.
  - API/Type: `.env:35-39` - current env-driven configuration pattern.
  - API/Type: `app/Config/Database.php:193-203` - environment-aware config conventions.
  - External: `https://docs.xendit.co/docs/payment-links` - payment link creation, redirect URLs, expiry, and callback expectations.

  **Acceptance Criteria** (agent-executable only):
  - [ ] There is one reusable service/function that creates a payment link request payload from internal transaction data.
  - [ ] There is one reusable mapper from Xendit statuses/events to the internal status enum.
  - [ ] Secrets and callback token are read from env/config, not hardcoded.
  - [ ] Service returns enough metadata to persist checkout URL, external ID/reference, and expiry.
  - [ ] Unknown/unsupported Xendit status values are logged and rejected without granting enrollment.

  **QA Scenarios** (MANDATORY - task incomplete without these):
  ```
  Scenario: Service builds a valid payment-link request from internal transaction
    Tool: Bash
    Steps: Execute a controller/service smoke path in local environment for course `premium-course-101`, user `student@example.com`, amount `149000`; dump the outbound payload before HTTP dispatch in a safe non-secret log/test endpoint.
    Expected: Payload contains the correct external/reference code, amount, redirect URLs, and expiry fields; no secret value is echoed into the browser.
    Evidence: .sisyphus/evidence/task-3-xendit-payload.txt

  Scenario: Unknown payment status does not grant access
    Tool: Bash
    Steps: Feed the mapper a simulated Xendit payload with an unsupported status string such as `MYSTERY_STATE`.
    Expected: Mapper resolves to a rejected/non-terminal path; transaction is not marked paid; no enrollment creation path runs.
    Evidence: .sisyphus/evidence/task-3-xendit-status-guard.txt
  ```

  **Commit**: NO | Message: `feat(payments): add xendit link service foundation` | Files: config/service/payment support files, `.env` example docs if applicable

- [x] 4. Extend existing admin course pages in-place for premium pricing management

  **What to do**: Modify the existing admin course create/edit/index flow so monetization is managed directly there, not on a new page. Add controls for `is_premium`, `price_amount`, `price_currency` (default `IDR`, read-only or hidden if only IDR is supported), and optional `is_purchasable`. Keep the same page structure/cards and validation style already used. On save, a free course must persist zero/null price consistently, while a premium course must require a positive integer amount. Also add small status badges/columns in the existing course listing so admins can visually distinguish free vs premium without redesigning the page.
  **Must NOT do**: Do not create a new admin menu section just for pricing. Do not force redesign of `admin/course` pages. Do not allow published premium courses with empty/zero price.

  **Recommended Agent Profile**:
  - Category: `unspecified-high` - Reason: controller validation + form extension + light UI changes on existing admin pages.
  - Skills: `[]` - Existing Bootstrap/admin patterns are sufficient.
  - Omitted: `[frontend-ui-ux]` - UI changes must stay minimal and existing-page-constrained.

  **Parallelization**: Can Parallel: YES | Wave 1 | Blocks: [5] | Blocked By: [1, 2]

  **References** (executor has NO interview context - be exhaustive):
  - Pattern: `app/Controllers/Admin/CourseController.php:48-99` - create/store validation and persistence path.
  - Pattern: `app/Controllers/Admin/CourseController.php:127-244` - edit/update validation and persistence path.
  - Pattern: `app/Views/admin/course/create.php:56-88` - existing settings card where premium fields should be inserted in-place.
  - Pattern: `app/Views/admin/course/edit.php:56-88` - existing settings card for edit flow.
  - Pattern: `app/Views/admin/course/index.php` - existing listing page to extend with premium/status badge, keeping layout intact.
  - API/Type: `app/Database/Migrations/2024-03-22-000003_create_courses_table.php:18-76` - current course fields and naming style.
  - API/Type: `app/Controllers/Admin/CourseController.php:51-59, 145-153` - existing validation approach to extend with monetization rules.

  **Acceptance Criteria** (agent-executable only):
  - [ ] Admin create/edit pages still use the same URLs and layout shell.
  - [ ] Saving `is_premium = false` clears or ignores payment-only fields consistently.
  - [ ] Saving `is_premium = true` requires `price_amount > 0`.
  - [ ] Existing admin course list shows whether a course is free or premium without opening detail/edit.
  - [ ] Plain admin can manage course monetization fields, but user-management privilege rules from T1 remain intact.

  **QA Scenarios** (MANDATORY - task incomplete without these):
  ```
  Scenario: Admin marks a course as premium using existing edit page
    Tool: Bash
    Steps: Authenticate as `admin`; GET `/admin/course/{id}/edit`; submit the existing form via POST with premium enabled and amount `149000`; then GET `/admin/course` and/or query the course row.
    Expected: Update request succeeds; stored course row has premium metadata; admin course list HTML contains the premium indicator/price.
    Evidence: .sisyphus/evidence/task-4-admin-course-premium.png

  Scenario: Premium course cannot be saved with zero price
    Tool: Bash
    Steps: Submit `/admin/course/{id}` with premium enabled and amount `0` using authenticated admin session.
    Expected: Validation response is returned and DB row is not changed into an invalid premium/zero-price state.
    Evidence: .sisyphus/evidence/task-4-admin-course-invalid-price.png
  ```

  **Commit**: NO | Message: `feat(admin): add premium pricing fields to course pages` | Files: `app/Controllers/Admin/CourseController.php`, `app/Views/admin/course/*`, course model if needed

- [x] 5. Replace premium user enroll CTA with internal checkout page and reusable pending transaction flow

  **What to do**: Keep free courses on the current direct-enroll path, but branch premium courses to a new internal checkout page under the authenticated user area. Update the user course list/detail CTAs so free courses keep “Daftar” behavior while premium courses show a buy/checkout action. On checkout submission, create or reuse exactly one active pending transaction per user/course, call the Xendit service, persist the returned checkout URL/external identifiers, and redirect the user to the payment link. If the user is already enrolled in the course, skip checkout and send them back to learning. If the course is unpublished/private or `is_purchasable = false`, block checkout with a clear error state.
  **Must NOT do**: Do not remove free-course enrollment behavior. Do not create a new pending transaction on every button click when a still-valid pending transaction already exists. Do not expose unpublished/private premium courses to checkout.

  **Recommended Agent Profile**:
  - Category: `unspecified-high` - Reason: touches user controllers, views, and transaction-creation behavior.
  - Skills: `[]` - Existing Tailwind/user page patterns suffice.
  - Omitted: `[frontend-ui-ux]` - Flow should follow existing user page patterns, not introduce design-heavy divergence.

  **Parallelization**: Can Parallel: YES | Wave 1 | Blocks: [6] | Blocked By: [2, 3, 4]

  **References** (executor has NO interview context - be exhaustive):
  - Pattern: `app/Views/user/view_course.php:156-176` - current user detail CTA branch to replace for premium courses.
  - Pattern: `app/Views/user/courses.php:93-111` - current course listing CTA branch to replace for premium courses.
  - Pattern: `app/Controllers/Users/CourseController.php:42-96` - user course listing data source; extend payload with monetization state if not already present.
  - Pattern: `app/Controllers/Users/CourseController.php:129-177` - user course detail page; inject premium/payment state for CTA rendering.
  - Pattern: `app/Controllers/CourseController2.php:37-65` - current free enroll path to preserve for non-premium courses only.
  - Pattern: `app/Config/Routes.php:70-77, 86-106` - existing authenticated course/user route groups where checkout routes should be added.
  - API/Type: `app/Models/EnrollmentModel.php:31-38, 114-145` - existing enrolled-user detection and grant behavior.
  - External: `https://docs.xendit.co/docs/payment-links` - redirect flow expectations.

  **Acceptance Criteria** (agent-executable only):
  - [ ] Free courses still route to the existing enroll path and create immediate enrollment.
  - [ ] Premium courses now show checkout/buy CTA on both list and detail pages.
  - [ ] Checkout page shows exact course title and price that will be charged.
  - [ ] Re-opening checkout for the same unpaid course/user reuses the active pending transaction instead of duplicating it.
  - [ ] Already-enrolled users cannot repurchase and are redirected back to learning/access page.

  **QA Scenarios** (MANDATORY - task incomplete without these):
  ```
  Scenario: Premium course sends user to checkout then to Xendit link
    Tool: Bash
    Steps: Authenticate as `student@example.com`; GET `/user/view-course/{premiumCourseId}` and assert checkout CTA marker exists; GET/POST the checkout route; inspect response headers/body and the persisted transaction row.
    Expected: Checkout page/response exposes amount `149000`; submit returns redirect or stored checkout URL pointing to Xendit; transaction row is `pending`.
    Evidence: .sisyphus/evidence/task-5-user-checkout-premium.png

  Scenario: Repeated checkout reuses active pending transaction
    Tool: Bash
    Steps: Create one pending transaction for `student@example.com` + `premium-course-101`; trigger checkout initiation again before expiry.
    Expected: No second active pending row is created; existing transaction/reference is reused and returned.
    Evidence: .sisyphus/evidence/task-5-pending-reuse.txt
  ```

  **Commit**: NO | Message: `feat(checkout): route premium courses through xendit checkout` | Files: user controllers/views/routes plus payment transaction model/service usage

- [x] 6. Process Xendit webhook and return pages with idempotent settlement-to-enrollment flow

  **What to do**: Add a public callback/webhook endpoint plus authenticated return/status pages. Verify webhook authenticity using the configured callback token/header before trusting payloads. On valid payment success, update the transaction to `paid`, store timestamps/payload, and create the enrollment exactly once if it does not already exist. On failure/expiry/cancellation, persist the correct terminal state and keep access blocked. Return pages must only read current DB transaction state and show `success`, `pending/processing`, or `failed/expired`; they must never create enrollment by themselves. If redirect arrives before webhook, the page should say processing/pending until the webhook updates state.
  **Must NOT do**: Do not grant access from redirect alone. Do not make webhook non-idempotent. Do not create duplicate enrollments on callback retries.

  **Recommended Agent Profile**:
  - Category: `unspecified-high` - Reason: callback security, state transitions, and idempotent access grant logic.
  - Skills: `[]` - Standard webhook/controller/database patterns only.
  - Omitted: `[review-work]` - Post-implementation review belongs to final verification, not this task.

  **Parallelization**: Can Parallel: YES | Wave 2 | Blocks: [7, 8] | Blocked By: [2, 3, 5]

  **References** (executor has NO interview context - be exhaustive):
  - Pattern: `app/Config/Routes.php:57-77, 121-191` - route registration style for public and protected endpoints.
  - Pattern: `app/Controllers/CourseController2.php:45-64` - current enroll side effect to move behind trusted payment settlement for premium courses.
  - Pattern: `app/Models/EnrollmentModel.php:114-145` - existing enrollment creation path; reuse or wrap idempotently.
  - Pattern: `app/Database/Migrations/2024-03-22-000007_create_enrollments_table.php:47-50` - unique user/course guard already present in enrollments.
  - API/Type: `app/Config/CURLRequest.php:7-19` - same integration layer family as outbound Xendit work.
  - External: `https://docs.xendit.co/docs/payment-links` - callback payload and verification semantics.

  **Acceptance Criteria** (agent-executable only):
  - [ ] Valid paid webhook transitions transaction to `paid` and creates one enrollment if absent.
  - [ ] Duplicate delivery of the same paid webhook does not create a second enrollment.
  - [ ] Failed/expired/cancelled webhook transitions do not create enrollment.
  - [ ] Return page displays processing state when webhook has not finished yet.
  - [ ] Invalid callback token/signature request is rejected and leaves transaction untouched.

  **QA Scenarios** (MANDATORY - task incomplete without these):
  ```
  Scenario: Paid webhook grants enrollment exactly once
    Tool: Bash
    Steps: POST a simulated valid Xendit paid callback for reference `COURSE-{courseId}-USER-{userId}` twice to the webhook endpoint with the correct callback token/header.
    Expected: Transaction ends in `paid`; exactly one enrollment row exists for the user/course; second POST is idempotent.
    Evidence: .sisyphus/evidence/task-6-webhook-paid-idempotent.txt

  Scenario: Redirect reaches app before webhook settles
    Tool: Bash
    Steps: Authenticate as the buyer; GET the payment return/success URL for a transaction still marked `pending`; inspect HTML and then verify no enrollment row exists yet.
    Expected: Response body shows pending/processing text and no learning-access success state until webhook updates transaction and enrollment exists.
    Evidence: .sisyphus/evidence/task-6-return-before-webhook.png
  ```

  **Commit**: NO | Message: `feat(payments): settle xendit webhooks into enrollments` | Files: routes, payment callback controller(s), models, user return pages

- [x] 7. Enforce premium access gating across learning, reviews, and course availability paths

  **What to do**: Ensure every user-facing access path checks enrollment and premium status consistently after the payment feature lands. Free courses should still use the current logic. Premium courses should allow public/user detail viewing, but learning access, direct enroll links, lesson entry, and review creation must require either an existing enrollment or a valid post-payment enrollment created from T6. If a course flips from free→premium, existing already-enrolled users retain access; only future unenrolled users are forced through checkout. If a course is premium but unpublished/private/not purchasable, block checkout and learning for unenrolled users with a clear message.
  **Must NOT do**: Do not hide course detail pages entirely. Do not break free-course review/enrollment behavior. Do not revoke access from users already enrolled before premium activation.

  **Recommended Agent Profile**:
  - Category: `deep` - Reason: cross-controller access invariants and edge-case handling.
  - Skills: `[]` - No external skill required.
  - Omitted: `[component-refactoring]` - Not a React component refactor task.

  **Parallelization**: Can Parallel: YES | Wave 2 | Blocks: [] | Blocked By: [2, 6]

  **References** (executor has NO interview context - be exhaustive):
  - Pattern: `app/Controllers/CourseController2.php:37-65` - current enroll shortcut to branch by premium state.
  - Pattern: `app/Controllers/CourseController2.php:112-155` - course redirect into lessons should continue to require enrollment.
  - Pattern: `app/Controllers/CourseController2.php:157-217` - lesson viewing currently trusts enrollment presence.
  - Pattern: `app/Controllers/Users/CourseController.php:150-177` - detail page currently derives `isEnrolled` and should add payment-aware CTA/state.
  - Pattern: `app/Controllers/Users/ReviewController.php` - exploration already identified this controller enforces enrollment before review; keep that invariant premium-safe.
  - Pattern: `app/Models/EnrollmentModel.php:31-38, 138-145` - current enrolled checks used broadly.
  - Pattern: `app/Views/user/view_course.php:156-176` - current action area should reflect gated status.
  - Pattern: `app/Views/user/courses.php:97-111` - listing CTA/state also needs consistent gating.

  **Acceptance Criteria** (agent-executable only):
  - [ ] Unpaid users cannot reach premium lesson content via direct URL or the old `/course/{id}/enroll` shortcut.
  - [ ] Existing enrolled users keep learning access even if the course later becomes premium.
  - [ ] Premium course detail pages remain viewable, but action CTA becomes payment-aware.
  - [ ] Review creation for premium courses still requires enrollment, which now indirectly means successful payment for new buyers.
  - [ ] Unpublished/private/not-purchasable premium courses cannot be checked out by unenrolled users.

  **QA Scenarios** (MANDATORY - task incomplete without these):
  ```
  Scenario: Direct lesson/deep-link access is blocked for unpaid user
    Tool: Bash
    Steps: Authenticate as `student@example.com` without enrollment; GET `/course/{premiumCourseId}` then request `/course/{premiumCourseId}/lesson/{lessonId}` and `/course/{premiumCourseId}/enroll`; inspect status, Location headers, and DB.
    Expected: Detail page is viewable through user route; lesson/deep-link access is denied or redirected to checkout/error; no enrollment is created from old shortcut.
    Evidence: .sisyphus/evidence/task-7-premium-access-block.png

  Scenario: Legacy enrolled user retains access after course becomes premium
    Tool: Bash
    Steps: Ensure a user has an enrollment row before toggling `is_premium=true` for that course; then log in as that user and open `/course/{courseId}`.
    Expected: Learning redirect still works; no forced checkout is introduced for the already-enrolled user.
    Evidence: .sisyphus/evidence/task-7-legacy-enrollment-retained.txt
  ```

  **Commit**: NO | Message: `fix(access): gate premium learning on settled payment enrollment` | Files: `app/Controllers/CourseController2.php`, user/review controllers, relevant views/models

- [x] 8. Add admin payment monitoring and preserve existing admin user-management UX with super-admin-only privilege controls

  **What to do**: Add minimal payment operations visibility inside the existing admin shell. Create an admin payments list/detail flow (recommended path: new `admin/payments` routes/controller/views under the existing layout) that shows course, user, amount, status, reference code, Xendit identifier, created/paid/expired timestamps, and a link back to course/user context. Keep `admin/users` page structure the same, but polish the role-based affordances so only super admins see the ability to create/elevate admins while plain admins can still manage non-privileged users if desired by the existing flow. If you add a nav item for payments, place it in the current admin sidebar style without restructuring the menu.
  **Must NOT do**: Do not turn enrollments page into a finance dashboard. Do not redesign the admin shell. Do not expose privileged user-management controls to plain admins.

  **Recommended Agent Profile**:
  - Category: `unspecified-high` - Reason: combines small admin UI extension with sensitive authorization behavior.
  - Skills: `[]` - Existing Bootstrap/admin conventions are enough.
  - Omitted: `[vercel-react-best-practices]` - Not applicable to this PHP/CI4 admin UI.

  **Parallelization**: Can Parallel: YES | Wave 2 | Blocks: [] | Blocked By: [1, 2, 6]

  **References** (executor has NO interview context - be exhaustive):
  - Pattern: `app/Controllers/Admin/EnrollmentController.php:23-69` - existing admin list/detail style for relational records.
  - Pattern: `app/Views/admin/enrollments/index.php` - list-page card/table style to mimic for payments.
  - Pattern: `app/Views/admin/enrollments/show.php` - detail-page style to mimic for transaction details.
  - Pattern: `app/Views/admin/layout.php:48-73` - current sidebar/navigation pattern; add payments entry minimally if needed.
  - Pattern: `app/Controllers/Admin/UserController.php:17-135` - existing user CRUD logic to preserve while enforcing super-admin-only privilege actions.
  - Pattern: `app/Views/admin/users/index.php` - existing users list page to keep structurally the same.
  - Pattern: `app/Views/admin/users/create.php:63-67` and `app/Views/admin/users/edit.php:63-67` - role controls already present and must be privilege-gated, not redesigned.
  - Pattern: `app/Config/Routes.php:121-191` - existing admin route group where payments routes belong.

  **Acceptance Criteria** (agent-executable only):
  - [ ] Admin shell gains payment monitoring access without changing existing page URLs for courses/users/enrollments.
  - [ ] Payments list shows user, course, amount, status, reference, and key timestamps.
  - [ ] Payment detail page exposes enough transaction context to debug pending/failed/paid flows.
  - [ ] Super admin sees controls to create/elevate admin accounts; plain admin does not.
  - [ ] Existing admin pages continue to render and navigate normally after the new menu/item additions.

  **QA Scenarios** (MANDATORY - task incomplete without these):
  ```
  Scenario: Admin can inspect payment transaction state from existing admin shell
    Tool: Bash
    Steps: Authenticate as `admin`; GET `/admin/payments`; then GET `/admin/payments/{id}` for a paid transaction; inspect returned HTML.
    Expected: Both pages render inside existing admin layout markup; transaction output includes course, user, amount, status, reference, timestamps.
    Evidence: .sisyphus/evidence/task-8-admin-payments-monitoring.png

  Scenario: Plain admin cannot elevate privileges on user management pages
    Tool: Bash
    Steps: Authenticate as plain `admin`; GET `/admin/users/create` and `/admin/users/{id}/edit`; inspect role options; attempt direct POST/submit to elevate a user to `admin` and `super_admin`.
    Expected: Role options or submit path prevent creating/updating any account to `admin` or `super_admin`; no privilege escalation occurs.
    Evidence: .sisyphus/evidence/task-8-admin-privilege-guard.png
  ```

  **Commit**: NO | Message: `feat(admin): add payment monitoring and super-admin-only user controls` | Files: admin payment controller/views/routes, admin layout, admin user controller/views

## Final Verification Wave (MANDATORY — after ALL implementation tasks)
> 4 review agents run in PARALLEL. ALL must APPROVE. Present consolidated results to user and get explicit "okay" before completing.
> **Do NOT auto-proceed after verification. Wait for user's explicit approval before marking work complete.**
> **Never mark F1-F4 as checked before getting user's okay.** Rejection or user feedback -> fix -> re-run -> present again -> wait for okay.
- [x] F1. Plan Compliance Audit — oracle
- [x] F2. Code Quality Review — unspecified-high
- [x] F3. HTTP/DB QA Sweep — unspecified-high
- [x] F4. Scope Fidelity Check — deep

## Commit Strategy
- Use one implementation branch.
- No commits during partial schema/controller churn unless a task explicitly requires it.
- Create one final commit only after F1-F4 pass and user gives explicit approval.
- Recommended final commit message: `feat(payments): add premium course checkout with xendit links and super admin guards`

## Success Criteria
- Premium course monetization works end-to-end with Xendit payment links.
- Enrollment remains the only access-grant artifact, but it is created solely from settled payment state.
- Free course flow remains intact.
- Admin layout/pages remain familiar; only fields/status sections are extended.
- Super admin can create admin users; admin cannot escalate privileges.
