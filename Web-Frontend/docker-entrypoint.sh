#!/bin/sh
set -e

echo "==> Waiting for MySQL to be ready..."
# Tunggu MySQL siap menerima koneksi
until php -r "
  try {
    \$host = getenv('DB_HOST') ?: 'mysql-db';
    \$port = getenv('DB_PORT') ?: 3306;
    \$db   = getenv('DB_DATABASE') ?: 'db_auth';
    \$user = getenv('DB_USERNAME') ?: 'root';
    \$pass = getenv('DB_PASSWORD') ?: 'root';
    
    \$conn = new PDO(\"mysql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass);
    echo 'MySQL ready!';
  } catch (PDOException \$e) {
    exit(1);
  }
" 2>/dev/null; do
  echo "==> MySQL not ready yet, retrying in 3 seconds..."
  sleep 3
done

echo "==> Running Laravel migrations..."
php artisan migrate --force

echo "==> Starting PHP-FPM..."
exec php-fpm
