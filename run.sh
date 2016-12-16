#!/usr/bin/env bash
npm install
cd public
coffee -mbc static/
cd ..
mkdir -p logs
php -S 0.0.0.0:8080 -t public public/index.php
