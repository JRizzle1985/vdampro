# Chimera Print Observability Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add Chimera print-job history plus default-and-override template selection so operators can see what was sent, to whom, and with which template path.

**Architecture:** Persist Chimera jobs in a dedicated table, add a bulk-print confirmation page that resolves the final template path before sending, and expose an admin job-history page through the repo’s existing Bootstrap Table + API list pattern. Keep delivery synchronous and extend the current Chimera service rather than replacing it.

**Tech Stack:** Laravel, Eloquent, FormRequest validation, Blade, Bootstrap Table, existing admin routes/API patterns

---

## File Map

- Create: `app/Models/ChimeraPrintJob.php`
- Create: `app/Http/Controllers/Api/ChimeraPrintJobsController.php`
- Create: `app/Http/Requests/StoreChimeraPrintJobRequest.php`
- Create: `app/Http/Transformers/ChimeraPrintJobsTransformer.php`
- Create: `app/Presenters/ChimeraPrintJobPresenter.php`
- Create: `database/migrations/2026_05_03_000001_add_chimera_template_path_to_settings_table.php`
- Create: `database/migrations/2026_05_03_000002_create_chimera_print_jobs_table.php`
- Create: `resources/views/hardware/bulk-chimera-print.blade.php`
- Create: `resources/views/settings/printer-jobs.blade.php`
- Modify: `app/Models/Setting.php`
- Modify: `app/Http/Requests/StoreChimeraPrinterSettings.php`
- Modify: `app/Http/Controllers/SettingsController.php`
- Modify: `app/Http/Controllers/Assets/BulkAssetsController.php`
- Modify: `app/Services/ChimeraPrintService.php`
- Modify: `resources/views/settings/printer.blade.php`
- Modify: `resources/views/settings/index.blade.php`
- Modify: `resources/lang/en-US/general.php`
- Modify: `resources/lang/en-GB/general.php`
- Modify: `resources/lang/en-US/admin/settings/general.php`
- Modify: `resources/lang/en-GB/admin/settings/general.php`
- Modify: `routes/web.php`
- Modify: `routes/api.php`

---

## Chunk 1: Settings Foundation

### Task 1: Add default template path persistence

**Files:**
- Create: `database/migrations/2026_05_03_000001_add_chimera_template_path_to_settings_table.php`
- Modify: `app/Models/Setting.php`

- [ ] **Step 1: Write the failing schema/behavior test**

Create or extend a focused feature test that loads settings and expects a `chimera_template_path` attribute to be mass assignable.

- [ ] **Step 2: Run the focused test to verify it fails**

Run: `php artisan test --filter=Setting`
Expected: failure or missing-field assertion for `chimera_template_path`

- [ ] **Step 3: Add the settings migration**

Add a nullable string column after the existing Chimera fields:

```php
$table->string('chimera_template_path')->nullable()->after('chimera_qr_prefix');
```

- [ ] **Step 4: Add the field to `Setting`**

Extend `$fillable` with:

```php
'chimera_template_path',
```

- [ ] **Step 5: Re-run the focused test**

Run: `php artisan test --filter=Setting`
Expected: pass

- [ ] **Step 6: Commit**

```bash
git add app/Models/Setting.php database/migrations/2026_05_03_000001_add_chimera_template_path_to_settings_table.php
git commit -m "Add Chimera default template setting"
```

### Task 2: Save and validate the default template path

**Files:**
- Modify: `app/Http/Requests/StoreChimeraPrinterSettings.php`
- Modify: `app/Http/Controllers/SettingsController.php`
- Modify: `resources/views/settings/printer.blade.php`

- [ ] **Step 1: Write the failing feature test**

Add a settings POST test asserting:
- superuser can save `chimera_template_path`
- value persists on `Setting::getSettings()`

- [ ] **Step 2: Run the focused test to verify it fails**

Run: `php artisan test --filter=ChimeraPrinterSettings`
Expected: assertion failure because template path is ignored

- [ ] **Step 3: Extend request validation**

Add:

```php
'chimera_template_path' => 'nullable|string|max:255',
```

- [ ] **Step 4: Persist the field in `postChimeraPrinter()`**

Assign:

```php
$setting->chimera_template_path = $request->input('chimera_template_path');
```

- [ ] **Step 5: Add the UI field**

Add a `Default Template Path` text input to the Chimera settings page with help text explaining this is the controller-side template file/path.

- [ ] **Step 6: Re-run the focused test**

Run: `php artisan test --filter=ChimeraPrinterSettings`
Expected: pass

- [ ] **Step 7: Commit**

```bash
git add app/Http/Requests/StoreChimeraPrinterSettings.php app/Http/Controllers/SettingsController.php resources/views/settings/printer.blade.php
git commit -m "Save default Chimera template path"
```

---

## Chunk 2: Job Model and Persistence

### Task 3: Create the Chimera print job tables and model

**Files:**
- Create: `database/migrations/2026_05_03_000002_create_chimera_print_jobs_table.php`
- Create: `app/Models/ChimeraPrintJob.php`

- [ ] **Step 1: Write the failing model/database test**

Create a test that inserts a Chimera print job with:
- user ID
- status
- asset count
- delivery method
- target
- payload
- template fields

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --filter=ChimeraPrintJob`
Expected: missing table/model failure

- [ ] **Step 3: Create the migration**

Create `chimera_print_jobs` with columns for:

```php
user_id
status
asset_count
delivery_method
target_host
target_port
target_path
payload
result_message
default_template_path
override_template_path
resolved_template_path
timestamps
```

Create `chimera_print_job_assets` with:

```php
chimera_print_job_id
asset_id
```

- [ ] **Step 4: Create the Eloquent model**

Add fillable/casts and relations:
- `user()`
- `assets()`

- [ ] **Step 5: Re-run the test**

Run: `php artisan test --filter=ChimeraPrintJob`
Expected: pass

- [ ] **Step 6: Commit**

```bash
git add app/Models/ChimeraPrintJob.php database/migrations/2026_05_03_000002_create_chimera_print_jobs_table.php
git commit -m "Create Chimera print job persistence"
```

---

## Chunk 3: Confirmation Flow and Delivery Overrides

### Task 4: Add the bulk confirmation page

**Files:**
- Create: `resources/views/hardware/bulk-chimera-print.blade.php`
- Create: `app/Http/Requests/StoreChimeraPrintJobRequest.php`
- Modify: `app/Http/Controllers/Assets/BulkAssetsController.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Write the failing feature test**

Add a feature test asserting that posting `bulk_actions=chimera_print` with selected asset IDs returns a confirmation page instead of immediately sending.

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --filter=ChimeraBulkPrint`
Expected: redirect flash result from immediate send path

- [ ] **Step 3: Add the confirmation route**

Add a POST route for confirming the Chimera job submission, for example:

```php
Route::post('hardware/bulkedit/chimera-print', ...)
```

- [ ] **Step 4: Refactor `BulkAssetsController`**

Change `case 'chimera_print'` to render the confirmation view with:
- selected assets
- default template path
- resolved target info
- payload preview lines

Add a new submit action that validates:

```php
'ids' => 'required|array|min:1',
'ids.*' => 'integer',
'chimera_template_override' => 'nullable|string|max:255',
```

- [ ] **Step 5: Build the confirmation Blade view**

Show:
- selected assets table/list
- default template path
- override text input
- payload preview `<pre>` or table
- confirm/cancel actions

- [ ] **Step 6: Re-run the test**

Run: `php artisan test --filter=ChimeraBulkPrint`
Expected: pass

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Assets/BulkAssetsController.php app/Http/Requests/StoreChimeraPrintJobRequest.php resources/views/hardware/bulk-chimera-print.blade.php routes/web.php
git commit -m "Add Chimera bulk print confirmation flow"
```

### Task 5: Extend delivery service to resolve template path and expose payload lines

**Files:**
- Modify: `app/Services/ChimeraPrintService.php`

- [ ] **Step 1: Write the failing unit/feature test**

Add tests covering:
- resolved template falls back to settings default
- override replaces default
- payload preview lines are exposed before send

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --filter=ChimeraPrintService`
Expected: missing override/template behavior

- [ ] **Step 3: Add minimal implementation**

Refactor the service to:
- accept an overrides array/DTO in the constructor or method
- add a helper returning generated lines
- add a helper returning the resolved template path
- include target info and resolved template path in the result array

- [ ] **Step 4: Re-run the test**

Run: `php artisan test --filter=ChimeraPrintService`
Expected: pass

- [ ] **Step 5: Commit**

```bash
git add app/Services/ChimeraPrintService.php
git commit -m "Add Chimera template resolution and payload preview"
```

### Task 6: Persist jobs during send

**Files:**
- Modify: `app/Http/Controllers/Assets/BulkAssetsController.php`

- [ ] **Step 1: Write the failing feature test**

Assert that confirming a Chimera print:
- creates a job row
- attaches the selected assets
- stores the resolved template path
- stores success/failure status and result message

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --filter=ChimeraPrintJobFlow`
Expected: no job record exists

- [ ] **Step 3: Implement minimal persistence**

Before sending:
- create a pending `ChimeraPrintJob`
- attach assets
- save payload and template fields

After sending:
- update status to `sent` or `failed`
- save result message

- [ ] **Step 4: Re-run the test**

Run: `php artisan test --filter=ChimeraPrintJobFlow`
Expected: pass

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Assets/BulkAssetsController.php
git commit -m "Persist Chimera print jobs"
```

---

## Chunk 4: History UI and API

### Task 7: Add the jobs API endpoint

**Files:**
- Create: `app/Http/Controllers/Api/ChimeraPrintJobsController.php`
- Create: `app/Http/Transformers/ChimeraPrintJobsTransformer.php`
- Create: `app/Presenters/ChimeraPrintJobPresenter.php`
- Modify: `routes/api.php`

- [ ] **Step 1: Write the failing feature test**

Assert that an authorized admin can fetch Chimera print jobs JSON and that each row includes:
- created_at
- status
- user
- asset_count
- delivery_method
- target
- resolved_template_path
- result_message

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --filter=ChimeraPrintJobsApi`
Expected: route/controller missing

- [ ] **Step 3: Implement the API controller**

Follow the `ReportsController` list pattern:
- authorize admin/superuser access
- allow search
- support sort, offset, limit
- eager load `user`

- [ ] **Step 4: Implement the presenter/transformer**

Provide column config and transformed row data for Bootstrap Table.

- [ ] **Step 5: Register the API route**

Add an API route under a sensible prefix, for example:

```php
Route::get('reports/chimera-print-jobs', ...)
```

- [ ] **Step 6: Re-run the test**

Run: `php artisan test --filter=ChimeraPrintJobsApi`
Expected: pass

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Api/ChimeraPrintJobsController.php app/Http/Transformers/ChimeraPrintJobsTransformer.php app/Presenters/ChimeraPrintJobPresenter.php routes/api.php
git commit -m "Add Chimera print jobs API"
```

### Task 8: Add the admin history page

**Files:**
- Create: `resources/views/settings/printer-jobs.blade.php`
- Modify: `app/Http/Controllers/SettingsController.php`
- Modify: `routes/web.php`
- Modify: `resources/views/settings/index.blade.php`
- Modify: language files

- [ ] **Step 1: Write the failing feature test**

Assert that an authorized admin can open the Chimera print jobs page and that it references the new API route.

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --filter=ChimeraPrintJobsPage`
Expected: route/view missing

- [ ] **Step 3: Add the web route and controller action**

Add:
- `getChimeraPrintJobs()` in `SettingsController`
- settings route with breadcrumbs

- [ ] **Step 4: Build the Blade page**

Reuse the activity-report table pattern with:

```php
\App\Presenters\ChimeraPrintJobPresenter::dataTableLayout()
```

- [ ] **Step 5: Add navigation entry**

Add a settings tile or contextual link from the Chimera printer settings page to the job history page.

- [ ] **Step 6: Add language strings**

Add labels/help text for:
- Chimera print jobs
- default template path
- override template path
- payload preview

- [ ] **Step 7: Re-run the test**

Run: `php artisan test --filter=ChimeraPrintJobsPage`
Expected: pass

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/SettingsController.php resources/views/settings/printer-jobs.blade.php resources/views/settings/index.blade.php resources/lang/en-US/general.php resources/lang/en-GB/general.php resources/lang/en-US/admin/settings/general.php resources/lang/en-GB/admin/settings/general.php routes/web.php
git commit -m "Add Chimera print jobs admin page"
```

---

## Chunk 5: Final Verification

### Task 9: Run focused verification

**Files:**
- Test: all files touched above

- [ ] **Step 1: Run focused tests**

Run:

```bash
php artisan test --filter=Chimera
```

Expected: Chimera-related tests pass

- [ ] **Step 2: Run targeted diagnostics**

Check edited PHP and Blade files for IDE/linter errors.

- [ ] **Step 3: Smoke-check critical paths manually**

Verify:
- Chimera settings page saves default template path
- main assets page shows `Send to Label Printer`
- bulk confirmation page appears
- confirmation page shows payload preview and template override
- confirmed send creates a history record

- [ ] **Step 4: Final commit if needed**

```bash
git status
git add <remaining feature files>
git commit -m "Complete Chimera print observability flow"
```

- [ ] **Step 5: Push**

```bash
git push
```
