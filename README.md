# weatherAPI

To install, you need php installed on your machine, and an apache server. (This was developed using Xampp)

setup mySQL and run `cities.sql` to setup the Database.

To install dependancies, install composer onto your machine, and run `comsposer install` in the root of the repository.

To run the server, run `php -S localhost:8888 -t rest-api\public\`

To run the api, you will need to create a .env file with your openWeather API key, stored as `OPENWEATHER_APIKEY`