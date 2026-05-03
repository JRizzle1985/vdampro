# Chimera Print Observability Design

## Goal

Add two missing operator capabilities to the Chimera integration:

1. Visibility into what VDOT attempted to send to the print controller
2. Explicit control over the Chimera template path, with a global default and per-job override

## Current Gaps

- VDOT sends Chimera jobs immediately and only returns a flash success or failure message.
- VDOT does not keep a durable print-job history.
- VDOT does not record which template path was intended for a print.
- VDOT does not allow the operator to override the template path for a one-off job.
- The current send flow skips any confirmation or payload preview.

## Scope

This design adds:

- A default Chimera template path stored on the singleton `settings` row
- A bulk-print confirmation page for Chimera jobs
- A per-job optional template-path override
- A dedicated Chimera print-job history table and asset pivot table
- An admin history page backed by the existing Bootstrap Table/API list pattern

This design does not add:

- A background queue worker
- Printer-side acknowledgement beyond the result VDOT can observe locally
- Chimera template parsing or `.ykr` introspection inside VDOT
- Automatic template discovery from the print controller

## Architecture

### Settings

Extend the existing Chimera settings page and request validation with a `chimera_template_path` field. This field stores the default controller-side template identifier as a plain file/path string.

### Print Confirmation

Change the `chimera_print` bulk action from an immediate send into a two-step flow:

1. Operator selects assets and chooses `Send to Label Printer`
2. VDOT renders a confirmation page showing:
   - selected assets
   - delivery target
   - resolved default template path
   - optional override field
   - generated payload preview
3. Operator confirms the send

### Job Persistence

Create a dedicated `chimera_print_jobs` table plus `chimera_print_job_assets` pivot table.

Each job stores:

- user
- status
- asset count
- payload lines
- delivery method
- target host/port or file path
- default template path
- override template path
- resolved template path
- result message
- timestamps

The pivot stores the printed asset IDs so a single job can be traced back to all selected assets.

### Delivery

Keep delivery synchronous for now. `ChimeraPrintService` will:

- accept optional per-job overrides
- expose payload lines before send
- resolve the final template path from settings + override
- return delivery result details that the controller can persist on the job

### History UI

Add a dedicated admin page for Chimera print jobs using the existing activity-report style list:

- web page under settings/admin
- API endpoint for Bootstrap Table JSON
- presenter/transformer for columns

The history table should show:

- created at
- status
- user
- asset count
- delivery method
- target
- resolved template path
- result message

The detail view can remain simple initially by exposing payload and assets through the API row / modal-friendly fields, without building a separate deep detail page.

## File Structure

### New

- `app/Models/ChimeraPrintJob.php`
- `app/Http/Controllers/Api/ChimeraPrintJobsController.php`
- `app/Http/Requests/StoreChimeraPrintJobRequest.php`
- `app/Http/Transformers/ChimeraPrintJobsTransformer.php`
- `app/Presenters/ChimeraPrintJobPresenter.php`
- `database/migrations/2026_05_03_000001_add_chimera_template_path_to_settings_table.php`
- `database/migrations/2026_05_03_000002_create_chimera_print_jobs_table.php`
- `resources/views/hardware/bulk-chimera-print.blade.php`
- `resources/views/settings/printer-jobs.blade.php`

### Modified

- `app/Models/Setting.php`
- `app/Http/Requests/StoreChimeraPrinterSettings.php`
- `app/Http/Controllers/SettingsController.php`
- `app/Http/Controllers/Assets/BulkAssetsController.php`
- `app/Services/ChimeraPrintService.php`
- `resources/views/settings/printer.blade.php`
- `resources/views/settings/index.blade.php`
- `resources/lang/en-US/general.php`
- `resources/lang/en-GB/general.php`
- `resources/lang/en-US/admin/settings/general.php`
- `resources/lang/en-GB/admin/settings/general.php`
- `routes/web.php`
- `routes/api.php`

## Data Flow

1. Operator enables Chimera and sets a default template path in settings.
2. Operator selects assets and chooses `Send to Label Printer`.
3. `BulkAssetsController` loads assets, builds preview lines, and renders the confirmation page.
4. Operator optionally overrides the template path and submits the confirmation form.
5. Controller creates a `chimera_print_jobs` row in a pending state and attaches assets.
6. Controller calls `ChimeraPrintService` with settings plus per-job override.
7. Service sends payload over TCP or writes the file.
8. Controller updates the job row with final status and result message.
9. Operator can review the job from the Chimera job history page.

## Error Handling

- If Chimera is disabled, the confirmation flow redirects back with an error.
- If no assets are selected, the flow redirects back with an error.
- If the template override is blank, VDOT falls back to the default template path.
- If both override and default are blank, VDOT still sends the job but records an empty resolved template path; the controller remains the source of truth for whether that path is required.
- Delivery failures update the job record to a failed status with the exact result message returned by the service.

## Testing Strategy

- Add focused tests for request validation and service template resolution.
- Add a feature test for the Chimera bulk confirmation and send flow that asserts a job record is created and updated.
- Add a feature test for the history API endpoint authorization and returned row shape.

## Implementation Notes

- Use a dedicated table rather than overloading `action_logs`; the Chimera job data shape is structured and multi-asset by nature.
- Keep the confirmation flow full-page, following the existing bulk delete/restore pattern.
- Keep the initial template input as a plain path string rather than designing a richer template catalog prematurely.
