
# Building and starting docker in foreground

```
docker-compose up --build
```

# Accessing docker instance

```
docker exec -it docker_new_local_ubuntu18_php7_mongo4_1 bash
```

* Use comamnd `docker-compose ps` to check if you have different docker name

# Testing

* Add local domain on your local machine `api.pbapp.net.local` to `127.0.0.1`
* Open http://127.0.0.1:8080/ to check default host of NGINX
* Open http://api.pbapp.net.local:8080/ to check PHP-enabled virtual host of NGINX
* Open http://api.pbapp.net.local:8080/phpinfo.php to check PHP version and extensions



