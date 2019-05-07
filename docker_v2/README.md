
# Docker

## 1. Build and run Docker with Codeigniter DEMO codebase

```
$ docker-compose -f docker-compose.yml -f docker-compose-dev-codeigniter-demo.yml build
$ docker-compose -f docker-compose.yml -f docker-compose-dev-codeigniter-demo.yml up
```

## 2. Build and run Docker with current Playbasis API codebase

```
$ docker-compose -f docker-compose.yml -f docker-compose-dev-current.yml build
$ docker-compose -f docker-compose.yml -f docker-compose-dev-current.yml up
```

### 2.1 Development config files

Plese check .gitignore file where the following app config files are not included in current codebase

```
<root>/application/config/database.php
<root>/application/config/config.php
<root>/application/config/mongodb.php
<root>/application/config/playbasis.php
```

With `<root>/application/config/mongodb.php` You can choose to copy the file from

```
<root>/docker_v2/main/php/codeigniter/application/config/mongodb.php
```

The following three files can be copied from `<root>/application/config/{database,config,playbasis}-example.php`
```
<root>/application/config/database.php
<root>/application/config/config.php
<root>/application/config/playbasis.php
```


### 3. Pre-configuration

* Added minimal MY_Model as currently using with existing codebase (https://github.com/playbasis/api/blob/master/application/core/MY_Model.php)
* Added Codeigniter's Mongo_db library as currently using with existing codebase (https://github.com/playbasis/api/blob/master/application/libraries/Mongo_db.php)

## 4. Testing

Check php extensions
```
http://localhost/phpinfo.php
```

DEVMODE check - ensure Docker can use old PHP MongoDB driver
```
http://localhost/devmode_check.php
```

App
```
http://localhost
```


## 5. Cleanup Docker instances (optional)

Some case we may need to clean up existing Docker instances.

Running the docker_v2 directory

```
docker-compose stop
docker rm $(docker ps -a -q)
```
