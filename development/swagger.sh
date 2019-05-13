#!/bin/bash

mkdir -p ../application/swagger
php ../vendor/bin/swagger --bootstrap ./swagger-constants.php --output ../swagger/ ./swagger-v1.php ../application/controllers
