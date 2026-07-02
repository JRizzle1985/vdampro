# Save

## Current State

- Chimera label-printer integration is implemented and committed.
- Git commit: `37876dccd5`
- Commit message: `Add Chimera printer integration and bulk print flow`
- Changes have been pushed to `origin/master`.

## VDOT Custom Build

- Dokploy project: `VDOT Custom Build`
- Deployment type: Dokploy compose stack
- Stack name: `vdot`
- Laravel runs in the `app` service.
- Production migration check is complete.
- `php artisan migrate --force` returned `Nothing to migrate`.
- `php artisan migrate:status` confirms `2026_05_01_120000_add_chimera_settings_to_settings_table` is `Ran`.
- App and DB containers are healthy.

## Raw Compose Notes

- Raw Dokploy deployments use `dokploy.docker-compose.raw.yml`.
- The raw compose app image is currently `ghcr.io/jrizzle1985/vdampro:latest`.
- This repo publishes GHCR images from `.github/workflows/ghcr-push.yml`.
- That workflow runs on push to `master` and also supports manual dispatch.
- Published tags include:
  - `ghcr.io/jrizzle1985/vdampro:latest`
  - `ghcr.io/jrizzle1985/vdampro:${github.sha}`

## Recommended Raw Compose Rollout

1. Confirm the GHCR workflow completed for commit `37876dccd5`.
2. In Dokploy raw compose, pin the app image to:

```yaml
services:
  app:
    image: ghcr.io/jrizzle1985/vdampro:37876dccd5
```

3. Redeploy the raw compose stack.
4. Run the migration in the app container:

```bash
php artisan migrate --force
```

5. Verify migration status:

```bash
php artisan migrate:status
```

6. Open admin settings and confirm `Label Printer (Chimera)` is visible.
7. Enable Chimera and test the connection.

## Important Limitation

- The available Dokploy MCP tools in this session can inspect metadata and redeploy applications, but they do not expose remote shell or compose exec access.
- Any artisan command on Dokploy compose stacks must be run through direct VPS or container access.
