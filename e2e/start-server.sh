#!/bin/bash
rm -f public/hot
npm run build
php artisan serve --no-reload
