
# Docker

## Build and run Docker with Codeigniter DEMO codebase

```
$ docker-compose -f docker-compose.yml -f docker-compose-dev-codeigniter-demo.yml build
$ docker-compose -f docker-compose.yml -f docker-compose-dev-codeigniter-demo.yml up
```

## Build and run Docker with current Playbasis API codebase

```
$ docker-compose -f docker-compose.yml -f docker-compose-dev-current.yml build
$ docker-compose -f docker-compose.yml -f docker-compose-dev-current.yml up
```

### Development config files

Plese check .gitignore file where the following app config files are not included in current codebase

```
<root>/application/config/database.php
<root>/application/config/config.php
<root>/application/config/mongodb.php
<root>/application/config/playbasis.php
```

You can choose to copy files from

```
<root>/docker_v2/main/php/codeigniter/application/config/database.php
<root>/docker_v2/main/php/codeigniter/application/config/config.php
<root>/docker_v2/main/php/codeigniter/application/config/mongodb.php
<root>/docker_v2/main/php/codeigniter/application/config/playbasis.php
```


###  Pre-configuration

* Added minimal MY_Model as currently using with existing codebase (https://github.com/playbasis/api/blob/master/application/core/MY_Model.php)
* Added Codeigniter's Mongo_db library as currently using with existing codebase (https://github.com/playbasis/api/blob/master/application/libraries/Mongo_db.php)

## Testing

PHP Version 5.6.40-1+ubuntu16.04.1+deb.sury.org+1
```
http://localhost/phpinfo.php
```

DEVMODE check - Ensure Docker can use old PHP MongoDB driver
```
http://localhost/devmode_check.php
```

DEVMODE check with minimal Codeigniter framework - Ensure Docker can use old PHP MongoDB driver
```
http://localhost
```


## Cleanup Docker instances (optional)

Some case we may need to clean up existing Docker instances.

Running the docker_v2 directory

```
docker-compose stop
docker rm $(docker ps -a -q)
```
