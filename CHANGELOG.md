# Changelog - Mespronos
## 8.x-2.0-dev, XXXX-XX-XX
  - Big refactoring
  - Sport form - update redirection
  - Leagues - Add « add league » menu link action

## 8.x-1.0-alpha15, XXXX-XX-XX
  - Ranking - add average ranking
  - Allow a group member to hide his profile on the general ranking

## 8.x-1.0-alpha14, 2018-03-14
  - Tokens - Add league logo
  - Group - Allow to delete group
  - Group - add email alert
  - Bet reminder - set up html version
  - Refactoring
  - Change User block
  - Drop support for PHP < 7
  - Change bet calculation method
  - Start domain integration
  - Multiple refactoring

## 8.x-1.0-alpha13, 2017-08-05
  - Remove useless "Welcome block"
  - Replace all calls to db_(), which is deprecated #2878983
  - Replace all usages of deprecated EntityManager #2878982
  - Entity Day - fix form plugin « string » to « string_textfield »
  - Fix cache error on games_marks form submit

## 8.x-1.0-alpha12, 2017-04-16
  - Fix entity deletion form
  - Fix sport creation
  - Fix error on Statistics controller when mespronos_groups is not enabled
  - Administration menu enhancement
  - Refactoring - lint - cleaning
  - Reminder - Send reminder only to those who already bet on the current league
  - Betting form - add last five results for each teams

## 8.x-1.0-alpha11, 2016-07-22
  - Group - add link to leave a group
  - Create a proper Game page
  - Create a proper League page
  - Create a proper Day page
  - Create a timezone issue during import
  - Add tokens for Games, Leagues, Days, Groups
  - Optimize way that rancking are calculated
  - Make use of fontawesome
  - Refactoring code
  - integrate with pathauto #105

## 8.x-1.0-alpha10, 2016-06-05
  - Add "Latest results" block #103
  - Enhance group page #98
  - Allow to edit a group #103
  - Fix a bug on day's details page concerning groups ranking.

## 8.x-1.0-alpha9, 2016-05-31
  - Add tests to group module #89
  - Add redirection when user want to access group he's not in #101
  - Add option to leave a group #95
  - Refactoring points calculation #97
  - User ca now join more than one group #90 #88
  - Allow a group to be hidden

## 8.x-1.0-alpha8, 2016-05-05
  - Group enhancements  (see #84 #80 #77 #79 #81)
  - Refactoring - code cleaning
  - Fix issue on last bet page #83
  - Clean User profile page #82

## 8.x-1.0-alpha7, 2016-04-29
  - Email reminders - fix problem in url when not logged in to avoid 403 (issue #76)
  - Add mespronos_group submodule

## 8.x-1.0-alpha6, 2016-04-25
  - Refactoring ranking management in order to allow groups and domains
  - Email reminders

## 8.x-1.0-alpha5, 2016-04-09
  - Enhance betting form
  - Add ability to remove bets on a given game
  - Add profile page
  - Add Docs folder
  - Add doc about league
  - Allow to archive league from administration dashboard
  - Add palmares to profile page

## 8.x-1.0-alpha4, 2016-03-09
  - Add logo to teams
  - Add logo to leagues
  - New administration dashboard
  - Enhance code

## 8.x-1.0-alpha3, 2016-02-27
  - Add breadcrumbs definition
  - Refactoring Controller
  - Enhance Importer feedback
  - Add option to recount points for a whole league
  - Flush cache on mark set.
  - Remove autofocus on login form

## 8.x-1.0-alpha2, 2016-02-19
  - Create a league details page with ranking
  - Enhance betting form display
  - Enhance table display
  - Create a global ranking block
  - Add licence to composer.json among other metadatas
  - Fix a lot of bugs
  - Tweak a lot of things

## 8.x-1.0-alpha1, 2016-02-14
  - Initial alpha release for open beta on [https://mespronos.net](https://mespronos.net)
