# Contribution Guidelines

## Commit and PR conventions

This repository follows Conventional Commits in PR titles and commit messages.

- PR title: `type(scope)?: description`
- Commit message: `type(scope)?: description` plus optional body and footer.
- Required types: `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `build`, `ci`, `chore`, `revert`.

### Breaking changes

Use one of the following for major changes:
- `feat(api)!: something changed`
- `fix(module)!: behavior changed`

Add a footer line:
`BREAKING CHANGE: description`

## Release process

- Releases are automated with release-please.
- Ensure version is managed in `pmpro-wfls-2fa.php`.
- Branch `main` / `master` triggers release PR creation automatically.
