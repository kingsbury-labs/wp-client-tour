# Contributing to WP Client Tour

Thanks for your interest. This is a small open source project — contributions are welcome and will be reviewed promptly.

## Before You Start

- Read [SPEC.md](SPEC.md) for the full architecture and constraints
- Check [open issues](https://github.com/kingsbury-labs/wp-client-tour/issues) to avoid duplicating work
- For significant changes, open an issue first to align on approach before writing code

## Stack Constraints (Non-Negotiable)

- **PHP 7.4+**, WordPress Coding Standards (WPCS)
- **Vanilla ES6 only** — no jQuery, no frameworks, no external libraries
- **No build step** — JS must be readable as-is, no webpack or npm
- **No CDN calls** — the plugin must work offline

## Submitting a Pull Request

1. Fork the repo and create a branch: `fix/your-fix` or `feature/your-feature`
2. Keep changes focused — one fix or feature per PR
3. Test in a real WordPress install (WP 6.0+ recommended)
4. Ensure PHP follows WPCS: nonces, sanitisation, escaping, capability checks on every admin action
5. Update `CHANGELOG.md` with a brief entry under `Unreleased`
6. Submit the PR with a clear description of what changed and why

## Reporting Bugs

Use the [bug report template](https://github.com/kingsbury-labs/wp-client-tour/issues/new?template=bug_report.md). Include your WordPress version, PHP version, and steps to reproduce.

## License

By contributing, you agree your code will be released under the [MIT License](LICENSE).
