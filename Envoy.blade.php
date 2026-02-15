@servers(['prolab' => 'sam@192.168.1.100'])

@setup
    $appDir = '/var/www/example.com';
    $branch = 'main';
@endsetup

@story('deploy')
    pull
    symlinks
    permissions
    composer
    frontend
    optimize
    migrate
@endstory

@task('pull')
    echo "Pulling latest from {{ $branch }}..."
    cd {{ $appDir }}
    git fetch origin {{ $branch }}
    git reset --hard origin/{{ $branch }}
@endtask

@task('symlinks')
    echo "Restoring storage symlinks..."
    rm -rf {{ $appDir }}/storage/app/public {{ $appDir }}/storage/app/private
    ln -s /storage/example.com {{ $appDir }}/storage/app/public
    ln -s /data/example.com {{ $appDir }}/storage/app/private
@endtask

@task('permissions')
    echo "Fixing permissions..."
    sudo chown -R sam:www-data {{ $appDir }}/storage {{ $appDir }}/bootstrap/cache
    sudo chmod -R 775 {{ $appDir }}/storage {{ $appDir }}/bootstrap/cache
@endtask

@task('composer')
    echo "Installing composer dependencies..."
    cd {{ $appDir }}
    composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
@endtask

@task('frontend')
    echo "Building frontend assets..."
    export NVM_DIR="$HOME/.nvm"
    [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
    cd {{ $appDir }}
    npm ci
    npm run build
@endtask

@task('optimize')
    echo "Optimizing Laravel..."
    cd {{ $appDir }}
    php artisan optimize:clear
    php artisan optimize
@endtask

@task('migrate')
    echo "Running migrations..."
    cd {{ $appDir }}
    php artisan migrate --force
@endtask


@story('install')
    setup-storage
    symlinks
    permissions
    composer
    frontend
    optimize
    migrate
    storage-link
    status
@endstory

@task('setup-storage')
    echo "Creating storage and data directories..."
    sudo mkdir -p /storage/example.com
    sudo chown -R sam:www-data /storage/example.com
    chmod -R 775 /storage/example.com
    chmod g+s /storage/example.com

    sudo mkdir -p /data/example.com
    sudo chown -R sam:www-data /data/example.com
    chmod -R 775 /data/example.com
    chmod g+s /data/example.com
@endtask

@task('storage-link')
    echo "Creating public storage link..."
    cd {{ $appDir }}
    php artisan storage:link
@endtask

@task('status')
    echo ""
    echo "========================================="
    echo "Status Check"
    echo "========================================="
    echo ""
    echo "--- Symlinks ---"
    ls -la {{ $appDir }}/storage/app/
    echo ""
    echo "--- Storage dirs ---"
    ls -la /storage/example.com/
    ls -la /data/example.com/
    echo ""
    echo "--- Permissions ---"
    ls -la {{ $appDir }}/storage/
    ls -la {{ $appDir }}/bootstrap/
    echo ""
    echo "--- PHP-FPM ---"
    sudo systemctl is-active php8.5-fpm
    echo ""
    echo "--- Nginx ---"
    sudo nginx -t 2>&1
    echo ""
    echo "========================================="
@endtask
