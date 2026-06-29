

## 2026-04-26 — Xendit Payment Links (legacy) research

### Official sources
- https://docs.xendit.co/docs/payment-links-api-overview
- https://docs.xendit.co/payment-link/notification-and-callback/
- https://docs.xendit.co/webhook
- https://docs.xendit.co/payment-link/statuses/
- https://docs.xendit.co/payment-link/faq/

### Confirmed behavior
- Create via `POST /v2/invoices`.
- Documented request fields include: `external_id`, `amount`, `description`, `invoice_duration`, `customer { given_names, surname, email, mobile_number }`, `success_redirect_url`, `failure_redirect_url`, `currency`, `items[] { name, quantity, price, category, url }`, and `metadata`.
- Response fields documented for payment-link creation include: `id`, `external_id`, `user_id`, `status` (`PENDING`), `merchant_name`, `merchant_profile_picture_url`, `amount`, `description`, `expiry_date`, `invoice_url`, channel availability arrays (`available_banks`, `available_retail_outlets`, `available_ewallets`, `available_qr_codes`, `available_direct_debits`, `available_paylaters`), `should_exclude_credit_card`, `should_send_email`, `success_redirect_url`, `failure_redirect_url`, `created`, `updated`, `currency`, `items`, `customer`, and `metadata`.
- The customer should be redirected using `invoice_url`.
- On success, the hosted checkout redirects to `success_redirect_url`; failures keep the user on the same payment page so they can retry.
- Lifecycle statuses documented for Payment Links are only `PENDING`, `PAID`, and `EXPIRED`.
- `PENDING` starts immediately after creation and remains until payment succeeds, the invoice expires, or the link is manually expired.
- `PAID` is emitted when payment succeeds; webhook notification includes the paid invoice payload.
- `EXPIRED` occurs when `invoice_duration` is reached or the link is manually expired; the end user loses access to `invoice_url`, and the link cannot be revived.
- The payment webhook docs say Xendit posts payment notifications to the configured webhook URL and recommends server-side handling with quick `2xx` acknowledgement.
- Webhook delivery retries up to six times with exponential backoff if the endpoint does not return `2xx`.
- Webhook authentication for Payment Link flows is documented via the `x-callback-token` header; the token is retrieved from Dashboard Webhook settings and should be kept secret.
- I did not find a Payment-Link-specific HMAC signature header in the Payment Link docs; the signature-style `X-Callback-Signature` documentation I found applies to bill-payment callbacks, not Payment Links.

### Practical takeaway
- For trusted server-side settlement, rely on the webhook plus `x-callback-token` verification and idempotency on `payment_id` / `external_id`.
- Do not treat redirect URLs as proof of payment; the docs position the webhook as the authoritative completion signal.
- Legacy Payment Links docs are still published in 2026, but Xendit also advertises newer Payment Sessions; this research intentionally stays on the legacy Payment Link flow.

## 2026-04-26 — Internal code-path map for plan T1 (RBAC) and T4 (admin course monetization)

### T1 RBAC / admin access / auth-session redirect map
- `app/Config/Routes.php:121-191` puts every `/admin/*` route behind the `admin` filter alias; this is the top-level guard for dashboard, courses, modules, lessons, categories, users, enrollments, and admin API endpoints.
- `app/Config/Filters.php:29-41` binds the `admin` alias to `App\Filters\AdminFilter` and the `user` alias to `App\Filters\UserFilter`, so all RBAC behavior fans out from those two filter classes.
- `app/Filters/AdminFilter.php:11-20` is the hard gate today: it redirects unauthenticated users to `/login`, then rejects any session whose `role !== 'admin'` by redirecting to `/user/dashboard` with an error flash. This is the most direct blocker for `super_admin` support.
- `app/Filters/UserFilter.php:11-16` only checks `isLoggedIn`; it does not care about role. Result: both `admin` and `user` can reach any route protected only by `user`.
- `app/Controllers/AuthController.php:9-17` and `55-63` redirect already-logged-in users using a binary check: `role === 'admin' ? '/admin/dashboard' : '/user/dashboard'`.
- `app/Controllers/AuthController.php:19-52` handles login. After password verification it stores the session via `setUserSession()` and then again uses a binary branch: only literal `admin` is routed to `/admin/dashboard`; everyone else goes to `/user/dashboard`.
- `app/Controllers/AuthController.php:65-77` forces signup-created accounts to `role = 'user'` regardless of posted input, which already matches the T1 acceptance criterion for public signup.
- `app/Controllers/AuthController.php:86-95` writes `id`, `username`, `email`, `role`, `full_name`, and `isLoggedIn` to session; any role expansion must preserve this payload because the rest of the app reads `session()->get('role')` directly.
- `app/Controllers/BaseController.php:64-77` defines shared helpers `isLoggedIn()` and `isAdmin()`. `isAdmin()` is also binary today (`role === 'admin'`), so any downstream code reusing it will inherit that assumption.
- `app/Controllers/BaseController.php:94-106` exposes `getCurrentUser()` with the raw session role. That means view/controller consumers already expect the role string to be available centrally.
- `app/Models/UserModel.php:15-24` allows `role` writes through the model, so CRUD restrictions are not enforced here by allowed-fields; they depend on validation/controller policy.
- `app/Models/UserModel.php:31-37` validates `role` using `in_list[admin,user]`, which rejects `super_admin` everywhere the model validation runs.
- `app/Models/UserModel.php:99-103` has an `isAdmin($userId)` helper that only returns true for literal `admin`; hidden dependency if later logic relies on that helper for privilege checks.
- `app/Database/Migrations/2024-03-22-000001_create_users_table.php:35-39` defines `role` as a plain `VARCHAR(20)` defaulting to `user`; the schema itself does not block `super_admin`, so the current limitation is application-side rather than storage-side.

### T1 admin user CRUD privilege map
- `app/Controllers/Admin/UserController.php:26-29` renders the create page without any privilege branching; any authenticated `admin` who passes `AdminFilter` can reach it today.
- `app/Controllers/Admin/UserController.php:31-60` validates create requests with `role in_list[admin,user]`, then inserts the posted role directly. There is no server-side restriction preventing a normal admin from creating another admin.
- `app/Controllers/Admin/UserController.php:63-76` renders the edit page for any found user, again with no privilege branching.
- `app/Controllers/Admin/UserController.php:78-121` validates edit requests with the same `in_list[admin,user]` rule and updates the posted role directly. There is no protection against self-promotion/demotion patterns beyond the current two-value role list.
- `app/Controllers/Admin/UserController.php:124-135` allows deletion of any found user; there is no guard for deleting privileged users or self-deletion.
- `app/Views/admin/users/create.php:62-68` renders the role select with only `user` and `admin` options.
- `app/Views/admin/users/edit.php:62-68` renders the same two role options for editing.
- `app/Views/admin/users/index.php:5-10` always shows the “Add New User” CTA; if T1 limits admin creation to `super_admin`, this CTA or the target route behavior must change.
- `app/Views/admin/users/index.php:47-53` renders only two visual states: `admin` gets a red badge and everything else is shown as `User`. `super_admin` would currently be mislabeled as `User` even if stored in DB.
- `app/Views/admin/layout.php:68-76` keeps “Users” and “Enrollments” in the shared admin nav for every admin-role session, so T1 needs in-place permission changes rather than navigation restructuring.
- `app/Views/admin/layout.php:84-87` hardcodes the footer label to `Admin User`; hidden presentation dependency if `super_admin` should be visible in the shell.

### T4 admin course monetization map
- `app/Config/Routes.php:131-137` defines the existing admin course CRUD endpoints that T4 must extend in place: list, create form, store, edit form, update, delete.
- `app/Controllers/Admin/CourseController.php:39-46` loads categories and renders `admin/course/create`; no monetization data is prepared yet.
- `app/Controllers/Admin/CourseController.php:48-99` stores courses. Validation covers title/slug/description/status/level/duration/thumbnail only (`51-59`), and persisted data only includes title, slug, description, short description, status, level, duration, thumbnail, `created_by`, `is_featured`, and `published_at` (`71-83`). There are no premium, price, currency, or purchasable fields anywhere in the current save path.
- `app/Controllers/Admin/CourseController.php:101-125` loads the edit page with `course`, `categories`, and `selectedCategories`; again no monetization data preparation exists.
- `app/Controllers/Admin/CourseController.php:127-245` updates courses. Validation again only covers title/slug/description/status/level/duration/thumbnail (`145-153`), and the update payload only includes title, slug, description, short description, status, level, duration, and `is_featured` (`168-177`) plus optional thumbnail and `published_at` (`185-196`). No pricing-related persistence exists.
- `app/Controllers/Admin/CourseController.php:23-37` builds the admin course list from `CourseModel->findAll()` and per-course review averages; there is no monetization decoration for the listing.
- `app/Models/CourseModel.php:15-19` limits mass assignment to title/slug/description/short_description/thumbnail/status/created_by/published_at/duration/level/is_featured. Any premium metadata added at schema level will not persist until this list expands.
- `app/Models/CourseModel.php:25-30` validates only title, slug, status, and level; price/premium validation does not exist yet.
- `app/Database/Migrations/2024-03-22-000003_create_courses_table.php:11-76` defines the current `courses` schema. Existing course-admin settings are `status`, `created_by`, `published_at`, `duration`, `level`, and `is_featured`; there are no monetization columns yet.
- `app/Views/admin/course/create.php:55-88` is the entire “Course Settings” card in the create form. It currently exposes `status`, `level`, `duration`, and `is_featured` only; this is the clearest in-place insertion point for premium/pricing UI while preserving the existing page structure.
- `app/Views/admin/course/edit.php:55-88` mirrors the same “Course Settings” card on edit, so T4 will likely need symmetric field additions here.
- `app/Views/admin/course/index.php:23-88` is the current admin list rendering. Columns are ID, Thumbnail, Title, Status, Level, Rating, Created, and Actions. No price/premium column or badge exists today, so T4 list rendering work belongs here if monetization must surface in the existing list.
- `app/Controllers/Admin/DashboardController.php:31-47` uses total course counts and course status counts only. If T4/T8 later need monetization visibility in the existing admin shell, this dashboard is an adjacent touch point but not part of the current course CRUD flow.

### Current free-course assumptions that T4/T7 will collide with
- `app/Controllers/CourseController2.php:37-65` auto-enrolls any logged-in user into a course as soon as they hit `/course/{id}/enroll`, with no premium/payment check. This is the main server-side path that must remain only for free courses.
- `app/Models/EnrollmentModel.php:114-136` creates the enrollment row directly and idempotently if already enrolled; no transaction/payment state is consulted.
- `app/Controllers/CourseController2.php:112-155` and `157-217` gate course-content access entirely through an existing enrollment record. That is good for paid access later, but it also means premium access will still be bypassed unless the enroll/create-enrollment path is changed.
- `app/Controllers/Users/CourseController.php:129-177` renders the user course detail page and computes only `isEnrolled`/`progress`; there is no concept of a pending checkout or premium metadata passed to the view.
- `app/Views/user/view_course.php:156-176` hardcodes the unenrolled CTA as `Daftar Kursus Ini (Gratis)` linking straight to `/course/{id}/enroll`.
- `app/Views/user/courses.php:81-87` hardcodes a `Gratis` badge on every course card in the catalog.
- `app/Views/user/courses.php:107-110` hardcodes the non-enrolled CTA to `/course/{id}/enroll` with `Daftar Sekarang`.
- `app/Cells/course_card.php:13-18` hardcodes a `Gratis` badge in the reusable card partial used outside the enrolled-progress mode.
- `app/Cells/course_card.php:77-80` hardcodes the non-enrolled CTA to `/course/{id}/enroll`; this is a second presentation path beyond `user/courses.php`.
- `app/Views/course/module.php:66` and `app/Views/components/module_list.php:57` display `(Gratis)`, which are additional hidden copy-level dependencies if premium labels must appear consistently.

### Hidden / adjacent dependencies worth carrying into T1 or T4 prompts
- `app/Config/Routes.php:176-190` defines admin API routes for `updateStatus` and `toggleFeatured`, but `app/Controllers/Admin/CourseController.php:10-260` does not currently implement those methods. This is unrelated to premium directly, but it shows the admin course area already has route/controller drift.
- `app/Views/admin/layout.php:33-37` and `78-80` link admin-shell settings to `user/profile`, so admin-role users still depend on user-space routes guarded only by the broad `user` filter.
- `app/Models/CourseModel.php:45-116` filters public/user course discovery by `status = 'published'` only. Premium metadata can be additive without changing course visibility, but purchasable logic should not assume unpublished/private courses are exposed.
- `app/Controllers/Users/ReviewController.php:47-60`, `77-90`, and `106-107` (found via grep) depend on enrollment to allow reviews. Because paid access will still materialize as enrollment, review permissions should continue to work if settlement grants enrollment correctly.
- `app/Controllers/Admin/EnrollmentController.php:23-69` is the only existing admin list/detail view over granted access records. Payment monitoring in later tasks will likely sit adjacent to this flow, not replace it.


## 2026-04-26 — CodeIgniter 4 docs research for payment plan

### Official sources
- https://codeigniter.com/user_guide/general/configuration.html
- https://codeigniter.com/user_guide/general/environments.html
- https://codeigniter.com/user_guide/concepts/services.html
- https://codeigniter.com/user_guide/libraries/curlrequest.html
- https://codeigniter.com/user_guide/dbmgmt/migration.html
- https://codeigniter.com/user_guide/incoming/filters.html
- https://codeigniter.com/user_guide/incoming/routing.html
- https://codeigniter.com/user_guide/incoming/controllers.html
- https://codeigniter.com/user_guide/libraries/sessions.html
- https://codeigniter.com/user_guide/outgoing/response.html

### Confirmed behavior
- `.env` is loaded automatically from the project root, and env values are read via `getenv()`, `$_SERVER`, or `$_ENV`.
- Config classes should extend `BaseConfig`; env vars can only replace existing scalar config values when the variable prefix matches the config class namespace or short name. They cannot add new properties or array elements.
- `service('curlrequest')` returns a shared HTTP client instance; later calls with different options are ignored because the same instance is reused.
- `CURLRequest` supports `baseURI`, which is the right fit for an API client pointed at a fixed upstream base path.
- CI4 migrations are timestamped, live in `app/Database/Migrations`, and run with `spark migrate` / `spark migrate --all`.
- A migration is executed against one DB group, while the `migrations` tracking table always lives in the default DB group.
- For foreign-key-heavy migrations, CI4 explicitly recommends temporarily disabling/enabling FK checks on the DB connection.
- The official PostgreSQL examples use DB-specific types like `inet`, `timestamptz`, and `bytea` for session tables.
- The safest filter pattern is to disable auto-routing and apply filters to routes or route groups.
- Route groups can carry filters, and route-level filters are a first-class pattern for auth / logging / gated areas.
- `redirect()->back()` uses session-backed history when available, supports `withInput()`, and supports flash messages via `with('key', 'value')`.
- Flashdata survives one request; `close()` can be used to release the session lock once session writes are done.
- Controllers should extend `BaseController`; controllers can be organized into subdirectories under `app/Controllers`, and routes should be declared in `app/Config/Routes.php` with verb-specific route methods rather than `add()` for new code.

### Repo-relevant takeaway
- Use env-backed config for secrets and deployment-specific values, a shared `curlrequest` service for the Xendit client, additive migrations with FK-safe handling, route-group filters for auth redirects, and controller namespaces/subdirectories that mirror route organization.

## 2026-04-26 — T3 Xendit integration foundation implementation note

- Added `app/Config/Xendit.php` as the single env-backed source for Xendit secret key, callback token, redirect URLs, duration, currency, and HTTP timeout values; nothing payment-secret-related lives in controllers.
- Added `Services::xenditHttpClient()` with a dedicated non-shared `curlrequest` instance so the Xendit base URI + auth headers do not leak across unrelated HTTP calls.
- Added `App\Services\Payments\XenditPaymentLinkService` to build the `POST /v2/invoices` payload from internal transaction-style data and to extract persistence-ready provider metadata (`checkout_url`, `xendit_invoice_id`, `xendit_external_id`, normalized `status`, `expires_at`).
- Reference-code strategy is deterministic: first attempt uses `COURSE-{courseId}-USER-{userId}`, and historical retries append `-R{n}` based on prior attempt count (example: third attempt => `COURSE-42-USER-9-R3`).
- Added `App\Services\Payments\XenditPaymentStatusMapper` as the canonical normalizer for provider status/event strings into internal `pending|paid|failed|expired|cancelled`; unsupported values are logged and throw immediately so later webhook/redirect handlers cannot silently grant access.
- Added `php spark xendit:smoke` as a safe dry-run hook for later QA. `php spark xendit:smoke payload 42 9 150000 2` exercises payload generation without network/enrollment, while `php spark xendit:smoke map PAID invoice.paid` and `php spark xendit:smoke map totally-unknown` cover accepted vs rejected status normalization.

## 2026-04-26 — T1 RBAC implementation note

- Updated RBAC in place without changing admin route structure: `app/Filters/AdminFilter.php` now admits both `admin` and `super_admin` to `/admin/*`.
- Centralized role checks in `app/Controllers/BaseController.php` via `hasRole()`, `isSuperAdmin()`, and a shared `getDashboardPath()` so auth redirects and downstream helpers stay consistent.
- Kept signup behavior unchanged: `app/Controllers/AuthController.php` still forces new public registrations to `role = user`.
- Expanded role validation in `app/Models/UserModel.php` to `super_admin|admin|user`, and updated `isAdmin()` so admin-area checks continue to treat `super_admin` as an admin-capable account.
- Hardened `app/Controllers/Admin/UserController.php` server-side: only `super_admin` can assign or manage privileged roles; plain `admin` can only create/edit `user` accounts and is blocked from direct POST escalation plus privileged edit/delete access.
- Mirrored those restrictions in `app/Views/admin/users/create.php` and `edit.php` by rendering only assignable roles for the current operator, while preserving existing page URLs and layout.
- Updated `app/Views/admin/users/index.php` badge rendering to show `Super Admin` distinctly.
- No migration was added because `app/Database/Migrations/2024-03-22-000001_create_users_table.php` already stores `role` as `VARCHAR(20)`, which is sufficient for `super_admin`.

## 2026-04-26 — T1 RBAC validation follow-up

- Adjusted admin user username validation in `app/Models/UserModel.php` and `app/Controllers/Admin/UserController.php` from `alpha_numeric_space` to a regex that still limits usernames to letters, numbers, spaces, and hyphens, so the QA scenario username `ops-admin` is accepted.
- Kept the server-side privileged-role enforcement unchanged: plain `admin` still only gets `user` in assignable role validation and remains blocked from direct POST creation/elevation to `admin` or `super_admin`.

## 2026-04-26 — T4 admin course monetization implementation note

- Extended the existing admin course create/edit settings card in place for `is_premium`, `price_amount`, `price_currency`, and `is_purchasable`, without changing admin course routes or page structure.
- Admin course save validation now treats `price_amount` as an integer and rejects premium saves unless `price_amount > 0`; the live `/admin/course/{id}` AJAX check returned `422` with `price_amount: Price must be greater than 0.` for the zero-price case.
- Free-course saves now normalize deterministically by forcing `is_premium = false`, `price_amount = null`, `price_currency = 'IDR'`, and `is_purchasable = false`, which was verified against the database after an authenticated admin update.
- Because this repo runs on PostgreSQL, the admin course controller now writes boolean course flags through explicit SQL casts in `CourseController` instead of relying on the older model update path that serialized booleans as `1/0` and failed on `BOOLEAN` columns.

## 2026-04-26 — T4 debug-noise cleanup follow-up

- Removed temporary edit-form `console.log` / `console.error` output and monetization-related `log_message('debug', ...)` traces from the T4 admin course files without changing the existing AJAX submit flow or premium-price validation behavior.

## 2026-04-26 — T4 implementation note (verification follow-up)

- The admin course create/edit flow now manages `is_premium`, integer `price_amount`, `price_currency`, and `is_purchasable` in the existing settings UI and keeps the same routes plus AJAX edit behavior.
- Premium saves require `price_amount > 0`, while free saves clear premium-only state so later checkout work can trust the stored course monetization fields.

## 2026-04-26 — T4 debug-noise cleanup note (verification follow-up)

- Removed leftover T4 debug output from the admin course files, including edit-page browser console noise and temporary monetization debug traces, without changing the verified premium-save or invalid-price behavior.

## 2026-04-26 — T7 premium access gating implementation note

- `app/Controllers/CourseController2.php` now treats active enrollment as the only learning artifact, but when an unenrolled user deep-links into a premium course it no longer falls through to enrollment-only 404s: published+purchasable premium routes redirect to checkout, while unpublished/private/not-purchasable premium routes redirect back to the visible course detail page with a payment-aware block message.
- The legacy `/course/{id}/enroll` shortcut still enrolls free courses immediately, but premium courses are never granted access there; they either redirect to checkout or back to detail depending on purchasable/published state.
- Write-side learning endpoints now also enforce the same trust boundary: `api/mark-complete/{course}/{lesson}` and the lesson-progress mutation route reject users without an active enrollment instead of letting lesson-progress rows materialize without course access.
- Review creation remains enrollment-gated, and `ReviewController::create()` now blocks unenrolled users before rendering the form, so for new premium buyers review access still depends on the webhook-created enrollment rather than on payment-return URLs.
- Verification used controlled PostgreSQL fixtures with one published premium course, one blocked premium course, one private premium course, and one legacy-enrolled course flipped to premium; unpaid deep links were redirected away from learning, no new enrollment row was created for the unpaid user, and the legacy enrolled user still reached both the lesson page and review-create page after the course became premium.
# Premium course payment / enrollment code-path map

## Route map relevant to T2, T5, T6, T7, T8
- `app/Config/Routes.php:66-83` defines the current course-learning flow under `course/*` with user filter: public listing at `/course`, then gated routes for `/:id`, `/:id/lesson/:lessonId`, `/:id/enroll`, plus lesson progress APIs. This is the main surface T5-T7 will need to extend or branch.
- `app/Config/Routes.php:85-106` defines user dashboard/list/detail routes: `/user/courses`, `/user/courses/enrolled`, `/user/view-course/:id`, and certificate/profile/settings pages. T5 user checkout CTA work has to hook into these pages, especially `view-course` and `courses/enrolled`/card rendering.
- `app/Config/Routes.php:108-116` defines review routes under `course/:id/reviews*`. T7 review gating must preserve this route family because it already ties review eligibility to enrollment checks in the controller.
- `app/Config/Routes.php:121-190` defines the admin shell. Existing relational list/detail pattern already exists for enrollments at `admin/enrollments` and `admin/enrollments/:id`; payment monitoring for T8 can mirror this without adding a new admin shell.

## Current free enrollment flow (source of truth today)
- Entry CTA for free enrollment is hardcoded in the user detail page at `app/Views/user/view_course.php:156-176`. If `isEnrolled` is false it renders `site_url('course/' . $course['id'] . '/enroll')` with text `Daftar Kursus Ini (Gratis)`.
- Course-list CTA is similarly hardcoded in `app/Views/user/courses.php:97-110`: non-enrolled users see `site_url('course/' . $course['id'] . '/enroll')`, while enrolled users go straight to `site_url('course/' . $course['id'])`.
- The actual free enrollment action is `CourseController2::enroll()` in `app/Controllers/CourseController2.php:37-65`:
  - checks course existence via `CourseModel::find()` (`:39-43`)
  - forces login (`:45-49`)
  - checks existing enrollment via `EnrollmentModel::getEnrollment()` (`:51-55`)
  - immediately grants access by calling `EnrollmentModel::enrollUser()` (`:57`)
  - redirects to `/course/{id}` on success (`:64`)
- `EnrollmentModel::enrollUser()` in `app/Models/EnrollmentModel.php:114-136` is the grant point today. It short-circuits if `isEnrolled()` is already true (`:117-119`) and otherwise inserts directly into `enrollments` with `user_id`, `course_id`, `is_active=true`, zero progress, and current `enrolled_at` (`:121-131`). This is the key T6 blocker: access is currently granted synchronously from a user-initiated GET, not from a trusted payment state.

## Enrollment model usage and constraints
- `app/Database/Migrations/2024-03-22-000007_create_enrollments_table.php:11-52` creates `enrollments` with `user_id`, `course_id`, `enrolled_at`, `completed_at`, `progress_percentage`, `is_active`; adds a unique key on `[user_id, course_id]` at `:48`. That unique key already enforces one enrollment per user/course, which is important when designing idempotent paid grant behavior in T6.
- `EnrollmentModel::isEnrolled()` (`app/Models/EnrollmentModel.php:31-38`) is the broad “has access” check used across user flows; it only tests for an active enrollment row.
- `EnrollmentModel::getUserEnrollments()` (`app/Models/EnrollmentModel.php:40-49`) is what powers the enrolled-course dashboard list.
- `EnrollmentModel::getEnrollmentWithProgress()` (`app/Models/EnrollmentModel.php:51-66`) loads the enrollment row and then attaches lesson progress. This is used to route users back into learning and to populate progress-aware UIs.
- `EnrollmentModel::updateProgress()` (`app/Models/EnrollmentModel.php:68-112`) recomputes course progress by traversing all lessons in the course and counting `lesson_progress.status === 'completed'`.
- `EnrollmentModel::getEnrollment()` (`app/Models/EnrollmentModel.php:138-145`) is the existence check used in the free enroll controller path.

## Lesson access enforcement and learning gates
- Learning entry route `/course/:id` hits `CourseController2::redirectCourse()` (`app/Controllers/CourseController2.php:112-155`). It requires login (`:114-117`), then loads `getEnrollmentWithProgress()` (`:119`), and rejects non-enrolled users with `show404("Anda belum melakukan enrollment untuk course ini")` at `:123-125`. If enrolled, it redirects either to the last-progress lesson (`:127-134`) or the first lesson in the first module (`:136-154`).
- Deep-link lesson route `/course/:id/lesson/:lessonId` hits `CourseController2::courseById()` (`app/Controllers/CourseController2.php:157-217`). It requires login (`:161-164`), loads course content (`:166-172`), reloads enrollment progress (`:174`), and hard-blocks non-enrolled users with `show404("Anda belum melakukan enrollment untuk course ini")` at `:176-179`. This is the main T7 server-side access gate for premium content.
- Lesson completion endpoints do **not** re-check enrollment before mutating progress:
  - `markCourseCompleted()` in `app/Controllers/CourseController2.php:68-89` only checks login, then calls `LessonProgressModel::markAsCompleted()`.
  - `markComplete()` in `app/Controllers/CourseController2.php:283-323` checks only lesson_id and logged-in user, updates lesson progress, then calls `EnrollmentModel::updateProgress()` on the lesson's course (`:315-317`).
  - `updateProgress()` in `app/Controllers/CourseController2.php:325-340` is mostly a stub and currently only checks login and lesson_id.
- `LessonProgressModel::markAsCompleted()` (`app/Models/LessonProgressModel.php:76-129`) mutates `lesson_progress` and then blindly updates course enrollment progress via `EnrollmentModel::updateProgress()` (`:88-99`, `:113-124`). T7 should be careful: route-level user auth exists, but write-side lesson progress methods assume access has already been validated elsewhere.
- Schema dependencies for learning gates:
  - `app/Database/Migrations/2024-03-22-000006_create_lessons_table.php:11-65` defines lesson ordering via `module_id` + `order_index`.
  - `app/Database/Migrations/2024-03-22_000009_create_lesson_progress_table.php:11-53` defines `lesson_progress` and unique `[user_id, lesson_id]` rows.

## User course list/detail pages and CTA seams for T5
- `Users\CourseController::index()` (`app/Controllers/Users/CourseController.php:42-96`) loads published/filterable courses via `CourseModel::getFilteredCourses()` and builds `enrolledCourseIds` plus `courseProgress` by repeatedly calling `EnrollmentModel::isEnrolled()` and then querying the enrollment row (`:66-77`). T5 premium/free CTA branching can reuse the same per-course enrollment state, but this controller currently has no payment or premium metadata.
- `Users\CourseController::enrolled()` (`app/Controllers/Users/CourseController.php:98-127`) gets courses from `EnrollmentModel::getUserEnrollments()`, buckets them by progress state, and renders `user/enrolled-courses`. This page is only for already-enrolled items, so paid courses should appear here automatically once an enrollment is created.
- `Users\CourseController::viewCourse()` (`app/Controllers/Users/CourseController.php:129-178`) is the detail page source. It loads the course, modules, lessons, enrollment state, and review stats; it sets `isEnrolled` via `EnrollmentModel::isEnrolled()` at `:154-162`. This is the cleanest T5 seam for “free enroll” vs “buy/checkout” CTA switching because the view already consumes `isEnrolled`.
- User-facing course-card list pattern for enrolled pages is indirect: `app/Views/user/enrolled-courses.php:38-48,61-71,84-94,107-115` renders `view_cell('App\Cells\CourseCardCell', ...)`. The cell wrapper is trivial in `app/Cells/CourseCardCell.php:17-26`, so any CTA/status change that must affect enrolled cards probably lives in the underlying `app/Views/course_card.php` partial (not yet needed for free-vs-premium list CTA because the public explorer uses `user/courses.php` directly).

## Review gates and how they relate to entitlement
- Review routes are public at routing level (`app/Config/Routes.php:108-116`) but controller-level auth is enforced.

## 2026-04-26 — T8 admin payment monitoring implementation note

## 2026-04-26 — T7 premium access gating concise follow-up

- Premium lesson deep links and the legacy `/course/{id}/enroll` shortcut now stay payment-aware: unenrolled users are redirected to checkout only when the premium course is published and purchasable, otherwise back to the still-viewable detail page with a blocked-access message.
- Existing enrolled users keep learning and review access even if a previously free course is later flipped to premium, because enrollment remains the sole access artifact.
- Review creation is now blocked before form render for unenrolled users, so new premium buyers only gain review access after the trusted enrollment grant exists.

- Added `App\Controllers\Admin\PaymentController` with `/admin/payments` list/detail pages that mirror the existing admin enrollment pattern: breadcrumb header, Bootstrap cards, bordered tables, and detail cards inside the current admin shell.
- The payment list now surfaces the transaction-monitoring fields ops needs in one scan: user identity, course, amount/currency, internal + provider status, reference/provider IDs, and key timestamps (`created_at`, `expires_at`, `paid_at`, `last_webhook_at`).
- The payment detail page is intentionally diagnostic rather than dashboard-like: it shows enrollment linkage, redirect/provider URLs, failure metadata, and stored request/response/webhook/status payload snapshots so pending/failed/paid flows can be debugged from the admin UI.
- The admin sidebar only gained a single `Payments` entry and the footer now reflects the logged-in admin name/role, preserving the existing shell structure while making `super_admin` presence visible.
- User-management polish stayed within the T1 rules: plain admins still reach the existing user pages, but the users index now hides edit/delete actions for privileged accounts and the create/edit forms explain privileged-role controls only when the operator is a `super_admin`.
- `Users\ReviewController::index()` (`app/Controllers/Users/ReviewController.php:35-62`) requires login (`:37-40`) and calculates `isEnrolled` (`:47`) only to drive UI state in `user/course-reviews`.
- `Users\ReviewController::create()` (`app/Controllers/Users/ReviewController.php:64-92`) requires login and course existence, calculates `isEnrolled` (`:76-77`), but does **not** block non-enrolled users server-side before rendering the form. It only blocks duplicate reviews (`:80-85`). This is a subtle gating gap relevant to T7.

## 2026-04-26 — T4 admin-course monetization summary

- T4 extended the existing admin course pages in place: `create.php` and `edit.php` now manage `is_premium`, `price_amount`, `price_currency`, and `is_purchasable` inside the current settings card, while `index.php` shows free-vs-premium status plus pricing context without changing routes or the admin shell.
- The admin save path enforces integer pricing for premium courses and rejects `is_premium = true` unless `price_amount > 0`.
- Free-course saves normalize monetization state by clearing premium-only values so `is_premium = false` does not leave stale payment data behind.
- A later T4 cleanup removed temporary debug noise from the admin course files, including browser `console.*` output and monetization-specific debug traces, without changing the verified save and validation behavior.
- `Users\ReviewController::store()` (`app/Controllers/Users/ReviewController.php:94-136`) is the true server-side review gate: it refuses non-enrolled users at `:105-109` before allowing insert. If premium enrollment becomes payment-backed, this gate will automatically behave correctly as long as paid access still culminates in a normal `enrollments` row.
- `user/course-reviews` view uses `isEnrolled` to show/hide the write-review CTA (`app/Views/user/course-reviews.php:22-39,109-113`).
- Review persistence schema/model:
  - `app/Database/Migrations/2024-05-15-000001_create_course_reviews_table.php:11-52` defines `course_reviews` with foreign keys to course and user.
  - `app/Models/CourseReviewModel.php:29-96` exposes `getCourseReviews`, `getAverageRating`, `getRatingDistribution`, `hasUserReviewed`, and `getUserReview`; there is no entitlement logic here.

## Admin relational list/detail patterns to mimic for T8 payment monitoring
- Best list/detail shell to imitate is enrollments:
  - controller list in `app/Controllers/Admin/EnrollmentController.php:23-39` does a joined select across `enrollments`, `users`, and `courses`, ordered by newest enrolled date.
  - controller detail in `app/Controllers/Admin/EnrollmentController.php:41-69` loads one joined record and then performs a second query for child rows (`lesson_progress`) scoped by the enrollment's `user_id` + `course_id`.
  - list view in `app/Views/admin/enrollments/index.php:11-87` uses a card + `simpleDatatables` table with a single “View Details” action.
  - detail view in `app/Views/admin/enrollments/show.php:12-139` uses a two-column layout: summary card on the left, related child records table on the right.
- Another related relational detail pattern exists for course reviews:
  - routes at `app/Config/Routes.php:127-129`
  - controller at `app/Controllers/Admin/CourseReviewController.php:23-79`
  - view at `app/Views/admin/course/reviews.php:12-137`
  This pattern is course-scoped rather than global list/detail, but it shows how the admin course index links into child records.
- Admin course index seam for monetization badges/links is `app/Views/admin/course/index.php:23-93`; it already displays status/level/rating columns and action buttons. T8 payment monitoring should likely stay as its own section under `admin/*`, but T4 monetization badges/columns can be added here in-place.

## Existing payment / integration patterns
- There is **no** existing payment, checkout, transaction, or Xendit implementation in `app/*` right now; repository search for `checkout|payment|transaction|xendit` returned no app-code matches.
- The closest existing external-output/integration style is `app/Controllers/Users/CertificateController.php:26-145`, which:
  - checks entitlement via enrollment/progress first (`:31-49`)
  - loads dependent data/models (`:51-72`)
  - delegates heavy output generation to a helper method (`generatePDF`) (`:74-145`)
  - relies on config objects (`Config\Dompdf`) rather than hardcoding settings (`:81-90`)
  This is a useful structural precedent for a future payment service/controller split, even though it is not an HTTP API integration.
- `app/Config/CURLRequest.php:7-19` is the existing CI config entrypoint for HTTP client behavior; if T3/T5 introduce Xendit calls, using framework HTTP client/config conventions will match the project style.

## Schema and model blockers/shared dependencies for T2, T5, T6, T7, T8
- `courses` currently has no premium/payment metadata. `app/Database/Migrations/2024-03-22-000003_create_courses_table.php:11-83` and `app/Models/CourseModel.php:15-19` only cover standard content fields (`status`, `duration`, `level`, `is_featured`, etc.). T2 must add monetization columns and T4/T5 must expand `CourseModel::$allowedFields`.
- Because `EnrollmentModel::enrollUser()` is the current entitlement grant point and `course/:id/enroll` is a GET route, T5/T6 must avoid reusing this endpoint for premium flows unless it becomes conditional on course monetization. Otherwise premium access would still be grantable by direct deep link.
- `CourseController2::redirectCourse()` and `courseById()` gate only on enrollment existence, not on transaction state. This is actually good for T7 if T6 remains the only place that creates paid enrollments; the access layer can stay simple if the trusted webhook path is the only way to insert premium enrollments.
- Review write-gating already depends on `EnrollmentModel::isEnrolled()` in `ReviewController::store()` (`app/Controllers/Users/ReviewController.php:105-109`), so paid entitlement can reuse the same model artifact with no review-specific payment logic.
- Admin monitoring should likely use the same joined-list + detail-child-table pattern as `Admin\EnrollmentController`, because the layout, breadcrumbs, `simpleDatatables`, and left-summary/right-related-detail composition already exist and align with the plan's “no admin redesign” constraint.

## Most reusable patterns by task
- T2 schema work: follow migration style from `create_courses_table`, `create_enrollments_table`, `create_course_reviews_table`; keep payment state out of `enrollments` because that table is currently a clean access grant artifact.
- T5 checkout initiation: start from `Users\CourseController::viewCourse()` + `user/view_course.php` CTA branch and from `Users\CourseController::index()` + `user/courses.php` for card CTA changes.
- T6 webhook settlement + idempotent grant: centralize around `EnrollmentModel::isEnrolled()` / `enrollUser()` semantics, but do not expose direct user GET access to the grant path for premium courses.
- T7 premium access gating: preserve `CourseController2::redirectCourse()` and `courseById()` as the deep-link gate; the safest design is to make premium users reach these routes only after T6 creates the enrollment row.
- T8 admin payment monitoring: mimic `Admin\EnrollmentController` + `admin/enrollments/index.php` + `admin/enrollments/show.php` for a global transactions list and a single-record detail page with related webhook / state history data.

## 2026-04-26 — T2 premium course payment schema

- Added `2026-04-26-000001_add_premium_metadata_to_courses_table.php` to extend `courses` with PostgreSQL-safe scalar monetization fields: `is_premium`, nullable `price_amount`, `price_currency` (default `IDR`), and `is_purchasable` (default `true`), plus simple indexes on the two booleans.
- Added `2026-04-26-000002_create_course_payment_transactions_table.php` to create `course_payment_transactions` as the dedicated one-time purchase ledger instead of overloading `enrollments`.
- Canonical internal transaction status is stored as plain `VARCHAR(20)` in `status`, intended for `pending|paid|failed|expired|cancelled`; upstream Xendit lifecycle can be mirrored separately in nullable `xendit_status`.
- The table persists internal `reference_code`, Xendit invoice identifiers/URLs, amount/currency, customer snapshot fields, expiry/paid/cancelled/webhook timestamps, failure fields, and raw request/response/webhook payloads as `TEXT` so the schema stays PostgreSQL-safe without enum/JSON coupling.
- Historical failed or expired attempts are preserved by design because uniqueness is on transaction identity (`reference_code`, nullable Xendit IDs), not on `(user_id, course_id)`; downstream logic can locate at most one active unpaid checkout by querying `status = pending` for a user/course and ignoring older terminal rows.
- Successful access is still expected to materialize as a normal `enrollments` row; the nullable `granted_enrollment_id` + `granted_at` link lets later webhook logic record which transaction produced the access grant without moving payment state into `enrollments`. The existing unique `(user_id, course_id)` key on `enrollments` remains the final duplicate-access backstop.
- Verification passed in this repo: `php spark migrate` applied both new migrations successfully on the configured PostgreSQL connection, and `php spark migrate:status` shows them as batch 2.

## 2026-04-26 — T2 schema correction follow-up

- Added a third additive migration, `2026-04-26-000003_correct_payment_amount_columns_and_generic_transaction_fields.php`, instead of rewriting the already-applied T2 migrations.
- The corrective migration converts `courses.price_amount` and `course_payment_transactions.amount` from `DECIMAL(12,2)` to PostgreSQL `integer` in place using `ALTER COLUMN ... TYPE ... USING ...`, so the current QA database and fresh databases converge on the same final schema.
- It also adds the generic minimum transaction fields `checkout_url` and `status_payload_json`; existing rows are backfilled from `xendit_invoice_url` and the best available payload field (`last_webhook_payload`, then `response_payload`, then `metadata_payload`).

## 2026-04-26 — T3 Xendit integration foundation

- Added `app/Config/Xendit.php` for env-backed Xendit settings and `app/Config/Services.php` bindings for a dedicated non-shared Xendit HTTP client plus reusable payment-link/status-mapper services.
- Added `app/Services/Payments/XenditPaymentLinkService.php` to build `POST /v2/invoices` payloads from internal transaction data and return persistence-ready provider metadata, and `app/Services/Payments/XenditPaymentStatusMapper.php` as the canonical mapper into internal `pending|paid|failed|expired|cancelled` states.
- Added `app/Commands/XenditSmoke.php` as the safe verification hook; `php spark xendit:smoke payload 42 9 150000 2` exercises payload/reference generation and `php spark xendit:smoke map PAID invoice.paid` / `php spark xendit:smoke map totally-unknown` exercises accepted vs rejected status normalization without creating enrollment.
- Unknown or unsupported Xendit statuses/events are logged and rejected by throwing immediately, so later webhook/return handlers cannot silently treat an unrecognized provider state as paid access.

## 2026-04-26 — T4 admin course monetization

- Extended `app/Controllers/Admin/CourseController.php`, `app/Models/CourseModel.php`, and the existing admin course create/edit/index views to manage `is_premium`, integer `price_amount`, `price_currency`, and `is_purchasable` in place without changing admin course URLs or the page shell.
- Premium saves now require `price_amount > 0`, while free saves clear payment-only state consistently (`is_premium=false`, `price_amount=null`, `price_currency='IDR'`, `is_purchasable=false`).
- The admin course list now exposes free vs premium status and pricing context inside the existing table layout.
- Follow-up cleanup removed T4 debug noise so the monetization change no longer emits stray `console.*` output or unnecessary debug traces.

## 2026-04-26 — T5 premium checkout initiation

- The user-side checkout flow now branches from the existing seams only: `app/Views/user/courses.php` and `app/Views/user/view_course.php` swap free `Daftar` links for premium checkout CTAs, `Users\CourseController` owns the authenticated internal checkout page, and `CourseController2::enroll()` keeps the free path but redirects premium deep-links into checkout instead of granting access.
- `app/Models/CoursePaymentTransactionModel.php` is the reusable pending-transaction seam for later tasks: `findActivePendingTransaction()` returns exactly one still-valid pending checkout by `user_id + course_id + status=pending + expires_at`, so repeated checkout submissions can redirect to the same Xendit link without duplicating rows.
- PostgreSQL boolean columns come back through the current stack as `'t'/'f'` strings, so user-side monetization checks must normalize booleans explicitly; `!empty('f')` is truthy and will incorrectly treat free or non-purchasable courses as premium unless you guard it.

## 2026-04-26 — T6 webhook settlement + return-status implementation note

- Added  as the public settlement endpoint. It rejects any callback whose  header does not match the configured Xendit callback token before trusting payload fields.
- Settlement lookup reuses  by  first, then  / , so webhook retries can resolve the same transaction deterministically.
- The paid path is idempotent: once a transaction is marked , later non-paid callbacks do not downgrade it, and webhook retries reuse the existing enrollment if  or the  enrollment row already exists.
- Return/status pages are read-only under authenticated  routes and only render the current DB transaction state (, , , , ); query params are used only to locate the user-owned transaction, never to grant access.
- Verified live against PostgreSQL + HTTP: invalid callback token => , first paid webhook => transaction  + one enrollment, paid webhook retry => still one enrollment, return-before-webhook => page shows  and no enrollment, expired webhook => transaction  and still no enrollment.

## 2026-04-26 — T6 webhook settlement + return-status implementation note (corrected)

- Added POST /payments/xendit/webhook as the public settlement endpoint. It rejects any callback whose x-callback-token header does not match the configured Xendit callback token before trusting payload fields.
- Settlement lookup reuses course_payment_transactions by xendit_invoice_id first, then xendit_external_id / reference_code, so webhook retries can resolve the same transaction deterministically.
- The paid path is idempotent: once a transaction is marked paid, later non-paid callbacks do not downgrade it, and webhook retries reuse the existing enrollment if granted_enrollment_id or the unique (user_id, course_id) enrollment row already exists.
- Return/status pages are read-only under authenticated user routes and only render the current DB transaction state (paid, pending, failed, expired, cancelled); query params are used only to locate the user-owned transaction, never to grant access.
- Verified live against PostgreSQL + HTTP: invalid callback token => 403, first paid webhook => transaction paid + one enrollment, paid webhook retry => still one enrollment, return-before-webhook => page shows DB status PENDING and no enrollment, expired webhook => transaction expired and still no enrollment.

## 2026-04-26 — T7 implementation note (final follow-up)

- Premium deep-link learning routes now stay payment-aware: unpaid users who hit `/course/{id}` or `/course/{id}/lesson/{lessonId}` are redirected to checkout only for published+purchasable premium courses, otherwise back to the still-visible course detail page with a blocked-access message.
- Legacy users who already had an enrollment keep access even if that course is later flipped from free to premium, because enrollment remains the single learning/review entitlement artifact.
- Review creation is now blocked before the form renders for unenrolled users, so new premium buyers only gain review access after trusted payment settlement creates the enrollment row.
- Completion endpoints now enforce the same gate on the write side: unpaid users receive `403` from lesson-completion APIs instead of being able to write progress without course access.

## 2026-04-26 — T8 admin payment monitoring

- Added `/admin/payments` and `/admin/payments/{id}` through `App\Controllers\Admin\PaymentController` plus matching `app/Views/admin/payments/index.php` and `show.php`, following the existing admin enrollment list/detail pattern instead of creating a new dashboard surface.
- The admin shell only changed minimally: `app/Views/admin/layout.php` gained a single `Payments` sidebar link, while the rest of the familiar admin navigation and page URLs stayed intact.
- The payment detail page is intentionally diagnostic: it shows transaction status, user/course context, enrollment linkage, provider/reference IDs, redirect URLs, terminal timestamps, failure metadata, and stored payload snapshots so pending/failed/paid flows can be debugged from the admin UI.
- Admin user-management affordances now reflect the T1 privilege rules more clearly: super admins still see create/elevate privileged-role controls, while plain admins can access the same user pages but do not see privileged-account actions or privileged role options.

## 2026-04-26 — Final-review fix: atomic pending checkout creation

- The checkout race was in `Users\CourseController::startCheckout()`: a plain `findActivePendingTransaction()` → insert path could create more than one active `pending` row, and a second request could also race into Xendit before the first request had stored `checkout_url`.
- The hardened flow now uses two PostgreSQL-backed guarantees together: a per-`user_id + course_id` advisory lock held across checkout initiation, plus a partial unique index on `course_payment_transactions(user_id, course_id) WHERE status = 'pending'`.
- Expired pending rows are actively transitioned from `pending` to `expired` before lookup/insert, so old stale rows no longer block fresh checkout creation while the unique pending invariant still holds.
