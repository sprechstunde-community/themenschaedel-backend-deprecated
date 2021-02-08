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

