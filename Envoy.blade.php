@servers(['prolab' => 'sam@192.168.1.100'])

@setup
    $repository = 'github:samlovescoding/TODO';
    $directory = '/var/www/example.com';
    $branch = 'main';
@endsetup

@story('deploy')
    pull
    composer
    frontend
    optimize
    migrate
    restart
@endstory

@task('pull')
    echo "Pulling latest from {{ $branch }}..."
    cd {{ $directory }}
    git pull origin {{ $branch }}
@endtask

@task('composer')
    echo "Installing composer dependencies..."
    cd {{ $directory }}
    composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
@endtask

@task('frontend')
    echo "Building frontend assets..."
    cd {{ $directory }}
    npm ci
    npm run build
@endtask

@task('optimize')
    echo "Optimizing Laravel..."
    cd {{ $directory }}
    php artisan optimize:clear
    php artisan optimize
@endtask

@task('migrate')
    echo "Running migrations..."
    cd {{ $directory }}
    php artisan migrate --force
@endtask

@task('restart')
    echo "Restarting services..."
    cd {{ $directory }}
    php artisan queue:restart
    sudo systemctl reload php8.3-fpm
    echo "Deploy complete!"
@endtask

@finished
    echo "Deployment finished successfully.";
@endfinished
