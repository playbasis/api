# Use Cases

These use cases describe existing primitives in the legacy Playbasis stack. They are not promises of turnkey production deployments. Any production use should include environment-specific security review, integration testing, and deployment hardening.

## Loyalty And Rewards Programs

Model customer actions, award points or badges, and redeem goods or rewards.

Existing primitives:

- Actions and activity tracking.
- Points and custom points.
- Badges, rewards, levels, and leaderboards.
- Goods, merchants, store organizations, and redemption.
- Reports and activity data for program review.

## Learning, Onboarding, And Training Journeys

Build structured journeys where people complete tasks, quizzes, and milestones.

Existing primitives:

- Quests, missions, and completion actions.
- Quizzes and answer flows.
- Badges, points, levels, and progress state.
- Content, links, and mobile-oriented surfaces.
- Reporting data for completion and participation.

## Campaign And Challenge Systems

Run time-bound or behavior-triggered engagement programs.

Existing primitives:

- Campaigns and games.
- Actions as event inputs.
- Rule-triggered rewards, points, and badges.
- Leaderboards and ranking helpers.
- Notifications and reporting surfaces.

## Retail, Merchant, And Store Engagement

Represent locations, merchants, goods groups, and redemption flows.

Existing primitives:

- Merchants and store organizations.
- Locations and branch-style modeling.
- Goods catalogs and redemption state.
- Reward goods and activity tracking.
- Notifications for fulfillment or follow-up flows.

## Community Or App Engagement Layers

Add an engagement layer around an existing application or community.

Existing primitives:

- Player profiles and progress.
- Actions, points, badges, rewards, and leaderboards.
- Content, feeds, widgets, links, and mobile-facing flows.
- Email, SMS, push, social, and integration callbacks.
- Reports and activity views for operators.

## Healthcare, Education, Or Operations Demos

Use the legacy mechanics as reusable engagement primitives for regulated or structured workflows.

Existing primitives:

- Checklists modeled as quests or missions.
- Appointment, lesson, or task completion modeled as actions.
- Progress, reminders, and reward state.
- Reports for follow-through, participation, and outcomes.
- Integration points for external systems, after security review.

## Migration And Modernization Reference

Use the codebase as a domain reference when rebuilding a modern Playbasis Engine.

Existing primitives:

- Legacy API contracts and controller behavior.
- Rules, points, badges, goods, quests, quizzes, and notifications.
- Historical integration assumptions.
- Docker/runtime repair work from the June 2026 maintenance reset.
- Regression and lint practices used to keep behavior stable during cleanup.
