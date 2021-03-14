Themenschädel
==============

This project was inspired by the german podcast called "Sprechstunde", where the podcasters use their "Themenschädel"
(skull of topics) filled with community contributed topics to discuss. The goal is to help manage these topics and
create an archive of all discussed topics in a centralized and easily searchable interface.

How it works
============

- The backend schedules a task to look every hour for new episodes and automatically imports them.
- Registered users can claim a single episode and add a list of discussed topics with their timestamps, so other users
  can search / filter easily for topics, that they are interested in.

Installation
============

For simplicity, we include a `docker-compose.yml` file, so the initial setup to get started is as minimal as possible.

1. Create a file called `.env` and fill in any environment variables needed for this application to run.  
   There is an example file included in this repository called `.env.example`.
2. Run `docker-compose up -d`-command to start the application.

Environment Variables
=====================

- `CLAIMS_MAX_AGE` - Defines the timespan (in minutes), that a claim is active. After that period, it will be released
  again, so that anyone else can claim it. Default is 2 hours.

Interacting with the API
========================

Make sure to store and send the session cookie on each request. This is used to remember the authenricated user across
multiple requests.

Following http headers are required on each request:

- `Accept: application/json`
- `Content-Type: application/json`
- `X-XSRF-TOKEN: TOKEN_HERE` - Only required on non-readonly requests (See [Authentication](#authentication) section on
  how to retrieve this token)

Authentication
--------------

To authenticate to the api, you have to follow some steps:

First Load CSRF-Token - This token has to be provided on any request on any non-readonly requests in the `X-XSRF-TOKEN`
http header. You can get it by making a `GET` repuest to the `/sanctum/csrf-token` endpoint.

After that, you can authenticate your user by making a `POST` request to the `/auth/login` endpoint with a JSON body
containing the email and password like this:

    {
        "email": "user@example.net",
        "password": "SECRET_PASSWORD"
    }

If the user has 2-factor enabled, the login endpoint will respond with: `"two_factor": true`.  
Post the OTP token to `/auth/two-factor-challenge` with a JSON body either containing `"code": "OTP_TOKEN"`
or `"recovery_code": "RECOVERY_CODE"`.  
Only then the session will be authenticated.

### Additional Information

The account management and authentication is build upon [Laravel Jetstream](https://github.com/laravel/jetstream) and
therefore on [Laravel Fortify](https://github.com/laravel/fortify)
and [Laravel Sanctum](https://github.com/laravel/sanctum).  
Please refer to those documentations for additional information.
