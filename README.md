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

API Documentation
-------------

The API is documented through the OpenAPI specification throughout the code base and can be viewed interactively.  
Running the following command and browse the application at `/_docs` to view the interactive API documentation.

```bash
make docs
```

Authentication
--------------

To authenticate to the api, make a `POST` request to the `/auth/login` endpoint with a JSON body containing the username and
password like this:

    {
        "username": "j.doe",
        "password": "SECRET_PASSWORD"
    }

The server will respond with a new bearer token, that has to be sent in the `Authorization`-header in each request,
that requires authentication. 

To log out (destroying the token) send a `DELETE` request to `/auth/logout` like so:

```bash
curl -X DELETE -H "Accept: application/json" -H "Authorization: Bearer YOUR_TOKEN" https://api.example.com/logout  
``` 

### Additional Information

The account management and authentication is build upon [Laravel Jetstream](https://github.com/laravel/jetstream) and
therefore on [Laravel Fortify](https://github.com/laravel/fortify)
and [Laravel Sanctum](https://github.com/laravel/sanctum).  
Please refer to those documentations for additional information.
