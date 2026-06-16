# Playbasis API

> This is a legacy Playbasis application stack. The June 2026 work focused on preserving the existing system, improving compatibility, removing unsafe defaults, hardening public surfaces, and making the repositories usable again. It was not a feature-building push or a rewrite.

Playbasis API is the legacy runtime API for Playbasis engagement mechanics. It exposes the player, event/action, rule, reward, badge, quest, quiz, content, redemption, notification, social callback, merchant/store, and reporting surfaces used by the historical Playbasis stack.

This repository is best understood as a reference implementation with substantial historical business logic. It is useful for preservation, audit, migration, adapter work, and understanding how Playbasis modeled rules, rewards, and player progress.

## What This Repo Is

- A CodeIgniter-era PHP API backed by MongoDB configuration.
- A legacy engagement engine for tracking player actions and applying reward logic.
- A rules, rewards, and player progress API used by the companion `playbasis/control` admin console.
- A collection of integration endpoints and Node side services for older real-time, social, and documentation workflows.
- A modernization reference for rebuilding a contract-first Playbasis Engine without losing domain behavior.

## What Is Actually Here

The current codebase includes controllers and models for:

- Player identity, authentication, sessions, registration, profiles, and progress.
- Event/action tracking, campaigns, games, rules, points, custom points, badges, levels, rewards, and leaderboards.
- Quests, missions, quizzes, content, links, files, CMS-like content, and mobile-facing flows.
- Goods, redemption, merchants, store organizations, location-aware flows, and inventory-style reward catalogs.
- Email, SMS, push, notification callbacks, social login/callback integrations, Janrain, Stripe, FullContact, Jive, Lithium, Google, Pipedrive, and similar legacy integration points.
- Reporting-oriented data surfaces, activity logs, ranking helpers, and operational endpoints.
- Docker runtime scaffolding and older Node services under `node_*` and `iodocs`.

See [docs/what-is-here.md](docs/what-is-here.md) for a goal-oriented map of the API surface.

## Example Use Cases

The code supports engagement primitives that can be adapted for:

- Loyalty and rewards programs that track actions, award points or badges, and redeem goods.
- Learning, onboarding, and training journeys built from quests, quizzes, badges, and progress reports.
- Campaign and challenge systems using actions, games, leaderboards, and rule-triggered rewards.
- Retail, merchant, and store engagement with goods, redemptions, branches, and location-aware flows.
- Community or app engagement layers using profiles, feeds, notifications, content, and widgets.
- Healthcare, education, or operations demos that reuse engagement mechanics for checklist and journey flows.
- Migration projects that need a source of legacy Playbasis domain behavior.

See [docs/use-cases.md](docs/use-cases.md) for a fuller use-case map.

## How API And Control Fit Together

`playbasis/api` is the runtime surface that applications call. It records player actions, evaluates mechanics, returns player progress, handles redemption and notification workflows, and exposes the integration callbacks.

`playbasis/control` is the admin control plane. Operators use Control to configure clients, sites, apps, rules, campaigns, rewards, goods, quests, quizzes, reports, widgets, users, and integrations that the API then serves at runtime.

## Setup And Configuration

This is a legacy PHP application. Expect old framework conventions and older dependency assumptions.

The Docker entrypoint can generate local config files from the example config files and wire the app to MongoDB:

```bash
docker compose -f docker/docker-compose.yml up --build
```

The compose file starts:

- `server`, exposing ports `80` and `443`.
- `app`, mounted at `/var/www/api/`.
- `mongo`, using the `mongo:3.6` image.

Important Docker environment variables used by the entrypoint:

- `MONGO_HOSTBASE`
- `MONGO_USERNAME`
- `MONGO_PASSWORD`
- `NODE_STREAM_URL`

Application and integration configuration is split across files under `application/config/`. Current env-backed values include:

- `JANRAIN_API_KEY`
- `TWILIO_MODE`
- `TWILIO_ACCOUNT_SID`
- `TWILIO_AUTH_TOKEN`
- `TWILIO_API_VERSION`
- `TWILIO_NUMBER`
- `STRIPE_API_KEY`
- `STRIPE_PUBLISHABLE_KEY`
- `FULLCONTACT_API_KEY`
- `GECKO_API_KEY`
- `DEBUG_KEY`
- `AMAZON_SES_SECRET_KEY`
- `AMAZON_SES_ACCESS_KEY`
- `S3_KEY`
- `S3_SECRET`
- `S3_ENDPOINT`
- `S3_BUCKET`
- `S3_IMAGE`

Legacy Node services also read `PORT`, and `iodocs` can read `REDISTOGO_URL`.

Do not commit live credentials. Use environment variables or deployment secrets for private keys and service tokens.

## Verification

For docs-only changes:

```bash
git diff --check
```

For PHP syntax checks:

```bash
find . -path './vendor' -prune -o -path '*/node_modules/*' -prune -o -path './system' -prune -o -name '*.php' -print0 | xargs -0 -n1 php -l
```

For stack replay work, replay open branches onto a fresh `origin/master`, then run whitespace and PHP lint checks before merging.

## Known Limitations

- This is a legacy application, not a polished new SaaS product.
- The public docs are being rebuilt after a maintenance reset and may still lag some historical behavior.
- The framework, PHP assumptions, Node services, and MongoDB version reflect the age of the stack.
- Some integrations are historical and require explicit credentials or environment configuration before they are usable.
- New work should prioritize preservation, compatibility, security, documentation, tests, and migration support before feature expansion.

## Contributing And Security

See [CONTRIBUTING.md](CONTRIBUTING.md) for the preferred contribution model.

Report vulnerabilities privately. See [SECURITY.md](SECURITY.md). Do not open public issues containing live credentials, API keys, webhook secrets, database dumps, or private customer data.
