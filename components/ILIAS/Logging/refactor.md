# Refactoring for Component Revision

## Open Questions

- Everything in the folder `error` seems to be independent of the 
  rest of the component (other that the settings are under `log`,
  both in the inis and the settings table). Should the classes be
  moved to `Init\ErrorHandling`? Maybe also introduce a separate GUI
  for the corresponding subtab in the administration?
- `ilLogger::writeMemoryUsage()` is unused, is that a bug or should
  we abandon it (along with the corresponding setting)?
- Would moving all settings (except for log levels by component) to
  the `ilias.ini.php` be a problem? Dependency management would become
  much easier, and we could get rid of a view in the administration.
- When the structure of `log` in the `ilias.ini.php` is changing
  anyways, we could merge `path` and `file` to just `file`. Maybe
  we could also move the `error` settings to their own settings
  (and move them from `client.ini.php` to `ilias.ini.php`)?
- Abandoning logging to the browser console for specific users would
  also make dependency management much easier. Is that feature used
  much? If not, the logger factories (or a wrapper around them) should
  offer a `registerUser` method. Then `User` can initialize the
  browser log handler, to avoid that additional dependency for `Logging` .

## Todos

- Implement `Config\Ini\Reader`.
- Move and clean up the GUI and Setup classes. Maybe also move the
  GUI to KS.
- When the big component revision PR is merged: Remove the old classes,
  move usages (both legacy and bootstrap) to new classes.
- Unit tests, where do we need null implementations?
