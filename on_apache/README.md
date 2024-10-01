# Banshee using Docker - Apache - PHP 8

[![Software License][ico-license]](LICENSE.md)

This is a simple example for running Bunshee CMS on a docker container with PHP-FPM and Apache.

# intro to  Banshee

Banshee is a PHP website framework, which aims at being secure, fast and easy
to use. It has a Model-View-Controller architecture (XSLT for the views).

Ready to use modules like a forum, photo album, weblog, poll and a guestbook
will save web developers a lot of work when creating a new website. Easy to use
libraries for e-mail, pagination, HTTP requests, database management, images,
cryptography and many more are also included.

Most software that can be used to create a website is either a framework or a
Content Management System (CMS). The disadvantage of a framework is that it
requires quite some time and work to build a website, because it has no
ready-to-use interface. The disadvantage of a CMS is that it requires knowledge
about the CMS (and hacking) to extend its functionality. Banshee has none of
these disadvantages as it is more of a hybrid, a framework with CMS
functionality and ready-to-use modules. That makes Banshee actually a, what we
call, Content Management Framework (CMF). The reason for still describing it as
a framework is because more people are familiar with that term than with CMF.


# Get it up and running

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
to see logs 
```
docker-compose logs -f
or  from outside of the containers
tail -f app/docker/apache/logs/access.log
tail -f app/docker/apache/logs/error.log
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

Steps
1) get rid of captacha 
2) add cleantalk, 
3) next add reactions after posts. 

Add this for spam protection- https://github.com/AndyWendt/spam-canner


cleantalk uni
```
    https://github.com/CleanTalk/php-uni
or 
    https://github.com/CleanTalk/php-antispam/tree/master
```
