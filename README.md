# Running the containers

## Update the configuration
You probably don't need to do anything, but there's a `.env` file in `.docker` folder. 
These are the environmental variables attached to docker containers.
Obviously, under normal circumstances this `.env` is omitted from any VCS.


## Build docker containers:
`docker-compose build`

## Run containers:
`docker-compose up -d`

The containers were tested on Ubuntu 19.10 and macOS High Sierra. Should "run on your machine", too.
Composer takes some time to install all the dependencies - be sure to wait until it's done before running the app.

## Update database 
`docker exec php-fpm bin/console d:s:u --force`

# Executing the API

`doggos.pdf` is an example pdf file used for testing.  (Images courtesy of @dog_rates)

The POST attachment endpoint expects raw data sent to it (rather than base64 encoded).

There's a file `scratch.http` - open it in phpStorm, it contains all the API requests for ease of testing.
You can run each individual request