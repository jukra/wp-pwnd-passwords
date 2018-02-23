# WP Pwnd Passwords

Check WordPress user passwords against passwords previously appeared in data breaches.

## Features

- Uses [Pwnd Passwords API](https://haveibeenpwned.com/Passwords) to check the passwords
- Validates passwords on WordPress password reset form
- Validates passwords on WordPress user edit page

## Why?

Even though WordPress offers password strength meter, many users might skip it and use their own passwords. This plugin at least prevents users from using passwords that are already appeared in some data breach. Bots bruteforcing WordPress logins use password lists that contain these passwords, so this plugin helps with users security.

## Screenshots

### Using bad password result
![Pwnd password](/assets/screenshot-1.png)