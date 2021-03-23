# Kanban board for Github issues

## TODOS
1. Api response validation (I made sample one for issue label)
2. Value Objects for model fields (I made sample one for Amount)
3. Tests adjusted to refactored code (I wrote tests for not refactored code. They are quite complicated since the code was not really testable. Now every sigle model or factory method can be tested separetly)
4. I didnt touch the view anyhow, since it want specified how it should be improved
5. class Authentication was only adjusted to refactored project structure. If I had more time I would rewrite it to use Guzzle as a HTTP client and samo library to manage session.
6. Some functionalities, should be moved to dedicated classes: e.g. strategies for sorting algorithms, aggregate for progress


## About

This is a simple, read-only, Kanban-board for Github issues.

### Concepts and workflow

* `Queued:` are open issues, in a milestone with no one assigned
* `Active:` are any open issue, in a milestone with someone assigned
   * Active issues can, optionally, be paused by adding any of the configured "pause labels" to the issue
* `Completed:` are any issues in a milestone that is closed

#### Required environment variables

* `GH_CLIENT_ID`
* `GH_CLIENT_SECRET`
* `GH_ACCOUNT`
* `GH_REPOSITORIES`

----

_Originally a "fork" of the [Kanban Board](https://github.com/ellislab/kanban-board) plugin to [ExpressionEngine](https://ellislab.com/expressionengine) then more or less completely rewritten._
