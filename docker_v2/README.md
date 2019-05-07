
# Docker

## Codeigniter DEMO codebase

```
$ docker-compose -f docker-compose.yml -f docker-compose-dev-codeigniter-demo.yml build
$ docker-compose -f docker-compose.yml -f docker-compose-dev-codeigniter-demo.yml up
```

###  Pre-configuration

* Added minimal MY_Model as currently using with existing codebase (https://github.com/playbasis/api/blob/master/application/core/MY_Model.php)
* Reuse Codeigniter's Mongo_db library as currently using with existing codebase (https://github.com/playbasis/api/blob/master/application/libraries/Mongo_db.php)

## Testing

PHP Version 5.6.40-1+ubuntu16.04.1+deb.sury.org+1
```
http://localhost/phpinfo.php
```

DEVMODE check - ensure Docker can use old PHP MongoDB driver
```
http://localhost/devmode_check.php
```

DEVMODE check with minial Codeigniter framework - ensure Docker can use old PHP MongoDB driver
```
http://localhost
```


## Cleanup Docker instances (optional)

Some case we may need to clean up existing Docker instances.

```
docker-compose ps
docker-compose stop
docker rm $(docker ps -a -q)
```
