# Banshee using Docker - Apache - PHP 8

[![Software License][ico-license]](LICENSE.md)

This is a simple example for running Bunshee CMS on a docker container with PHP-FPM and Apache.

## Get it up and running

to build the deployment
```
docker-compose build
docker-compose up -d 
```

to enter the containers
```
docker exec -it docker.apache sh
docker exec -it docker.mysql bash
```
see logs 
```
docker-compose logs -f
```

visit urls 
banshee http://127.0.0.1:8080/ 

banshee login http://127.0.0.1:8080/login

phpmyadmin http://127.0.0.1:8082/ 

You can import from phpmyadmin 
the db located in public_html/database/mysql.sql on a new db named developement 
edit banshee.conf 
```
nano app/public_html/settings/banshee.conf
```
change 
```
DB_HOSTNAME = mysql
DB_DATABASE = development
DB_USERNAME = mysql
DB_PASSWORD = mysql
```

# Resources 
banshee
    - https://gitlab.com/hsleisink/banshee

pipecode
    -  https://pipedot.org/source
    -  https://github.com/pipedot/pipecode


### Future implementations 

cleantalk uni
```

    https://github.com/CleanTalk/php-uni
or 
    https://github.com/CleanTalk/php-antispam/tree/master
```
