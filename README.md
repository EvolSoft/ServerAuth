![start2](https://cloud.githubusercontent.com/assets/10303538/6315586/9463fa5c-ba06-11e4-8f30-ce7d8219c27d.png)

**THE PLUGIN IS NOT READY TO BE USED YET. IT HAS NOT BEEN TESTED CAREFULLY AND IT STILL CONTAINS LOTS OF BUGS! PLEASE WAIT THE OFFICIAL RELEASE ON POGGIT!**

# ServerAuth

[![Join the chat at https://gitter.im/EvolSoft/ServerAuth](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/EvolSoft/ServerAuth?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

An advanced authentication plugin for PocketMine

## Category

PocketMine-MP plugins, PHP Web scripts

## Requirements

[PocketMine-MP](https://github.com/pmmp/PocketMine-MP) API 3.0.0-ALPHA7 - 3.0.0-ALPHA8<br>
PHP >= 5.4.0 *(for ServerAuthAccountManager)*<br>
PHP MySQLi extension<br>

## Overview

**ServerAuth** is the most advanced authentication system for PocketMine-MP.

***This Plugin uses the New API. You can't install it on old versions of PocketMine.***

***To prevent bugs, delete all old plugin data if you are updating ServerAuth.***

***WARNING: If you're updating from old versions of ServerAuth to ServerAuth v2.12 or newer you MAY NEED to delete the current language folder!!!***

***Features:***

- MySQL support
- Multi-language support
- Web API
- Online Account Manager
- IP Sessions
- /register, /unregister, /login, /logout and /changepassword commands

And more...

**What is included?**

In the ZIP file you will find:<br>
*- ServerAuth_v2.13.phar : ServerAuth Plugin + API*<br>
*- ServerAuthAccountManager : An advanced online script to manage ServerAuth accounts*<br>
*- ServerAuthWebAPI : ServerAuth Web API to use on your own web scripts*<br>

**Commands:**

***/serverauth*** *- ServerAuth commands (aliases: [sa, sauth, auth])*<br>
***/register*** *- Allows registering an account (aliases: [reg])*<br>
***/login*** *- Allows logging into an account*<br>
***/changepassword*** *- Allows changing account password (aliases: [ch, chp, chpass])*<br>
***/unregister*** *- Allows unregistering an account*<br>
***/logout*** *- Allows to do the log out*

***To-Do:***

<dd><i>- Bug fix (if bugs will be found)</i></dd>

## Documentation

Documentation available at [ServerAuth Wiki](https://github.com/EvolSoft/ServerAuth/wiki)

## Download

You can download precompiled versions of ServerAuth on [ServerAuth Releases](https://github.com/EvolSoft/ServerAuth/releases) section

## Extensions

[EvolSoft/ChatLogin](https://github.com/EvolSoft/ChatLogin): A ServerAuth extension to do login/register directly on chat

[EvolSoft/InvisibleLogin](https://github.com/EvolSoft/InvisibleLogin): A ServerAuth extension to make players invisible when they are not authenticated

[EvolSoft/EmailConfirm](https://github.com/EvolSoft/EmailConfirm): A ServerAuth extension which implements email confirmation when registering ServerAuth accounts

<dd><i>If you want to submit your own ServerAuth Extension PM us on Twitter <b><a href="https://twitter.com/Flavius12_">@Flavius12_</a> or <a href="https://twitter.com/_EvolSoft">@_EvolSoft</a></b> or ask in <b><a href="https://gitter.im/EvolSoft/ServerAuth">ServerAuth Gitter Channel</a></b></i></dd>

## Contributing

If you want to contribute to this project please follow the [Contribution Guidelines](https://github.com/EvolSoft/ServerAuth/blob/master/CONTRIBUTING.md)
