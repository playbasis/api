# What Is Here

This repository contains the legacy Playbasis runtime API. It is a preservation-focused codebase with substantial historical engagement-engine logic, not a new feature launch or a rewrite.

The map below describes the repo by user goal rather than by controller name.

## Identify Players And Clients

Use the API to model the actors and tenants that participate in engagement flows:

- Clients, sites, apps, and API authentication.
- Players, registration, login, profile data, and session-like player auth.
- OAuth and social identity callbacks.
- Mobile-facing identity and profile flows.

Related surfaces include `auth`, `client`, `player`, `oauth`, `mobile`, `facebook`, `instagram`, and `janrain`.

## Track Actions And Activity

Use actions as the basic event input for the engagement engine:

- Register, login, invite, complete quest, complete quiz, and custom app events.
- Activity tracking for player progress and rule evaluation.
- Tracker and timestamp helpers for operational flows.

Related surfaces include `action`, `tracker`, `engine`, `playbasis`, and `timestamp`.

## Apply Rules, Points, Rewards, And Progress

The core runtime behavior is the legacy engagement engine:

- Rule-triggered points and custom points.
- Badges, levels, rewards, leaderboards, and player progress.
- Campaign and game mechanics.
- Reward assignment and reporting-oriented state.

Related surfaces include `engine`, `point`, `custompoint`, `badge`, `reward`, `campaign`, and `game`.

## Build Journeys, Quests, And Quizzes

The API contains primitives for structured engagement journeys:

- Quests and missions.
- Quizzes and completion flows.
- Content and links attached to engagement experiences.
- Mobile and content-oriented retrieval paths.

Related surfaces include `quest`, `quiz`, `content`, `cms`, `link`, `file`, and `mobile`.

## Redeem Goods And Model Stores

The repo includes legacy loyalty and redemption concepts:

- Goods catalogs and reward goods.
- Redemption workflows.
- Merchants, store organizations, and locations.
- Inventory-style reward flows and store-aware engagement.

Related surfaces include `goods`, `redeem`, `merchant`, `store_org`, and `location`.

## Notify And Integrate

The API has endpoints and config for older notification and integration workflows:

- Email, SMS, push, and webhook-style notification callbacks.
- Amazon SES, S3, Twilio, Stripe, FullContact, Google, Jive, Lithium, Pipedrive, and Janrain configuration points.
- Node side services for real-time or social workflows.

Related surfaces include `email`, `pb_sms`, `push`, `notification`, `service`, `social`, `pipedrive`, and the `node_*` directories.

## Operate And Inspect

This repo also contains operational pieces:

- Docker scaffolding for PHP-FPM, a web server, and MongoDB.
- Config examples under `application/config/`.
- API documentation tooling under `iodocs`.
- Reporting and activity data surfaces used by the historical stack.

## What This Is Not

- It is not a clean-room rewrite.
- It is not a current SaaS launch.
- It is not a guarantee that every historical integration works without fresh credentials and environment-specific setup.
- It is not the only recommended shape for a modern Playbasis Engine.

Treat it as a maintained legacy reference: useful to run, inspect, harden, document, migrate from, and compare against future implementations.
